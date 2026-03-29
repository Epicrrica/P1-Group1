<?php
include "inc/db.inc.php";

if (!isset($_SESSION['logged_in_user'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['logged_in_user'];
$success = '';
$error = '';

// Process the form update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_reject();
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_no']);
    $bio = trim($_POST['bio']);
    
    $imgData = null;
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
        $uploadError = null;
        $imgData = uploaded_image_contents($_FILES['profile_img'], MAX_PROFILE_IMAGE_BYTES, $uploadError);
        if ($imgData === null) {
            $error = $uploadError ?? 'Profile image upload failed.';
        }
    }

    if ($error === '' && $imgData !== null) {
        // Update with new image
        $update_stmt = $conn->prepare("UPDATE USER SET user_email=?, user_phone_no=?, user_bio=?, user_profile_img=? WHERE username=?");
        $update_stmt->bind_param("sssss", $email, $phone, $bio, $imgData, $username);
    } elseif ($error === '') {
        // Update without changing image
        $update_stmt = $conn->prepare("UPDATE USER SET user_email=?, user_phone_no=?, user_bio=? WHERE username=?");
        $update_stmt->bind_param("ssss", $email, $phone, $bio, $username);
    }

    if ($error === '') {
        if ($update_stmt->execute()) {
            $success = "Profile updated successfully!";
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
        $update_stmt->close();
    }
}

// Fetch current user data to populate the form
$stmt = $conn->prepare("SELECT * FROM USER WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - GCE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php include "inc/navbar.inc.php"; ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0 fw-bold">Edit Profile</h2>
                    <a href="profile.php" class="btn btn-outline-secondary">Back to Profile</a>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
                            <?= csrf_input() ?>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Username (Cannot be changed)</label>
                                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['username'] ?? '') ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label text-muted">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['user_email'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="phone_no" class="form-label text-muted">Phone Number</label>
                                <input type="tel" class="form-control" id="phone_no" name="phone_no" value="<?= htmlspecialchars($user['user_phone_no'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="bio" class="form-label text-muted">Short Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4"><?= htmlspecialchars($user['user_bio'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="profile_img" class="form-label text-muted">Update Profile Image</label>
                                <input class="form-control" type="file" id="profile_img" name="profile_img" accept="image/*">
                                <div class="form-text">Leave this blank to keep your current profile picture.</div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary py-2">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
