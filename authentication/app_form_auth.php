<?php
include_once __DIR__ . '/../config/app.php';
include_once __DIR__ . '/../controllers/app_form_controller.php';


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['application_btn'])) {

    $business_name    = mysqli_real_escape_string($db->conn, trim($_POST['business_name'] ?? ""));

    $country_code     = mysqli_real_escape_string($db->conn, trim($_POST['country_code'] ?? "+63"));
    $raw_contact      = mysqli_real_escape_string($db->conn, trim($_POST['contact_number'] ?? ""));
    $contact_num      = $country_code . $raw_contact;

    $dti_reg          = mysqli_real_escape_string($db->conn, trim($_POST['dti_reg'] ?? ""));
    $bir_reg          = mysqli_real_escape_string($db->conn, trim($_POST['bir_reg'] ?? ""));
    $address          = mysqli_real_escape_string($db->conn, trim($_POST['address'] ?? ""));
    $business_permit  = $_FILES['business_permit']['name'] ?? "";
    $valid_id         = $_FILES['valid_id']['name'] ?? "";

    $confirm1 = isset($_POST['confirm1']) ? $_POST['confirm1'] : '';
    $confirm2 = isset($_POST['confirm2']) ? $_POST['confirm2'] : '';

    $userid = $_SESSION['auth_user']['userid'] ?? null;

    $controller = new ApplicationFormController();

    $error = null;

    if (empty($userid)) {
        header('Location: ' . SITE_URL . 'login.php');
        exit();
    } elseif (empty($business_name))        $error = 'Please enter your business name.';
    elseif (empty($raw_contact))      $error = 'Please enter your contact number.';
    elseif (empty($dti_reg))          $error = 'Please enter your DTI registration number.';
    elseif (empty($bir_reg))          $error = 'Please enter your BIR registration number.';
    elseif (empty($address))          $error = 'Please enter your address.';
    elseif (empty($business_permit))  $error = 'Please upload your business permit.';
    elseif (empty($valid_id))         $error = 'Please upload a valid ID.';
    elseif (empty($confirm1) || empty($confirm2))
        $error = 'You must check both confirmation boxes to submit your application.';
    elseif (!preg_match('/^\d{10}$/', $raw_contact))
        $error = 'Contact number must be exactly 10 digits after the country code (e.g., 9171234567).';
    elseif (!preg_match('/^\d{7}$/', $dti_reg))
        $error = 'DTI registration number must be exactly 7 digits.';
    elseif (!preg_match('/^\d{9}$/', $bir_reg))
        $error = 'BIR registration number must be exactly 9 digits.';

    if ($error !== null) {
        echo "<script>alert('" . addslashes($error) . "'); window.history.back();</script>";
        exit();
    } else {
        $success = $controller->submitApplication(
            $business_name, $contact_num, $dti_reg, $bir_reg, $address, $business_permit, $valid_id, $userid
        );

        if ($success) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['is_seller_applicant'] = true;

            echo "<script>
                alert('Application submitted successfully! Our team will review it within 2\u20133 business days.');
                window.location.href = '/PAWSTER/sellerprofile.php';
            </script>";
            exit();
        } else {
            echo "<script>alert('Something went wrong. Please try again.'); window.history.back();</script>";
            exit();
        }
    }
}