// 

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Function to check if we're in mobile view
    function isMobileView() {
        return window.innerWidth <= 768;
    }

    // Function to show/hide burger menu based on screen size
    function updateBurgerMenuVisibility() {
        if (isMobileView()) {
            // Show burger menu on mobile
            sidebarToggle.style.display = 'block';
        } else {
            // Hide burger menu on desktop and reset sidebar state
            sidebarToggle.style.display = 'none';
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        }
    }

    // Toggle sidebar only on mobile
    sidebarToggle.addEventListener('click', function() {
        if (isMobileView()) {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        }
    });

    // Close sidebar when clicking overlay (mobile only)
    sidebarOverlay.addEventListener('click', function() {
        if (isMobileView()) {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        updateBurgerMenuVisibility();
    });

    // Initialize burger menu visibility on page load
    updateBurgerMenuVisibility();
});
