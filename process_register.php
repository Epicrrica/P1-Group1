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

// 2. Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Grab text inputs
    $user = $_POST['username'];
    $pwd = $_POST['password'];
    $email = $_POST['email'];
    $phone = $_POST['phone_no'];
    $bio = $_POST['bio'];
    
    // Hash the password for security
    $hashed_password = password_hash($pwd, PASSWORD_DEFAULT);

    // 3. Process the Profile Image (LONGBLOB)
    $imgData = "";
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
        $imgData = file_get_contents($_FILES['profile_img']['tmp_name']);
    }

    // 4. Insert into the database using the EXACT SQLTools column names
    $stmt = $conn->prepare("INSERT INTO USER (username, user_password_hash, user_email, user_phone_no, user_bio, user_profile_img) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Bind the parameters (6 strings/blobs = "ssssss")
    $stmt->bind_param("ssssss", $user, $hashed_password, $email, $phone, $bio, $imgData);

    // 5. Execute and respond
    if ($stmt->execute()) {
        echo "<div style='text-align: center; margin-top: 50px; font-family: sans-serif;'>";
        echo "<h1>Registration successful!</h1>";
        echo "<p>Your account has been created in the live database.</p>";
        echo "<a href='login.php' style='padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>Click here to login</a>";
        echo "</div>";
    } else {
        echo "Error saving to database: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>