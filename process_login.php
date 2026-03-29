<?php
require_once __DIR__ . '/inc/security.inc.php';
security_bootstrap_session();

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
    verify_csrf_or_reject();
    
    $login_user = $_POST['username'];
    $login_pwd  = $_POST['password'];
    
    // If the user didn't select a method (fallback to email just in case)
    $two_fa_method = isset($_POST['two_fa_method']) ? $_POST['two_fa_method'] : 'email';

    // 3. First, check if the user is a normal USER
    $stmt = $conn->prepare("SELECT * FROM USER WHERE username = ?");
    $stmt->bind_param("s", $login_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Redirect if suspended
        if ($row['account_status'] === 'suspended') {
            header("Location: suspended.php");
            exit();
        }
        
        // Redirect if pending
        if ($row['account_status'] === 'pending') {
            header("Location: pending.php");
            exit();
        }

        if (password_verify($login_pwd, $row['user_password_hash'])) {
            // Generate the 6-digit OTP for Regular User
            $otp = rand(100000, 999999);
            
            $_SESSION['user_type'] = 'user'; // Flag them as a regular user
            $_SESSION['temp_user'] = $row['username'];
            $_SESSION['otp'] = $otp;
            $_SESSION['two_fa_method'] = $two_fa_method;
            $_SESSION['otp_expires_at'] = time() + 300;
            
            if ($two_fa_method === 'sms') {
                $_SESSION['temp_contact'] = $row['user_phone_no'];
            } else {
                $_SESSION['temp_contact'] = $row['user_email'];
                $to = $row['user_email'];
                $subject = "Game Console Exchange Login Code";
                $message = "Your 6-digit login code is: " . $otp;
                $headers = "From: noreply@gameconsoleexchange.com";
                @mail($to, $subject, $message, $headers);
            }
            
            header("Location: verify_2fa.php");
            exit();
            
        } else {
            echo "<h1 style='text-align:center; margin-top:50px;'>Invalid username or password.</h1>";
            echo "<div style='text-align:center;'><a href='login.php'>Click here to try again</a></div>";
        }
    } else {
        // 4. If not a user, check the MODERATOR table
        $stmt_mod = $conn->prepare("SELECT * FROM MODERATOR WHERE mod_name = ?");
        $stmt_mod->bind_param("s", $login_user);
        $stmt_mod->execute();
        $result_mod = $stmt_mod->get_result();

        if ($result_mod->num_rows > 0) {
            $row_mod = $result_mod->fetch_assoc();
            
            if (password_verify($login_pwd, $row_mod['mod_password_hash'])) {
                // Generate the 6-digit OTP for Moderator
                $otp = rand(100000, 999999);
                
                $_SESSION['user_type'] = 'moderator'; // Flag them as a moderator
                $_SESSION['temp_user'] = $row_mod['mod_name'];
                $_SESSION['otp'] = $otp;
                $_SESSION['two_fa_method'] = $two_fa_method;
                $_SESSION['otp_expires_at'] = time() + 300;
                
                if ($two_fa_method === 'sms') {
                    $_SESSION['temp_contact'] = $row_mod['mod_phone_no'];
                } else {
                    $_SESSION['temp_contact'] = $row_mod['mod_email'];
                    $to = $row_mod['mod_email'];
                    $subject = "Game Console Exchange Mod Login Code";
                    $message = "Your 6-digit login code is: " . $otp;
                    $headers = "From: noreply@gameconsoleexchange.com";
                    @mail($to, $subject, $message, $headers);
                }
                
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
        $stmt_mod->close();
    }
    $stmt->close();
}

$conn->close();
?>
