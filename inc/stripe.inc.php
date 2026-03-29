<?php

include_once __DIR__ . '/db.inc.php';
include_once __DIR__ . '/product_images.inc.php';

function stripe_secret_key(): string
{
    return getenv('STRIPE_SECRET_KEY') ?: 'sk_test_replace_me';
}

function stripe_publishable_key(): string
{
    return getenv('STRIPE_PUBLISHABLE_KEY') ?: 'pk_test_replace_me';
}

function stripe_configured(): bool
{
    $secret = stripe_secret_key();
    $publishable = stripe_publishable_key();

    return $secret !== 'sk_test_replace_me' && $publishable !== 'pk_test_replace_me';
}

function app_base_url(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

    if ($scriptDir === '' || $scriptDir === '.') {
        return $scheme . '://' . $host;
    }

    return $scheme . '://' . $host . $scriptDir;
}

function stripe_api_request(string $method, string $endpoint, array $params = []): array
{
    $secretKey = stripe_secret_key();
    if (!stripe_configured()) {
        throw new RuntimeException('Stripe keys are not configured.');
    }

    $url = 'https://api.stripe.com/v1/' . ltrim($endpoint, '/');
    $ch = curl_init();

    if ($method === 'GET' && !empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => $secretKey . ':',
        CURLOPT_HTTPHEADER => ['Stripe-Version: 2025-06-30.basil'],
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    }

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Stripe request failed: ' . $error);
    }

    curl_close($ch);

    $decoded = json_decode($response, true);
    if ($statusCode >= 400) {
        $message = $decoded['error']['message'] ?? 'Stripe API error.';
        throw new RuntimeException($message);
    }

    if (!is_array($decoded)) {
        throw new RuntimeException('Invalid response from Stripe.');
    }

    return $decoded;
}

function stripe_create_checkout_session(array $product, string $buyerUsername): array
{
    $productId = (int) ($product['product_id'] ?? 0);
    $productName = (string) ($product['product_name'] ?? 'Product');
    $price = (float) ($product['price'] ?? 0);
    $productDescription = trim((string) ($product['product_desc'] ?? ''));
    $sellerUsername = (string) ($product['seller_username'] ?? '');
    $baseUrl = app_base_url();
    $unitAmount = (int) round($price * 100);
    $lineItemParams = [
        'mode' => 'payment',
        'success_url' => $baseUrl . '/stripe_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $baseUrl . '/stripe_cancel.php?product_id=' . $productId,
        'line_items[0][quantity]' => 1,
        'line_items[0][price_data][currency]' => 'sgd',
        'line_items[0][price_data][unit_amount]' => $unitAmount,
        'line_items[0][price_data][product_data][name]' => $productName,
        'metadata[product_id]' => (string) $productId,
        'metadata[buyer_username]' => $buyerUsername,
        'metadata[seller_username]' => $sellerUsername,
    ];

    if ($productDescription !== '') {
        $shortDescription = mb_substr($productDescription, 0, 200);
        $lineItemParams['line_items[0][price_data][product_data][description]'] = $shortDescription;
    }

    global $conn;
    if (isset($conn) && $conn instanceof mysqli) {
        $primaryImage = get_primary_product_image($conn, $productId);
        if ($primaryImage) {
            $absoluteImageUrl = $baseUrl . '/' . ltrim($primaryImage, '/');
            $lineItemParams['line_items[0][price_data][product_data][images][0]'] = $absoluteImageUrl;
        }
    }

    return stripe_api_request('POST', 'checkout/sessions', $lineItemParams);
}

function stripe_create_checkout_session_for_products(array $products, string $buyerUsername): array
{
    $baseUrl = app_base_url();
    $params = [
        'mode' => 'payment',
        'success_url' => $baseUrl . '/stripe_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $baseUrl . '/cart.php?error=payment_cancelled',
        'metadata[buyer_username]' => $buyerUsername,
        'metadata[product_ids]' => implode(',', array_map(static fn (array $p): string => (string) ((int) $p['product_id']), $products)),
    ];

    foreach (array_values($products) as $index => $product) {
        $productId = (int) ($product['product_id'] ?? 0);
        $productName = (string) ($product['product_name'] ?? 'Product');
        $price = (float) ($product['price'] ?? 0);
        $productDescription = trim((string) ($product['product_desc'] ?? ''));
        $unitAmount = (int) round($price * 100);

        $params["line_items[{$index}][quantity]"] = 1;
        $params["line_items[{$index}][price_data][currency]"] = 'sgd';
        $params["line_items[{$index}][price_data][unit_amount]"] = $unitAmount;
        $params["line_items[{$index}][price_data][product_data][name]"] = $productName;

        if ($productDescription !== '') {
            $params["line_items[{$index}][price_data][product_data][description]"] = mb_substr($productDescription, 0, 200);
        }

        global $conn;
        if (isset($conn) && $conn instanceof mysqli) {
            $primaryImage = get_primary_product_image($conn, $productId);
            if ($primaryImage) {
                $params["line_items[{$index}][price_data][product_data][images][0]"] = $baseUrl . '/' . ltrim($primaryImage, '/');
            }
        }
    }

    return stripe_api_request('POST', 'checkout/sessions', $params);
}

function stripe_retrieve_checkout_session(string $sessionId): array
{
    return stripe_api_request('GET', 'checkout/sessions/' . rawurlencode($sessionId));
}
?>
