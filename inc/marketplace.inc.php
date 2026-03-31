<?php

function marketplace_table_exists(mysqli $conn, string $tableName): bool
{
    static $cache = [];

    if (array_key_exists($tableName, $cache)) {
        return $cache[$tableName];
    }

    $safeName = $conn->real_escape_string($tableName);
    $result = $conn->query("SHOW TABLES LIKE '{$safeName}'");
    $exists = $result !== false && $result->num_rows > 0;

    if ($result !== false) {
        $result->free();
    }

    $cache[$tableName] = $exists;
    return $exists;
}

function current_username(): ?string
{
    return $_SESSION['logged_in_user'] ?? null;
}

function current_user_role(): string
{
    return $_SESSION['user_role'] ?? 'user';
}

function can_edit_product(array $product): bool
{
    $currentUser = current_username();
    return $currentUser !== null && ($product['seller_username'] ?? '') === $currentUser;
}

function can_delete_product(array $product): bool
{
    $currentUser = current_username();
    $role = current_user_role();
    return $currentUser !== null && (($product['seller_username'] ?? '') === $currentUser || $role === 'moderator');
}

function get_product_rating_summary(mysqli $conn, int $productId): array
{
    if (!marketplace_table_exists($conn, 'PRODUCT_REVIEW')) {
        return ['average_rating' => null, 'review_count' => 0];
    }

    $stmt = $conn->prepare(
        "SELECT AVG(rating) AS average_rating, COUNT(*) AS review_count
         FROM PRODUCT_REVIEW
         WHERE product_id = ?"
    );

    if ($stmt === false) {
        return ['average_rating' => null, 'review_count' => 0];
    }

    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $summary = $result->fetch_assoc() ?: [];
    $stmt->close();

    return [
        'average_rating' => $summary['average_rating'] !== null ? (float) $summary['average_rating'] : null,
        'review_count' => (int) ($summary['review_count'] ?? 0),
    ];
}

function get_product_reviews(mysqli $conn, int $productId): array
{
    if (!marketplace_table_exists($conn, 'PRODUCT_REVIEW')) {
        return [];
    }

    $stmt = $conn->prepare(
        "SELECT review_id, reviewer_username, rating, review_text, created_at
         FROM PRODUCT_REVIEW
         WHERE product_id = ?
         ORDER BY created_at DESC, review_id DESC"
    );

    if ($stmt === false) {
        return [];
    }

    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }

    $stmt->close();
    return $reviews;
}

