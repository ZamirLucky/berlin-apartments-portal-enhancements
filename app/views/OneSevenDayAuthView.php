<?php
// app/views/OneSevenDayAuthView.php

require_once '../controllers/SmartlockController.php';
require_once '../../config/config.php';
require_once '../controllers/LoginController.php';

/**
 * Tries to parse a raw date string in two formats:
 * 1) Y-m-d
 * 2) m/d/Y
 * If both fail, returns false.
 */
function parseFallbackDate($rawDate) {
    // Attempt 'Y-m-d'
    $dt = DateTime::createFromFormat('Y-m-d', $rawDate);
    if ($dt !== false) {
        return $dt;
    }

    // Attempt 'm/d/Y'
    $dt = DateTime::createFromFormat('m/d/Y', $rawDate);
    if ($dt !== false) {
        return $dt;
    }

    // If neither works, return false
    return false;
}

// Check session
$loginController = new LoginController();
if (!$loginController->isLoggedIn()) {
    header('Location: LoginView.php');
    exit();
}

// Fetch the raw data array
$controller = new SmartlockController();
$smartlockJson = $controller->fetchSmartlockDataAsJson();
$smartlockArray = json_decode($smartlockJson, true);

// Filter logic (Option A):
// Only filter by "allowedUntilDate" being between tomorrow and (today + 7 days).
// We remove any check on "allowedFromDate >= today".
$filteredData = [];
if (is_array($smartlockArray)) {
    $today = new DateTime();
    $today->setTime(0, 0, 0);

    // Tomorrow
    $tomorrow = clone $today;
    $tomorrow->modify('+1 day');

    // 7 days from now (end of that day)
    $limitDay = clone $today;
    $limitDay->modify('+7 days')->setTime(23, 59, 59);

    foreach ($smartlockArray as $item) {
        // Ensure both from & until fields are present
        if (!empty($item['allowedFromDate']) && !empty($item['allowedUntilDate'])) {
            $fromObj  = parseFallbackDate($item['allowedFromDate']);
            $untilObj = parseFallbackDate($item['allowedUntilDate']);

            if ($fromObj && $untilObj) {
                // Ignore times (compare only dates)
                $fromObj->setTime(0, 0, 0);
                $untilObj->setTime(0, 0, 0);

                // The key check: allowedUntilDate in [tomorrow, limitDay]
                if ($untilObj >= $tomorrow && $untilObj <= $limitDay) {
                    $filteredData[] = $item;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>1–7 Day Authorization</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/styles.css">
</head>
<body>
    <?php include '../views/nav.php'; ?>

    <div class="container mt-5">
        <h2>Smartlocks - Allowed Until in 1–7 Day Range</h2>

        <div class="row mb-4">
            <div class="col-md-6">
                <!-- Search input (Smartlock.js handles the logic) -->
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by Device or User Name">
                    <button id="searchButton" class="btn btn-secondary ms-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <a href="EmailSmartlockView.php" class="btn btn-secondary ms-2" target="_blank">
                    <i class="fas fa-envelope"></i> Show Email Accounts
                </a>
            </div>
        </div>

        <!-- Table populated by Smartlock.js -->
        <div class="table-responsive mb-5">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Device Name</th>
                        <th>Name</th>
                        <th class="text-center">Creation Date</th>
                        <th class="text-center">Allowed From</th>
                        <th class="text-center">Allowed Until</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="smartlockTableBody">
                    <!-- Filled by Smartlock.js -->
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div id="paginationControls" class="my-3"></div>
    </div>

    <script>
        // Pass the filtered data to JavaScript
        const filteredSmartlockData = <?php echo json_encode($filteredData); ?>;
        window.smartlockData = filteredSmartlockData;
    </script>

    <script src="../../public/js/Smartlock.js"></script>
    <script src="../../public/js/AuthorizationCode.js"></script>
</body>
</html>
