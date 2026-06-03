<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_DATABASE', 'pawster');
define('SITE_URL', 'http://localhost/PAWSTER/');

include_once __DIR__ . '/databaseconnection.php';
$db = new DatabaseConnection();

if (($_SESSION['authenticated'] ?? false) === true) {
    if (!isset($_COOKIE['user_login'])) {
        unset($_SESSION['authenticated']);
        unset($_SESSION['auth_user']);
        header('Location: ' . SITE_URL . 'login.php');
        exit();
    }
}

function redirect(string $message = "", string $location = ""): void
{
    if (!session_id()) {
        session_start();
    }
    if ($message !== "") {
        $_SESSION['message'] = $message;
    }

    if (preg_match('#^https?://#i', $location)) {
        header('Location: ' . $location);
    } else {
        header('Location: ' . SITE_URL . ltrim($location, '/'));
    }
    exit();
}