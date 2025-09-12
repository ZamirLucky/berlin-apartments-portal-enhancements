<?php
// Enable error reporting for debugging (remove these lines in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();
session_start();

require_once '../controllers/BatteryController.php';
require_once '../controllers/LoginController.php';

// Check if user is logged in; if not, redirect to login page
$loginController = new LoginController();
if (!$loginController->isLoggedIn()) {
    header('Location: LoginView.php');
    exit();
}

$batteryController = new BatteryController();
$smartlocks = $batteryController->getSortedSmartlockData();

// Get search term from query parameters
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 25;
$start = ($page - 1) * $perPage;

// Filter smartlocks if search term is provided
if (!empty($searchTerm)) {
    $searchTermLower = strtolower($searchTerm);
    $smartlocks = array_filter($smartlocks, function ($smartlock) use ($searchTermLower) {
        $nameMatch = strpos(strtolower($smartlock['name']), $searchTermLower) !== false;
        $batteryStatus = (isset($smartlock['state']['batteryCritical']) && $smartlock['state']['batteryCritical']) ? 'critical' : 'normal';
        $batteryMatch = strpos($batteryStatus, $searchTermLower) !== false;
        $batteryTypeMapping = [
            0 => 'alkali',
            1 => 'accumulator',
            2 => 'lithium'
        ];
        $batteryType = 'not available';
        if (isset($smartlock['advancedConfig']['batteryType'])) {
            $bt = $smartlock['advancedConfig']['batteryType'];
            $batteryType = isset($batteryTypeMapping[$bt]) ? $batteryTypeMapping[$bt] : 'unknown';
        }
        $typeMatch = strpos($batteryType, $searchTermLower) !== false;
        return $nameMatch || $batteryMatch || $typeMatch;
    });
}

$totalSmartlocks = count($smartlocks);
$smartlocksPage = array_slice($smartlocks, $start, $perPage);

// Define mapping for battery type values (for display)
$batteryTypeMapping = [
    0 => 'Alkali',
    1 => 'Accumulator',
    2 => 'Lithium'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Battery Status of Nuki Devices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../views/nav.php'; ?>
    <div class="container mt-5">
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header text-white">
                <h2 class="card-title mb-0">Battery Status of Nuki Devices</h2>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <form method="get" action="" class="d-flex">
                            <input type="text" name="search" id="searchInput" class="form-control"
                                   placeholder="Search by Name, Battery Status, or Battery Type"
                                   value="<?= htmlspecialchars($searchTerm) ?>">
                            <button type="submit" id="searchButton" class="btn btn-primary ms-2">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </form>
                    </div>
                </div>
                <?php if (isset($smartlocks['error'])): ?>
                    <div class="alert alert-danger text-center">
                        <?= htmlspecialchars($smartlocks['error']); ?>
                    </div>
                <?php else: ?>
                    <?php if (count($smartlocksPage) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Battery Status</th>
                                        <th>Battery Charge (%)</th>
                                        <th>Battery Type</th>
                                    </tr>
                                </thead>
                                <tbody id="smartlockTableBody">
                                    <?php foreach ($smartlocksPage as $smartlock): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($smartlock['name']) ?></td>
                                            <td>
                                                <?php if (isset($smartlock['state']['batteryCritical']) && $smartlock['state']['batteryCritical']): ?>
                                                    <span class="badge bg-danger">Critical</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Normal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= isset($smartlock['state']['batteryCharge']) 
                                                    ? htmlspecialchars($smartlock['state']['batteryCharge']) . '%' 
                                                    : 'Not available'; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if (isset($smartlock['advancedConfig']['batteryType'])) {
                                                    $bt = $smartlock['advancedConfig']['batteryType'];
                                                    echo isset($batteryTypeMapping[$bt]) ? $batteryTypeMapping[$bt] : 'Unknown';
                                                } else {
                                                    echo 'Not available';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination Controls (Optional) -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link btn btn-primary me-2"
                                           href="?page=<?= $page - 1 ?>&search=<?= urlencode($searchTerm) ?>"
                                           aria-label="Previous">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($start + $perPage < $totalSmartlocks): ?>
                                    <li class="page-item">
                                        <a class="page-link btn btn-primary"
                                           href="?page=<?= $page + 1 ?>&search=<?= urlencode($searchTerm) ?>"
                                           aria-label="Next">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            No smartlocks found.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../../public/js/battery.js"></script>
</body>
</html>
