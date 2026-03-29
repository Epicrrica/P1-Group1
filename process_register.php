<?php
require_once __DIR__ . '/inc/security.inc.php';
security_bootstrap_session();

// 1. Connect local PHP directly to the Live Google Cloud Database
$servername = "35.212.172.254";
$username = "group_login";
$password = "group_project123";
$dbname = "project_information_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function redirect_back_with_errors(array $errors, array $oldInput): never
{
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_old'] = $oldInput;
    header("Location: register.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_reject();
    $user = trim($_POST['username'] ?? '');
    $pwd = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone_no'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    $oldInput = [
        'username' => $user,
        'email' => $email,
        'phone_no' => $phone,
        'bio' => $bio,
    ];

    $errors = [];

    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $user)) {
        $errors[] = 'Username must be 3 to 30 characters and contain only letters, numbers, or underscores.';
    }

    if (strlen($pwd) < 5) {
        $errors[] = 'Password must be at least 5 characters.';
    }

    if (!preg_match('/[a-zA-Z]/', $pwd) || !preg_match('/\d/', $pwd)) {
        $errors[] = 'Password must contain at least one letter and one number.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9]{8}$/', $phone)) {
        $errors[] = 'Phone number must be exactly 8 digits for Singapore numbers.';
    } else {
        $firstDigit = $phone[0];
        if (!in_array($firstDigit, ['6', '8', '9'], true)) {
            $errors[] = 'Singapore phone numbers must start with 6, 8, or 9.';
        }
    }

    if ($user !== '') {
        $stmt = $conn->prepare("SELECT user_id FROM USER WHERE LOWER(username) = LOWER(?) LIMIT 1");
        if ($stmt !== false) {
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = 'That username is already taken.';
            }
            $stmt->close();
        }
    }

    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("SELECT user_id FROM USER WHERE LOWER(user_email) = LOWER(?) LIMIT 1");
        if ($stmt !== false) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = 'That email address is already registered.';
            }
            $stmt->close();
        }
    }

    if (!empty($errors)) {
        $conn->close();
        redirect_back_with_errors($errors, $oldInput);
    }

    $hashed_password = password_hash($pwd, PASSWORD_DEFAULT);

    $imgData = "";
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
        $uploadError = null;
        $imageContents = uploaded_image_contents($_FILES['profile_img'], MAX_PROFILE_IMAGE_BYTES, $uploadError);
        if ($imageContents === null) {
            $conn->close();
            redirect_back_with_errors([$uploadError ?? 'Profile image upload failed.'], $oldInput);
        }
        $imgData = $imageContents;
    }

    $stmt = $conn->prepare(
        "INSERT INTO USER (username, user_password_hash, user_email, user_phone_no, user_bio, user_profile_img)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    if ($stmt === false) {
        $conn->close();
        redirect_back_with_errors(['Could not prepare registration query.'], $oldInput);
    }

    $stmt->bind_param("ssssss", $user, $hashed_password, $email, $phone, $bio, $imgData);

    if ($stmt->execute()) {
        unset($_SESSION['register_errors'], $_SESSION['register_old']);

        echo "<div style='text-align: center; margin-top: 50px; font-family: sans-serif;'>";
        echo "<h1 style='color: #198754;'>Registration successful!</h1>";
        echo "<p>Your account has been created and is pending moderator approval.</p>";
        echo "<br>";
        echo "<a href='login.php' style='padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>Click here to login</a>";
        echo "</div>";
    } else {
        $errors = ['Could not complete registration: ' . $stmt->error];
        $stmt->close();
        $conn->close();
        redirect_back_with_errors($errors, $oldInput);
    }

    $stmt->close();
}

$conn->close();
?>
