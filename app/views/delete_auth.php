<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../controllers/AccountController.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['smartlockId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Incomplete data.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['smartlockId'])) {
    $controller = new AccountController();

    // Execute the deletion
    $response = $controller->deleteSmartLockAuthorization($data['smartlockId']);

    // If the response is empty, force an error
    if (!$response) {
        echo json_encode(["success" => false, "error" => "Empty response from deleteSmartLockAuthorization"]);
        exit;
    }

    echo json_encode(["success" => !isset($response['error']), "error" => $response['error'] ?? null]);
} else {
    echo json_encode(["success" => false, "error" => "Invalid request."]);
}
