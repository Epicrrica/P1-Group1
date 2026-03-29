<?php

require_once __DIR__ . '/security.inc.php';

function product_images_table_available(mysqli $conn): bool
{
    static $available = null;

    if ($available !== null) {
        return $available;
    }

    $result = $conn->query("SHOW TABLES LIKE 'PRODUCT_IMAGE'");
    $available = $result !== false && $result->num_rows > 0;

    if ($result !== false) {
        $result->free();
    }

    return $available;
}

function get_product_images(mysqli $conn, int $productId): array
{
    if (!product_images_table_available($conn)) {
        return [];
    }

    $stmt = $conn->prepare(
        "SELECT image_id, image_path, sort_order
         FROM PRODUCT_IMAGE
         WHERE product_id = ?
         ORDER BY sort_order ASC, image_id ASC"
    );

    if ($stmt === false) {
        return [];
    }

    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    $images = [];
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }

    $stmt->close();

    return $images;
}

function get_primary_product_image(mysqli $conn, int $productId): ?string
{
    $images = get_product_images($conn, $productId);
    return $images[0]['image_path'] ?? null;
}

function save_uploaded_product_images(mysqli $conn, int $productId, array $files, string $uploadDirAbsolute, string $uploadDirRelative): array
{
    $savedPaths = [];
    $errors = [];

    if (!product_images_table_available($conn)) {
        if (!empty($files['name'][0])) {
            $errors[] = 'Image uploads need the PRODUCT_IMAGE table. Run sql/product_images.sql first.';
        }
        return [$savedPaths, $errors];
    }

    if (empty($files['name'][0])) {
        return [$savedPaths, $errors];
    }

    if (!is_dir($uploadDirAbsolute) && !mkdir($uploadDirAbsolute, 0775, true) && !is_dir($uploadDirAbsolute)) {
        $errors[] = 'Could not create the product upload directory.';
        return [$savedPaths, $errors];
    }

    $fileCount = count($files['name']);
    $existingSortOrder = 0;

    $sortStmt = $conn->prepare("SELECT COALESCE(MAX(sort_order), -1) AS max_sort_order FROM PRODUCT_IMAGE WHERE product_id = ?");
    if ($sortStmt !== false) {
        $sortStmt->bind_param("i", $productId);
        $sortStmt->execute();
        $sortResult = $sortStmt->get_result();
        $sortRow = $sortResult->fetch_assoc();
        $existingSortOrder = ((int) ($sortRow['max_sort_order'] ?? -1)) + 1;
        $sortStmt->close();
    }

    for ($index = 0; $index < $fileCount; $index++) {
        if (($files['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if (($files['error'][$index] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $errors[] = 'One of the images could not be uploaded.';
            continue;
        }

        $tmpName = $files['tmp_name'][$index] ?? '';
        $singleFile = [
            'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'tmp_name' => $tmpName,
            'size' => $files['size'][$index] ?? 0,
        ];
        $validationError = null;
        $extension = validate_uploaded_image_file($singleFile, MAX_PRODUCT_IMAGE_BYTES, $validationError);
        if ($extension === null) {
            $errors[] = $validationError ?? 'One of the product images is invalid.';
            continue;
        }

        $safeFileName = uniqid('product_', true) . '.' . $extension;
        $absolutePath = $uploadDirAbsolute . '/' . $safeFileName;
        $relativePath = $uploadDirRelative . '/' . $safeFileName;

        if (!move_uploaded_file($tmpName, $absolutePath)) {
            $errors[] = 'Failed to move one of the uploaded images.';
            continue;
        }

        $sortOrder = $existingSortOrder + count($savedPaths);
        $stmt = $conn->prepare(
            "INSERT INTO PRODUCT_IMAGE (product_id, image_path, sort_order)
             VALUES (?, ?, ?)"
        );

        if ($stmt === false) {
            @unlink($absolutePath);
            $errors[] = 'Could not save image information to the database.';
            continue;
        }

        $stmt->bind_param("isi", $productId, $relativePath, $sortOrder);
        $stmt->execute();
        $stmt->close();

        $savedPaths[] = $relativePath;
    }

    return [$savedPaths, $errors];
}

function delete_product_image_by_id(mysqli $conn, int $productId, int $imageId, string $projectRoot): bool
{
    if (!product_images_table_available($conn)) {
        return false;
    }

    $stmt = $conn->prepare("SELECT image_path FROM PRODUCT_IMAGE WHERE image_id = ? AND product_id = ?");
    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param("ii", $imageId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    $stmt->close();

    if (!$image) {
        return false;
    }

    $deleteStmt = $conn->prepare("DELETE FROM PRODUCT_IMAGE WHERE image_id = ? AND product_id = ?");
    if ($deleteStmt === false) {
        return false;
    }

    $deleteStmt->bind_param("ii", $imageId, $productId);
    $deleteStmt->execute();
    $deleteStmt->close();

    $absolutePath = $projectRoot . '/' . ltrim($image['image_path'], '/');
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }

    return true;
}

function delete_all_product_images(mysqli $conn, int $productId, string $projectRoot): void
{
    if (!product_images_table_available($conn)) {
        return;
    }

    $images = get_product_images($conn, $productId);
    foreach ($images as $image) {
        $absolutePath = $projectRoot . '/' . ltrim($image['image_path'], '/');
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    $stmt = $conn->prepare("DELETE FROM PRODUCT_IMAGE WHERE product_id = ?");
    if ($stmt === false) {
        return;
    }

    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();
}
?>
