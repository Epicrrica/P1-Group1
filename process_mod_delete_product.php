<?php
include "inc/db.inc.php";
include "inc/product_images.inc.php"; 


if (!isset($_SESSION['logged_in_user']) || $_SESSION['user_role'] !== 'moderator') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    verify_csrf_or_reject();
    $product_id_to_delete = $_POST['product_id'];

    delete_all_product_images($conn, $product_id_to_delete, __DIR__);

    $delete_stmt = $conn->prepare("DELETE FROM PRODUCT WHERE product_id = ?");
    $delete_stmt->bind_param("i", $product_id_to_delete);

    if ($delete_stmt->execute()) {
        header("Location: moderator_dashboard.php?success=product_deleted");
    } else {
        echo "Error deleting product: " . $conn->error;
    }
    $delete_stmt->close();
}
$conn->close();
?>
