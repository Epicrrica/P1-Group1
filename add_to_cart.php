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
    $stmt = $conn->prepare("SELECT seller_username FROM PRODUCT WHERE product_id = ?");
    if ($stmt !== false) {
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        if ($product && ($product['seller_username'] ?? '') !== $_SESSION['logged_in_user']) {
            add_to_cart($productId);
        }
    }
}

$conn->close();
header("Location: " . $redirect);
exit();
?>
