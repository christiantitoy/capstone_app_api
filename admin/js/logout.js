// Get modal elements
const modal = document.getElementById('logoutModal');
const logoutBtn = document.getElementById('logoutBtn');
const cancelBtn = document.getElementById('cancelLogoutBtn');
const confirmBtn = document.getElementById('confirmLogoutBtn');

// Open modal when logout button is clicked
logoutBtn.addEventListener('click', function() {
    modal.style.display = 'block';
});

// Close modal when cancel is clicked
cancelBtn.addEventListener('click', function() {
    modal.style.display = 'none';
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

// Confirm logout
confirmBtn.addEventListener('click', function() {
    window.location.href = '../backend/logout.php';
});