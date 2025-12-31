<?php
$pageTitle = 'Loyalty Points';
requireLogin();
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$currentUser = getCurrentUser();
$db = getDB();

// Get loyalty settings
$stmt = $db->query("SELECT * FROM loyalty_settings WHERE is_active = 1 LIMIT 1");
$settings = $stmt->fetch();

// Get loyalty transactions
$stmt = $db->prepare("SELECT * FROM loyalty_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="bi bi-star-fill text-warning"></i> Loyalty Points</h2>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-coffee mb-3">Poin Anda</h3>
                    <div class="display-4 fw-bold text-coffee">
                        <?php echo number_format($currentUser['loyalty_points']); ?>
                    </div>
                    <p class="text-muted mt-3">Poin</p>
                    
                    <hr>
                    
                    <div class="text-start">
                        <p class="mb-1"><strong>Nilai Tukar:</strong></p>
                        <p class="text-muted">
                            <?php echo $settings ? number_format($settings['rupiah_per_point']) . ' poin = Rp 1' : ''; ?>
                        </p>
                        
                        <p class="mb-1 mt-3"><strong>Minimum Redeem:</strong></p>
                        <p class="text-muted">
                            <?php echo $settings ? number_format($settings['min_points_redeem']) . ' poin' : ''; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-coffee text-white">
                    <h5 class="mb-0">Riwayat Poin</h5>
                </div>
                <div class="card-body">
                    <?php if (count($transactions) > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Deskripsi</th>
                                    <th>Poin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $trans): ?>
                                <tr>
                                    <td><?php echo formatDate($trans['created_at'], 'd M Y'); ?></td>
                                    <td><?php echo htmlspecialchars($trans['description']); ?></td>
                                    <td>
                                        <?php if ($trans['transaction_type'] == 'earned'): ?>
                                        <span class="text-success">+<?php echo $trans['points']; ?></span>
                                        <?php else: ?>
                                        <span class="text-danger">-<?php echo $trans['points']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted">Belum ada riwayat poin</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="alert alert-info mt-4">
                <h6><i class="bi bi-info-circle"></i> Cara Mendapatkan Poin:</h6>
                <ul class="mb-0">
                    <li>Lakukan pembelian dan dapatkan poin otomatis</li>
                    <li>Setiap Rp 1.000 pembelian = <?php echo $settings ? $settings['points_per_rupiah'] * 1000 : '10'; ?> poin</li>
                    <li>Gunakan poin untuk diskon di pembelian berikutnya</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
