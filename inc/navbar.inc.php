<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <<a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <span class="bg-warning text-dark px-2 py-1 rounded-3 me-2 fs-5 shadow-sm">GCE</span>
            <span class="text-white tracking-wide">Game Console Exchange</span>
        </a>
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="product_list.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="add_product.php">Sell</a>
                </li>
                
                <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'moderator'): ?>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-bold" href="moderator_dashboard.php">Mod Dashboard</a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link" href="#">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>