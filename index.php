<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Get featured products
$db = getDB();
$stmt = $db->query("SELECT p.*, c.name as category_name FROM products p 
                    JOIN categories c ON p.category_id = c.id 
                    WHERE p.is_featured = 1 AND p.is_active = 1 
                    LIMIT 6");
$featuredProducts = $stmt->fetchAll();

// Get active categories
$categories = getCategories();

// Get partner discounts
$stmt = $db->query("SELECT * FROM partner_discounts WHERE is_active = 1 AND end_date >= NOW() LIMIT 3");
$partnerDiscounts = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-3 fw-bold mb-4 fade-in">
                    Nikmati Kopi Terbaik<br>
                    <span class="text-secondary">Setiap Hari</span>
                </h1>
                <p class="lead mb-4">
                    DailyCup menghadirkan pengalaman kopi premium dengan biji kopi pilihan berkualitas tinggi. Pesan sekarang dan dapatkan poin loyalitas!
                </p>
                <div class="d-flex gap-3">
                    <a href="<?php echo SITE_URL; ?>/customer/menu.php" class="btn btn-light btn-lg">
                        <i class="bi bi-cup-hot"></i> Lihat Menu
                    </a>
                    <?php if (!isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-outline-light btn-lg">
                        Daftar Sekarang
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 text-center mt-5 mt-lg-0">
                <i class="bi bi-cup-hot-fill" style="font-size: 15rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section-padding">
    <div class="container">
        <h2 class="section-title">Kategori Menu</h2>
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
            <div class="col-md-6 col-lg-3">
                <a href="<?php echo SITE_URL; ?>/customer/menu.php?category=<?php echo $category['id']; ?>" 
                   class="text-decoration-none">
                    <div class="card product-card text-center">
                        <div class="card-body p-4">
                            <i class="bi bi-cup-straw text-coffee" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 mb-2"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="text-muted small">
                                <?php echo htmlspecialchars($category['description']); ?>
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="section-padding bg-white">
    <div class="container">
        <h2 class="section-title">Produk Unggulan</h2>
        <p class="section-subtitle">Produk terpopuler dan paling disukai pelanggan kami</p>
        
        <div class="row g-4">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card product-card">
                    <img src="<?php echo SITE_URL; ?>/assets/images/products/placeholder.jpg" 
                         class="product-card-img" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="product-card-body">
                        <span class="category-badge"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="product-description text-truncate-2">
                            <?php echo htmlspecialchars($product['description']); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="product-price"><?php echo formatCurrency($product['base_price']); ?></div>
                            <a href="<?php echo SITE_URL; ?>/customer/product_detail.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-coffee btn-sm">
                                <i class="bi bi-cart-plus"></i> Pesan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?php echo SITE_URL; ?>/customer/menu.php" class="btn btn-outline-coffee btn-lg">
                Lihat Semua Menu <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Partner Discounts Section -->
<?php if (count($partnerDiscounts) > 0): ?>
<section class="section-padding">
    <div class="container">
        <h2 class="section-title">Diskon Partner</h2>
        <p class="section-subtitle">Nikmati diskon spesial dari partner kami</p>
        
        <div class="row g-4">
            <?php foreach ($partnerDiscounts as $discount): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-award-fill text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="card-title"><?php echo htmlspecialchars($discount['partner_name']); ?></h5>
                        <h3 class="text-coffee mb-3">
                            <?php 
                            if ($discount['discount_type'] == 'percentage') {
                                echo $discount['discount_value'] . '%';
                            } else {
                                echo formatCurrency($discount['discount_value']);
                            }
                            ?>
                            <small class="d-block fs-6 text-muted">OFF</small>
                        </h3>
                        <p class="text-muted"><?php echo htmlspecialchars($discount['description']); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Features Section -->
<section class="section-padding bg-white">
    <div class="container">
        <h2 class="section-title">Kenapa DailyCup?</h2>
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="p-4">
                    <i class="bi bi-cup-hot text-coffee" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Kopi Premium</h5>
                    <p class="text-muted">Biji kopi berkualitas tinggi dari petani lokal terpilih</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-4">
                    <i class="bi bi-truck text-coffee" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Pengiriman Cepat</h5>
                    <p class="text-muted">Pesanan diantar dengan cepat dan aman ke lokasi Anda</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-4">
                    <i class="bi bi-star text-coffee" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Loyalty Points</h5>
                    <p class="text-muted">Kumpulkan poin dan tukar dengan diskon menarik</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-4">
                    <i class="bi bi-headset text-coffee" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Layanan Terbaik</h5>
                    <p class="text-muted">Tim kami siap melayani dengan ramah dan profesional</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="section-padding">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="fw-bold mb-4">Tentang DailyCup</h2>
                <p class="lead mb-3">
                    DailyCup adalah coffee shop yang berdedikasi untuk menyajikan kopi berkualitas tinggi dengan pelayanan terbaik.
                </p>
                <p class="text-muted mb-3">
                    Kami percaya bahwa setiap cangkir kopi adalah sebuah pengalaman yang istimewa. Dengan biji kopi pilihan dari berbagai daerah di Indonesia, kami berkomitmen memberikan cita rasa yang autentik dan konsisten.
                </p>
                <p class="text-muted">
                    Visi kami adalah menjadi coffee shop pilihan utama untuk semua pecinta kopi, dengan menyediakan produk berkualitas, layanan excellent, dan pengalaman berbelanja yang menyenangkan.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card text-center p-4 bg-coffee text-white">
                            <h2 class="fw-bold mb-0">1000+</h2>
                            <p class="mb-0">Pelanggan Setia</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-center p-4 bg-coffee text-white">
                            <h2 class="fw-bold mb-0">50+</h2>
                            <p class="mb-0">Varian Menu</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-center p-4 bg-coffee text-white">
                            <h2 class="fw-bold mb-0">5★</h2>
                            <p class="mb-0">Rating Pelanggan</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-center p-4 bg-coffee text-white">
                            <h2 class="fw-bold mb-0">24/7</h2>
                            <p class="mb-0">Layanan Online</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<?php if (!isLoggedIn()): ?>
<section class="section-padding bg-coffee text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Mulai Petualangan Kopi Anda!</h2>
        <p class="lead mb-4">Daftar sekarang dan dapatkan diskon 10% untuk pembelian pertama</p>
        <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-light btn-lg">
            Daftar Gratis Sekarang <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</section>
<?php endif; ?>

<?php 
require_once __DIR__ . '/includes/footer.php';
?>
