<?php
include "inc/db.inc.php";
include "inc/marketplace.inc.php";
include "inc/stripe.inc.php";

if (!isset($_SESSION['logged_in_user'])) {
    header("Location: login.php");
    exit();
}

verify_csrf_or_reject();

$currentUser = $_SESSION['logged_in_user'];

if (!stripe_configured()) {
    $conn->close();
    $redirectTarget = isset($_POST['checkout_mode']) && $_POST['checkout_mode'] === 'cart'
        ? 'cart.php?error=stripe_not_configured'
        : 'product_detail.php?id=' . (int) ($_POST['product_id'] ?? 0) . '&error=stripe_not_configured';
    header("Location: " . $redirectTarget);
    exit();
}

try {
    if (isset($_POST['checkout_mode']) && $_POST['checkout_mode'] === 'cart') {
        $cartProducts = fetch_products_by_ids($conn, cart_item_ids());
        $cartProducts = array_values(array_filter($cartProducts, static fn (array $product): bool => ($product['seller_username'] ?? '') !== $currentUser));

        if (empty($cartProducts)) {
            $conn->close();
            header("Location: cart.php?error=empty_cart");
            exit();
        }

        $session = stripe_create_checkout_session_for_products($cartProducts, $currentUser);
    } else {
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

        if ($productId <= 0) {
            die('Invalid product.');
        }

        $stmt = $conn->prepare("SELECT * FROM PRODUCT WHERE product_id = ?");
        if ($stmt === false) {
            die('Query preparation failed: ' . $conn->error);
        }

        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        if (!$product) {
            die('Product not found.');
        }

        if (($product['seller_username'] ?? '') === $currentUser) {
            $conn->close();
            header("Location: product_detail.php?id={$productId}&error=own_product");
            exit();
        }

        $session = stripe_create_checkout_session($product, $currentUser);
    }
} catch (Throwable $e) {
    $conn->close();
    $redirectTarget = isset($_POST['checkout_mode']) && $_POST['checkout_mode'] === 'cart'
        ? 'cart.php?error=stripe_checkout_failed'
        : 'product_detail.php?id=' . (int) ($_POST['product_id'] ?? 0) . '&error=stripe_checkout_failed';
    header("Location: " . $redirectTarget);
    exit();
}

$conn->close();
header("Location: " . $session['url']);
exit();
?>
