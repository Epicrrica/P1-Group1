<?php
require_once __DIR__ . '/inc/security.inc.php';
require_once __DIR__ . '/inc/db.inc.php';

function finish_reset_request(): never
{
    header("Location: forgot_password.php?success=1");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot_password.php");
    exit();
}

verify_csrf_or_reject();

$email = trim($_POST['email'] ?? '');
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: forgot_password.php?error=" . urlencode('Please enter a valid email address.'));
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

$accountType = null;
$accountIdentifier = null;

$userStmt = $conn->prepare("SELECT username FROM USER WHERE LOWER(user_email) = LOWER(?) LIMIT 1");
$userStmt->bind_param("s", $email);
$userStmt->execute();
$userStmt->bind_result($username);
if ($userStmt->fetch()) {
    $accountType = 'user';
    $accountIdentifier = $username;
}
$userStmt->close();

if ($accountType === null) {
    $modStmt = $conn->prepare("SELECT mod_name FROM MODERATOR WHERE LOWER(mod_email) = LOWER(?) LIMIT 1");
    if ($modStmt !== false) {
        $modStmt->bind_param("s", $email);
        $modStmt->execute();
        $modStmt->bind_result($modName);
        if ($modStmt->fetch()) {
            $accountType = 'moderator';
            $accountIdentifier = $modName;
        }
        $modStmt->close();
    }
}

if ($accountType === null || $accountIdentifier === null) {
    finish_reset_request();
}

$deleteStmt = $conn->prepare("DELETE FROM PASSWORD_RESET_TOKEN WHERE account_type = ? AND account_identifier = ?");
$deleteStmt->bind_param("ss", $accountType, $accountIdentifier);
$deleteStmt->execute();
$deleteStmt->close();

$rawToken = bin2hex(random_bytes(32));
$tokenHash = password_hash($rawToken, PASSWORD_DEFAULT);
$expiresAt = date('Y-m-d H:i:s', time() + 3600);

$insertStmt = $conn->prepare(
    "INSERT INTO PASSWORD_RESET_TOKEN (account_type, account_identifier, reset_email, token_hash, expires_at)
     VALUES (?, ?, ?, ?, ?)"
);
$insertStmt->bind_param("sssss", $accountType, $accountIdentifier, $email, $tokenHash, $expiresAt);
$insertStmt->execute();
$insertStmt->close();

$resetLink = sprintf(
    '%s://%s%s/reset_password.php?token=%s',
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http',
    $_SERVER['HTTP_HOST'] ?? 'localhost',
    rtrim(dirname($_SERVER['PHP_SELF']), '/\\'),
    urlencode($rawToken)
);

$_SESSION['password_reset_preview_link'] = $resetLink;

$subject = 'Game Console Exchange Password Reset';
$message = "Use this link to reset your password: " . $resetLink . "\n\nThis link will expire in 1 hour.";
$headers = "From: noreply@gameconsoleexchange.com";
@mail($email, $subject, $message, $headers);

finish_reset_request();
?>
