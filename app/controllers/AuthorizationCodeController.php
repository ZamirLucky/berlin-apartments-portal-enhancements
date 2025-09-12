<?php

require_once '../../config/config.php';
require_once '../models/AuthorizationModel.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class AuthorizationController
{
    public function sendCode()
    {
        error_log("Debug: Entering sendCode method");

        // Get the raw POST data
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        error_log("Debug: Received POST data - " . print_r($data, true));

        $smartlockId    = $data['smartlockId']    ?? null;
        $userName       = $data['userName']       ?? null;
        $accountUserId  = $data['accountUserId']  ?? null;

        // Fetch accountUserId dynamically if not provided
        if (!$accountUserId || $accountUserId === "null") {
            $accountUserId = $this->fetchAccountUserId($smartlockId);
            error_log("Debug: Fetched accountUserId - " . $accountUserId);
        }

        if (!$smartlockId || !$accountUserId) {
            $this->jsonResponse(['error' => true, 'details' => 'Missing smartlock ID or account user ID']);
            return;
        }

        // Prepare data for authorization request
        $invitationData = [
            'name'                => $userName,
            'allowedFromDate'     => date('c'),
            'allowedUntilDate'    => date('c', strtotime('+1 day')),
            'allowedWeekDays'     => 127,
            'allowedFromTime'     => 0,
            'allowedUntilTime'    => 0,
            'accountUserId'       => (int)$accountUserId,
            'smartlockIds'        => [(int)$smartlockId],
            'remoteAllowed'       => true,
            'smartActionsEnabled' => true,
            'type'                => 0,
        ];

        error_log("Debug: Sending invitation data - " . print_r($invitationData, true));

        $this->sendAuthorizationRequest($invitationData);
    }

    private function fetchAccountUserId($smartlockId)
    {
        if (empty($smartlockId)) {
            error_log("Error: Missing smartlock ID.");
            return null;
        }

        $apiUrl = "https://api.nuki.io/smartlock/auth";
        $options = [
            'http' => [
                'header' => "Authorization: Bearer " . API_TOKEN . "\r\n",
                'method' => 'GET',
            ],
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($apiUrl, false, $context);

        if ($result === FALSE) {
            error_log("Error fetching data from Nuki API for smartlock ID: $smartlockId");
            return null;
        }

        error_log("Debug: Nuki API response - " . $result);

        $data = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error: Failed to decode JSON. Response was: $result");
            return null;
        }

        // Search for the correct entry by smartlockId and retrieve accountUserId
        foreach ($data as $authEntry) {
            if (isset($authEntry['smartlockId']) && $authEntry['smartlockId'] == $smartlockId) {
                if (isset($authEntry['accountUserId'])) {
                    error_log("Debug: Found accountUserId - " . $authEntry['accountUserId'] . " for smartlockId - " . $smartlockId);
                    return $authEntry['accountUserId'];
                } else {
                    error_log("Debug: accountUserId not found in authEntry for smartlock ID: $smartlockId");
                }
            }
        }

        error_log("Debug: No matching entry found for smartlock ID: $smartlockId");
        return null;
    }

    private function sendAuthorizationRequest($invitationData)
    {
        $apiUrl = "https://api.nuki.io/smartlock/auth";

        $ch = curl_init($apiUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . API_TOKEN,
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invitationData));

        $result   = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($result === false || ($httpCode !== 200 && $httpCode !== 204)) {
            $this->jsonResponse(['error' => true, 'details' => 'Error calling Nuki API']);
            return;
        }

        $this->jsonResponse(['error' => false, 'details' => 'Invitation sent successfully']);
    }

    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Instantiate and call the method directly if needed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AuthorizationController();
    $controller->sendCode();
}
