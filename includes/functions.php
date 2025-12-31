<?php
/**
 * Core Functions Library
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

/**
 * Security Functions
 */

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin');
}

// Check if user is super admin
function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// Require admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

// Require super admin
function requireSuperAdmin() {
    requireLogin();
    if (!isSuperAdmin()) {
        header('Location: ' . SITE_URL . '/admin/index.php');
        exit;
    }
}

/**
 * User Functions
 */

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Update user loyalty points
function updateUserPoints($userId, $points, $type = 'earned', $orderId = null, $description = '') {
    $db = getDB();
    
    // Update user points
    $stmt = $db->prepare("UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?");
    $stmt->execute([$points, $userId]);
    
    // Log transaction
    $stmt = $db->prepare("INSERT INTO loyalty_transactions (user_id, order_id, points, transaction_type, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $orderId, $points, $type, $description]);
}

/**
 * Product Functions
 */

// Get all active categories
function getCategories($activeOnly = true) {
    $db = getDB();
    $sql = "SELECT * FROM categories";
    if ($activeOnly) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY display_order, name";
    
    $stmt = $db->query($sql);
    return $stmt->fetchAll();
}

// Get products by category
function getProductsByCategory($categoryId, $activeOnly = true) {
    $db = getDB();
    $sql = "SELECT * FROM products WHERE category_id = ?";
    if ($activeOnly) {
        $sql .= " AND is_active = 1";
    }
    $sql .= " ORDER BY name";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll();
}

// Get product by ID
function getProduct($productId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetch();
}

// Get product variants
function getProductVariants($productId, $type = null) {
    $db = getDB();
    $sql = "SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1";
    $params = [$productId];
    
    if ($type) {
        $sql .= " AND variant_type = ?";
        $params[] = $type;
    }
    
    $sql .= " ORDER BY variant_type, price_adjustment";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Order Functions
 */

// Generate unique order number
function generateOrderNumber() {
    return ORDER_PREFIX . date('Ymd') . rand(1000, 9999);
}

// Calculate loyalty points for order
function calculateLoyaltyPoints($amount) {
    $db = getDB();
    $stmt = $db->query("SELECT points_per_rupiah FROM loyalty_settings WHERE is_active = 1 LIMIT 1");
    $settings = $stmt->fetch();
    
    if ($settings) {
        return floor($amount * $settings['points_per_rupiah']);
    }
    
    return 0;
}

/**
 * Notification Functions
 */

// Create notification
function createNotification($userId, $title, $message, $type, $orderId = null) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO notifications (user_id, order_id, title, message, type) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$userId, $orderId, $title, $message, $type]);
}

// Get unread notification count
function getUnreadNotificationCount($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

/**
 * File Upload Functions
 */

// Upload image
function uploadImage($file, $folder = '') {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file'];
    }
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }
    
    // Check file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    
    // Create folder if not exists
    $uploadPath = UPLOAD_PATH . $folder;
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }
    
    // Move file
    $destination = $uploadPath . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to move file'];
}

// Upload multiple images
function uploadMultipleImages($files, $folder = '') {
    $uploadedFiles = [];
    
    foreach ($files['tmp_name'] as $key => $tmp_name) {
        $file = [
            'name' => $files['name'][$key],
            'type' => $files['type'][$key],
            'tmp_name' => $files['tmp_name'][$key],
            'error' => $files['error'][$key],
            'size' => $files['size'][$key]
        ];
        
        $result = uploadImage($file, $folder);
        if ($result['success']) {
            $uploadedFiles[] = $result['filename'];
        }
    }
    
    return $uploadedFiles;
}

/**
 * Formatting Functions
 */

// Format currency
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Format date
function formatDate($date, $format = 'd M Y H:i') {
    return date($format, strtotime($date));
}

// Time ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return $difference . ' detik yang lalu';
    } elseif ($difference < 3600) {
        return floor($difference / 60) . ' menit yang lalu';
    } elseif ($difference < 86400) {
        return floor($difference / 3600) . ' jam yang lalu';
    } elseif ($difference < 604800) {
        return floor($difference / 86400) . ' hari yang lalu';
    } else {
        return date('d M Y H:i', $timestamp);
    }
}

/**
 * Email Functions
 */

// Send email (basic implementation)
function sendEmail($to, $subject, $message, $headers = '') {
    // In production, use proper SMTP library like PHPMailer
    // This is a basic implementation
    $defaultHeaders = "From: " . SMTP_FROM_EMAIL . "\r\n";
    $defaultHeaders .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $defaultHeaders .= "MIME-Version: 1.0\r\n";
    $defaultHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    $allHeaders = $defaultHeaders . $headers;
    
    return mail($to, $subject, $message, $allHeaders);
}

/**
 * Pagination Functions
 */

// Get pagination data
function getPagination($totalItems, $currentPage = 1, $itemsPerPage = ITEMS_PER_PAGE) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset
    ];
}
