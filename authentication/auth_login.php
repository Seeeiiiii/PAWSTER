<?php
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../controllers/logincontroller.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['logout_btn'])) {
        session_unset();
        session_destroy();

        if (isset($_COOKIE['user_login'])) {
            setcookie("user_login", "", time() - 1, "/");
        }

        session_start();
        $_SESSION['message'] = "Logged out successfully.";
        header('Location: ' . SITE_URL . 'login.php');
        exit();
    }

    $email    = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    if (empty($email) || empty($password)) {
        redirect("All fields are required.", "login.php");
        exit();
    }

    $auth = new LoginController();
    $isLoggedIn = $auth->isuserLogin($email, $password);

    if ($isLoggedIn) {
        redirect("Login successful.", "index.php");
    } else {
        redirect("Invalid email or password.", "login.php");
    }
}