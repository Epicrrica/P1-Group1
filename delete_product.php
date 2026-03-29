<?php
include "inc/db.inc.php";
include "inc/product_images.inc.php";
include "inc/marketplace.inc.php";

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($productId <= 0) {
    die('Invalid product ID.');
}

$stmt = $conn->prepare(
    "SELECT product_id, product_name, category, price
     FROM PRODUCT
     WHERE product_id = ?"
);

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

if (!can_delete_product($product)) {
    $conn->close();
    header("Location: product_detail.php?id={$productId}");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_reject();
    delete_all_product_images($conn, $productId, __DIR__);

    $deleteStmt = $conn->prepare("DELETE FROM PRODUCT WHERE product_id = ?");

    if ($deleteStmt === false) {
        die('Delete preparation failed: ' . $conn->error);
    }

    $deleteStmt->bind_param("i", $productId);
    $deleteStmt->execute();
    $deleteStmt->close();

    header("Location: product_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Product</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
    <?php include "inc/navbar.inc.php"; ?>

    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h1 class="h3 mb-3">Delete Product</h1>
                            <p class="text-muted">
                                Are you sure you want to delete <strong><?= htmlspecialchars($product['product_name']) ?></strong>?
                            </p>
                            <ul class="list-group mb-4">
                                <li class="list-group-item"><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></li>
                                <li class="list-group-item"><strong>Price:</strong> $<?= number_format((float) $product['price'], 2) ?></li>
                            </ul>
                            <form method="post" class="d-flex gap-3">
                                <?= csrf_input() ?>
                                <button type="submit" class="btn btn-danger">Delete Product</button>
                                <a href="product_detail.php?id=<?= $productId ?>" class="btn btn-outline-secondary">Cancel</a>
                            </form>
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
<?php $conn->close(); ?>
