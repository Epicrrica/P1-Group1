<?php
include "inc/db.inc.php";
include "inc/marketplace.inc.php";
include "inc/stripe.inc.php";

if (!isset($_SESSION['logged_in_user'])) {
    header("Location: login.php");
    exit();
}

$sessionId = trim($_GET['session_id'] ?? '');

if ($sessionId === '') {
    die('Missing Stripe session ID.');
}

try {
    $checkoutSession = stripe_retrieve_checkout_session($sessionId);
} catch (Throwable $e) {
    die('Could not verify Stripe payment: ' . htmlspecialchars($e->getMessage()));
}

$paymentVerified = ($checkoutSession['payment_status'] ?? '') === 'paid';
$metadata = $checkoutSession['metadata'] ?? [];
$buyerUsername = (string) ($metadata['buyer_username'] ?? '');
$productIds = [];

if (!empty($metadata['product_ids'])) {
    $productIds = array_values(array_filter(array_map('intval', explode(',', (string) $metadata['product_ids']))));
} elseif (!empty($metadata['product_id'])) {
    $productIds = [(int) $metadata['product_id']];
}

if ($paymentVerified && !empty($productIds) && $buyerUsername !== '' && !purchase_exists_for_session($conn, $sessionId)) {
    $products = fetch_products_by_ids($conn, $productIds);
    foreach ($products as $index => $product) {
        $sessionReference = $index === 0 ? $sessionId : $sessionId . '#item' . $index;
        create_purchase($conn, $product, $buyerUsername, $sessionReference);
        remove_from_cart((int) ($product['product_id'] ?? 0));
    }
}

if ($paymentVerified) {
    foreach ($productIds as $id) {
        if (cart_contains((int) $id)) {
            remove_from_cart((int) $id);
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "inc/navbar.inc.php"; ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5 text-center">
                        <h1 class="h2 mb-3">Payment Completed</h1>
                        <p class="text-muted mb-4">
                            <?php if ($paymentVerified): ?>
                                Your Stripe payment was verified and your purchase has been recorded.
                            <?php else: ?>
                                We could not confirm a successful payment yet.
                            <?php endif; ?>
                        </p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="purchase_history.php" class="btn btn-primary">View Purchase History</a>
                            <a href="product_list.php" class="btn btn-outline-secondary">Back to Products</a>
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
