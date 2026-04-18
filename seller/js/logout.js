document.addEventListener('DOMContentLoaded', () => {
    // Logout modal elements
    const logoutModal = document.getElementById('logoutModal');
    const logoutTrigger = document.querySelector('.logout-trigger');
    const closeBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelLogout');

    function openLogoutModal() {
        logoutModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLogoutModal() {
        logoutModal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Open modal
    if (logoutTrigger) {
        logoutTrigger.addEventListener('click', openLogoutModal);
    }

    // Close modal buttons
    if (closeBtn) {
        closeBtn.addEventListener('click', closeLogoutModal);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeLogoutModal);
    }

    // Click outside modal content
    if (logoutModal) {
        logoutModal.addEventListener('click', (e) => {
            if (e.target === logoutModal) {
                closeLogoutModal();
            }
        });
    }

    // ESC key closes modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && logoutModal?.classList.contains('active')) {
            closeLogoutModal();
        }
    });

    // Profile redirect
    const userProfile = document.getElementById('userProfile');
    if (userProfile) {
        userProfile.addEventListener('click', () => {
            window.location.href = '/seller/ui/profile.php';
        });
    }
});