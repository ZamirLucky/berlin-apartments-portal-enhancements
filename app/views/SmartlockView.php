<?php
// app/views/SmartlockView.php

require_once '../controllers/SmartlockController.php';
require_once '../../config/config.php';
require_once '../controllers/LoginController.php';

// Check session
$loginController = new LoginController();
if (!$loginController->isLoggedIn()) {
    header('Location: LoginView.php');
    exit();
}

// Fetch data
$controller      = new SmartlockController();
$smartlockJson   = $controller->fetchSmartlockDataAsJson();
$smartlockArray  = json_decode($smartlockJson, true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Smartlock Information</title>

    <!-- Bootstrap CSS (5.3.x) -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/css/styles.css">

    <!-- FontAwesome (optional) -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Matomo (optional) -->
    <script>
        var _paq = window._paq = window._paq || [];
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u = "https://stat.msmhost.de/";
            _paq.push(['setTrackerUrl', u + 'matomo.php']);
            _paq.push(['setSiteId', '12']);
            var d = document,
                g = d.createElement('script'),
                s = d.getElementsByTagName('script')[0];
            g.async = true;
            g.src = u + 'matomo.js';
            s.parentNode.insertBefore(g, s);
        })();
    </script>
    <!-- End Matomo Code -->
</head>

<body>
    <?php include '../views/nav.php'; ?>

    <div class="container mt-5">
        <h2>Smartlock Accounts Information</h2>

        <div class="row mb-4">
            <div class="col-md-6">
                <!-- Search -->
                <div class="input-group">
                    <input
                        type="text"
                        id="searchInput"
                        class="form-control"
                        placeholder="Search by Device or User Name">
                    <button
                        id="searchButton"
                        class="btn btn-secondary ms-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <!-- Example link to "EmailSmartlockView.php" -->
                <a
                    href="EmailSmartlockView.php"
                    class="btn btn-secondary ms-2">
                    <i class="fas fa-envelope"></i> Show Email Accounts
                </a>
            </div>
        </div>

        <!-- Main Table -->
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
                    <!-- Filled dynamically by Smartlock.js -->
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div id="paginationControls" class="my-3"></div>
    </div>

    <!-- Pass the PHP array to JS -->
    <script>
        const smartlockData = <?php echo json_encode($smartlockArray); ?>;
    </script>

    <!-- Bootstrap Bundle (includes Popper for tooltips if needed, but we’re not using them) -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


    <!-- Load JS files: updated table logic, then code-sending logic -->
    <script src="../../public/js/Smartlock.js"></script>
    <script src="../../public/js/AuthorizationCode.js"></script>
</body>

</html>