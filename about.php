<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us – Game Console Exchange</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link href="static/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "inc/navbar.inc.php"; ?>

    <main>

        <!-- ── Hero ── -->
        <section class="py-5">
            <div class="container py-4">
                <div class="about-hero p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-7">
                            <span class="hero-badge mb-3">Our story</span>
                            <h1 class="display-5 fw-bold mb-3">Built by players, for&nbsp;players.</h1>
                            <p class="lead" style="color:#cbd8f0;">
                                Game Console Exchange was born out of a simple frustration — buying and selling
                                pre-owned consoles online felt sketchy, slow, and over-complicated. We set out to
                                fix&nbsp;that.
                            </p>
                            <p style="color:#a8b8d8; line-height:1.75;">
                                What started as a university project has grown into a focused marketplace where
                                trust, transparency, and a clean user experience come first. Whether you're
                                offloading a console you've outgrown or hunting for a deal on a childhood
                                favourite, GCE is built with you in mind.
                            </p>
                            <div class="d-flex flex-wrap gap-3 mt-4">
                                <a href="product_list.php" class="btn btn-primary btn-lg">Browse Listings</a>
                                <a href="register.php"     class="btn btn-outline-secondary btn-lg">Join the Community</a>
                            </div>
                        </div>
                        <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                            <div style="
                                width: 100%;
                                max-width: 340px;
                                aspect-ratio: 1;
                                border-radius: 28px;
                                background: rgba(255,255,255,0.04);
                                border: 1px solid var(--gce-border);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 7rem;
                            ">🎮</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Stats strip ── -->
        <section class="pb-4">
            <div class="container">
                <div class="stats-strip">
                    <div class="row g-0">
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">5k+</div>
                                <div class="stat-label">Listings posted</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">2.1k</div>
                                <div class="stat-label">Happy members</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">98%</div>
                                <div class="stat-label">Positive reviews</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">15+</div>
                                <div class="stat-label">Console brands</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Mission ── -->
        <section class="py-5">
            <div class="container">
                <div class="mission-shell p-4 p-lg-5">
                    <div class="row g-5 align-items-center">
                        <div class="col-lg-5">
                            <div class="mission-accent-line"></div>
                            <p class="text-uppercase small mb-2" style="color:var(--gce-muted); letter-spacing:.08em;">Our mission</p>
                            <h2 class="display-6 fw-bold mb-4">Make second-hand gaming trustworthy.</h2>
                            <p style="color:#a8b8d8; line-height:1.8;">
                                The pre-owned gaming market is huge — but it's riddled with uncertainty.
                                Misrepresented conditions, delayed payments, and zero recourse leave buyers
                                and sellers frustrated.
                            </p>
                            <p style="color:#a8b8d8; line-height:1.8;">
                                GCE's mission is to eliminate that uncertainty with verified listings,
                                transparent reviews, and a moderation layer that keeps the platform healthy.
                                Every feature we build traces back to one goal: making the trade feel safe.
                            </p>
                        </div>
                        <div class="col-lg-7">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="value-card h-100">
                                        <div class="value-icon value-icon-blue">🔍</div>
                                        <h3 class="h6 fw-bold mb-2">Transparency</h3>
                                        <p class="mb-0" style="color:var(--gce-muted); font-size:.9rem; line-height:1.6;">
                                            Every listing shows condition notes, seller history, and genuine buyer reviews — no hidden surprises.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="value-card h-100">
                                        <div class="value-icon value-icon-red">🛡️</div>
                                        <h3 class="h6 fw-bold mb-2">Safety first</h3>
                                        <p class="mb-0" style="color:var(--gce-muted); font-size:.9rem; line-height:1.6;">
                                            Moderation tools, report flows, and Stripe-powered payments keep both sides of every deal protected.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="value-card h-100">
                                        <div class="value-icon value-icon-green">⚡</div>
                                        <h3 class="h6 fw-bold mb-2">Speed &amp; simplicity</h3>
                                        <p class="mb-0" style="color:var(--gce-muted); font-size:.9rem; line-height:1.6;">
                                            List in minutes. Buy in seconds. No bloated forms, no confusing flows — just a clean path to done.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="value-card h-100">
                                        <div class="value-icon value-icon-gold">🤝</div>
                                        <h3 class="h6 fw-bold mb-2">Community</h3>
                                        <p class="mb-0" style="color:var(--gce-muted); font-size:.9rem; line-height:1.6;">
                                            Reviews, ratings, and seller profiles build a reputation economy that rewards honest traders.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── CTA ── -->
        <section class="py-5">
            <div class="container pb-4">
                <div class="about-cta p-4 p-lg-5 text-center">
                    <span class="hero-badge mb-3">Ready to trade?</span>
                    <h2 class="display-6 fw-bold mb-3">Join thousands of players already on&nbsp;GCE.</h2>
                    <p class="mx-auto mb-4" style="max-width:520px; color:#a8b8d8; line-height:1.75;">
                        Create a free account, browse hundreds of listings, or post your first product in minutes.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="register.php"      class="btn btn-primary btn-lg">Create an Account</a>
                        <a href="product_list.php"  class="btn btn-outline-secondary btn-lg">Browse Listings</a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?php include "inc/footer.inc.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>