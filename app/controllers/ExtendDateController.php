<?php
require_once '../../config/config.php';

// 1. Read JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 2. Extract fields
$smartlockId = $data['smartlockId'] ?? null;
$name        = $data['name']        ?? 'DefaultName';

// Handle allowedFromDate safely (avoid "N/A" crashing DateTime)
if (!empty($data['allowedFromDate']) && $data['allowedFromDate'] !== 'N/A') {
    try {
        $allowedFromDate = new DateTime($data['allowedFromDate']);
    } catch (Exception $e) {
        $allowedFromDate = new DateTime(); // fallback if parse fails
    }
} else {
    // fallback if not provided or is 'N/A'
    $allowedFromDate = new DateTime();
}

// Same approach for allowedUntilDate:
if (!empty($data['allowedUntilDate']) && $data['allowedUntilDate'] !== 'N/A') {
    try {
        $allowedUntilDate = new DateTime($data['allowedUntilDate']);
    } catch (Exception $e) {
        $allowedUntilDate = new DateTime();
    }
} else {
    $allowedUntilDate = new DateTime();
}

// Additional fields (default if not provided)
$addDays         = $data['addDays']         ?? 3;
$allowedWeekDays = $data['allowedWeekDays'] ?? 127;
$allowedFromTime = $data['allowedFromTime'] ?? 0;
$allowedUntilTime= $data['allowedUntilTime']?? 0;
$enabled         = $data['enabled']         ?? true;
$remoteAllowed   = $data['remoteAllowed']   ?? true;

// 3. Validate the ID
if (!$smartlockId) {
    echo json_encode(['error' => true, 'details' => 'Smartlock ID is missing']);
    exit;
}

// 4. Add days to 'allowedUntilDate'
$newAllowedUntilDate = (clone $allowedUntilDate)->modify('+' . $addDays . ' days');

// 5. Prepare data for your API (Notice the API requires `id` not `smartlockId`)
$requestData = [
    'name'             => $name,
    'allowedFromDate'  => $allowedFromDate->format('Y-m-d\TH:i:s\Z'),
    'allowedUntilDate' => $newAllowedUntilDate->format('Y-m-d\TH:i:s\Z'),
    'allowedWeekDays'  => $allowedWeekDays,
    'allowedFromTime'  => $allowedFromTime,
    'allowedUntilTime' => $allowedUntilTime,
    'enabled'          => $enabled,
    'remoteAllowed'    => $remoteAllowed,
    'id'               => $smartlockId,
];

// 6. Convert to JSON
$jsonData = json_encode($requestData);

// 7. cURL setup
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . API_TOKEN
]);
curl_setopt($ch, CURLOPT_POST, true);
// If your API expects an array of objects, we wrap in [ ... ]
curl_setopt($ch, CURLOPT_POSTFIELDS, '[' . $jsonData . ']');
curl_setopt($ch, CURLOPT_VERBOSE, true);

// 8. Execute cURL
$response = curl_exec($ch);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    echo json_encode(['error' => true, 'details' => $error_msg]);
    curl_close($ch);
    exit;
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 9. For debugging
error_log("API Response: " . $response);
error_log("HTTP Status Code: " . $http_code);

// 10. Check HTTP code
if ($http_code != 200 && $http_code != 204) {
    echo json_encode([
        'error'    => true,
        'details'  => 'Failed to extend the date, API returned HTTP status ' . $http_code,
        'response' => $response
    ]);
    exit;
}

// 11. Return success + new from/until date for updating the table
echo json_encode([
    'error'               => false,
    'details'             => 'Updated successfully',
    'response'            => $response,
    'responseNewFromDate' => $allowedFromDate->format('Y-m-d'),
    'responseNewUntilDate'=> $newAllowedUntilDate->format('Y-m-d')
]);
