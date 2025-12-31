<?php
/**
 * Cart API Endpoint
 * Handles all cart operations
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Require login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

// Initialize cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? ($_POST['action'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? $action;
}

switch ($action) {
    case 'get':
        // Get cart contents
        echo json_encode([
            'success' => true,
            'cart' => $_SESSION['cart'],
            'count' => count($_SESSION['cart']),
            'total' => calculateCartTotal()
        ]);
        break;
        
    case 'add':
        // Add item to cart
        $productId = $data['product_id'] ?? null;
        $productName = $data['product_name'] ?? '';
        $price = $data['price'] ?? 0;
        $size = $data['size'] ?? null;
        $temperature = $data['temperature'] ?? null;
        $quantity = $data['quantity'] ?? 1;
        
        if (!$productId || !$productName || $price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Data produk tidak lengkap']);
            exit;
        }
        
        // Create cart key
        $cartKey = $productId . '_' . ($size ?? '') . '_' . ($temperature ?? '');
        
        // Check if item already in cart
        $found = false;
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['cart_key'] === $cartKey) {
                $_SESSION['cart'][$key]['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $_SESSION['cart'][] = [
                'cart_key' => $cartKey,
                'product_id' => $productId,
                'product_name' => $productName,
                'price' => $price,
                'size' => $size,
                'temperature' => $temperature,
                'quantity' => $quantity
            ];
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Produk ditambahkan ke keranjang',
            'cart' => $_SESSION['cart'],
            'count' => count($_SESSION['cart'])
        ]);
        break;
        
    case 'update':
        // Update cart item quantity
        $cartKey = $data['cart_key'] ?? null;
        $quantity = intval($data['quantity'] ?? 1);
        
        if ($cartKey === null || !isset($_SESSION['cart'][$cartKey])) {
            echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
            exit;
        }
        
        if ($quantity < 1) {
            unset($_SESSION['cart'][$cartKey]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index
        } else {
            $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
        }
        
        echo json_encode([
            'success' => true,
            'cart' => $_SESSION['cart'],
            'count' => count($_SESSION['cart'])
        ]);
        break;
        
    case 'remove':
        // Remove item from cart
        $cartKey = $data['cart_key'] ?? null;
        
        if ($cartKey === null || !isset($_SESSION['cart'][$cartKey])) {
            echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
            exit;
        }
        
        unset($_SESSION['cart'][$cartKey]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index
        
        echo json_encode([
            'success' => true,
            'message' => 'Item dihapus dari keranjang',
            'cart' => $_SESSION['cart'],
            'count' => count($_SESSION['cart'])
        ]);
        break;
        
    case 'clear':
        // Clear entire cart
        $_SESSION['cart'] = [];
        
        echo json_encode([
            'success' => true,
            'message' => 'Keranjang dikosongkan',
            'cart' => [],
            'count' => 0
        ]);
        break;
        
    case 'apply_discount':
        // Apply discount code
        $code = $data['code'] ?? '';
        
        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Kode diskon tidak valid']);
            exit;
        }
        
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM discounts 
                             WHERE code = ? 
                             AND is_active = 1 
                             AND start_date <= NOW() 
                             AND end_date >= NOW()
                             AND (usage_limit IS NULL OR usage_count < usage_limit)");
        $stmt->execute([$code]);
        $discount = $stmt->fetch();
        
        if (!$discount) {
            echo json_encode(['success' => false, 'message' => 'Kode diskon tidak valid atau sudah kadaluarsa']);
            exit;
        }
        
        // Check minimum purchase
        $cartTotal = calculateCartTotal();
        if ($cartTotal < $discount['min_purchase']) {
            echo json_encode([
                'success' => false,
                'message' => 'Minimum pembelian ' . formatCurrency($discount['min_purchase'])
            ]);
            exit;
        }
        
        // Calculate discount
        if ($discount['discount_type'] === 'percentage') {
            $discountAmount = $cartTotal * ($discount['discount_value'] / 100);
            if ($discount['max_discount']) {
                $discountAmount = min($discountAmount, $discount['max_discount']);
            }
        } else {
            $discountAmount = $discount['discount_value'];
        }
        
        $_SESSION['discount_code'] = $code;
        $_SESSION['discount_amount'] = $discountAmount;
        
        echo json_encode([
            'success' => true,
            'message' => 'Kode diskon berhasil diterapkan',
            'discount_amount' => $discountAmount
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function calculateCartTotal() {
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    }
    return $total;
}
