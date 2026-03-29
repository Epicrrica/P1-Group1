<?php
include "inc/db.inc.php";
include "inc/product_images.inc.php";
include "inc/marketplace.inc.php";

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($productId <= 0) {
    die('Invalid product ID.');
}

// Fetch product details including the seller's username
$stmt = $conn->prepare("SELECT * FROM PRODUCT WHERE product_id = ?");
if ($stmt === false) die('Query preparation failed: ' . $conn->error);

$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die('Product not found.');
}

$productImages = get_product_images($conn, $productId);

// Permissions Logic
$currentUser = $_SESSION['logged_in_user'] ?? null;
$userRole = $_SESSION['user_role'] ?? 'user';
$sellerUsername = $product['seller_username'] ?? ''; // Safely grab the poster
$sellerDetails = null;

if ($sellerUsername !== '') {
    $sellerStmt = $conn->prepare(
        "SELECT username, user_email, user_phone_no, user_bio
         FROM USER
         WHERE username = ?"
    );

    if ($sellerStmt !== false) {
        $sellerStmt->bind_param("s", $sellerUsername);
        $sellerStmt->execute();
        $sellerResult = $sellerStmt->get_result();
        $sellerDetails = $sellerResult->fetch_assoc();
        $sellerStmt->close();
    }
}

$canEdit = ($currentUser && $currentUser === $sellerUsername);
$canDelete = ($currentUser && ($currentUser === $sellerUsername || $userRole === 'moderator'));
$favoriteCount = get_favorite_count($conn, $productId);
$ratingSummary = get_product_rating_summary($conn, $productId);
$reviews = get_product_reviews($conn, $productId);
$userHasFavorited = $currentUser ? user_has_favorited_product($conn, $productId, $currentUser) : false;
$existingReview = $currentUser ? get_user_review_for_product($conn, $productId, $currentUser) : null;
$reviewMessage = '';
$reviewError = '';
$paymentMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit']) && $currentUser) {
    verify_csrf_or_reject();
    $rating = (int) ($_POST['rating'] ?? 0);
    $reviewText = trim($_POST['review_text'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $reviewError = 'Please choose a rating from 1 to 5.';
    } elseif ($existingReview) {
        $reviewError = 'You have already reviewed this product.';
    } elseif (upsert_product_review($conn, $productId, $currentUser, $rating, $reviewText)) {
        header("Location: product_detail.php?id={$productId}&review=success");
        exit();
    } else {
        $reviewError = 'Could not save your review. Make sure the review table is created.';
    }
}

if (isset($_GET['review']) && $_GET['review'] === 'success') {
    $reviewMessage = 'Your review was saved.';
}

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'stripe_not_configured') {
        $paymentMessage = 'Stripe is not configured yet. Add your Stripe test keys first.';
    } elseif ($_GET['error'] === 'stripe_checkout_failed') {
        $paymentMessage = 'Could not start Stripe Checkout. Please try again.';
    } elseif ($_GET['error'] === 'payment_cancelled') {
        $paymentMessage = 'Stripe payment was cancelled.';
    } elseif ($_GET['error'] === 'own_product') {
        $paymentMessage = 'You cannot buy your own product.';
    } elseif ($_GET['error'] === 'purchase_failed') {
        $paymentMessage = 'The purchase could not be recorded.';
    }
}

