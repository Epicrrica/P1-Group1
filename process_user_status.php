<?php
include "inc/db.inc.php";

// CHANGE THIS LINE: use 'user_role' to match process_2fa.php
if (!isset($_SESSION['logged_in_user']) || $_SESSION['user_role'] !== 'moderator') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_reject();
    $target_user_id = (int) ($_POST['user_id'] ?? 0);
    $new_status = $_POST['action'] ?? '';

    $allowed_statuses = ['approved', 'suspended'];
    if ($target_user_id <= 0 || !in_array($new_status, $allowed_statuses, true)) {
        http_response_code(400);
        exit("Invalid moderation request.");
    }

    $update_stmt = $conn->prepare("UPDATE USER SET account_status = ? WHERE user_id = ?");
    $update_stmt->bind_param("si", $new_status, $target_user_id);

    if ($update_stmt->execute()) {
        header("Location: moderator_dashboard.php?success=status_updated");
    } else {
        echo "Error updating status: " . $conn->error;
    }
    $update_stmt->close();
}
$conn->close();
?>
