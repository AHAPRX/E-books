<?php
session_start();

if (!isset($_POST['otp'])) {
    exit("No OTP submitted.");
}

$enteredOtp = trim($_POST['otp']);
$storedOtp  = $_SESSION['otp'] ?? null;

if ($enteredOtp === (string)$storedOtp) {
    // OTP is correct — proceed to reset password
    $_SESSION['otp_verified'] = true;
    $_SESSION['user_email'] = $_SESSION['email'] ?? '';
    header("Location: reset-password.php");
    exit;
}

exit("Invalid OTP!");
?>
