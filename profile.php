<?php
session_start();
if (!isset($_SESSION['logged_in_user'])) {
    header("Location: login.php");
    exit();
}

include "inc/db.inc.php";

$username = $_SESSION['logged_in_user'];

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM USER WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fallback for Moderator Account viewing profile
if (!$user && $_SESSION['user_role'] === 'moderator') {
    $user = [
        'username' => 'Admin Mod',
        'user_email' => 'moderator@gce.com',
        'user_phone_no' => 'N/A',
        'user_bio' => 'Platform Moderator Account.',
        'user_profile_img' => null
    ];
}

// Convert BLOB image to base64, or use a default placeholder
$profile_img = !empty($user['user_profile_img']) 
    ? 'data:image/jpeg;base64,' . base64_encode($user['user_profile_img']) 
    : 'https://via.placeholder.com/150';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - GCE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php include "inc/navbar.inc.php"; ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="bg-primary" style="height: 120px;"></div>
                    <div class="card-body px-5 pb-5 position-relative">
                        
                        <div class="text-center" style="margin-top: -65px;">
                            <img src="<?= $profile_img ?>" alt="Profile Picture" class="rounded-circle border border-4 border-white bg-white" style="width: 130px; height: 130px; object-fit: cover; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        </div>
                        
                        <div class="text-center mt-3 mb-5">
                            <h2 class="fw-bold mb-0"><?= htmlspecialchars($user['username'] ?? '') ?></h2>
                            <p class="text-muted"><?= htmlspecialchars($user['user_email'] ?? '') ?></p>
                            <a href="edit_profile.php" class="btn btn-outline-primary btn-sm px-4 rounded-pill">Edit Profile</a>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-muted fw-bold mb-1">Phone Number</h6>
                                <p class="fs-5"><?= htmlspecialchars($user['user_phone_no'] ?? 'Not provided') ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted fw-bold mb-1">Account Status</h6>
                                <p class="fs-5 text-success fw-semibold">Active</p>
                            </div>
                            <div class="col-12">
                                <h6 class="text-muted fw-bold mb-2">About Me</h6>
                                <div class="p-3 bg-light rounded text-muted">
                                    <?= nl2br(htmlspecialchars($user['user_bio'] ?? 'No bio provided.')) ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>