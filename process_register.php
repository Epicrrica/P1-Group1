<?php
// 1. Grab the text data submitted from the form
$username = $_POST['username'];
$email = $_POST['email'];
$phone_no = $_POST['phone_no'];
$password = $_POST['password'];
$bio = $_POST['bio'];

// 2. Hash the password for security
$pwd_hashed = password_hash($password, PASSWORD_DEFAULT);

// 3. Handle the Profile Picture (LONGBLOB method)
$imgData = NULL; 
if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
    // Read the file contents as binary data to store directly in the database
    $imgData = file_get_contents($_FILES["profile_picture"]["tmp_name"]);
}

// 4. Connect to the Database
// This uses the secure ini file method taught in your lab [cite: 524, 559]
$config = parse_ini_file('/var/www/private/db-config.ini');

if (!$config) {
    die("Error: Failed to read database config file.");
}

$conn = new mysqli($config['servername'], $config['username'], $config['password'], "project_information_db");

if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

// 5. Insert data using Prepared Statements [cite: 548, 580]
// Notice how the column names here perfectly match your VS Code screenshot
$stmt = $conn->prepare("INSERT INTO USER (username, user_password_hash, user_email, user_phone_no, user_bio, user_profile_img) VALUES (?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssss", $username, $pwd_hashed, $email, $phone_no, $bio, $imgData);

if ($stmt->execute()) {
    echo "<h1>Registration successful!</h1>";
    echo "<a href='login.php'>Click here to login</a>";
} else {
    echo "Error saving to database: " . $stmt->error;
}

// 6. Close the connections [cite: 590, 592]
$stmt->close();
$conn->close();
?>