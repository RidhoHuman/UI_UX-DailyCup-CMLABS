/**
 * Notification System JavaScript
 */

// Notification settings
const NOTIFICATION_CHECK_INTERVAL = 30000; // 30 seconds
let notificationCheckTimer = null;

// Initialize notifications
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    startNotificationPolling();
    initNotificationEvents();
});

/**
 * Load notifications from server
 */
function loadNotifications() {
    fetch('/dailycup/api/notifications.php?action=get')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationDisplay(data.notifications);
                updateNotificationCount(data.unread_count);
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
}

/**
 * Update notification count badge
 */
function updateNotificationCount(count) {
    const badges = document.querySelectorAll('.notification-count');
    badges.forEach(badge => {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    });
}

/**
 * Update notification display
 */
function updateNotificationDisplay(notifications) {
    const container = document.getElementById('notificationsList');
    if (!container) return;
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-bell-slash" style="font-size: 3rem; color: #ccc;"></i>
                <p class="mt-3 text-muted">Tidak ada notifikasi</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    notifications.forEach(notif => {
        const isRead = notif.is_read == 1;
        const bgClass = isRead ? '' : 'bg-light';
        
        html += `
            <div class="notification-item ${bgClass} border-bottom p-3" 
                 data-notif-id="${notif.id}" 
                 onclick="markAsRead(${notif.id})">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 ${isRead ? 'text-muted' : 'fw-bold'}">
                            ${notif.title}
                        </h6>
                        <p class="mb-1 small ${isRead ? 'text-muted' : ''}">
                            ${notif.message}
                        </p>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> ${formatNotificationTime(notif.created_at)}
                        </small>
                    </div>
                    ${!isRead ? '<span class="badge bg-primary ms-2">New</span>' : ''}
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

/**
 * Mark notification as read
 */
function markAsRead(notificationId) {
    fetch('/dailycup/api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'mark_read',
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Mark all notifications as read
 */
function markAllAsRead() {
    fetch('/dailycup/api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'mark_all_read'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
            if (window.DailyCup) {
                window.DailyCup.showAlert('Semua notifikasi ditandai sebagai dibaca', 'success');
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Delete notification
 */
function deleteNotification(notificationId) {
    if (!confirm('Hapus notifikasi ini?')) {
        return;
    }
    
    fetch('/dailycup/api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'delete',
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
            if (window.DailyCup) {
                window.DailyCup.showAlert('Notifikasi dihapus', 'info');
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Start polling for new notifications
 */
function startNotificationPolling() {
    // Check immediately
    checkNewNotifications();
    
    // Then check every interval
    notificationCheckTimer = setInterval(() => {
        checkNewNotifications();
    }, NOTIFICATION_CHECK_INTERVAL);
}

/**
 * Stop polling
 */
function stopNotificationPolling() {
    if (notificationCheckTimer) {
        clearInterval(notificationCheckTimer);
        notificationCheckTimer = null;
    }
}

/**
 * Check for new notifications
 */
function checkNewNotifications() {
    fetch('/dailycup/api/notifications.php?action=check_new')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.has_new) {
                // Update count
                updateNotificationCount(data.unread_count);
                
                // Show toast notification if new notifications exist
                if (data.latest_notification) {
                    showToastNotification(data.latest_notification);
                }
                
                // Play notification sound (optional)
                // playNotificationSound();
            }
        })
        .catch(error => console.error('Error checking notifications:', error));
}

/**
 * Show toast notification
 */
function showToastNotification(notification) {
    const toastHTML = `
        <div class="toast align-items-center text-white bg-primary border-0 position-fixed top-0 end-0 m-3" 
             role="alert" style="z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${notification.title}</strong><br>
                    ${notification.message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                        data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const div = document.createElement('div');
    div.innerHTML = toastHTML;
    document.body.appendChild(div.firstElementChild);
    
    const toastElement = document.querySelector('.toast');
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();
    
    // Remove after shown
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

/**
 * Format notification time
 */
function formatNotificationTime(datetime) {
    const date = new Date(datetime);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // difference in seconds
    
    if (diff < 60) {
        return 'Baru saja';
    } else if (diff < 3600) {
        const minutes = Math.floor(diff / 60);
        return `${minutes} menit yang lalu`;
    } else if (diff < 86400) {
        const hours = Math.floor(diff / 3600);
        return `${hours} jam yang lalu`;
    } else if (diff < 604800) {
        const days = Math.floor(diff / 86400);
        return `${days} hari yang lalu`;
    } else {
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    }
}

/**
 * Initialize notification events
 */
function initNotificationEvents() {
    // Mark all as read button
    const markAllBtn = document.getElementById('markAllReadBtn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', markAllAsRead);
    }
    
    // Stop polling when page is hidden
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopNotificationPolling();
        } else {
            startNotificationPolling();
        }
    });
}

/**
 * Play notification sound (optional)
 */
function playNotificationSound() {
    // You can add a notification sound here
    // const audio = new Audio('/dailycup/assets/sounds/notification.mp3');
    // audio.play();
}

// Make functions available globally
window.markAsRead = markAsRead;
window.markAllAsRead = markAllAsRead;
window.deleteNotification = deleteNotification;
window.loadNotifications = loadNotifications;
