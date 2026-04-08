// Add this to your existing DOMContentLoaded event
document.addEventListener('DOMContentLoaded', () => {
    // Your existing logout modal code
    const modal = document.getElementById('logoutModal');
    const trigger = document.querySelector('.logout-trigger');
    const closeBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelLogout');

    function openModal() {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (trigger) trigger.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', e => {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });

    // Add profile click - redirect to profile page
    const userProfile = document.getElementById('userProfile');
    if (userProfile) {
        userProfile.addEventListener('click', () => {
            window.location.href = '/seller/ui/profile.php';
        });
    }
});