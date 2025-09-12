<?php
require_once '../controllers/DeviceSmarlockController.php';
require_once '../controllers/LoginController.php';
require_once '../controllers/User.php';
require_once '../../config/config.php';

$loginController = new LoginController();
if (!$loginController->isLoggedIn()) {
    header('Location: LoginView.php');
    exit();
}

$controller = new SmartlockDeviceController();
try {
    $devices = $controller->fetchSmartlockDevice();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Metadata for character encoding and viewport settings -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartlock Devices</title>

    <!-- Bootstrap CSS for styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">

    <!-- Custom stylesheet for additional styles -->
    <link rel="stylesheet" href="../../public/css/styles.css">

    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <!-- Include the navigation bar -->
    <?php include '../views/nav.php'; ?>

    <!-- Main container -->
    <div class="container mt-5">
        <div class="card shadow-sm border-0">
            <!-- Card header with custom style and title -->
            <div class="card-header text-white custom-table-header">
                <h2 class="card-title mb-0">Smartlock Devices</h2>
            </div>
            <div class="card-body">
                <!-- Display an error message if the $error variable is set -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <strong>Error:</strong> <?= htmlspecialchars($error); ?>
                    </div>
                <?php else: ?>
                    <!-- Search and sort input group -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <!-- Search input field -->
                                <input type="text" id="deviceSearch" class="form-control" placeholder="Search Devices">
                                <!-- Search button -->
                                <button class="btn btn-primary" id="searchButton" type="button">Search</button>
                                <!-- Sort button -->
                                <button class="btn btn-secondary ms-2" id="sortButton" type="button">Sort Alphabetically</button>
                            </div>
                        </div>
                    </div>

                    <!-- Table to display Smartlock devices -->
                    <table class="table table-hover table-bordered table-striped">
                        <thead class="custom-table-header">
                            <tr>
                                <!-- Table headers -->
                                <th>Device Name</th>
                                <th>Smartlock ID</th>
                                <th>Account ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loop through the $devices array and display each device -->
                            <?php foreach ($devices as $device): ?>
                                <tr>
                                    <!-- Display the device name, Smartlock ID, and Account ID -->
                                    <td><?= htmlspecialchars($device->getName()); ?></td>
                                    <td><?= htmlspecialchars($device->getSmartlockId()); ?></td>
                                    <td><?= htmlspecialchars($device->getAccountId()); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- jQuery for handling JavaScript dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

    <!-- Popper.js for handling Bootstrap tooltips and dropdowns -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

    <!-- Bootstrap JavaScript for dynamic components -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>

    <!-- Custom JavaScript file for device functionality -->
    <script src="../../public/js/device.js"></script>
</body>

</html>
