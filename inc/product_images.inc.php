<?php

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

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $fileCount = count($files['name']);

    for ($index = 0; $index < $fileCount; $index++) {
        if (($files['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if (($files['error'][$index] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $errors[] = 'One of the images could not be uploaded.';
            continue;
        }

        $originalName = $files['name'][$index] ?? '';
        $tmpName = $files['tmp_name'][$index] ?? '';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            $errors[] = 'Only JPG, PNG, and WEBP images are allowed.';
            continue;
        }

        $safeFileName = uniqid('product_', true) . '.' . $extension;
        $absolutePath = $uploadDirAbsolute . '/' . $safeFileName;
        $relativePath = $uploadDirRelative . '/' . $safeFileName;

        if (!move_uploaded_file($tmpName, $absolutePath)) {
            $errors[] = 'Failed to move one of the uploaded images.';
            continue;
        }

        $sortOrder = count($savedPaths);
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

function delete_product_image_by_id(mysqli $conn, int $imageId, string $projectRoot): bool
{
    if (!product_images_table_available($conn)) {
        return false;
    }

    $stmt = $conn->prepare("SELECT image_path FROM PRODUCT_IMAGE WHERE image_id = ?");
    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    $stmt->close();

    if (!$image) {
        return false;
    }

    $deleteStmt = $conn->prepare("DELETE FROM PRODUCT_IMAGE WHERE image_id = ?");
    if ($deleteStmt === false) {
        return false;
    }

    $deleteStmt->bind_param("i", $imageId);
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
