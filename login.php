<?php
require_once __DIR__ . '/inc/security.inc.php';
security_bootstrap_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Game Console Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php include "inc/navbar.inc.php"; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card auth-card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="auth-brand text-center mb-3 mt-2">
                            <img src="static/img/gce-logo.png" alt="Game Console Exchange" class="auth-brand-logo">
                        </div>
                        <h3 class="mb-4 text-center">Login to your account</h3>
                        
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success text-center"><?= htmlspecialchars($_GET['success']) ?></div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger text-center"><?= htmlspecialchars($_GET['error']) ?></div>
                        <?php endif; ?>
                        
                        <form action="process_login.php" method="POST">
                            <?= csrf_input() ?>
                            <div class="mb-3">
                                <label for="username" class="form-label text-muted">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label text-muted">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-4 p-3 bg-light border rounded">
                                <label class="form-label text-muted fw-bold mb-2">Send my 6-digit code via:</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="two_fa_method" id="methodEmail" value="email" checked>
                                    <label class="form-check-label" for="methodEmail">Email Address</label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="two_fa_method" id="methodSms" value="sms">
                                    <label class="form-check-label" for="methodSms">SMS (Text Message)</label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary py-2">Login</button>
                            </div>
                        </form>

                        <div class="mt-3 text-center">
                            <a href="forgot_password.php" class="text-decoration-none">Forgot your password?</a>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <p class="text-muted">Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
