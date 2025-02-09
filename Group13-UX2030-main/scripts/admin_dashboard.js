document.addEventListener('DOMContentLoaded', function() {
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationList = document.getElementById('notificationList');
    const userProfile = document.querySelector('.user-profile');
    const profileDropdown = document.querySelector('.profile-dropdown');

    notificationIcon.addEventListener('click', function(event) {
        event.stopPropagation();
        notificationList.style.display = notificationList.style.display === 'block' ? 'none' : 'block';
        profileDropdown.style.display = 'none';
    });

    userProfile.addEventListener('click', function(event) {
        event.stopPropagation();
        profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
        notificationList.style.display = 'none';
    });

    document.addEventListener('click', function(event) {
        if (!notificationIcon.contains(event.target) && !notificationList.contains(event.target)) {
            notificationList.style.display = 'none';
        }
        if  (!userProfile.contains(event.target) && !profileDropdown.contains(event.target)) {
            profileDropdown.style.display = 'none';
        }
    });

    // Prevent closing when clicking inside the dropdown
    profileDropdown.addEventListener('click', function(event) {
        event.stopPropagation();
    });
});