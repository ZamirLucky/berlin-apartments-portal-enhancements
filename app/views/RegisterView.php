<?php
// views/RegisterView.php

require_once '../../config/config.php';
require_once '../controllers/RegisterController.php';

// Start session if it has not been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify if the user is authenticated
if (!isset($_SESSION['user_email'])) {
    header('Location: LoginView.php'); // Redirect to the login page if not logged in
    exit(); // Stop further execution of the script
}

// Include the navigation bar
include 'nav.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8"> <!-- Set character encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ensure proper rendering on mobile devices -->
    <title>Register New User</title>
    <!-- Link to Bootstrap CSS for styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <!-- Link to external custom CSS -->
    <link rel="stylesheet" href="../../public/css/styles.css">
</head>

<body>
    <?php include '../views/nav.php'; ?>

    <div class="container mt-5">
        <div class="card shadow-sm border-0">
            <div class="card-header text-white custom-table-header">
                <h2 class="card-title mb-0">Register New User</h2>
            </div>
            <div class="card-body">
                <!-- Registration form -->
                <form action="RegisterHandler.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label> <!-- Label for email input -->
                        <input type="email" class="form-control" id="email" name="email" required> <!-- Email input field -->
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label> <!-- Label for name input -->
                        <input type="text" class="form-control" id="name" name="name" required> <!-- Name input field -->
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label> <!-- Label for password input -->
                        <input type="password" class="form-control" id="password" name="password" required> <!-- Password input field -->
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password</label> <!-- Label for password confirmation input -->
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required> <!-- Confirm Password input field -->
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button> <!-- Submit button to register the user -->
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>

</html>
