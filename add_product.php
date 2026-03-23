<?php
include "inc/db.inc.php";
include "inc/product_images.inc.php";

$errors = [];
$successMessage = '';

$productName = trim($_POST['product_name'] ?? '');
$category = trim($_POST['category'] ?? '');
$price = trim($_POST['price'] ?? '');
$productDesc = trim($_POST['product_desc'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($productName === '') {
        $errors[] = 'Product name is required.';
    }

    if ($category === '') {
        $errors[] = 'Category is required.';
    }

    if ($price === '' || !is_numeric($price) || (float) $price < 0) {
        $errors[] = 'Price must be a valid positive number.';
    }

    if (empty($errors)) {
        $productImg = '';

        $stmt = $conn->prepare(
            "INSERT INTO PRODUCT (product_name, category, price, product_desc, product_img)
             VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt === false) {
            die('Insert preparation failed: ' . $conn->error);
        }

        $priceValue = (float) $price;
        $stmt->bind_param(
            "ssdss",
            $productName,
            $category,
            $priceValue,
            $productDesc,
            $productImg
        );

        $stmt->execute();
        $productId = (int) $stmt->insert_id;
        $stmt->close();

        [, $uploadErrors] = save_uploaded_product_images(
            $conn,
            $productId,
            $_FILES['product_images'] ?? [],
            __DIR__ . '/uploads/products',
            'uploads/products'
        );

        if (!empty($uploadErrors)) {
            $errors = array_merge($errors, $uploadErrors);
        }

        $successMessage = empty($errors)
            ? 'Product added successfully.'
            : 'Product added, but some images could not be uploaded.';

        $productName = '';
        $category = '';
        $price = '';
        $productDesc = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link href="static/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "inc/navbar.inc.php"; ?>

    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h2 mb-1">Add Product</h1>
                            <p class="text-muted mb-0">Create a new product listing with multiple images.</p>
                        </div>
                        <a href="product_list.php" class="btn btn-outline-secondary">Back to Products</a>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($successMessage !== ''): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>

                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <form method="post" enctype="multipart/form-data" class="row g-3">
                                <div class="col-md-6">
                                    <label for="product_name" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="product_name" name="product_name" value="<?= htmlspecialchars($productName) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="category" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="category" name="category" placeholder="Console, Game, Accessory" value="<?= htmlspecialchars($category) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="price" class="form-label">Price</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?= htmlspecialchars($price) ?>" required>
                                </div>
                                <div class="col-12">
                                    <label for="product_desc" class="form-label">Description</label>
                                    <textarea class="form-control" id="product_desc" name="product_desc" rows="5" placeholder="Describe the product and any important notes."><?= htmlspecialchars($productDesc) ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="product_images" class="form-label">Product Images</label>
                                    <input type="file" class="form-control" id="product_images" name="product_images[]" accept=".jpg,.jpeg,.png,.webp" multiple>
                                    <div class="form-text">You can select images multiple times. New selections will be added to the current preview.</div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                        <p id="image-preview-count" class="mb-0 text-muted small">0 images selected</p>
                                        <button type="button" id="clear-image-selection" class="btn btn-outline-secondary btn-sm d-none">
                                            Clear Images
                                        </button>
                                    </div>
                                    <div id="image-preview-empty" class="border rounded bg-light text-muted text-center py-4">
                                        No images selected yet.
                                    </div>
                                    <div id="image-preview-grid" class="row g-3 d-none mt-1"></div>
                                </div>
                                <div class="col-12 d-flex gap-3">
                                    <button type="submit" class="btn btn-primary">Save Product</button>
                                    <a href="product_list.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="static/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>
