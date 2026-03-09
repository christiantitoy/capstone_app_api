// /seller/backend/auth/js/signup.js

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Get form values
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;
            
            // Array to store error messages
            let errors = [];
            
            // Validate each field
            if (name === '') {
                errors.push('Full name is required');
            }
            
            if (email === '') {
                errors.push('Email is required');
            } else if (!email.includes('@') || !email.includes('.')) {
                errors.push('Please enter a valid email address');
            }
            
            if (password === '') {
                errors.push('Password is required');
            } else if (password.length < 8) {
                errors.push('Password must be at least 8 characters long');
            }
            
            if (confirmPassword === '') {
                errors.push('Please confirm your password');
            } else if (password !== confirmPassword) {
                errors.push('Passwords do not match');
            }
            
            if (!terms) {
                errors.push('You must agree to the Terms of Service');
            }
            
            // If there are errors, prevent form submission and show alert
            if (errors.length > 0) {
                e.preventDefault();
                alert('Please fix the following errors:\n\n• ' + errors.join('\n• '));
            }
            // If no errors, form will submit normally to the backend
        });
    }
});