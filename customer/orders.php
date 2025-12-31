<?php
$pageTitle = 'Pesanan Saya';
requireLogin();
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="bi bi-bag"></i> Pesanan Saya</h2>
    
    <?php if (count($orders) > 0): ?>
        <?php foreach ($orders as $order): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5><?php echo htmlspecialchars($order['order_number']); ?></h5>
                        <p class="mb-0 text-muted"><?php echo formatDate($order['created_at']); ?></p>
                    </div>
                    <div class="text-end">
                        <div class="mb-2">
                            <span class="badge status-<?php echo $order['status']; ?>">
                                <?php echo ORDER_STATUS[$order['status']]; ?>
                            </span>
                        </div>
                        <h5 class="mb-0 text-coffee"><?php echo formatCurrency($order['final_amount']); ?></h5>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="<?php echo SITE_URL; ?>/customer/order_detail.php?id=<?php echo $order['id']; ?>" 
                       class="btn btn-outline-coffee btn-sm">
                        <i class="bi bi-eye"></i> Lihat Detail
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-bag-x" style="font-size: 4rem; color: #ccc;"></i>
            <h4 class="mt-3">Belum Ada Pesanan</h4>
            <a href="<?php echo SITE_URL; ?>/customer/menu.php" class="btn btn-coffee mt-3">Mulai Belanja</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
