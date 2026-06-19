<?php
if (isset($_GET['toggle_navbar_mode'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $current = $_SESSION['navbar_mode'] ?? 'user';
    $_SESSION['navbar_mode'] = ($current === 'seller') ? 'user' : 'seller';
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}