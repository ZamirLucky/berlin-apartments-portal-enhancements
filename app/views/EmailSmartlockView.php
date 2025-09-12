<?php
// app/views/EmailSmartlockView.php

require_once '../controllers/SmartlockController.php';
require_once '../../config/config.php';
require_once '../controllers/LoginController.php';

// Check session
$loginController = new LoginController();
if (!$loginController->isLoggedIn()) {
    header('Location: LoginView.php');
    exit();
}

$controller = new SmartlockController();
$smartlockJson = $controller->fetchSmartlockDataAsJson();
$smartlockArray = json_decode($smartlockJson, true);

// Filter in PHP: only items that have '@' in accountEmail
$emailData = [];
if (is_array($smartlockArray)) {
    foreach ($smartlockArray as $item) {
        // Check if 'accountEmail' exists and contains '@'
        if (!empty($item['accountEmail']) && strpos($item['accountEmail'], '@') !== false) {
            $emailData[] = $item;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email-Based Smartlocks</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="../../public/css/styles.css">
</head>
<body>
    <?php include '../views/nav.php'; ?>

    <div class="container mt-5">
        <h2>Email-Based Smartlocks</h2>

        <div class="table-responsive mb-5">
            <table class="table table-bordered">
                <thead class="table-success">
                    <tr>
                        <th>Device Name</th>
                        <th>Email (User Name)</th>
                        <th class="text-center">Creation Date</th>
                        <th class="text-center">Allowed From</th>
                        <th class="text-center">Allowed Until</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($emailData)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No smartlocks with email found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($emailData as $row): ?>
                            <tr>
                                <td><?php echo $row['deviceName'] ?? 'N/A'; ?></td>
                                <td><?php echo $row['accountEmail'] ?? 'N/A'; ?></td>
                                <td class="text-center"><?php echo $row['creationDate'] ?? 'N/A'; ?></td>
                                <td class="text-center"><?php echo $row['allowedFromDate'] ?? 'N/A'; ?></td>
                                <td class="text-center"><?php echo $row['allowedUntilDate'] ?? 'N/A'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
