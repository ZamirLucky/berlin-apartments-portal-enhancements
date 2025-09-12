<?php
// Prevent output before headers are sent
if (!headers_sent()) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <!-- Logo + Brand Name -->
        <a class="navbar-brand d-flex align-items-center" href="SmartlockView.php">
            <img src="../../public/imagenes/logo.jpg" alt=" Berlin Logo"
                class="rounded-circle" style="height: 50px; width: 50px;">
            <span class="navbar-title ms-2"> Berlin Apartments</span>
        </a>

        <!-- Toggler for mobile view -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav"
            aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <?php if (isset($_SESSION['user_email'])): ?>
                    <li class="nav-item"><a class="nav-link" href="SmartlockView.php">Smartlocks</a></li>
                    <li class="nav-item"><a class="nav-link" href="UserView.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="OneSevenDayAuthView.php">7 Day Access</a></li>
                    <li class="nav-item"><a class="nav-link" href="AccountView.php">Delete Auth</a></li>
                    <li class="nav-item"><a class="nav-link" href="SmartlockDeviceView.php">Devices</a></li>
                    <li class="nav-item"><a class="nav-link" href="BatteryView.php">Battery</a></li>
                    <li class="nav-item"><a class="nav-link" href="Nuki_StateView.php">State</a></li>
                    <li class="nav-item"><a class="nav-link" href="RegisterView.php">Register</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://status.nuki.io/en/" target="_blank">Support</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger text-white ms-lg-3 px-3 py-2" href="Logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white ms-lg-3 px-3 py-2" href="LoginView.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
