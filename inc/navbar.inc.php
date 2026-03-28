<?php
// Ensure session is started so we can read login status securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <span class="bg-warning text-dark px-2 py-1 rounded-3 me-2 fs-5 shadow-sm">GCE</span>
            <span class="text-white tracking-wide">Game Console Exchange</span>
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
                <?php endif; ?>
                
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'moderator'): ?>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-bold <?= ($current_page == 'moderator_dashboard.php') ? 'active' : '' ?>" href="moderator_dashboard.php">Mod Dashboard</a>
                </li>
                <?php endif; ?>

                <?php if (isset($_SESSION['logged_in_user'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array($current_page, ['profile.php', 'edit_profile.php']) ? 'active' : '' ?>" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Profile
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="profileDropdown">
                            <li><a class="dropdown-item" href="profile.php">View Profile</a></li>
                            <li><a class="dropdown-item" href="edit_profile.php">Edit Profile</a></li>
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