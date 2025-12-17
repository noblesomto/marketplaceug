// resources/js/message-notification.js

// This will be lazy-loaded only when needed
export async function initializeMessageNotifications() {
    // Lazy load Echo and Pusher
    const Echo = (await import('laravel-echo')).default;
    const Pusher = (await import('pusher-js')).default;

    // Setup Pusher
    window.Pusher = Pusher;

    // Get CSRF token
    let token = document.querySelector('meta[name="csrf-token"]');

    // Setup Echo
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY || '41a30e61ffd80a89fc68',
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'eu',
        forceTLS: true,
        encrypted: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': token ? token.content : '',
            }
        }
    });

    // Now initialize notifications
    setupNotifications();
}

function setupNotifications() {
    if (!window.Laravel) {
        console.warn("window.Laravel is not defined yet.");
        return;
    }

    const userId = window.Laravel?.userId;
    const notificationSound = new Audio(window.Laravel?.soundUrl || '');
    const notificationIcon = window.Laravel?.iconUrl || '';
    const UNREAD_URL = window.Laravel.unreadUrl ?? '/unread-messages-count';

    let previousCount = 0;

    console.log('User ID:', userId);

    // Request notification permission
    if ("Notification" in window && Notification.permission !== 'granted') {
        Notification.requestPermission().then(permission => {
            console.log("Notification permission:", permission);
        });
    }

    // Listen for real-time messages
    if (userId) {
        window.Echo.private(`user.${userId}`) // ✅ Fixed syntax error (was missing opening quote)
            .listen('.new.message', (e) => {
                console.log("📩 New message received:", e.message);

                // Play sound
                if (notificationSound) {
                    notificationSound.play().catch(err => console.warn("Sound failed:", err));
                }

                // Show browser notification
                showNotification("📩 New Message", "You received a new message!");

                // Refresh badge count
                updateUnreadMessages();
            });
    }

    // Polling fallback for unread count
    function updateUnreadMessages() {
        fetch(UNREAD_URL)
            .then(response => response.json())
            .then(data => {
                const badges = document.querySelectorAll('.unread-badge');
                badges.forEach(badge => {
                    if (data.count > 0) {
                        badge.style.display = 'flex';
                        badge.textContent = data.count;
                    } else {
                        badge.style.display = 'none';
                    }
                });

                if (data.count > 0 && previousCount === 0) {
                    showNotification("📩 New Message", `You have ${data.count} unread message(s).`);
                    if (notificationSound) {
                        notificationSound.play().catch(e => console.warn('Sound failed:', e));
                    }
                }

                previousCount = data.count;
            })
            .catch(error => {
                console.error("Unread message check failed:", error);
            });
    }

    function showNotification(title, body) {
        if ("Notification" in window) {
            if (Notification.permission === "granted") {
                new Notification(title, {
                    body: body,
                    icon: notificationIcon
                });
            } else if (Notification.permission !== "denied") {
                Notification.requestPermission().then(permission => {
                    if (permission === "granted") {
                        new Notification(title, {
                            body: body,
                            icon: notificationIcon
                        });
                    }
                });
            }
        }
    }

    // Start polling every 10 seconds
    updateUnreadMessages();
    setInterval(updateUnreadMessages, 10000);
}

// Auto-initialize when this module is imported
initializeMessageNotifications();
