<?php
include "inc/db.inc.php";
include "inc/marketplace.inc.php";

if (!isset($_SESSION['logged_in_user'])) {
    header("Location: login.php");
    exit();
}

verify_csrf_or_reject();

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$redirect = $_POST['redirect'] ?? 'product_list.php';

if ($productId > 0) {
    toggle_product_favorite($conn, $productId, $_SESSION['logged_in_user']);
}

$conn->close();
header("Location: " . $redirect);
exit();
?>
