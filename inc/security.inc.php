<?php

declare(strict_types=1);

const SESSION_TIMEOUT_SECONDS = 1800;
const MAX_PROFILE_IMAGE_BYTES = 2 * 1024 * 1024;
const MAX_PRODUCT_IMAGE_BYTES = 5 * 1024 * 1024;
const ALLOWED_IMAGE_MIME_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

function security_bootstrap_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        security_enforce_session_timeout();
        return;
    }

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
    security_enforce_session_timeout();
}

function security_enforce_session_timeout(): void
{
    $now = time();
    $lastActivity = (int) ($_SESSION['last_activity'] ?? 0);

    if ($lastActivity > 0 && ($now - $lastActivity) > SESSION_TIMEOUT_SECONDS) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
        session_start();
    }

    $_SESSION['last_activity'] = $now;
}

function security_finalize_login(string $username, string $role): void
{
    session_regenerate_id(true);
    $_SESSION['logged_in_user'] = $username;
    $_SESSION['user_role'] = $role;
    $_SESSION['last_activity'] = time();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf_or_reject(): void
{
    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!is_string($token) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
        http_response_code(403);
        exit('Invalid security token. Please refresh the page and try again.');
    }
}

function uploaded_image_contents(array $file, int $maxBytes, ?string &$error = null): ?string
{
    $error = null;

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $error = 'Image upload failed.';
        return null;
    }

    $tmpName = $file['tmp_name'] ?? '';
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        $error = 'Invalid upload detected.';
        return null;
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        $error = 'Image exceeds the allowed file size.';
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpName);
    if (!isset(ALLOWED_IMAGE_MIME_TYPES[$mimeType])) {
        $error = 'Only JPG, PNG, or WEBP images are allowed.';
        return null;
    }

    $contents = file_get_contents($tmpName);
    if ($contents === false || @getimagesizefromstring($contents) === false) {
        $error = 'Uploaded file is not a valid image.';
        return null;
    }

    return $contents;
}

function validate_uploaded_image_file(array $file, int $maxBytes, ?string &$error = null): ?string
{
    $error = null;

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $error = 'Image upload failed.';
        return null;
    }

    $tmpName = $file['tmp_name'] ?? '';
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        $error = 'Invalid upload detected.';
        return null;
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        $error = 'Image exceeds the allowed file size.';
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpName);
    if (!isset(ALLOWED_IMAGE_MIME_TYPES[$mimeType])) {
        $error = 'Only JPG, PNG, or WEBP images are allowed.';
        return null;
    }

    if (@getimagesize($tmpName) === false) {
        $error = 'Uploaded file is not a valid image.';
        return null;
    }

    return ALLOWED_IMAGE_MIME_TYPES[$mimeType];
}
