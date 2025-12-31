<?php
$pageTitle = 'Checkout';
requireLogin();
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header('Location: ' . SITE_URL . '/customer/cart.php');
    exit;
}

$db = getDB();
$paymentMethods = $db->query("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY display_order")->fetchAll();
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="bi bi-credit-card"></i> Checkout</h2>
    
    <form method="POST" action="<?php echo SITE_URL; ?>/customer/payment.php">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Delivery Method -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-coffee text-white">
                        <h5 class="mb-0">Metode Pengiriman</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="delivery_method" id="dine-in" value="dine-in" required>
                            <label class="form-check-label" for="dine-in">
                                <strong>Dine In</strong> - Makan di tempat
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="delivery_method" id="takeaway" value="takeaway" required>
                            <label class="form-check-label" for="takeaway">
                                <strong>Takeaway</strong> - Bawa pulang
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="delivery_method" id="delivery" value="delivery" required>
                            <label class="form-check-label" for="delivery">
                                <strong>Delivery</strong> - Diantar ke alamat
                            </label>
                        </div>
                        
                        <div id="deliveryAddress" class="mt-3" style="display:none;">
                            <label class="form-label">Alamat Pengiriman</label>
                            <textarea name="delivery_address" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Notes -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-coffee text-white">
                        <h5 class="mb-0">Catatan</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="customer_notes" class="form-control" rows="3" 
                                  placeholder="Catatan untuk pesanan (opsional)"></textarea>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="card shadow-sm">
                    <div class="card-header bg-coffee text-white">
                        <h5 class="mb-0">Metode Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($paymentMethods as $method): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" 
                                   id="payment-<?php echo $method['id']; ?>" 
                                   value="<?php echo $method['id']; ?>" required>
                            <label class="form-check-label" for="payment-<?php echo $method['id']; ?>">
                                <strong><?php echo htmlspecialchars($method['method_name']); ?></strong>
                                <?php if ($method['account_number']): ?>
                                - <?php echo htmlspecialchars($method['account_number']); ?>
                                <?php endif; ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-coffee text-white">
                        <h5 class="mb-0">Ringkasan</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span class="cart-subtotal"></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="cart-total text-coffee"></strong>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-coffee btn-lg">
                                <i class="bi bi-check-circle"></i> Buat Pesanan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('delivery').addEventListener('change', function() {
    document.getElementById('deliveryAddress').style.display = 'block';
});

document.getElementById('dine-in').addEventListener('change', function() {
    document.getElementById('deliveryAddress').style.display = 'none';
});

document.getElementById('takeaway').addEventListener('change', function() {
    document.getElementById('deliveryAddress').style.display = 'none';
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
