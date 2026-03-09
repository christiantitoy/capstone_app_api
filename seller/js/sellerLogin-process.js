
document.querySelector('form').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    let errors = [];
    
    if (!email) errors.push('Email is required');
    if (!password) errors.push('Password is required');
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Please fix the following errors:\n\n• ' + errors.join('\n• '));
    }
});