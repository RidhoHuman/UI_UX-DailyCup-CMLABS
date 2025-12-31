    <!-- Footer -->
    <footer class="footer bg-dark text-white mt-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="text-uppercase mb-3">DailyCup Coffee Shop</h5>
                    <p>Nikmati pengalaman kopi terbaik dengan menu pilihan dari biji kopi berkualitas premium.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="text-uppercase mb-3">Menu</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>/customer/menu.php" class="text-white-50">Coffee</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/customer/menu.php" class="text-white-50">Non-Coffee</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/customer/menu.php" class="text-white-50">Snacks</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/customer/menu.php" class="text-white-50">Desserts</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6 class="text-uppercase mb-3">Layanan</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>/customer/orders.php" class="text-white-50">Pesanan Saya</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/customer/favorites.php" class="text-white-50">Favorit</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/customer/loyalty_points.php" class="text-white-50">Loyalty Points</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/index.php#about" class="text-white-50">Tentang Kami</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6 class="text-uppercase mb-3">Kontak</h6>
                    <ul class="list-unstyled">
                        <li class="text-white-50"><i class="bi bi-geo-alt"></i> Jakarta, Indonesia</li>
                        <li class="text-white-50"><i class="bi bi-telephone"></i> +62 812-3456-7890</li>
                        <li class="text-white-50"><i class="bi bi-envelope"></i> info@dailycup.com</li>
                    </ul>
                </div>
            </div>
            <hr class="bg-white">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> DailyCup Coffee Shop. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isLoggedIn()): ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/notification.js"></script>
    <?php endif; ?>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/cart.js"></script>
    
</body>
</html>
