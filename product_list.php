<?php
session_start();
include "inc/db.inc.php";
include "inc/product_images.inc.php";

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$sort = $_GET['sort'] ?? 'newest';

$sql = "SELECT product_id, product_name, category, price, product_desc FROM PRODUCT WHERE 1=1";
$types = '';
$params = [];

if ($search !== '') {
    $sql .= " AND product_name LIKE ?";
    $types .= 's';
    $params[] = '%' . $search . '%';
}

if ($category !== '') {
    $sql .= " AND category = ?";
    $types .= 's';
    $params[] = $category;
}

// Apply the Sort Logic
$order_sql = " ORDER BY product_id DESC"; // Default
if ($sort === 'oldest') $order_sql = " ORDER BY product_id ASC";
if ($sort === 'price_asc') $order_sql = " ORDER BY price ASC";
if ($sort === 'price_desc') $order_sql = " ORDER BY price DESC";
$sql .= $order_sql;

$stmt = $conn->prepare($sql);
if ($stmt === false) die('Query preparation failed: ' . $conn->error);
if (!empty($params)) $stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();

// Get categories for the custom dropdown
$categoriesResult = $conn->query("SELECT DISTINCT category FROM PRODUCT WHERE category IS NOT NULL AND category <> '' ORDER BY category");
$categories = [];
if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Game Console Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "inc/navbar.inc.php"; ?>

    <main class="py-5">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h2 mb-1">Product Marketplace</h1>
                    <p class="text-muted mb-0">Browse all listed consoles and gaming products.</p>
                </div>
                
                <?php if (isset($_SESSION['logged_in_user'])): ?>
                    <a href="add_product.php" class="btn btn-primary shadow-sm px-4">Add Product</a>
                <?php endif; ?>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <form method="get" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search" class="form-label fw-bold">Search</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Search by product name..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Category</label>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start bg-white" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="categoryDropdownBtn">
                                    <?= htmlspecialchars($category === '' ? 'All categories' : $category) ?>
                                </button>
                                <ul class="dropdown-menu w-100 p-2 shadow-sm">
                                    <li>
                                        <input type="text" class="form-control mb-2" id="categorySearchInput" placeholder="Type to filter..." onclick="event.stopPropagation()">
                                    </li>
                                    <li><a class="dropdown-item category-item" href="#" data-value="">All categories</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php foreach ($categories as $cat): ?>
                                        <li><a class="dropdown-item category-item" href="#" data-value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <input type="hidden" name="category" id="categoryHiddenInput" value="<?= htmlspecialchars($category) ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="sort" class="form-label fw-bold">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest to Oldest</option>
                                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest to Newest</option>
                                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Lowest to Highest</option>
                                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: Highest to Lowest</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary fw-semibold">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-4">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body d-flex flex-column">
                                    <h2 class="h5 mb-2 fw-bold"><?= htmlspecialchars($product['product_name']) ?></h2>
                                    <p class="text-muted small mb-3"><?= htmlspecialchars($product['category']) ?></p>
                                    
                                    <?php $primaryImage = get_primary_product_image($conn, (int) $product['product_id']); ?>
                                    <?php if ($primaryImage): ?>
                                        <img src="<?= htmlspecialchars($primaryImage) ?>" class="card-img-top mb-3 rounded" alt="<?= htmlspecialchars($product['product_name']) ?>" style="height: 220px; object-fit: cover;">
                                    <?php endif; ?>
                                    
                                    <p class="text-muted mb-3 flex-grow-1">
                                        <?= nl2br(htmlspecialchars($product['product_desc'] ?? 'No description provided.')) ?>
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                        <span class="badge text-bg-warning fs-5 px-3 py-2 fw-bold shadow-sm text-dark">$<?= number_format((float) $product['price'], 2) ?></span>
                                        <a href="product_detail.php?id=<?= $product['product_id'] ?>" class="btn btn-outline-primary px-4 fw-semibold">View</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info py-4 text-center fs-5 border-0 shadow-sm">
                            No products found matching your search.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('categorySearchInput');
            const items = document.querySelectorAll('.category-item');
            const hiddenInput = document.getElementById('categoryHiddenInput');
            const dropdownBtn = document.getElementById('categoryDropdownBtn');

            // 1. Filter as user types
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                items.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    // Keep "All categories" always visible, filter the rest
                    if (item.getAttribute('data-value') === '') return; 
                    
                    if (text.includes(term)) {
                        item.parentElement.style.display = 'block';
                    } else {
                        item.parentElement.style.display = 'none';
                    }
                });
            });

            // 2. Select an option
            items.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const val = this.getAttribute('data-value');
                    const text = this.textContent;
                    
                    hiddenInput.value = val;
                    dropdownBtn.textContent = text;
                });
            });
        });
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>