<?php
include "inc/db.inc.php";
include "inc/marketplace.inc.php";
include "inc/order.inc.php";

if (!isset($_SESSION['logged_in_user'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['logged_in_user'];

// Initialize OrderManager and process updates
$orderManager = new OrderManager($conn);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $purchase_id = $_POST['purchase_id'];
    $new_status = $_POST['order_status'];
    $tracking_number = $_POST['tracking_number'];
    
    if ($orderManager->updateFulfillment($purchase_id, $username, $new_status, $tracking_number)) {
        $success_msg = "Order updated successfully!";
    }
}

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM USER WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fallback for Moderator Account viewing profile
if (!$user && $_SESSION['user_role'] === 'moderator') {
    $user = [
        'username' => 'Admin Mod',
        'user_email' => 'moderator@gce.com',
        'user_phone_no' => 'N/A',
        'user_bio' => 'Platform Moderator Account.',
        'user_profile_img' => null
    ];
}

// Convert BLOB image to base64, or use a default placeholder
$profile_img = !empty($user['user_profile_img']) 
    ? 'data:image/jpeg;base64,' . base64_encode($user['user_profile_img']) 
    : 'static/img/default-avatar.svg';

$favoriteProducts = get_user_favorites($conn, $username);
$purchaseHistory = get_user_purchases($conn, $username);
$myProducts = get_user_products($conn, $username);
$mySales = $orderManager->getOrdersBySeller($username); // Fetch sales data
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - GCE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
</head>
<body class="profile-page">

    <?php include "inc/navbar.inc.php"; ?>

    <main class="container py-5 profile-dashboard">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 overflow-hidden profile-card">
                    <div class="profile-hero" style="height: 120px;"></div>
                    <div class="card-body px-5 pb-5 position-relative">
                        
                        <div class="text-center profile-avatar-wrap" style="margin-top: -65px;">
                            <img src="<?= $profile_img ?>" alt="Profile Picture" class="rounded-circle profile-avatar" style="width: 130px; height: 130px; object-fit: cover;">
                        </div>
                        
                        <div class="text-center mt-3 mb-5 profile-header-block">
                            <h2 class="fw-bold mb-0"><?= htmlspecialchars($user['username'] ?? '') ?></h2>
                            <p class="text-muted profile-email"><?= htmlspecialchars($user['user_email'] ?? '') ?></p>
                            <a href="edit_profile.php" class="btn btn-outline-primary btn-sm px-4 rounded-pill">Edit Profile</a>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="profile-stat-card">
                                    <h6 class="text-muted fw-bold mb-1">Phone Number</h6>
                                    <p class="fs-5 mb-0"><?= htmlspecialchars($user['user_phone_no'] ?? 'Not provided') ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="profile-stat-card">
                                    <h6 class="text-muted fw-bold mb-1">Account Status</h6>
                                    <p class="fs-5 fw-semibold mb-0 profile-status">Active</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <h6 class="text-muted fw-bold mb-2">About Me</h6>
                                <div class="p-3 rounded text-muted profile-section-box">
                                    <?= nl2br(htmlspecialchars($user['user_bio'] ?? 'No bio provided.')) ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <h6 class="text-muted fw-bold mb-2">My Listings</h6>
                                <div class="p-3 rounded profile-section-box">
                                    <?php if (!empty($myProducts)): ?>
                                        <div class="vstack gap-2">
                                            <?php foreach ($myProducts as $product): ?>
                                                <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2 profile-inline-card">
                                                    <div>
                                                        <div class="fw-semibold"><?= htmlspecialchars($product['product_name']) ?></div>
                                                        <small class="text-muted profile-inline-meta"><?= htmlspecialchars($product['category']) ?></small>
                                                    </div>
                                                    <a href="product_detail.php?id=<?= (int) $product['product_id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">You have not listed any products yet.</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-12">
                                <h6 class="text-muted fw-bold mb-2">My Sales (Order Fulfillment)</h6>
                                <div class="p-3 rounded profile-section-box">
                                    <?php if (isset($success_msg)): ?>
                                        <div class="alert alert-success py-2"><?= $success_msg ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($mySales)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Order #</th>
                                                        <th>Buyer</th>
                                                        <th>Status</th>
                                                        <th>Tracking #</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($mySales as $sale): ?>
                                                    <tr>
                                                        <form method="POST" action="profile.php">
                                                            <input type="hidden" name="purchase_id" value="<?= $sale['purchase_id'] ?>">
                                                            <td><?= $sale['purchase_id'] ?></td>
                                                            <td><?= htmlspecialchars($sale['buyer_username']) ?></td>
                                                            <td>
                                                                <select name="order_status" class="form-select form-select-sm">
                                                                    <option value="Pending" <?= $sale['order_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                                    <option value="Processing" <?= $sale['order_status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                                                                    <option value="Shipped" <?= $sale['order_status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                                                    <option value="Delivered" <?= $sale['order_status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                                                    <option value="Cancelled" <?= $sale['order_status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="text" name="tracking_number" class="form-control form-control-sm" placeholder="Tracking URL/No." value="<?= htmlspecialchars($sale['tracking_number'] ?? '') ?>">
                                                            </td>
                                                            <td>
                                                                <button type="submit" name="update_order" class="btn btn-sm btn-primary">Save</button>
                                                            </td>
                                                        </form>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">You have no sales to fulfill yet.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted fw-bold mb-2">Favorites</h6>
                                <div class="p-3 rounded h-100 profile-section-box">
                                    <?php if (!empty($favoriteProducts)): ?>
                                        <div class="vstack gap-2">
                                            <?php foreach ($favoriteProducts as $favorite): ?>
                                                <a href="product_detail.php?id=<?= (int) $favorite['product_id'] ?>" class="text-decoration-none">
                                                    <div class="border rounded px-3 py-2 profile-inline-card">
                                                        <div class="fw-semibold"><?= htmlspecialchars($favorite['product_name']) ?></div>
                                                        <small class="text-muted profile-inline-meta">$<?= number_format((float) $favorite['price'], 2) ?></small>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No favorite products yet.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted fw-bold mb-2">Recent Purchases</h6>
                                <div class="p-3 rounded h-100 profile-section-box">
                                    <?php if (!empty($purchaseHistory)): ?>
                                        <div class="vstack gap-2">
                                            <?php foreach (array_slice($purchaseHistory, 0, 5) as $purchase): ?>
                                                <a href="product_detail.php?id=<?= (int) $purchase['product_id'] ?>" class="text-decoration-none">
                                                    <div class="border rounded px-3 py-2 profile-inline-card">
                                                        <div class="fw-semibold"><?= htmlspecialchars($purchase['product_name']) ?></div>
                                                        <small class="text-muted profile-inline-meta">$<?= number_format((float) $purchase['price_paid'], 2) ?></small>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No purchases yet.</span>
                                    <?php endif; ?>
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