$favoriteCount = get_favorite_count($conn, $productId);
$ratingSummary = get_product_rating_summary($conn, $productId);
$reviews = get_product_reviews($conn, $productId);
$userHasFavorited = $currentUser ? user_has_favorited_product($conn, $productId, $currentUser) : false;
$existingReview = $currentUser ? get_user_review_for_product($conn, $productId, $currentUser) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "inc/navbar.inc.php"; ?>

    <main class="py-5 product-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-4 product-page-header">
                        <div>
                            <h1 class="h2 mb-1 fw-bold"><?= htmlspecialchars($product['product_name']) ?></h1>
                            <p class="text-muted mb-0">Posted by: <span class="fw-semibold"><?= htmlspecialchars($sellerUsername) ?></span></p>
                        </div>
                        <a href="product_list.php" class="btn btn-outline-secondary">Back to Products</a>
                    </div>

                    <div class="card product-detail-card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <?php if ($paymentMessage !== ''): ?>
                                <div class="alert alert-warning"><?= htmlspecialchars($paymentMessage) ?></div>
                            <?php endif; ?>
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <?php if (!empty($productImages)): ?>
                                        <div id="productCarousel" class="carousel slide mb-4">
                                            <div class="carousel-inner rounded">
                                                <?php foreach ($productImages as $index => $image): ?>
                                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                                        <img src="<?= htmlspecialchars($image['image_path']) ?>" class="d-block w-100 product-gallery-image" alt="<?= htmlspecialchars($product['product_name']) ?>" style="height: 420px; object-fit: cover;">
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
                                    <div class="product-sidebar rounded p-4 h-100 border">
                                        <p class="mb-2 text-muted">Category:</p>
                                        <p class="fs-5 fw-semibold"><?= htmlspecialchars($product['category']) ?></p>
                                        <hr>
                                        <p class="mb-2 text-muted">Price:</p>
                                        <p class="fs-3 fw-bold product-price">$<?= number_format((float) $product['price'], 2) ?></p>
                                        <hr>
                                        <p class="mb-2 text-muted">Seller Contact:</p>
                                        <?php if ($sellerDetails): ?>
                                            <p class="fw-semibold mb-1"><?= htmlspecialchars($sellerDetails['username']) ?></p>
                                            <p class="small text-muted mb-1">Email: <?= htmlspecialchars($sellerDetails['user_email'] ?: 'Not provided') ?></p>
                                            <p class="small text-muted mb-3">Phone: <?= htmlspecialchars($sellerDetails['user_phone_no'] ?: 'Not provided') ?></p>
                                        <?php else: ?>
                                            <p class="small text-muted mb-3">Seller contact information is not available.</p>
                                        <?php endif; ?>
                                        <hr>
                                        <p class="mb-2 text-muted">Favorites:</p>
                                        <p class="fw-semibold"><?= $favoriteCount ?></p>
                                        <p class="mb-2 text-muted">Rating:</p>
                                        <p class="fw-semibold">
                                            <?php if ($ratingSummary['average_rating'] !== null): ?>
                                                <?= number_format($ratingSummary['average_rating'], 1) ?>/5
                                            <?php else: ?>
                                                No ratings yet
                                            <?php endif; ?>
                                        </p>

                                        <?php if ($currentUser && $currentUser !== $sellerUsername): ?>
                                            <div class="d-grid gap-2 mt-4">
                                                <form method="post" action="toggle_favorite.php">
                                                    <?= csrf_input() ?>
                                                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                                                    <input type="hidden" name="redirect" value="product_detail.php?id=<?= $productId ?>">
                                                    <button type="submit" class="btn btn-outline-secondary w-100">
                                                        <?= $userHasFavorited ? 'Remove Favorite' : 'Add to Favorites' ?>
                                                    </button>
                                                </form>
                                                <form method="post" action="add_to_cart.php">
                                                    <?= csrf_input() ?>
                                                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                                                    <input type="hidden" name="redirect" value="product_detail.php?id=<?= $productId ?>">
                                                    <button type="submit" class="btn btn-outline-primary w-100 fw-semibold">Add to Cart</button>
                                                </form>
                                                <form method="post" action="purchase_product.php">
                                                    <?= csrf_input() ?>
                                                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                                                    <button type="submit" class="btn btn-success w-100 fw-semibold">Buy Now</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($canEdit || $canDelete): ?>
                                            <div class="d-grid gap-2 mt-4 pt-3 border-top">
                                                <?php if ($canEdit): ?>
                                                    <a href="edit_product.php?id=<?= $product['product_id'] ?>" class="btn btn-primary fw-semibold">Edit Product</a>
                                                <?php endif; ?>
                                                <?php if ($canDelete): ?>
                                                    <a href="delete_product.php?id=<?= $product['product_id'] ?>" class="btn btn-outline-danger fw-semibold">Delete Product</a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card product-detail-card border-0 shadow-sm mt-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h4 mb-0">Reviews</h2>
                                <span class="text-muted small"><?= $ratingSummary['review_count'] ?> review(s)</span>
                            </div>

                            <?php if ($reviewMessage !== ''): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($reviewMessage) ?></div>
                            <?php endif; ?>

                            <?php if ($reviewError !== ''): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($reviewError) ?></div>
                            <?php endif; ?>

                            <?php if ($currentUser && $currentUser !== $sellerUsername && !$existingReview): ?>
                                <form method="post" class="border rounded p-3 product-review-form mb-4">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="review_submit" value="1">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="rating" class="form-label">Rating</label>
                                            <select class="form-select" id="rating" name="rating" required>
                                                <option value="">Choose</option>
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php $selectedRating = (int) ($_POST['rating'] ?? ($existingReview['rating'] ?? 0)); ?>
                                                    <option value="<?= $i ?>" <?= $selectedRating === $i ? 'selected' : '' ?>><?= $i ?>/5</option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-9">
                                            <label for="review_text" class="form-label">Review</label>
                                            <textarea class="form-control" id="review_text" name="review_text" rows="3" placeholder="Share your thoughts about this product."><?= htmlspecialchars($_POST['review_text'] ?? ($existingReview['review_text'] ?? '')) ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">Save Review</button>
                                        </div>
                                    </div>
                                </form>
                            <?php elseif ($existingReview): ?>
                                <div class="alert alert-secondary mb-4">
                                    You have already submitted a review for this product.
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($reviews)): ?>
                                <div class="vstack gap-3">
                                    <?php foreach ($reviews as $review): ?>
                                        <div class="border rounded p-3 review-card">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong><?= htmlspecialchars($review['reviewer_username']) ?></strong>
                                                <span class="rating-chip"><?= (int) $review['rating'] ?>/5</span>
                                            </div>
                                            <p class="mb-1 text-muted"><?= nl2br(htmlspecialchars($review['review_text'] ?: 'No written review.')) ?></p>
                                            <small class="text-muted"><?= htmlspecialchars($review['created_at']) ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">No reviews yet.</div>
                            <?php endif; ?>
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
