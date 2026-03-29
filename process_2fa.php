<?php
require_once __DIR__ . '/inc/security.inc.php';
security_bootstrap_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_reject();
    $entered_otp = $_POST['otp'];
    
    $otpExpiresAt = (int) ($_SESSION['otp_expires_at'] ?? 0);
    if ($otpExpiresAt > 0 && time() > $otpExpiresAt) {
        echo "<h1>Your verification code has expired. Please log in again.</h1>";
        echo "<a href='login.php'>Back to Login</a>";
        exit();
    }

    if ($entered_otp == $_SESSION['otp']) {
        
        $loggedInUser = $_SESSION['temp_user'];
        $role = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'user';

        unset($_SESSION['temp_user']);
        unset($_SESSION['temp_contact']);
        unset($_SESSION['two_fa_method']);
        unset($_SESSION['otp']);
        unset($_SESSION['otp_expires_at']);
        unset($_SESSION['user_type']);

        security_finalize_login($loggedInUser, $role);

        if ($role === 'moderator') {
            header("Location: moderator_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
        
    } else {
        echo "<h1>Incorrect Code. Please go back and try again.</h1>";
        echo "<a href='verify_2fa.php'>Go Back</a>";
    }
}
?>
