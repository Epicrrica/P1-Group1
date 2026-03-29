<?php
include "inc/db.inc.php";

// CHANGE THIS LINE: use 'user_role' to match process_2fa.php
if (!isset($_SESSION['logged_in_user']) || $_SESSION['user_role'] !== 'moderator') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_reject();
    $target_user_id = $_POST['user_id'];
    $new_status = $_POST['action']; 

    // Ensure this uses 'user_id' to match your database
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
