<?php
require_once __DIR__ . '/inc/security.inc.php';
require_once __DIR__ . '/inc/db.inc.php';

$token = trim($_GET['token'] ?? '');
$tokenIsValid = false;

if ($token !== '') {
    $createTableSql = <<<SQL
CREATE TABLE IF NOT EXISTS PASSWORD_RESET_TOKEN (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    account_type VARCHAR(20) NOT NULL,
    account_identifier VARCHAR(255) NOT NULL,
    reset_email VARCHAR(255) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reset_email (reset_email),
    INDEX idx_reset_identifier (account_type, account_identifier)
)
SQL;

    if ($conn->query($createTableSql)) {
        $cleanupStmt = $conn->prepare("DELETE FROM PASSWORD_RESET_TOKEN WHERE expires_at < NOW()");
        if ($cleanupStmt) {
            $cleanupStmt->execute();
            $cleanupStmt->close();
        }

        $tokenStmt = $conn->prepare("SELECT token_hash FROM PASSWORD_RESET_TOKEN WHERE expires_at >= NOW()");
        if ($tokenStmt) {
            $tokenStmt->execute();
            $tokenRes = $tokenStmt->get_result();

            while ($row = $tokenRes->fetch_assoc()) {
                if (password_verify($token, $row['token_hash'])) {
                    $tokenIsValid = true;
                    break;
                }
            }
            $tokenStmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Game Console Exchange</title>
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
                        <h3 class="mb-3 text-center">Choose a new password</h3>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                        <?php endif; ?>

                        <?php if (!$tokenIsValid): ?>
                            <div class="alert alert-danger">
                                This password reset link is invalid or has expired.
                            </div>
                            <div class="text-center">
                                <a href="forgot_password.php" class="text-decoration-none">Request a new reset link</a>
                            </div>
                        <?php else: ?>
                            <form action="process_reset_password.php" method="POST">
                                <?= csrf_input() ?>
                                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                                <div class="mb-3">
                                    <label for="password" class="form-label text-muted">New password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label text-muted">Confirm new password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary py-2">Update password</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
