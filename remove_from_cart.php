<?php
require_once __DIR__ . '/inc/security.inc.php';
security_bootstrap_session();

if (!isset($_SESSION['logged_in_user'])) {
    header("Location: login.php");
    exit();
}

verify_csrf_or_reject();

include "inc/marketplace.inc.php";

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
remove_from_cart($productId);

header("Location: cart.php");
exit();
?>
