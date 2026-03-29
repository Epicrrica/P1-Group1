<?php
include "inc/db.inc.php";
include "inc/product_images.inc.php";
include "inc/marketplace.inc.php";

if (!isset($_SESSION['logged_in_user'])) {
    header("Location: login.php");
    exit();
}

$cartItems = fetch_products_by_ids($conn, cart_item_ids());
$total = 0.0;
foreach ($cartItems as $item) {
    $total += (float) ($item['price'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "inc/navbar.inc.php"; ?>

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">My Cart</h1>
                <p class="text-muted mb-0">Review your selected products before checkout.</p>
            </div>
            <a href="product_list.php" class="btn btn-outline-secondary">Continue Shopping</a>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info">Your cart is empty.</div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="vstack gap-3">
                        <?php foreach ($cartItems as $item): ?>
                            <?php $image = get_primary_product_image($conn, (int) $item['product_id']); ?>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-3">
                                            <?php if ($image): ?>
                                                <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="img-fluid rounded" style="height: 140px; width: 100%; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="border rounded d-flex align-items-center justify-content-center bg-white text-muted" style="height: 140px;">No image</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <h2 class="h5 mb-1"><?= htmlspecialchars($item['product_name']) ?></h2>
                                            <p class="text-muted mb-1"><?= htmlspecialchars($item['category']) ?></p>
                                            <p class="mb-0 small text-muted">Seller: <?= htmlspecialchars($item['seller_username'] ?? 'Unknown') ?></p>
                                        </div>
                                        <div class="col-md-3 text-md-end">
                                            <div class="fw-bold fs-5 mb-3 text-success">$<?= number_format((float) $item['price'], 2) ?></div>
                                            <div class="d-flex flex-column gap-2">
                                                <a href="product_detail.php?id=<?= (int) $item['product_id'] ?>" class="btn btn-outline-primary btn-sm">View</a>
                                                <form method="post" action="remove_from_cart.php">
                                                    <?= csrf_input() ?>
                                                    <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">Remove</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3">Order Summary</h2>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Items</span>
                                <span><?= count($cartItems) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Total</span>
                                <strong>$<?= number_format($total, 2) ?></strong>
                            </div>
                            <form method="post" action="purchase_product.php" class="d-grid">
                                <?= csrf_input() ?>
                                <input type="hidden" name="checkout_mode" value="cart">
                                <button type="submit" class="btn btn-success fw-semibold">Checkout with Stripe</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include "inc/footer.inc.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
