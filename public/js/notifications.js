document.addEventListener('DOMContentLoaded', function() {
    // Update notifications every 30 seconds
    function updateNotifications() {
        fetch('/notifications/unread', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin' // Ensure cookies are sent
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Notification data received:', data); // Debugging
                
                // Update the notification badge count
                const badge = document.querySelector('#navbarNotifications .badge');
                const count = data.count;
                
                if (count > 0) {
                    if (badge) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = 'inline-block';
                    } else {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                        newBadge.textContent = count > 99 ? '99+' : count;
                        document.querySelector('#navbarNotifications').appendChild(newBadge);
                    }
                } else if (badge) {
                    badge.style.display = 'none';
                }
                
                // Update notification list
                const notificationList = document.getElementById('notification-list');
                if (notificationList) {
                    let html = '';
                    
                    if (data.notifications && data.notifications.length > 0) {
                        data.notifications.forEach(notification => {
                            // Format the date
                            const createdAt = new Date(notification.created_at).toLocaleString();
                            
                            html += `
                                <a class="dropdown-item d-flex align-items-center" href="${notification.data.link}">
                                    <div class="me-3">
                                        <div class="bg-primary icon-circle">
                                            <i class="bi bi-card-checklist text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="small text-gray-500">${createdAt}</span>
                                        <p class="mb-0">${notification.data.message}</p>
                                    </div>
                                </a>
                            `;
                        });
                    } else {
                        html = `
                            <div class="dropdown-item text-center">
                                <p class="mb-0">No new notifications</p>
                            </div>
                        `;
                    }
                    
                    notificationList.innerHTML = html;
                }
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
            });
    }
    
    // Update initially and then every 30 seconds
    updateNotifications();
    setInterval(updateNotifications, 30000);
});