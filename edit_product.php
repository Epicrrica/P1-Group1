<?php
include "inc/db.inc.php";
include "inc/product_images.inc.php";
include "inc/marketplace.inc.php";

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($productId <= 0) {
    die('Invalid product ID.');
}

$errors = [];
$imageNotice = '';

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
$stmt->close();

if (!$product) {
    die('Product not found.');
}

if (!can_edit_product($product) && current_user_role() !== 'moderator') {
    $conn->close();
    header("Location: product_detail.php?id={$productId}");
    exit();
}

$productName = $product['product_name'];
$category = $product['category'];
$price = $product['price'];
$productDesc = $product['product_desc'];
$productImages = get_product_images($conn, $productId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image_id'])) {
    verify_csrf_or_reject();
    $imageId = (int) $_POST['delete_image_id'];
    if (delete_product_image_by_id($conn, $productId, $imageId, __DIR__)) {
        header("Location: edit_product.php?id=" . $productId);
        exit;
    }
    $errors[] = 'Could not delete the selected image.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_reject();
    $productName = trim($_POST['product_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $productDesc = trim($_POST['product_desc'] ?? '');

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
        $priceValue = (float) $price;

        $updateStmt = $conn->prepare(
            "UPDATE PRODUCT
             SET product_name = ?, category = ?, price = ?, product_desc = ?
             WHERE product_id = ?"
        );

        if ($updateStmt === false) {
            die('Update preparation failed: ' . $conn->error);
        }

        $updateStmt->bind_param("ssdsi", $productName, $category, $priceValue, $productDesc, $productId);
        $updateStmt->execute();
        $updateStmt->close();

        [$savedImages, $uploadErrors] = save_uploaded_product_images(
            $conn,
            $productId,
            $_FILES['product_images'] ?? [],
            __DIR__ . '/uploads/products',
            'uploads/products'
        );

        if (!empty($uploadErrors)) {
            $errors = array_merge($errors, $uploadErrors);
        }

        if (empty($errors)) {
            header("Location: product_detail.php?id=" . $productId);
            exit;
        }

        $imageNotice = 'Product updated, but some images could not be uploaded.';
    }
}

$productImages = get_product_images($conn, $productId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
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
                            <h1 class="h2 mb-1">Edit Product</h1>
                            <p class="text-muted mb-0">Update an existing product listing.</p>
                        </div>
                        <a href="product_detail.php?id=<?= $productId ?>" class="btn btn-outline-secondary">Back to Product</a>
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

                    <?php if ($imageNotice !== ''): ?>
                        <div class="alert alert-warning"><?= htmlspecialchars($imageNotice) ?></div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <form method="post" enctype="multipart/form-data" class="row g-3">
                                <?= csrf_input() ?>
                                <div class="col-md-6">
                                    <label for="product_name" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="product_name" name="product_name" value="<?= htmlspecialchars($productName) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="category" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="category" name="category" value="<?= htmlspecialchars($category) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="price" class="form-label">Price</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?= htmlspecialchars((string) $price) ?>" required>
                                </div>
                                <div class="col-12">
                                    <label for="product_desc" class="form-label">Description</label>
                                    <textarea class="form-control" id="product_desc" name="product_desc" rows="5"><?= htmlspecialchars($productDesc ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="product_images" class="form-label">Add More Images</label>
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
                                        No new images selected yet.
                                    </div>
                                    <div id="image-preview-grid" class="row g-3 d-none mt-1"></div>
                                </div>
                                <div class="col-12 d-flex gap-3">
                                    <button type="submit" class="btn btn-primary">Update Product</button>
                                    <a href="product_detail.php?id=<?= $productId ?>" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>

                            <?php if (!empty($productImages)): ?>
                                <hr class="my-4">
                                <div>
                                    <label class="form-label">Existing Images</label>
                                    <div class="row g-3">
                                        <?php foreach ($productImages as $image): ?>
                                            <div class="col-md-4">
                                                <div class="card h-100">
                                                    <img src="<?= htmlspecialchars($image['image_path']) ?>" class="card-img-top" alt="Product image" style="height: 180px; object-fit: cover;">
                                                    <div class="card-body p-3">
                                                        <form method="post">
                                                            <?= csrf_input() ?>
                                                            <input type="hidden" name="delete_image_id" value="<?= (int) $image['image_id'] ?>">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Remove Image</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
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
