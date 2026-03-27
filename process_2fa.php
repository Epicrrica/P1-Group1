<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'];
    
    if ($entered_otp == $_SESSION['otp']) {
        
        // 1. Success! Log the user in permanently
        $_SESSION['logged_in_user'] = $_SESSION['temp_user'];
        
        // 2. Capture the user type BEFORE we unset the temporary session
        $role = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'user';
        
        // 3. Clean up all temporary variables
        unset($_SESSION['temp_user']);
        unset($_SESSION['temp_contact']);
        unset($_SESSION['two_fa_method']);
        unset($_SESSION['otp']);
        unset($_SESSION['user_type']); // Clear this so it doesn't linger
        
        // 4. Re-set the permanent user type for the rest of the site to use
        $_SESSION['user_role'] = $role;

        // 5. Route the user based on their role
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