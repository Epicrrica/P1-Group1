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
    <link href="static/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "inc/navbar.inc.php"; ?>

    <main>
        <section class="py-5">
            <div class="container py-5">
                <div class="hero-shell p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-6">
                            <span class="hero-badge mb-3">Trusted resale marketplace</span>
                            <h1 class="display-5 fw-bold">Buy and sell pre-owned game consoles with confidence.</h1>
                            <p class="lead mt-3 soft-muted">
                                Game Console Exchange helps players browse listings, save favorites, and manage
                                purchases in one clean marketplace.
                            </p>
                            <div class="d-flex flex-wrap gap-3 mt-4">
                                <a href="product_list.php" class="btn btn-primary btn-lg">Browse Products</a>
                                <a href="add_product.php" class="btn btn-outline-secondary btn-lg">Start Selling</a>
                            </div>
                            <div class="hero-stat-grid mt-4">
                                <div class="hero-stat">
                                    <strong>Browse</strong>
                                    <span>Find consoles, games, and bundles fast.</span>
                                </div>
                                <div class="hero-stat">
                                    <strong>List</strong>
                                    <span>Post products with photos and pricing.</span>
                                </div>
                                <div class="hero-stat">
                                    <strong>Trust</strong>
                                    <span>Use reviews and moderation to trade safer.</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="surface-card hero-logo-panel border-0 p-4 p-lg-5">
                                <img src="static/img/gce-logo.png" alt="Game Console Exchange" class="hero-logo mb-4">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="feature-tile">
                                            <h3 class="h6">Accounts</h3>
                                            <p class="mb-0 small text-muted">Registration, login, and profile management.</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="feature-tile">
                                            <h3 class="h6">Marketplace</h3>
                                            <p class="mb-0 small text-muted">Browse, search, and filter listed consoles.</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="feature-tile">
                                            <h3 class="h6">Checkout</h3>
                                            <p class="mb-0 small text-muted">Simple purchase flow with Stripe support.</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="feature-tile">
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
                <div class="section-header">
                    <p class="text-uppercase small soft-muted mb-2">Why it feels different</p>
                    <h2 class="display-6 fw-bold mb-0">A cleaner trade flow for players who know what they want.</h2>
                </div>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="feature-tile">
                            <div class="card-body">
                                <h2 class="h5">Buy Consoles</h2>
                                <p class="text-muted mb-0">
                                    Explore listings for popular consoles, accessories, and bundled sets.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-tile">
                            <div class="card-body">
                                <h2 class="h5">Sell Easily</h2>
                                <p class="text-muted mb-0">
                                    Let users create listings with product details, pricing, and condition notes.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-tile">
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
