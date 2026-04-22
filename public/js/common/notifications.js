(function () {
    function initNotifications() {
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationsDropdown = document.getElementById('notificationsDropdown');
        const notificationsList = document.getElementById('notificationsList');

        if (!notificationBtn || !notificationsDropdown) return;

        notificationBtn.addEventListener('click', function (e) {
            if (notificationsDropdown.contains(e.target)) return;
            notificationsDropdown.classList.toggle('show');
            if (notificationsDropdown.classList.contains('show')) {
                fetchNotifications();
                if (window.innerWidth <= 768) {
                    document.body.style.overflow = 'hidden';
                }
            } else {
                document.body.style.overflow = '';
            }
        });

        document.addEventListener('click', function (e) {
            if (!notificationBtn.contains(e.target)) {
                notificationsDropdown.classList.remove('show');
                document.body.style.overflow = '';
            }
        });

        function getNotificationText(type) {
            switch ((type || '').toLowerCase()) {
                case 'like': return 'liked your post';
                case 'comment': return 'commented on your post';
                case 'share': return 'shared your post';
                case 'friendrequest': return 'sent you a friend request';
                case 'message': return 'sent you a message';
                case 'newevent': return 'created a new event';
                case 'groupinvite': return 'invited you to a group';
                case 'mention': return 'mentioned you in a post';
                default: return 'interacted with your profile';
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(String(text || '')));
            return div.innerHTML;
        }

        function formatTimeAgo(dateStr) {
            const date = new Date(dateStr);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            if (diff < 60) return 'just now';
            if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
            return Math.floor(diff / 86400) + 'd ago';
        }

        function resolveAvatar(pic) {
            if (!pic) return 'https://via.placeholder.com/40';
            if (pic.startsWith('http')) return pic;
            return '../../../public/img/' + pic;
        }

        function fetchNotifications() {
            if (!notificationsList) return;
            notificationsList.innerHTML = '<div style="padding:15px;text-align:center;"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>';
            fetch('../../../app/Controllers/Explore/explore_controller.php?action=get_notifications')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success || !data.notifications || data.notifications.length === 0) {
                        notificationsList.innerHTML = '<div style="padding:20px;text-align:center;color:#64748b;">No new notifications</div>';
                        return;
                    }
                    notificationsList.innerHTML = data.notifications.map(function (n) {
                        return '<div class="notification-item ' + (n.IsRead == 0 ? 'unread' : '') + '">' +
                            '<img src="' + resolveAvatar(n.ProfilePic) + '" class="notification-avatar" onerror="this.src=\'https://via.placeholder.com/40\'">' +
                            '<div class="notification-content">' +
                            '<div><strong>' + escapeHtml(n.Username) + '</strong> ' + getNotificationText(n.Type) + '</div>' +
                            '<div class="notification-time">' + formatTimeAgo(n.CreatedAt) + '</div>' +
                            '</div></div>';
                    }).join('');
                })
                .catch(function () {
                    notificationsList.innerHTML = '<div style="padding:15px;text-align:center;color:#ef4444;">Failed to load notifications</div>';
                });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotifications);
    } else {
        initNotifications();
    }
})();
