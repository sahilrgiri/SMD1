
document.addEventListener('DOMContentLoaded', function() {
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationList = document.getElementById('notificationList');

    notificationIcon.addEventListener('click', function() {
        notificationList.style.display = notificationList.style.display === 'block' ? 'none' : 'block';
    });

    // Close notification list when clicking outside
    document.addEventListener('click', function(event) {
        if (!notificationIcon.contains(event.target) && !notificationList.contains(event.target)) {
            notificationList.style.display = 'none';
        }
    });
});