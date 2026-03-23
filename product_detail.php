<?php
include "inc/db.inc.php";
include "inc/product_images.inc.php";

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($productId <= 0) {
    die('Invalid product ID.');
}

$stmt = $conn->prepare(
    "SELECT product_id, product_name, category, price, product_desc
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

if (!$product) {
    die('Product not found.');
}

$productImages = get_product_images($conn, $productId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_name']) ?></title>
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
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h2 mb-1"><?= htmlspecialchars($product['product_name']) ?></h1>
                            <p class="text-muted mb-0">Product details page</p>
                        </div>
                        <a href="product_list.php" class="btn btn-outline-secondary">Back to Products</a>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <?php if (!empty($productImages)): ?>
                                        <div id="productCarousel" class="carousel slide mb-4">
                                            <div class="carousel-inner rounded">
                                                <?php foreach ($productImages as $index => $image): ?>
                                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                                        <img src="<?= htmlspecialchars($image['image_path']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($product['product_name']) ?>" style="height: 420px; object-fit: cover;">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php if (count($productImages) > 1): ?>
                                                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Previous</span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Next</span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-white border rounded d-flex align-items-center justify-content-center mb-4" style="height: 420px;">
                                            <span class="text-muted">No product images uploaded yet.</span>
                                        </div>
                                    <?php endif; ?>
                                    <h2 class="h5">Description</h2>
                                    <p class="text-muted mb-0">
                                        <?= nl2br(htmlspecialchars($product['product_desc'] ?? 'No description provided.')) ?>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3 h-100">
                                        <p class="mb-2"><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
                                        <p class="mb-3"><strong>Price:</strong> $<?= number_format((float) $product['price'], 2) ?></p>
                                        <div class="d-grid gap-2">
                                            <a href="edit_product.php?id=<?= $product['product_id'] ?>" class="btn btn-primary">Edit Product</a>
                                            <a href="delete_product.php?id=<?= $product['product_id'] ?>" class="btn btn-outline-danger">Delete Product</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
<?php
$stmt->close();
$conn->close();
?>
