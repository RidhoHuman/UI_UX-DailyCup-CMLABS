<?php
/**
 * Notifications API Endpoint
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Require login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$userId = $_SESSION['user_id'];
$db = getDB();

$action = $_GET['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? $action;
}

switch ($action) {
    case 'get':
        // Get all notifications
        $stmt = $db->prepare("SELECT * FROM notifications 
                             WHERE user_id = ? 
                             ORDER BY created_at DESC 
                             LIMIT 50");
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll();
        
        $unreadCount = getUnreadNotificationCount($userId);
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
        break;
        
    case 'check_new':
        // Check for new notifications
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications 
                             WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        $unreadCount = $stmt->fetchColumn();
        
        $hasNew = $unreadCount > 0;
        
        // Get latest notification if exists
        $latestNotif = null;
        if ($hasNew) {
            $stmt = $db->prepare("SELECT * FROM notifications 
                                 WHERE user_id = ? AND is_read = 0 
                                 ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$userId]);
            $latestNotif = $stmt->fetch();
        }
        
        echo json_encode([
            'success' => true,
            'has_new' => $hasNew,
            'unread_count' => $unreadCount,
            'latest_notification' => $latestNotif
        ]);
        break;
        
    case 'mark_read':
        // Mark single notification as read
        $notificationId = $data['notification_id'] ?? null;
        
        if (!$notificationId) {
            echo json_encode(['success' => false, 'message' => 'ID notifikasi tidak valid']);
            exit;
        }
        
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 
                             WHERE id = ? AND user_id = ?");
        $stmt->execute([$notificationId, $userId]);
        
        echo json_encode(['success' => true]);
        break;
        
    case 'mark_all_read':
        // Mark all notifications as read
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 
                             WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        echo json_encode(['success' => true]);
        break;
        
    case 'delete':
        // Delete notification
        $notificationId = $data['notification_id'] ?? null;
        
        if (!$notificationId) {
            echo json_encode(['success' => false, 'message' => 'ID notifikasi tidak valid']);
            exit;
        }
        
        $stmt = $db->prepare("DELETE FROM notifications 
                             WHERE id = ? AND user_id = ?");
        $stmt->execute([$notificationId, $userId]);
        
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
