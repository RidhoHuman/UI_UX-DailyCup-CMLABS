<?php
$pageTitle = 'Add Product';
$isAdminPage = true;
requireAdmin();

require_once __DIR__ . '/../../includes/header.php';

$db = getDB();
$categories = getCategories(false);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $categoryId = intval($_POST['category_id'] ?? 0);
        $description = sanitizeInput($_POST['description'] ?? '');
        $basePrice = floatval($_POST['base_price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($name) || $categoryId == 0 || $basePrice <= 0) {
            $error = 'Please fill all required fields';
        } else {
            $stmt = $db->prepare("INSERT INTO products (category_id, name, description, base_price, stock, is_featured, is_active) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$categoryId, $name, $description, $basePrice, $stock, $isFeatured, $isActive])) {
                $success = 'Product created successfully';
                header('Location: ' . SITE_URL . '/admin/products/');
                exit;
            } else {
                $error = 'Failed to create product';
            }
        }
    }
}
?>

<div class="admin-layout">
    <?php require_once __DIR__ . '/../../includes/sidebar_admin.php'; ?>
    
    <div class="admin-main">
        <div class="page-header">
            <h1 class="page-title"><i class="bi bi-plus-circle"></i> Add New Product</h1>
            <a href="<?php echo SITE_URL; ?>/admin/products/" class="btn btn-outline-coffee">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="admin-form">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Base Price (Rp) *</label>
                                <input type="number" name="base_price" class="form-control" step="0.01" min="0" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock</label>
                                <input type="number" name="stock" class="form-control" min="0" value="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Product Image</label>
                            <div class="image-upload-area">
                                <i class="bi bi-cloud-upload"></i>
                                <p>Click to upload image</p>
                                <input type="file" name="image" accept="image/*" class="d-none">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                                <label class="form-check-label" for="is_featured">
                                    Featured Product
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-coffee">
                        <i class="bi bi-check-circle"></i> Create Product
                    </button>
                    <a href="<?php echo SITE_URL; ?>/admin/products/" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
