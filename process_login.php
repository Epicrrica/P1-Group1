<?php
session_start();

// 1. Connect local PHP directly to the Live Google Cloud Database
$servername = "35.212.172.254";       
$username = "group_login";            
$password = "group_project123";       // <-- CHANGE THIS TO THE REAL PASSWORD
$dbname = "project_information_db";   

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Process the login attempt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $login_user = $_POST['username'];
    $login_pwd  = $_POST['password'];
    
    // If the user didn't select a method (fallback to email just in case)
    $two_fa_method = isset($_POST['two_fa_method']) ? $_POST['two_fa_method'] : 'email';

    // 3. Find the user in the database
    $stmt = $conn->prepare("SELECT * FROM USER WHERE username = ?");
    $stmt->bind_param("s", $login_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 4. Verify the hashed password
        if (password_verify($login_pwd, $row['user_password_hash'])) {
            
            // Generate the 6-digit OTP
            $otp = rand(100000, 999999);
            
            // Store details in the session for the verification page
            $_SESSION['temp_user'] = $row['username'];
            $_SESSION['otp'] = $otp;
            $_SESSION['two_fa_method'] = $two_fa_method;
            
            // 5. Route the 2FA based on user choice
            if ($two_fa_method === 'sms') {
                $_SESSION['temp_contact'] = $row['user_phone_no'];
                // Note: Real SMS requires a paid API (e.g., Twilio). 
                // The OTP will still display in the yellow testing box on the next page.
            } else {
                $_SESSION['temp_contact'] = $row['user_email'];
                // Send the Email
                $to = $row['user_email'];
                $subject = "Game Console Exchange Login Code";
                $message = "Your 6-digit login code is: " . $otp;
                $headers = "From: noreply@gameconsoleexchange.com";
                @mail($to, $subject, $message, $headers);
            }
            
            // Send user to the verification typing screen
            header("Location: verify_2fa.php");
            exit();
            
        } else {
            echo "<h1 style='text-align:center; margin-top:50px;'>Invalid username or password.</h1>";
            echo "<div style='text-align:center;'><a href='login.php'>Click here to try again</a></div>";
        }
    } else {
        echo "<h1 style='text-align:center; margin-top:50px;'>Invalid username or password.</h1>";
        echo "<div style='text-align:center;'><a href='login.php'>Click here to try again</a></div>";
    }
    
    $stmt->close();
}

$conn->close();
?>