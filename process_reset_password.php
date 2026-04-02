<?php
require_once __DIR__ . '/inc/security.inc.php';
require_once __DIR__ . '/inc/db.inc.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot_password.php");
    exit();
}

verify_csrf_or_reject();

$token = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($token === '') {
    header("Location: forgot_password.php?error=" . urlencode('Missing reset token.'));
    exit();
}

if (strlen($password) < 5) {
    header("Location: reset_password.php?token=" . urlencode($token) . "&error=" . urlencode('Password must be at least 5 characters.'));
    exit();
}

if (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/\d/', $password)) {
    header("Location: reset_password.php?token=" . urlencode($token) . "&error=" . urlencode('Password must contain at least one letter and one number.'));
    exit();
}

if ($password !== $confirmPassword) {
    header("Location: reset_password.php?token=" . urlencode($token) . "&error=" . urlencode('Passwords do not match.'));
    exit();
}

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

if (!$conn->query($createTableSql)) {
    header("Location: forgot_password.php?error=" . urlencode('Could not prepare password reset storage.'));
    exit();
}

$tokenStmt = $conn->prepare(
    "SELECT reset_id, account_type, account_identifier, token_hash
     FROM PASSWORD_RESET_TOKEN
     WHERE expires_at >= NOW()"
);
$tokenStmt->execute();
$tokenRes = $tokenStmt->get_result();

$matchedReset = null;
while ($row = $tokenRes->fetch_assoc()) {
    if (password_verify($token, $row['token_hash'])) {
        $matchedReset = $row;
        break;
    }
}
$tokenStmt->close();

if ($matchedReset === null) {
    header("Location: forgot_password.php?error=" . urlencode('That reset link is invalid or has expired.'));
    exit();
}

$newHash = password_hash($password, PASSWORD_DEFAULT);

if ($matchedReset['account_type'] === 'moderator') {
    $updateStmt = $conn->prepare("UPDATE MODERATOR SET mod_password_hash = ? WHERE mod_name = ?");
} else {
    $updateStmt = $conn->prepare("UPDATE USER SET user_password_hash = ? WHERE username = ?");
}

$updateStmt->bind_param("ss", $newHash, $matchedReset['account_identifier']);
$updateStmt->execute();
$updateStmt->close();

$cleanupStmt = $conn->prepare("DELETE FROM PASSWORD_RESET_TOKEN WHERE account_type = ? AND account_identifier = ?");
$cleanupStmt->bind_param("ss", $matchedReset['account_type'], $matchedReset['account_identifier']);
$cleanupStmt->execute();
$cleanupStmt->close();

header("Location: login.php?success=" . urlencode('Password updated successfully. Please log in with your new password.'));
exit();
?>
