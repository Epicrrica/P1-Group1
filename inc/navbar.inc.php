<?php
require_once __DIR__ . '/security.inc.php';
security_bootstrap_session();

$current_page = basename($_SERVER['PHP_SELF']);
$navbarProfileImage = 'static/img/default-avatar.svg';

if (isset($_SESSION['logged_in_user'])) {
    if (!isset($conn) && ($_SESSION['user_role'] ?? null) !== 'moderator') {
        require_once __DIR__ . '/db.inc.php';
    }

    if (isset($conn) && ($_SESSION['user_role'] ?? null) !== 'moderator') {
        try {
            $navbarUserStmt = $conn->prepare("SELECT user_profile_img FROM USER WHERE username = ? LIMIT 1");
        } catch (Error $e) {
            $navbarUserStmt = false;
        }

        if ($navbarUserStmt) {
            $navbarUserStmt->bind_param("s", $_SESSION['logged_in_user']);
            $navbarUserStmt->execute();
            $navbarUserStmt->bind_result($navbarProfileBlob);

            if ($navbarUserStmt->fetch() && !empty($navbarProfileBlob)) {
                $navbarProfileImage = 'data:image/jpeg;base64,' . base64_encode($navbarProfileBlob);
            }

            $navbarUserStmt->close();
        }
    }
}
?>
<nav class="navbar navbar-expand-lg gce-navbar shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold gce-brand" href="index.php">
            <span class="navbar-title">Game Console Exchange</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= in_array($current_page, ['product_list.php', 'product_detail.php', 'edit_product.php']) ? 'active' : '' ?>" href="product_list.php">Products</a>
                </li>
                
                <?php if (isset($_SESSION['logged_in_user'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'add_product.php') ? 'active' : '' ?>" href="add_product.php">Sell</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'cart.php') ? 'active' : '' ?>" href="cart.php">
                        Cart
                        <?php if (function_exists('cart_count') && cart_count() > 0): ?>
                            <span class="badge rounded-pill bg-warning text-dark ms-1"><?= cart_count() ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'moderator'): ?>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-bold <?= ($current_page == 'moderator_dashboard.php') ? 'active' : '' ?>" href="moderator_dashboard.php">Mod Dashboard</a>
                </li>
                <?php endif; ?>

                <?php if (isset($_SESSION['logged_in_user'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle profile-trigger <?= in_array($current_page, ['profile.php', 'edit_profile.php']) ? 'active' : '' ?>" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?= htmlspecialchars($navbarProfileImage) ?>" alt="Profile picture" class="navbar-avatar">
                            <span>Profile</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="profileDropdown">
                            <li><a class="dropdown-item" href="profile.php">View Profile</a></li>
                            <li><a class="dropdown-item" href="edit_profile.php">Edit Profile</a></li>
                            <li><a class="dropdown-item" href="purchase_history.php">Purchase History</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger fw-bold" href="logout.php">Log out</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= in_array($current_page, ['login.php', 'register.php', 'verify_2fa.php']) ? 'active' : '' ?>" href="login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
