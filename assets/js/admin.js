// Admin JavaScript functions

// Format currency helper
function formatVND(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount) + ' đ';
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        notification.textContent = 'Đã copy!';
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 2000);
    });
}

// Auto-refresh dashboard data
if (window.location.pathname === '/admin.php' && new URLSearchParams(window.location.search).get('tab') === 'DASHBOARD') {
    setInterval(() => {
        fetch('/api/get_stats.php')
            .then(r => r.json())
            .then(data => {
                // Update stats if needed
                console.log('Stats updated', data);
            })
            .catch(err => console.error('Error updating stats:', err));
    }, 5000);
}

