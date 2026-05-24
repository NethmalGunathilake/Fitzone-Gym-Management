document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    menuToggle.addEventListener('click', function() {
        document.querySelector('.user-dashboard').classList.toggle('sidebar-collapsed');
    });
    
    // Responsive adjustments
    function handleResize() {
        if (window.innerWidth < 768) {
            document.querySelector('.user-dashboard').classList.add('sidebar-collapsed');
        } else {
            document.querySelector('.user-dashboard').classList.remove('sidebar-collapsed');
        }
    }
    
    window.addEventListener('resize', handleResize);
    handleResize();
    
    // Notification dropdown
    const notificationIcon = document.querySelector('.notification-icon');
    if (notificationIcon) {
        notificationIcon.addEventListener('click', function() {
            // Implement notification dropdown functionality
            console.log('Notifications clicked');
        });
    }
});