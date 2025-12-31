<?php
$pageTitle = 'Menu';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

// Get filter parameters
$categoryId = $_GET['category'] ?? null;
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Build query
$sql = "SELECT p.*, c.name as category_name FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1";
$params = [];

if ($categoryId) {
    $sql .= " AND p.category_id = ?";
    $params[] = $categoryId;
}

if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Count total
$countSql = str_replace("SELECT p.*, c.name as category_name", "SELECT COUNT(*)", $sql);
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$totalProducts = $stmt->fetchColumn();

// Pagination
$pagination = getPagination($totalProducts, $page);

// Get products
$sql .= " ORDER BY p.is_featured DESC, p.name ASC LIMIT ? OFFSET ?";
$params[] = $pagination['items_per_page'];
$params[] = $pagination['offset'];

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories
$categories = getCategories();
?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-coffee text-white">
                    <h5 class="mb-0"><i class="bi bi-filter"></i> Filter</h5>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <form method="GET" action="" class="mb-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-coffee btn-sm w-100 mt-2">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                    
                    <!-- Categories -->
                    <h6 class="fw-bold mb-3">Kategori</h6>
                    <div class="list-group">
                        <a href="<?php echo SITE_URL; ?>/customer/menu.php" 
                           class="list-group-item list-group-item-action <?php echo !$categoryId ? 'active' : ''; ?>">
                            Semua Produk
                        </a>
                        <?php foreach ($categories as $category): ?>
                        <a href="<?php echo SITE_URL; ?>/customer/menu.php?category=<?php echo $category['id']; ?>" 
                           class="list-group-item list-group-item-action <?php echo $categoryId == $category['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Menu Kami</h2>
                <span class="text-muted"><?php echo $totalProducts; ?> produk</span>
            </div>
            
            <?php if (count($products) > 0): ?>
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card product-card h-100">
                        <?php if ($product['is_featured']): ?>
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-star-fill"></i> Featured
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <img src="<?php echo SITE_URL; ?>/assets/images/products/placeholder.jpg" 
                             class="product-card-img" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        
                        <div class="product-card-body">
                            <span class="category-badge"><?php echo htmlspecialchars($product['category_name']); ?></span>
                            <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="product-description text-truncate-3">
                                <?php echo htmlspecialchars($product['description']); ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="product-price"><?php echo formatCurrency($product['base_price']); ?></div>
                                <div class="btn-group">
                                    <?php if (isLoggedIn()): ?>
                                    <button class="btn btn-outline-danger btn-sm favorite-icon" 
                                            onclick="window.DailyCup.toggleFavorite(<?php echo $product['id']; ?>, this)">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                    <?php endif; ?>
                                    <a href="<?php echo SITE_URL; ?>/customer/product_detail.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-coffee btn-sm">
                                        <i class="bi bi-cart-plus"></i> Pesan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?>">
                            Previous
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $page >= $pagination['total_pages'] ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?>">
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-search" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">Produk tidak ditemukan</h4>
                <p class="text-muted">Coba kata kunci lain atau lihat semua produk</p>
                <a href="<?php echo SITE_URL; ?>/customer/menu.php" class="btn btn-coffee">Lihat Semua</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
