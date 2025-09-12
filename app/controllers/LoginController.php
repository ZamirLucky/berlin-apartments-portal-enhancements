<?php
require_once '../models/LoginModel.php';

class LoginController
{
    private $model;

    public function __construct()
    {
        $this->model = new LoginModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Authenticate the user
    public function authenticate($email, $password)
    {
        $user = $this->model->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_email'] = $email;
            return true;
        }
        return false;
    }

    // Check if the user is logged in
    public function isLoggedIn()
    {
        return isset($_SESSION['user_email']);
    }

    // Logout the user
    public function logout()
    {
        session_destroy();
    }
}
