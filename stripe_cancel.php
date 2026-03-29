<?php
$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
$redirect = $productId > 0 ? "product_detail.php?id={$productId}&error=payment_cancelled" : 'product_list.php';
header("Location: " . $redirect);
exit();
?>
