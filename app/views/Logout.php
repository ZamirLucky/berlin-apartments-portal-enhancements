<?php
require_once '../controllers/LoginController.php';

$loginController = new LoginController();
$loginController->logout(); // Destruye la sesión

header('Location: LoginView.php'); // Redirige al login
exit();
?>

