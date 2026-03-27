<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'];
    
    // Check if what they typed matches the Session OTP
    if ($entered_otp == $_SESSION['otp']) {
        
        // SUCCESS! Promote them from "temp" to fully logged in
        $_SESSION['logged_in_user'] = $_SESSION['temp_user'];
        
        // Clean up the temporary variables so the OTP can't be reused
        unset($_SESSION['temp_user']);
        unset($_SESSION['temp_email']);
        unset($_SESSION['otp']);
        
        // Send them to the homepage!
        header("Location: index.php");
        exit();
        
    } else {
        echo "<h1>Incorrect Code. Please go back and try again.</h1>";
        echo "<a href='verify_2fa.php'>Go Back</a>";
    }
}
?>