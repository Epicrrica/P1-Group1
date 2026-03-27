<?php
// Always start the session at the very top of the file!
session_start();

// 1. Connect to the Database
// (If you switched to using Lewis's connection file, you can delete these 4 lines 
// and just write: include 'inc/db.inc.php'; instead)
$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Grab the inputs from your login.php form
    $login_user = $_POST['username'];
    $login_pwd  = $_POST['password'];

    // 3. Search for the user securely using Prepared Statements
    $stmt = $conn->prepare("SELECT * FROM USER WHERE username = ?");
    $stmt->bind_param("s", $login_user);
    $stmt->execute();
    $result = $stmt->get_result();

    // 4. If the username exists in the database...
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 5. Check if the typed password matches the hashed password in the DB
        if (password_verify($login_pwd, $row['user_password_hash'])) {
            
            // ==========================================
            //       TWO-FACTOR AUTHENTICATION (2FA)
            // ==========================================
            
            // A. Generate a secure, random 6-digit number
            $otp = rand(100000, 999999);
            
            // B. Store the user's info and the OTP in the temporary "waiting room" session
            $_SESSION['temp_user'] = $row['username'];
            $_SESSION['temp_email'] = $row['user_email']; // Ensure this matches your DB column!
            $_SESSION['otp'] = $otp;
            
            // C. Draft the Email
            $to = $row['user_email'];
            $subject = "Your Game Console Exchange Login Code";
            $message = "Hello " . $row['username'] . ",\n\nYour 6-digit login code is: " . $otp . "\n\nPlease enter this on the verification page to access your account.";
            $headers = "From: noreply@gameconsoleexchange.com";
            
            // D. Send the Email 
            // (The '@' suppresses warnings on your local laptop if mail isn't configured)
            @mail($to, $subject, $message, $headers);
            
            // E. Redirect them to the OTP typing page
            header("Location: verify_2fa.php");
            exit(); // Always exit after a header redirect!
            
        } else {
            // Wrong Password
            echo "<h1 style='text-align:center; margin-top:50px;'>Invalid username or password.</h1>";
            echo "<div style='text-align:center;'><a href='login.php'>Click here to try again</a></div>";
        }
    } else {
        // Username doesn't exist
        echo "<h1 style='text-align:center; margin-top:50px;'>Invalid username or password.</h1>";
        echo "<div style='text-align:center;'><a href='login.php'>Click here to try again</a></div>";
    }

    $stmt->close();
}

$conn->close();
?>