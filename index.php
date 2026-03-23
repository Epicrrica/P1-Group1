<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Console Exchange</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
    <?php include "inc/navbar.inc.php"; ?>

    <main>
        <section class="py-5 bg-dark text-white">
            <div class="container py-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <span class="badge text-bg-warning text-dark mb-3">INF1005 Project</span>
                        <h1 class="display-5 fw-bold">Buy and sell pre-owned game consoles with confidence.</h1>
                        <p class="lead mt-3">
                            Game Console Exchange is a third-party resale platform where users can list products,
                            browse available consoles, leave reviews, and manage purchases in one place.
                        </p>
                        <div class="d-flex flex-wrap gap-3 mt-4">
                            <a href="product_list.php" class="btn btn-warning btn-lg">Browse Products</a>
                            <a href="add_product.php" class="btn btn-outline-light btn-lg">Start Selling</a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card shadow-lg border-0">
                            <div class="card-body p-4">
                                <h2 class="h4 mb-3">Platform Highlights</h2>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="p-3 rounded bg-light h-100">
                                            <h3 class="h6">User Accounts</h3>
                                            <p class="mb-0 small text-muted">Registration, login, and profile management.</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-3 rounded bg-light h-100">
                                            <h3 class="h6">Marketplace</h3>
                                            <p class="mb-0 small text-muted">Browse, search, and filter listed consoles.</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-3 rounded bg-light h-100">
                                            <h3 class="h6">Checkout</h3>
                                            <p class="mb-0 small text-muted">Simple purchase flow for buyers.</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-3 rounded bg-light h-100">
                                            <h3 class="h6">Reviews</h3>
                                            <p class="mb-0 small text-muted">Ratings and comments for listed products.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <h2 class="h5">Buy Consoles</h2>
                                <p class="text-muted mb-0">
                                    Explore listings for popular consoles, accessories, and bundled sets.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <h2 class="h5">Sell Easily</h2>
                                <p class="text-muted mb-0">
                                    Let users create listings with product details, pricing, and condition notes.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <h2 class="h5">Build Trust</h2>
                                <p class="text-muted mb-0">
                                    Use reviews, ratings, and moderation features to keep the platform safe.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include "inc/footer.inc.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