function get_user_review_for_product(mysqli $conn, int $productId, string $username): ?array
{
    if (!marketplace_table_exists($conn, 'PRODUCT_REVIEW')) {
        return null;
    }

    $stmt = $conn->prepare(
        "SELECT review_id, rating, review_text
         FROM PRODUCT_REVIEW
         WHERE product_id = ? AND reviewer_username = ?"
    );

    if ($stmt === false) {
        return null;
    }

    $stmt->bind_param("is", $productId, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $review = $result->fetch_assoc() ?: null;
    $stmt->close();

    return $review;
}

function upsert_product_review(mysqli $conn, int $productId, string $username, int $rating, string $reviewText): bool
{
    if (!marketplace_table_exists($conn, 'PRODUCT_REVIEW')) {
        return false;
    }

    $existing = get_user_review_for_product($conn, $productId, $username);

    if ($existing) {
        return false;
    }

    $stmt = $conn->prepare(
        "INSERT INTO PRODUCT_REVIEW (product_id, reviewer_username, rating, review_text)
         VALUES (?, ?, ?, ?)"
    );

    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param("isis", $productId, $username, $rating, $reviewText);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function user_has_favorited_product(mysqli $conn, int $productId, string $username): bool
{
    if (!marketplace_table_exists($conn, 'PRODUCT_FAVORITE')) {
        return false;
    }

    $stmt = $conn->prepare(
        "SELECT favorite_id
         FROM PRODUCT_FAVORITE
         WHERE product_id = ? AND username = ?"
    );

    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param("is", $productId, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();

    return $exists;
}

function toggle_product_favorite(mysqli $conn, int $productId, string $username): bool
{
    if (!marketplace_table_exists($conn, 'PRODUCT_FAVORITE')) {
        return false;
    }

    if (user_has_favorited_product($conn, $productId, $username)) {
        $stmt = $conn->prepare(
            "DELETE FROM PRODUCT_FAVORITE
             WHERE product_id = ? AND username = ?"
        );

        if ($stmt === false) {
            return false;
        }

        $stmt->bind_param("is", $productId, $username);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    $stmt = $conn->prepare(
        "INSERT INTO PRODUCT_FAVORITE (product_id, username)
         VALUES (?, ?)"
    );

    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param("is", $productId, $username);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function get_favorite_count(mysqli $conn, int $productId): int
{
    if (!marketplace_table_exists($conn, 'PRODUCT_FAVORITE')) {
        return 0;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) AS favorite_count FROM PRODUCT_FAVORITE WHERE product_id = ?");
    if ($stmt === false) {
        return 0;
    }

    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc() ?: [];
    $stmt->close();

    return (int) ($row['favorite_count'] ?? 0);
}

function purchase_exists_for_session(mysqli $conn, string $stripeSessionId): bool
{
    if (!marketplace_table_exists($conn, 'PRODUCT_PURCHASE')) {
        return false;
    }

    $stmt = $conn->prepare("SELECT purchase_id FROM PRODUCT_PURCHASE WHERE stripe_session_id = ?");
    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param("s", $stripeSessionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

function create_purchase(mysqli $conn, array $product, string $buyerUsername, ?string $stripeSessionId = null): bool
{
    if (!marketplace_table_exists($conn, 'PRODUCT_PURCHASE')) {
        return false;
    }

    $sellerUsername = $product['seller_username'] ?? '';
    $pricePaid = (float) ($product['price'] ?? 0);
    $productId = (int) ($product['product_id'] ?? 0);

    if ($stripeSessionId !== null) {
        $stmt = $conn->prepare(
            "INSERT INTO PRODUCT_PURCHASE (product_id, buyer_username, seller_username, price_paid, stripe_session_id)
             VALUES (?, ?, ?, ?, ?)"
        );
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO PRODUCT_PURCHASE (product_id, buyer_username, seller_username, price_paid)
             VALUES (?, ?, ?, ?)"
        );
    }

    if ($stmt === false) {
        return false;
    }

    if ($stripeSessionId !== null) {
        $stmt->bind_param("issds", $productId, $buyerUsername, $sellerUsername, $pricePaid, $stripeSessionId);
    } else {
        $stmt->bind_param("issd", $productId, $buyerUsername, $sellerUsername, $pricePaid);
    }

    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function get_user_purchases(mysqli $conn, string $username): array
{
    if (!marketplace_table_exists($conn, 'PRODUCT_PURCHASE')) {
        return [];
    }

    $stmt = $conn->prepare(
        "SELECT pp.purchase_id, pp.price_paid, pp.purchased_at, pp.order_status, pp.tracking_number, p.product_id, p.product_name, p.category
         FROM PRODUCT_PURCHASE pp
         JOIN PRODUCT p ON p.product_id = pp.product_id
         WHERE pp.buyer_username = ?
         ORDER BY pp.purchased_at DESC, pp.purchase_id DESC"
    );

    if ($stmt === false) {
        return [];
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $purchases = [];
    while ($row = $result->fetch_assoc()) {
        $purchases[] = $row;
    }

    $stmt->close();
    return $purchases;
}

function get_user_favorites(mysqli $conn, string $username): array
{
    if (!marketplace_table_exists($conn, 'PRODUCT_FAVORITE')) {
        return [];
    }

    $stmt = $conn->prepare(
        "SELECT pf.favorite_id, pf.created_at, p.product_id, p.product_name, p.category, p.price
         FROM PRODUCT_FAVORITE pf
         JOIN PRODUCT p ON p.product_id = pf.product_id
         WHERE pf.username = ?
         ORDER BY pf.created_at DESC, pf.favorite_id DESC"
    );

    if ($stmt === false) {
        return [];
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $favorites = [];
    while ($row = $result->fetch_assoc()) {
        $favorites[] = $row;
    }

    $stmt->close();
    return $favorites;
}

function get_user_products(mysqli $conn, string $username): array
{
    $stmt = $conn->prepare(
        "SELECT product_id, product_name, category, price
         FROM PRODUCT
         WHERE seller_username = ?
         ORDER BY product_id DESC"
    );

    if ($stmt === false) {
        return [];
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $stmt->close();
    return $products;
}

function cart_item_ids(): array
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    return array_values(array_unique(array_map('intval', $_SESSION['cart'])));
}

function cart_count(): int
{
    return count(cart_item_ids());
}

function cart_contains(int $productId): bool
{
    return in_array($productId, cart_item_ids(), true);
}

function add_to_cart(int $productId): void
{
    $items = cart_item_ids();
    if (!in_array($productId, $items, true)) {
        $items[] = $productId;
    }
    $_SESSION['cart'] = $items;
}

function remove_from_cart(int $productId): void
{
    $_SESSION['cart'] = array_values(array_filter(
        cart_item_ids(),
        static fn (int $id): bool => $id !== $productId
    ));
}

function clear_cart(): void
{
    $_SESSION['cart'] = [];
}

function fetch_products_by_ids(mysqli $conn, array $productIds): array
{
    $productIds = array_values(array_filter(array_map('intval', $productIds), static fn (int $id): bool => $id > 0));
    if (empty($productIds)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $types = str_repeat('i', count($productIds));

    $stmt = $conn->prepare("SELECT * FROM PRODUCT WHERE product_id IN ($placeholders)");
    if ($stmt === false) {
        return [];
    }

    $stmt->bind_param($types, ...$productIds);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[(int) $row['product_id']] = $row;
    }

    $stmt->close();

    $ordered = [];
    foreach ($productIds as $id) {
        if (isset($products[$id])) {
            $ordered[] = $products[$id];
        }
    }

    return $ordered;
}
?>
