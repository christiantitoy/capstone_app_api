<?php
// /seller/ui/payment.php
require_once __DIR__ . '/../backend/session/auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Payment - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/error.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/logout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/payment.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="page-header">
    <div class="header-container">
        <a href="javascript:history.back()" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <h1>Make a Payment</h1>
    </div>
</header>

<div class="payment-container">
    <div class="payment-grid">
        <!-- Left Column -->
        <div>
            <div class="amount-card">
                <div class="amount-label">Total Amount Due</div>
                <div class="amount-value" id="amountDisplay">₱0.00</div>
                <div class="amount-sub">PalitOra Payment</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-qrcode"></i> Scan QR Code</h2>
                </div>
                <div class="card-body">
                    <div class="qr-section">
                        <div class="qr-image" onclick="openQRModal()">
                            <img src="/seller/image/qr.jpg" alt="QR Code" onerror="this.src='https://placehold.co/200x200?text=QR+Code'">
                        </div>
                        <p class="qr-hint">
                            <i class="fas fa-expand-alt"></i> Click QR code to enlarge
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> How to Pay</h2>
                </div>
                <div class="card-body">
                    <ul class="instruction-list">
                        <li class="instruction-item"><span class="step-number">1</span> Scan the QR code using GCash</li>
                        <li class="instruction-item"><span class="step-number">2</span> Enter the exact amount: <strong id="instructionAmount">₱0.00</strong></li>
                        <li class="instruction-item"><span class="step-number">3</span> Complete payment and take screenshot</li>
                        <li class="instruction-item"><span class="step-number">4</span> Upload screenshot and enter GCash number</li>
                        <li class="instruction-item"><span class="step-number">5</span> Submit payment proof</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-receipt"></i> Proof of Payment</h2>
                </div>
                <div class="card-body">
                    <div>
                        <div class="form-label">Payment Screenshot</div>
                        <div class="upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <div class="upload-text">Click to upload screenshot</div>
                            <div class="upload-hint">PNG, JPG up to 5MB</div>
                            <img id="previewImg" class="preview-image" style="display: none;">
                            <button id="removeImageBtn" class="remove-image" style="display: none;" onclick="event.stopPropagation(); removeImage()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <input type="file" id="fileInput" accept="image/*" style="display: none;" onchange="handleImageUpload(event)">
                    </div>

                    <div class="form-group">
                        <label class="form-label">GCash Number Used</label>
                        <input type="tel" 
                               id="gcashNumber" 
                               class="form-input" 
                               placeholder="09XXXXXXXXX"
                               maxlength="11"
                               oninput="validateGCashNumber()">
                        <div id="gcashHint" class="input-hint"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="submit-section">
        <button class="submit-btn" id="submitBtn" onclick="submitPayment()" disabled>
            <i class="fas fa-check-circle"></i>
            <span id="submitText">Submit Payment Proof</span>
        </button>
    </div>
</div>

<footer class="page-footer">
    <p>&copy; 2026 PalitOra. All rights reserved.</p>
</footer>

<!-- QR Modal -->
<div id="qrModal" class="modal" onclick="closeQRModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <img src="/seller/image/qr.jpg" alt="QR Code" onerror="this.src='https://placehold.co/300x300?text=QR+Code'">
    </div>
</div>

<!-- Toast -->
<div id="toast" class="toast"></div>

<!-- Logout Modal -->
<div class="logout-modal-overlay" id="logoutModal">
    <div class="logout-modal-content">
        <div class="logout-modal-header">
            <h3>Sign Out</h3>
            <button class="logout-modal-close" id="closeModal">×</button>
        </div>
        <div class="logout-modal-body">
            <p>Are you sure you want to sign out?</p>
            <p class="logout-text-secondary">You will need to log in again.</p>
        </div>
        <div class="logout-modal-footer">
            <button class="logout-btn logout-btn-secondary" id="cancelLogout">Cancel</button>
            <a href="/seller/backend/auth/logout.php" class="logout-btn logout-btn-danger">Sign Out</a>
        </div>
    </div>
</div>

<script src="/seller/js/logout.js"></script>

<script>
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const plan     = urlParams.get('plan') || 'silver';
    const billing  = urlParams.get('billing') || 'monthly';
    let amount     = parseFloat(urlParams.get('amount')) || 300.00;

    // State variables
    let isSubmitting = false;
    let isSubmitted = false;        // ← New: Prevents re-submission after success

    // Display amount
    document.getElementById('amountDisplay').innerText = `₱${amount.toFixed(2)}`;
    document.getElementById('instructionAmount').innerText = `₱${amount.toFixed(2)}`;

    // Handle image upload preview
    function handleImageUpload(event) {
        const file = event.target.files[0];
        if (!file || !file.type.startsWith('image/')) {
            showToast('Please select a valid image file', 'error');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            showToast('File too large. Maximum 5MB allowed.', 'error');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('previewImg');
            const removeBtn = document.getElementById('removeImageBtn');
            const uploadArea = document.getElementById('uploadArea');

            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
            removeBtn.style.display = 'flex';
            uploadArea.classList.add('has-image');

            uploadArea.querySelector('.upload-icon').style.display = 'none';
            uploadArea.querySelector('.upload-text').style.display = 'none';
            uploadArea.querySelector('.upload-hint').style.display = 'none';

            checkFormComplete();
        };
        reader.readAsDataURL(file);
    }

    function removeImage() {
        const previewImg = document.getElementById('previewImg');
        const removeBtn = document.getElementById('removeImageBtn');
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');

        previewImg.style.display = 'none';
        removeBtn.style.display = 'none';
        uploadArea.classList.remove('has-image');

        uploadArea.querySelector('.upload-icon').style.display = 'block';
        uploadArea.querySelector('.upload-text').style.display = 'block';
        uploadArea.querySelector('.upload-hint').style.display = 'block';

        fileInput.value = '';
        checkFormComplete();
    }

    function validateGCashNumber() {
        const input = document.getElementById('gcashNumber');
        const hint = document.getElementById('gcashHint');
        let value = input.value.replace(/\D/g, '');

        input.value = value;

        if (value.length === 0) {
            hint.innerHTML = '';
            hint.className = 'input-hint';
            input.classList.remove('error');
        } else if (value.length < 11) {
            hint.innerHTML = 'Please enter a complete 11-digit GCash number';
            hint.className = 'input-hint error';
            input.classList.add('error');
        } else if (value.length === 11 && !value.startsWith('09')) {
            hint.innerHTML = 'GCash number must start with "09"';
            hint.className = 'input-hint error';
            input.classList.add('error');
        } else if (value.length === 11 && value.startsWith('09')) {
            hint.innerHTML = '<i class="fas fa-check-circle"></i> Valid GCash number';
            hint.className = 'input-hint success';
            input.classList.remove('error');
        }

        checkFormComplete();
    }

    function checkFormComplete() {
        if (isSubmitted) return;

        const gcashNumber = document.getElementById('gcashNumber').value;
        const fileInput = document.getElementById('fileInput');
        const isImageSelected = fileInput.files.length > 0;
        const isGcashValid = gcashNumber.length === 11 && gcashNumber.startsWith('09');

        const isComplete = isImageSelected && isGcashValid && !isSubmitting;

        document.getElementById('submitBtn').disabled = !isComplete;
    }

    function openQRModal() {
        document.getElementById('qrModal').classList.add('active');
    }

    function closeQRModal() {
        document.getElementById('qrModal').classList.remove('active');
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = `toast ${type}`;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    }

    // ==================== MAIN SUBMIT FUNCTION ====================
    async function submitPayment() {
        const gcashNumber = document.getElementById('gcashNumber').value.trim();
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const fileInput = document.getElementById('fileInput');

        if (isSubmitting || isSubmitted || !gcashNumber || fileInput.files.length === 0) return;

        isSubmitting = true;
        submitBtn.disabled = true;
        submitText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

        const formData = new FormData();
        formData.append('plan', plan);
        formData.append('billing', billing);
        formData.append('amount', amount);
        formData.append('gcash_number', gcashNumber);
        formData.append('proof_image', fileInput.files[0]);

        try {
            const res = await fetch('/seller/backend/payment/submit_payment.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                // SUCCESS - Make button permanently unclickable
                isSubmitted = true;
                isSubmitting = false;

                submitBtn.disabled = true;
                submitBtn.style.opacity = "0.7";
                submitBtn.style.cursor = "not-allowed";
                submitText.innerHTML = '<i class="fas fa-check-circle"></i> Submitted Successfully';

                showToast(data.message || 'Payment proof submitted successfully!', 'success');

                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = '/seller/ui/my_plan.php';
                }, 2000);
            } else {
                showToast(data.message || 'Submission failed. Please try again.', 'error');
                resetSubmitButton();
            }
        } catch (err) {
            console.error(err);
            showToast('Network error. Please check your connection and try again.', 'error');
            resetSubmitButton();
        }
    }

    // Reset button state on error
    function resetSubmitButton() {
        isSubmitting = false;
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        
        submitBtn.disabled = false;
        submitText.innerHTML = '<i class="fas fa-check-circle"></i> Submit Payment Proof';
    }

    // Event listeners
    document.getElementById('gcashNumber').addEventListener('input', validateGCashNumber);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeQRModal();
    });

    // Initial check
    checkFormComplete();
</script>

</body>
</html>