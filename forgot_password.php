<?php
require_once __DIR__ . '/inc/security.inc.php';
security_bootstrap_session();

$previewLink = $_SESSION['password_reset_preview_link'] ?? null;
unset($_SESSION['password_reset_preview_link']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Game Console Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "inc/navbar.inc.php"; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card auth-card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h3 class="mb-3 text-center">Reset your password</h3>
                        <p class="text-muted text-center mb-4">
                            Enter your email address and we will send you a password reset link.
                        </p>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">
                                If that email address exists in our system, a reset link has been sent.
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($_GET['error']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($previewLink): ?>
                            <div class="alert alert-warning">
                                <strong>Testing only:</strong>
                                <a href="<?= htmlspecialchars($previewLink) ?>">Open your password reset link</a>
                            </div>
                        <?php endif; ?>

                        <form action="process_forgot_password.php" method="POST">
                            <?= csrf_input() ?>
                            <div class="mb-3">
                                <label for="email" class="form-label text-muted">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary py-2">Send reset link</button>
                            </div>
                        </form>

                        <div class="mt-4 text-center">
                            <a href="login.php" class="text-decoration-none">Back to login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
