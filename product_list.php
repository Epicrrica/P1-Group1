<?php
include "inc/db.inc.php";
include "inc/product_images.inc.php";

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

$sql = "SELECT product_id, product_name, category, price, product_desc
        FROM PRODUCT
        WHERE 1=1";
$types = '';
$params = [];

if ($search !== '') {
    $sql .= " AND product_name LIKE ?";
    $types .= 's';
    $params[] = '%' . $search . '%';
}

if ($category !== '') {
    $sql .= " AND category = ?";
    $types .= 's';
    $params[] = $category;
}

$sql .= " ORDER BY product_id DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Query preparation failed: ' . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$categoriesResult = $conn->query("SELECT DISTINCT category FROM PRODUCT WHERE category IS NOT NULL AND category <> '' ORDER BY category");
$categories = [];
if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
    <?php include "inc/navbar.inc.php"; ?>

    <main class="py-5">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h2 mb-1">Product Marketplace</h1>
                    <p class="text-muted mb-0">Browse all listed consoles and gaming products.</p>
                </div>
                <a href="add_product.php" class="btn btn-primary">Add Product</a>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input
                                type="text"
                                class="form-control"
                                id="search"
                                name="search"
                                placeholder="Search by product name"
                                value="<?= htmlspecialchars($search) ?>"
                            >
                        </div>
                        <div class="col-md-4">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All categories</option>
                                <?php foreach ($categories as $categoryOption): ?>
                                    <option value="<?= htmlspecialchars($categoryOption) ?>" <?= $category === $categoryOption ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($categoryOption) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <label class="form-label d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-outline-primary">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-4">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                        <h2 class="h5 mb-0"><?= htmlspecialchars($product['product_name']) ?></h2>
                                        <span class="badge text-bg-warning">$<?= number_format((float) $product['price'], 2) ?></span>
                                    </div>
                                    <?php $primaryImage = get_primary_product_image($conn, (int) $product['product_id']); ?>
                                    <?php if ($primaryImage): ?>
                                        <img src="<?= htmlspecialchars($primaryImage) ?>" class="card-img-top mb-3 rounded" alt="<?= htmlspecialchars($product['product_name']) ?>" style="height: 220px; object-fit: cover;">
                                    <?php endif; ?>
                                    <p class="text-muted small mb-2"><?= htmlspecialchars($product['category']) ?></p>
                                    <p class="text-muted mb-3">
                                        <?= nl2br(htmlspecialchars($product['product_desc'] ?? 'No description provided.')) ?>
                                    </p>
                                    <div class="mt-auto d-flex flex-wrap gap-2">
                                        <a href="product_detail.php?id=<?= $product['product_id'] ?>" class="btn btn-outline-primary btn-sm">View</a>
                                        <a href="edit_product.php?id=<?= $product['product_id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                        <a href="delete_product.php?id=<?= $product['product_id'] ?>" class="btn btn-outline-danger btn-sm">Delete</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            No products found yet. Add your first product listing.
                        </div>
                    </div>
                <?php endif; ?>
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
