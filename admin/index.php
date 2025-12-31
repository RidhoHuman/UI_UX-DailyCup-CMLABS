<?php
$pageTitle = 'Admin Dashboard';
$isAdminPage = true;
requireAdmin();

require_once __DIR__ . '/../includes/header.php';

$db = getDB();

// Get statistics
$stmt = $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
$todayOrders = $stmt->fetchColumn();

$stmt = $db->query("SELECT SUM(final_amount) FROM orders 
                   WHERE status = 'completed' AND DATE(created_at) = CURDATE()");
$todayRevenue = $stmt->fetchColumn() ?? 0;

$stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$totalCustomers = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
$totalProducts = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$pendingOrders = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM returns WHERE status = 'pending'");
$pendingReturns = $stmt->fetchColumn();

// Get recent orders
$stmt = $db->query("SELECT o.*, u.name as customer_name 
                   FROM orders o 
                   JOIN users u ON o.user_id = u.id 
                   ORDER BY o.created_at DESC 
                   LIMIT 10");
$recentOrders = $stmt->fetchAll();
?>

<div class="admin-layout">
    <?php require_once __DIR__ . '/../includes/sidebar_admin.php'; ?>
    
    <div class="admin-main">
        <div class="page-header">
            <h1 class="page-title"><i class="bi bi-speedometer2"></i> Dashboard</h1>
            <div class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="quick-stats">
            <div class="stat-card stat-orders">
                <div class="stat-icon">
                    <i class="bi bi-bag-check"></i>
                </div>
                <div class="stat-value"><?php echo $todayOrders; ?></div>
                <div class="stat-label">Pesanan Hari Ini</div>
            </div>
            
            <div class="stat-card stat-revenue">
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-value"><?php echo formatCurrency($todayRevenue); ?></div>
                <div class="stat-label">Revenue Hari Ini</div>
            </div>
            
            <div class="stat-card stat-customers">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-value"><?php echo $totalCustomers; ?></div>
                <div class="stat-label">Total Pelanggan</div>
            </div>
            
            <div class="stat-card stat-products">
                <div class="stat-icon">
                    <i class="bi bi-cup-straw"></i>
                </div>
                <div class="stat-value"><?php echo $totalProducts; ?></div>
                <div class="stat-label">Produk Aktif</div>
            </div>
        </div>
        
        <!-- Alerts -->
        <?php if ($pendingOrders > 0): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            Ada <strong><?php echo $pendingOrders; ?></strong> pesanan menunggu konfirmasi.
            <a href="<?php echo SITE_URL; ?>/admin/orders/" class="alert-link">Lihat Pesanan</a>
        </div>
        <?php endif; ?>
        
        <?php if ($pendingReturns > 0): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Ada <strong><?php echo $pendingReturns; ?></strong> permintaan retur menunggu review.
            <a href="<?php echo SITE_URL; ?>/admin/returns/" class="alert-link">Lihat Retur</a>
        </div>
        <?php endif; ?>
        
        <!-- Recent Orders -->
        <div class="admin-table">
            <div class="p-3 border-bottom">
                <h5 class="mb-0">Pesanan Terbaru</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo formatCurrency($order['final_amount']); ?></td>
                            <td>
                                <span class="badge status-<?php echo $order['status']; ?>">
                                    <?php echo ORDER_STATUS[$order['status']]; ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($order['created_at']); ?></td>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/admin/orders/view.php?id=<?php echo $order['id']; ?>" 
                                   class="btn btn-sm btn-view">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (count($recentOrders) == 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Belum ada pesanan
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?php echo SITE_URL; ?>/admin/orders/" class="btn btn-coffee">
                Lihat Semua Pesanan <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
