<?php
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../controllers/signupcontroller.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $first_name       = trim($_POST['first_name'] ?? "");
    $last_name        = trim($_POST['last_name'] ?? "");
    $contact_number   = trim($_POST['contact_number'] ?? "");
    $email            = trim($_POST['email'] ?? "");
    $password         = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";


    $full_contact = $contact_number;

    $register = new RegisterController();

    if (empty($first_name) || empty($last_name) || empty($contact_number) || empty($email) || empty($password) || empty($confirm_password)) {
        redirect("All fields are required.", "signup.php");
    } elseif ($password !== $confirm_password) {
        redirect("Passwords do not match.", "signup.php");
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        redirect("Invalid email format.", "signup.php");
    } elseif (strlen($password) < 8) {
        redirect("Password must be at least 8 characters.", "signup.php");
    } else {
        $userExists = $register->isUserExists($email);

        if ($userExists) {
            redirect("Email already exists.", "signup.php");
        } else {
            $success = $register->registration($first_name, $last_name, $full_contact, $email, $password);

            if ($success) {
                redirect("Registered successfully! Please log in.", "login.php");
            } else {
                redirect("Something went wrong. Please try again.", "signup.php");
            }
        }
    }
}