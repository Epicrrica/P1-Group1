<?php
include "inc/db.inc.php";
include "inc/marketplace.inc.php";

if (!isset($_SESSION['logged_in_user'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['logged_in_user'];
$purchases = get_user_purchases($conn, $username);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "inc/navbar.inc.php"; ?>

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">Purchase History</h1>
                <p class="text-muted mb-0">Track the products you have purchased.</p>
            </div>
            <a href="product_list.php" class="btn btn-outline-secondary">Back to Products</a>
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'purchased'): ?>
            <div class="alert alert-success">Purchase recorded successfully.</div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <?php if (!empty($purchases)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price Paid</th>
                                    <th>Purchased At</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($purchases as $purchase): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($purchase['product_name']) ?></td>
                                        <td><?= htmlspecialchars($purchase['category']) ?></td>
                                        <td>$<?= number_format((float) $purchase['price_paid'], 2) ?></td>
                                        <td><?= htmlspecialchars($purchase['purchased_at']) ?></td>
                                        <td><a href="product_detail.php?id=<?= (int) $purchase['product_id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">You do not have any recorded purchases yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
