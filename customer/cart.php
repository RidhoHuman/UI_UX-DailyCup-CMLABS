<?php
$pageTitle = 'Keranjang Belanja';
requireLogin();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$cart = $_SESSION['cart'] ?? [];
$discountAmount = $_SESSION['discount_amount'] ?? 0;

$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$total = $subtotal - $discountAmount;
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="bi bi-cart3"></i> Keranjang Belanja</h2>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body" id="cartItems">
                    <!-- Cart items will be loaded by JavaScript -->
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-coffee text-white">
                    <h5 class="mb-0">Ringkasan Belanja</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span class="cart-subtotal"><?php echo formatCurrency($subtotal); ?></span>
                    </div>
                    
                    <?php if ($discountAmount > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Diskon:</span>
                        <span>-<?php echo formatCurrency($discountAmount); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong class="cart-total text-coffee"><?php echo formatCurrency($total); ?></strong>
                    </div>
                    
                    <!-- Discount Code -->
                    <div class="mb-3">
                        <label class="form-label">Kode Diskon</label>
                        <div class="input-group">
                            <input type="text" id="discountCode" class="form-control" placeholder="Masukkan kode">
                            <button class="btn btn-outline-coffee" onclick="applyDiscountCode(document.getElementById('discountCode').value)">
                                Gunakan
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo SITE_URL; ?>/customer/checkout.php" class="btn btn-coffee btn-lg">
                            <i class="bi bi-credit-card"></i> Checkout
                        </a>
                        <a href="<?php echo SITE_URL; ?>/customer/menu.php" class="btn btn-outline-coffee">
                            <i class="bi bi-arrow-left"></i> Lanjut Belanja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load cart on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartDisplay();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
