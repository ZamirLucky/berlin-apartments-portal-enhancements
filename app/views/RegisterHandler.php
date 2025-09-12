<?php
// views/RegisterHandler.php

require_once '../controllers/RegisterController.php';

// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Gather form data
        $data = [
            'email'           => $_POST['email']           ?? '',
            'name'            => $_POST['name']            ?? '',
            'password'        => $_POST['password']        ?? '',
            'confirmPassword' => $_POST['confirmPassword'] ?? ''
        ];

        // Instantiate the controller
        $registerController = new RegisterController();

        // Call the register method
        $registerController->register($data);

        // Redirect on success
        header('Location: LoginView.php?success=1');
        exit();
    } catch (Exception $e) {
        // Show error if something went wrong
        echo "Error during registration: " . $e->getMessage();
    }
} else {
    // If not POST, redirect back to the form
    header('Location: RegisterView.php');
    exit();
}
