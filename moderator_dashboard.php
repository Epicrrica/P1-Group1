<?php
session_start();

// Security check: Only moderators allowed
if (!isset($_SESSION['logged_in_user']) || $_SESSION['user_role'] !== 'moderator') { 
    header("Location: login.php");
    exit();
}

include "inc/db.inc.php"; 

// --- Search & Filter Logic ---
$search_term = $_GET['search'] ?? '';
$search_param = '%' . $search_term . '%';
$sort = $_GET['sort'] ?? 'newest';

// Define sorting SQL for Users
$user_order = "ORDER BY user_id DESC";
if ($sort == 'oldest') $user_order = "ORDER BY user_id ASC";
if ($sort == 'name_asc') $user_order = "ORDER BY username ASC";

// Define sorting SQL for Products
$prod_order = "ORDER BY product_id DESC";
if ($sort == 'oldest') $prod_order = "ORDER BY product_id ASC";
if ($sort == 'name_asc') $prod_order = "ORDER BY product_name ASC";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderator Dashboard - GCE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "inc/navbar.inc.php"; ?>
    
    <main class="container mt-5">
        
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-body p-4">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Global Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search usernames, emails, or products..." value="<?= htmlspecialchars($search_term) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                            <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 py-2">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <h2 class="mb-4">Pending User Approvals</h2>
        <div class="row mb-5">
            <?php
            $p_stmt = $conn->prepare("SELECT user_id, username, user_email FROM USER WHERE account_status = 'pending' AND (username LIKE ? OR user_email LIKE ?) $user_order");
            $p_stmt->bind_param("ss", $search_param, $search_param);
            $p_stmt->execute();
            $p_res = $p_stmt->get_result();

            if ($p_res->num_rows > 0) {
                while($row = $p_res->fetch_assoc()) {
                    echo '<div class="col-md-4 mb-3">';
                    echo '<div class="card p-3 shadow-sm border-0">';
                    echo '<h4>' . htmlspecialchars($row['username']) . '</h4>';
                    echo '<p class="text-muted">' . htmlspecialchars($row['user_email']) . '</p>';
                    echo '<form action="process_user_status.php" method="POST" class="d-flex gap-2">';
                    echo '<input type="hidden" name="user_id" value="' . $row['user_id'] . '">';
                    echo '<button type="submit" name="action" value="approved" class="btn btn-success btn-sm">Approve</button>';
                    echo '<button type="submit" name="action" value="suspended" class="btn btn-danger btn-sm">Suspend</button>';
                    echo '</form></div></div>';
                }
            } else {
                echo "<div class='col-12'><div class='alert alert-info'>No pending users found matching search.</div></div>";
            }
            ?>
        </div>

        <h2 class="mb-4">Manage Active Users</h2>
        <div class="row mb-5">
            <?php
            $a_stmt = $conn->prepare("SELECT user_id, username, user_email FROM USER WHERE account_status = 'approved' AND (username LIKE ? OR user_email LIKE ?) $user_order");
            $a_stmt->bind_param("ss", $search_param, $search_param);
            $a_stmt->execute();
            $a_res = $a_stmt->get_result();

            if ($a_res->num_rows > 0) {
                while($row = $a_res->fetch_assoc()) {
                    echo '<div class="col-md-4 mb-3">';
                    echo '<div class="card p-3 shadow-sm border-0">';
                    echo '<h4>' . htmlspecialchars($row['username']) . '</h4>';
                    echo '<p class="text-muted">' . htmlspecialchars($row['user_email']) . '</p>';
                    echo '<form action="process_user_status.php" method="POST">';
                    echo '<input type="hidden" name="user_id" value="' . $row['user_id'] . '">';
                    echo '<button type="submit" name="action" value="suspended" class="btn btn-warning btn-sm">Suspend User</button>';
                    echo '</form></div></div>';
                }
            } else {
                echo "<div class='col-12'><div class='alert alert-info'>No active users found matching search.</div></div>";
            }
            ?>
        </div>

        <h2 class="mb-4">Marketplace Moderation</h2>
        <div class="row mb-5">
            <?php
            $pr_stmt = $conn->prepare("SELECT product_id, product_name, category, price FROM PRODUCT WHERE (product_name LIKE ? OR category LIKE ?) $prod_order");
            $pr_stmt->bind_param("ss", $search_param, $search_param);
            $pr_stmt->execute();
            $pr_res = $pr_stmt->get_result();

            if ($pr_res->num_rows > 0) {
                while($row = $pr_res->fetch_assoc()) {
                    echo '<div class="col-md-4 mb-3">';
                    echo '<div class="card p-3 shadow-sm border-0">';
                    echo '<h4>' . htmlspecialchars($row['product_name']) . '</h4>';
                    echo '<p class="text-muted">' . htmlspecialchars($row['category']) . ' - $' . number_format($row['price'], 2) . '</p>';
                    echo '<form action="process_mod_delete_product.php" method="POST">';
                    echo '<input type="hidden" name="product_id" value="' . $row['product_id'] . '">';
                    echo '<button type="submit" class="btn btn-outline-danger btn-sm">Remove Listing</button>';
                    echo '</form></div></div>';
                }
            } else {
                echo "<div class='col-12'><div class='alert alert-info'>No products found matching search.</div></div>";
            }
            ?>
        </div>

    </main>

    <?php include "inc/footer.inc.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>