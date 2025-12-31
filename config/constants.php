<?php
/**
 * Application Constants
 */

// Site Configuration
define('SITE_NAME', 'DailyCup Coffee Shop');
define('SITE_URL', 'http://localhost/dailycup');
define('ADMIN_EMAIL', 'admin@dailycup.com');

// File Upload Configuration
define('UPLOAD_PATH', __DIR__ . '/../assets/images/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);

// Session Configuration
define('SESSION_TIMEOUT', 7200); // 2 hours in seconds

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Order Configuration
define('ORDER_PREFIX', 'DC');

// Email Configuration (for order notifications)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@dailycup.com');
define('SMTP_FROM_NAME', 'DailyCup Coffee Shop');

// Theme Colors
define('PRIMARY_COLOR', '#6F4E37'); // Coffee brown
define('SECONDARY_COLOR', '#D4A574'); // Cream/beige
define('ACCENT_COLOR', '#4A3728'); // Dark brown

// Status Constants
define('ORDER_STATUS', [
    'pending' => 'Menunggu Pembayaran',
    'confirmed' => 'Dikonfirmasi',
    'processing' => 'Sedang Diproses',
    'ready' => 'Siap Diambil/Diantar',
    'delivering' => 'Dalam Pengiriman',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
]);

define('PAYMENT_STATUS', [
    'pending' => 'Menunggu Pembayaran',
    'paid' => 'Sudah Dibayar',
    'failed' => 'Pembayaran Gagal'
]);

define('RETURN_REASONS', [
    'wrong_order' => 'Pesanan Salah',
    'damaged' => 'Produk Rusak/Tumpah',
    'quality_issue' => 'Kualitas Tidak Sesuai',
    'missing_items' => 'Item Kurang',
    'other' => 'Lainnya'
]);
