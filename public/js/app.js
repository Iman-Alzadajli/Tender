// 

document.addEventListener('DOMContentLoaded', function () {
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
    sidebarToggle.addEventListener('click', function () {
        if (isMobileView()) {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        }
    });

    // Close sidebar when clicking overlay (mobile only)
    sidebarOverlay.addEventListener('click', function () {
        if (isMobileView()) {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        }
    });

    // Handle window resize
    window.addEventListener('resize', function () {
        updateBurgerMenuVisibility();
    });

    // Initialize burger menu visibility on page load
    updateBurgerMenuVisibility();
    // دالة لتحويل كل التواريخ في الصفحة
    function convertAllTimestamps() {
        // تأكد من وجود مكتبة dayjs قبل محاولة استخدامها
        if (typeof dayjs === 'undefined') {
            return; // لا تفعل شيئاً إذا لم يتم تحميل المكتبة
        }

        document.querySelectorAll('.dynamic-time').forEach(function (element) {
            const timestamp = element.dataset.timestamp;
            if (timestamp) {
                let format = 'DD MMM, YYYY hh:mm A'; // الصيغة الافتراضية

                if (element.dataset.format && element.dataset.format === 'd M, Y') {
                    format = 'DD MMM, YYYY'; // صيغة التاريخ فقط
                }

                element.textContent = dayjs.utc(timestamp).local().format(format);
            }
        });
    }

    // 1. قم بتشغيل الدالة عند تحميل الصفحة
    convertAllTimestamps();

    // 2. مهم جداً لـ Livewire: أعد تشغيل الدالة بعد كل تحديث
    document.addEventListener('livewire:navigated', () => {
        convertAllTimestamps();
    });
});