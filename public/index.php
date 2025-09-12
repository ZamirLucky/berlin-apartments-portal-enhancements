<?php
ob_start(); // Start output buffering
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_email'])) {
    header('Location: /app/views/LoginView.php');
    exit();
}

// Redirect to the main view if logged in
header('Location: /app/views/SmartlockView.php');
exit();

ob_end_flush(); // End output buffering

 //...