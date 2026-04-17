<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Payment - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #fafafa;
            color: #2c3e50;
            min-height: 100vh;
        }

        /* Header */
        .payment-header {
            background: white;
            border-bottom: 1px solid #ebedf0;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }

        .back-btn {
            background: none;
            border: none;
            font-size: 1.4rem;
            cursor: pointer;
            color: #5f6b7a;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-btn:hover {
            background: #f0f2f5;
            color: var(--primary);
        }

        .payment-header h1 {
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
        }

        /* Main Container */
        .payment-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 1rem 1rem 5rem 1rem;
        }

        /* Amount Card */
        .amount-card {
            background: linear-gradient(135deg, #001433 0%, #001a4d 100%);
            border-radius: 20px;
            padding: 1.8rem;
            text-align: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .amount-label {
            color: rgba(255,255,255,0.8);
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .amount-value {
            color: white;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .amount-sub {
            color: rgba(255,255,255,0.7);
            font-size: 0.75rem;
        }

        /* QR Card */
        .qr-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            transition: transform 0.2s;
        }

        .qr-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #333;
        }

        .qr-card p {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-bottom: 1rem;
        }

        .qr-image {
            width: 180px;
            height: 180px;
            margin: 0 auto;
            background: #f5f5f5;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
            transition: transform 0.2s;
        }

        .qr-image:hover {
            transform: scale(1.02);
        }

        .qr-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .qr-hint {
            font-size: 0.7rem;
            color: #95a5a6;
            margin-top: 0.75rem;
        }

        /* Instruction Card */
        .instruction-card {
            background: #e8f4fd;
            border-radius: 16px;
            padding: 1.2rem;
            margin-bottom: 1rem;
        }

        .instruction-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .instruction-header i {
            color: #001433;
            font-size: 1.1rem;
        }

        .instruction-header span {
            font-weight: 700;
            font-size: 0.9rem;
            color: #001433;
        }

        .instruction-step {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .step-number {
            width: 22px;
            height: 22px;
            background: #001433;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .step-text {
            font-size: 0.8rem;
            color: #333;
            line-height: 1.4;
        }

        .divider {
            height: 1px;
            background: rgba(0,20,51,0.1);
            margin: 0.75rem 0;
        }

        .gcash-note {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.7rem;
            color: #7f8c8d;
            margin-top: 0.5rem;
        }

        /* Pay with GCash Button */
        .gcash-btn {
            width: 100%;
            background: linear-gradient(135deg, #00b4db, #0083b0);
            color: white;
            border: none;
            padding: 0.9rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0,180,219,0.3);
        }

        .gcash-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,180,219,0.4);
        }

        /* Proof of Payment Card */
        .proof-card {
            background: white;
            border-radius: 20px;
            padding: 1.2rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }

        .proof-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.25rem;
            color: #333;
        }

        .proof-sub {
            font-size: 0.7rem;
            color: #7f8c8d;
            margin-bottom: 1rem;
        }

        /* Upload Area */
        .upload-area {
            width: 100%;
            height: 160px;
            background: #f9f9f9;
            border: 1.5px dashed #ddd;
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
        }

        .upload-area:hover {
            border-color: #001433;
            background: #f5f5f5;
        }

        .upload-area.has-image {
            border: 1.5px solid #4CAF50;
            background: #f0f9f0;
        }

        .upload-icon {
            font-size: 2.5rem;
            color: #001433;
            margin-bottom: 0.5rem;
        }

        .upload-text {
            font-size: 0.8rem;
            color: #666;
        }

        .upload-hint {
            font-size: 0.65rem;
            color: #999;
            margin-top: 0.25rem;
        }

        .preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .preview-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background: rgba(0,0,0,0.5);
            padding: 0.25rem 0.5rem;
            border-radius: 20px 0 0 0;
            font-size: 0.7rem;
            color: white;
        }

        /* Input Field */
        .input-group {
            margin-top: 0.5rem;
        }

        .input-label {
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 0.4rem;
            display: block;
            color: #333;
        }

        .gcash-input {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1.5px solid #e0e0e0;
            border-radius: 14px;
            font-size: 1rem;
            transition: all 0.2s;
            outline: none;
            font-family: monospace;
            font-size: 1rem;
        }

        .gcash-input:focus {
            border-color: #001433;
            box-shadow: 0 0 0 3px rgba(0,20,51,0.1);
        }

        .gcash-input.error {
            border-color: #e74c3c;
        }

        .input-hint {
            font-size: 0.7rem;
            margin-top: 0.3rem;
        }

        .input-hint.error {
            color: #e74c3c;
        }

        .input-hint.valid {
            color: #4CAF50;
        }

        /* Submit Button */
        .submit-btn {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 0.75rem 1rem;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.08);
            border-top: 1px solid #ebedf0;
            z-index: 99;
        }

        .submit-btn button {
            width: 100%;
            background: #4CAF50;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .submit-btn button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76,175,80,0.3);
        }

        .submit-btn button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        /* Modal */
        .qr-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.95);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .qr-modal.active {
            display: flex;
        }

        .qr-modal-content {
            text-align: center;
            padding: 1rem;
        }

        .qr-modal img {
            width: 85%;
            max-width: 350px;
            border-radius: 20px;
            background: white;
            padding: 1rem;
        }

        .qr-modal-text {
            color: white;
            margin-top: 1rem;
        }

        .qr-modal-text p {
            margin: 0.25rem 0;
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #333;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 0.85rem;
            z-index: 1100;
            transition: transform 0.3s;
            white-space: nowrap;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
        }

        .toast.success {
            background: #4CAF50;
        }

        .toast.error {
            background: #e74c3c;
        }

        /* Loading Spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
            .amount-value { font-size: 2.5rem; }
            .qr-image { width: 150px; height: 150px; }
            .toast { white-space: normal; text-align: center; max-width: 80%; }
        }
    </style>
</head>
<body>

<div class="payment-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <h1>Payment</h1>
</div>

<div class="payment-container">
    <!-- Amount Card -->
    <div class="amount-card">
        <div class="amount-label">TOTAL AMOUNT DUE</div>
        <div class="amount-value" id="amountDisplay">₱0.00</div>
        <div class="amount-sub">PalitOra Payment</div>
    </div>

    <!-- QR Code Section -->
    <div class="qr-card">
        <h3>Scan QR Code</h3>
        <p>Open GCash and scan this code</p>
        <div class="qr-image" onclick="openQRModal()">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23001433'/%3E%3Crect x='30' y='30' width='60' height='60' fill='white'/%3E%3Crect x='110' y='30' width='60' height='60' fill='white'/%3E%3Crect x='30' y='110' width='60' height='60' fill='white'/%3E%3Crect x='110' y='110' width='25' height='25' fill='white'/%3E%3Crect x='145' y='110' width='25' height='25' fill='white'/%3E%3Crect x='110' y='145' width='25' height='25' fill='white'/%3E%3Crect x='145' y='145' width='25' height='25' fill='white'/%3E%3C/svg%3E" alt="QR Code">
        </div>
        <div class="qr-hint">
            <i class="fas fa-expand"></i> Tap QR code to enlarge
        </div>
    </div>

    <!-- Instructions -->
    <div class="instruction-card">
        <div class="instruction-header">
            <i class="fas fa-info-circle"></i>
            <span>How to Pay</span>
        </div>
        
        <div class="instruction-step">
            <div class="step-number">1</div>
            <div class="step-text">Click 'Open GCash App' button below</div>
        </div>
        <div class="instruction-step">
            <div class="step-number">2</div>
            <div class="step-text">Scan QR code above</div>
        </div>
        <div class="instruction-step">
            <div class="step-number">3</div>
            <div class="step-text">Complete the payment</div>
        </div>
        <div class="instruction-step">
            <div class="step-number">4</div>
            <div class="step-text">Take a screenshot of successful payment confirmation</div>
        </div>
        <div class="instruction-step">
            <div class="step-number">5</div>
            <div class="step-text">Return and upload the screenshot</div>
        </div>
        <div class="instruction-step">
            <div class="step-number">6</div>
            <div class="step-text">Enter GCash number used</div>
        </div>
        <div class="instruction-step">
            <div class="step-number">7</div>
            <div class="step-text">Click 'Submit Payment Proof'</div>
        </div>

        <div class="divider"></div>
        
        <div class="gcash-note">
            <i class="fab fa-google-play"></i>
            <span>No GCash app? Download it from Google Play Store first.</span>
        </div>
    </div>

    <!-- Pay with GCash Button -->
    <button class="gcash-btn" onclick="openGCashApp()">
        <i class="fab fa-gcash" style="font-size: 1.2rem;"></i>
        Open GCash App
    </button>

    <!-- Proof of Payment -->
    <div class="proof-card">
        <div class="proof-title">Proof of Payment</div>
        <div class="proof-sub">Upload screenshot and enter GCash number used</div>

        <!-- Upload Area -->
        <div class="upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()">
            <i class="fas fa-camera upload-icon"></i>
            <div class="upload-text">Tap to upload payment screenshot</div>
            <div class="upload-hint">Show successful payment confirmation</div>
            <img id="previewImg" class="preview-image" style="display: none;">
            <div id="previewOverlay" class="preview-overlay" style="display: none;">
                <i class="fas fa-check-circle"></i> Selected
            </div>
        </div>
        <input type="file" id="fileInput" accept="image/*" style="display: none;" onchange="handleImageUpload(event)">

        <!-- GCash Number Input -->
        <div class="input-group">
            <label class="input-label">GCash Number Used</label>
            <input type="tel" 
                   id="gcashNumber" 
                   class="gcash-input" 
                   placeholder="09*********"
                   maxlength="11"
                   pattern="[0-9]{11}"
                   oninput="validateGCashNumber()">
            <div id="gcashHint" class="input-hint"></div>
        </div>
    </div>
</div>

<!-- Submit Button -->
<div class="submit-btn">
    <button id="submitBtn" onclick="submitPayment()" disabled>
        <i class="fas fa-check-circle"></i>
        <span id="submitText">Submit Payment Proof</span>
    </button>
</div>

<!-- QR Modal -->
<div id="qrModal" class="qr-modal" onclick="closeQRModal()">
    <div class="qr-modal-content">
        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23001433'/%3E%3Crect x='30' y='30' width='60' height='60' fill='white'/%3E%3Crect x='110' y='30' width='60' height='60' fill='white'/%3E%3Crect x='30' y='110' width='60' height='60' fill='white'/%3E%3Crect x='110' y='110' width='25' height='25' fill='white'/%3E%3Crect x='145' y='110' width='25' height='25' fill='white'/%3E%3Crect x='110' y='145' width='25' height='25' fill='white'/%3E%3Crect x='145' y='145' width='25' height='25' fill='white'/%3E%3C/svg%3E" alt="QR Code">
        <div class="qr-modal-text">
            <p>Scan this QR code using GCash</p>
            <p style="font-size: 0.8rem; opacity: 0.7;">Open GCash → Tap QR → Scan</p>
            <p style="font-size: 0.7rem; opacity: 0.5; margin-top: 0.5rem;">Tap anywhere to close</p>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="toast"></div>

<script>
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    let amount = parseFloat(urlParams.get('amount')) || 299.00;
    
    // State variables
    let selectedImage = null;
    let isSubmitting = false;
    
    // Display amount
    document.getElementById('amountDisplay').innerText = `₱${amount.toFixed(2)}`;
    
    // Handle image upload
    function handleImageUpload(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                selectedImage = e.target.result;
                const previewImg = document.getElementById('previewImg');
                const previewOverlay = document.getElementById('previewOverlay');
                const uploadArea = document.getElementById('uploadArea');
                
                previewImg.src = selectedImage;
                previewImg.style.display = 'block';
                previewOverlay.style.display = 'block';
                uploadArea.classList.add('has-image');
                
                checkFormComplete();
            };
            reader.readAsDataURL(file);
        }
    }
    
    // Validate GCash number
    function validateGCashNumber() {
        const input = document.getElementById('gcashNumber');
        const hint = document.getElementById('gcashHint');
        const value = input.value;
        
        if (value.length === 0) {
            hint.innerHTML = '';
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
            hint.innerHTML = '✓ Valid GCash number';
            hint.className = 'input-hint valid';
            input.classList.remove('error');
        } else {
            hint.innerHTML = '';
            input.classList.remove('error');
        }
        
        checkFormComplete();
    }
    
    // Check if form is complete
    function checkFormComplete() {
        const gcashNumber = document.getElementById('gcashNumber').value;
        const isImageSelected = selectedImage !== null;
        const isGcashValid = gcashNumber.length === 11 && gcashNumber.startsWith('09');
        const isComplete = isImageSelected && isGcashValid && !isSubmitting;
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = !isComplete;
        
        if (!isComplete) {
            let reason = '';
            if (!isImageSelected) reason = 'Upload payment screenshot';
            else if (!isGcashValid) reason = 'Enter valid GCash number';
            if (reason) submitBtn.title = reason;
        } else {
            submitBtn.title = '';
        }
    }
    
    // Open GCash app (mobile) or show message (desktop)
    function openGCashApp() {
        // For mobile: try to open GCash app
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        if (isMobile) {
            // Try to open GCash app
            window.location.href = 'gcash://';
            
            // Fallback: show toast if app doesn't open
            setTimeout(() => {
                showToast('If GCash doesn\'t open, please open it manually', 'info');
            }, 1000);
        } else {
            showToast('Please open GCash on your mobile device', 'info');
        }
    }
    
    // Open QR modal
    function openQRModal() {
        document.getElementById('qrModal').classList.add('active');
    }
    
    // Close QR modal
    function closeQRModal() {
        document.getElementById('qrModal').classList.remove('active');
    }
    
    // Show toast message
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = `toast ${type}`;
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }
    
    // Submit payment
    function submitPayment() {
        const gcashNumber = document.getElementById('gcashNumber').value;
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        
        if (isSubmitting) return;
        
        isSubmitting = true;
        submitBtn.disabled = true;
        submitText.innerHTML = '<div class="spinner" style="margin-right: 8px;"></div> Processing...';
        
        // Simulate API call
        setTimeout(() => {
            // Here you would actually send to your backend
            console.log('Payment submitted:', {
                amount: amount,
                gcash_number: gcashNumber,
                proof_image: selectedImage ? 'image_data_here' : null
            });
            
            showToast('Payment proof submitted successfully!', 'success');
            
            setTimeout(() => {
                // Redirect back to orders or dashboard
                window.location.href = '/seller/ui/orders.php';
            }, 1500);
        }, 2000);
    }
    
    // Add input event listener for GCash number
    document.getElementById('gcashNumber').addEventListener('input', validateGCashNumber);
    
    // Prevent modal close when clicking on content
    document.getElementById('qrModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeQRModal();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQRModal();
        }
    });
</script>

</body>
</html>