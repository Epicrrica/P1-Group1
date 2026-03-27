<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'];
    
    if ($entered_otp == $_SESSION['otp']) {
        
        // Success! Log the user in
        $_SESSION['logged_in_user'] = $_SESSION['temp_user'];
        
        // Clean up all temporary variables
        unset($_SESSION['temp_user']);
        unset($_SESSION['temp_contact']);
        unset($_SESSION['two_fa_method']);
        unset($_SESSION['otp']);
        
        header("Location: index.php");
        exit();
        
    } else {
        echo "<h1>Incorrect Code. Please go back and try again.</h1>";
        echo "<a href='verify_2fa.php'>Go Back</a>";
    }
}
?>