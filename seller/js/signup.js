document.getElementById('signupForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const terms = document.getElementById('terms').checked;
    
    let errors = [];
    
    if (!name) errors.push('Full name is required');
    if (!email) errors.push('Email is required');
    if (!password) errors.push('Password is required');
    if (!confirmPassword) errors.push('Please confirm your password');
    if (password && password.length < 8) errors.push('Password must be at least 8 characters');
    if (password !== confirmPassword) errors.push('Passwords do not match');
    if (!terms) errors.push('You must agree to the Terms of Service');
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Please fix the following errors:\n\n• ' + errors.join('\n• '));
    }
});