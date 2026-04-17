<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #e67e22;
            --success: #2ecc71;
            --danger: #e74c3c;
            --dark: #2c3e50;
            --gray: #7f8c8d;
            --light: #ecf0f1;
            --border: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #f5f7fa;
            color: var(--dark);
            min-height: 100vh;
        }

        /* Header */
        .page-header {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 1.25rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .back-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: var(--gray);
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-btn:hover {
            background: var(--light);
            color: var(--primary);
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        /* Main Container */
        .payment-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        /* Two Column Layout */
        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: #fafbfc;
        }

        .card-header h2 {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-header h2 i {
            color: var(--primary);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Amount Card */
        .amount-card {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .amount-label {
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        .amount-value {
            color: white;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .amount-sub {
            color: rgba(255,255,255,0.6);
            font-size: 0.8rem;
        }

        /* QR Section */
        .qr-section {
            text-align: center;
        }

        .qr-image {
            width: 200px;
            height: 200px;
            margin: 0 auto 1rem;
            background: white;
            border-radius: 16px;
            padding: 0.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: pointer;
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
            font-size: 0.75rem;
            color: var(--gray);
            margin-top: 0.5rem;
        }

        /* Instructions */
        .instruction-list {
            list-style: none;
        }

        .instruction-item {
            display: flex;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
        }

        .instruction-item:last-child {
            border-bottom: none;
        }

        .step-number {
            width: 26px;
            height: 26px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }

        .step-text {
            font-size: 0.85rem;
            color: #4a5568;
            line-height: 1.4;
        }

        /* Upload Area */
        .upload-area {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #fafbfc;
        }

        .upload-area:hover {
            border-color: var(--primary);
            background: #f0f7ff;
        }

        .upload-area.has-image {
            border-color: var(--success);
            background: #f0fdf4;
            padding: 0;
            position: relative;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .upload-icon {
            font-size: 2.5rem;
            color: var(--gray);
            margin-bottom: 0.75rem;
        }

        .upload-text {
            font-size: 0.9rem;
            color: var(--dark);
        }

        .upload-hint {
            font-size: 0.7rem;
            color: var(--gray);
            margin-top: 0.5rem;
        }

        .preview-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 12px;
            max-height: 200px;
        }

        .remove-image {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(0,0,0,0.6);
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .remove-image:hover {
            background: var(--danger);
        }

        /* Form Inputs */
        .form-group {
            margin-top: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.2s;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }

        .form-input.error {
            border-color: var(--danger);
        }

        .input-hint {
            font-size: 0.7rem;
            margin-top: 0.4rem;
        }

        .input-hint.error {
            color: var(--danger);
        }

        .input-hint.success {
            color: var(--success);
        }

        /* Submit Button */
        .submit-section {
            margin-top: 2rem;
            text-align: center;
        }

        .submit-btn {
            background: var(--success);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .submit-btn:hover:not(:disabled) {
            background: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46,204,113,0.3);
        }

        .submit-btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            max-width: 400px;
            width: 90%;
            text-align: center;
            cursor: default;
        }

        .modal-content img {
            width: 100%;
            border-radius: 12px;
        }

        .modal-close {
            margin-top: 1rem;
            padding: 0.5rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--dark);
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
            background: var(--success);
        }

        .toast.error {
            background: var(--danger);
        }

        /* Footer */
        .page-footer {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
            font-size: 0.8rem;
            border-top: 1px solid var(--border);
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .payment-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .payment-container {
                padding: 0 1rem;
            }
            
            .amount-value {
                font-size: 2rem;
            }
            
            .qr-image {
                width: 160px;
                height: 160px;
            }
            
            .toast {
                white-space: normal;
                text-align: center;
                max-width: 80%;
            }
        }
    </style>
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
            <!-- Amount Card -->
            <div class="amount-card">
                <div class="amount-label">Total Amount Due</div>
                <div class="amount-value" id="amountDisplay">₱0.00</div>
                <div class="amount-sub">PalitOra Payment</div>
            </div>

            <!-- QR Code Card -->
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
            <!-- Instructions Card -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> How to Pay</h2>
                </div>
                <div class="card-body">
                    <ul class="instruction-list">
                        <li class="instruction-item">
                            <span class="step-number">1</span>
                            <span class="step-text">Scan the QR code using GCash or any QR-enabled payment app</span>
                        </li>
                        <li class="instruction-item">
                            <span class="step-number">2</span>
                            <span class="step-text">Enter the exact amount: <strong id="instructionAmount">₱0.00</strong></span>
                        </li>
                        <li class="instruction-item">
                            <span class="step-number">3</span>
                            <span class="step-text">Complete the payment and take a screenshot of the confirmation</span>
                        </li>
                        <li class="instruction-item">
                            <span class="step-number">4</span>
                            <span class="step-text">Upload the screenshot below and enter your GCash number</span>
                        </li>
                        <li class="instruction-item">
                            <span class="step-number">5</span>
                            <span class="step-text">Click "Submit Payment Proof" to complete your transaction</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Proof of Payment Card -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-receipt"></i> Proof of Payment</h2>
                </div>
                <div class="card-body">
                    <!-- Upload Area -->
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

                    <!-- GCash Number Input -->
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
    <p>&copy; 2026 PalitOra Seller Dashboard. All rights reserved.</p>
</footer>

<!-- QR Modal -->
<div id="qrModal" class="modal" onclick="closeQRModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <img src="/seller/image/qr.jpg" alt="QR Code" onerror="this.src='https://placehold.co/300x300?text=QR+Code'">
        <button class="modal-close" onclick="closeQRModal()">Close</button>
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
    document.getElementById('instructionAmount').innerText = `₱${amount.toFixed(2)}`;
    
    // Handle image upload
    function handleImageUpload(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                selectedImage = e.target.result;
                const previewImg = document.getElementById('previewImg');
                const removeBtn = document.getElementById('removeImageBtn');
                const uploadArea = document.getElementById('uploadArea');
                
                previewImg.src = selectedImage;
                previewImg.style.display = 'block';
                removeBtn.style.display = 'flex';
                uploadArea.classList.add('has-image');
                
                // Hide upload text
                const uploadIcon = uploadArea.querySelector('.upload-icon');
                const uploadText = uploadArea.querySelector('.upload-text');
                const uploadHint = uploadArea.querySelector('.upload-hint');
                if (uploadIcon) uploadIcon.style.display = 'none';
                if (uploadText) uploadText.style.display = 'none';
                if (uploadHint) uploadHint.style.display = 'none';
                
                checkFormComplete();
            };
            reader.readAsDataURL(file);
        } else {
            showToast('Please select a valid image file', 'error');
        }
    }
    
    // Remove uploaded image
    function removeImage() {
        selectedImage = null;
        const previewImg = document.getElementById('previewImg');
        const removeBtn = document.getElementById('removeImageBtn');
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        
        previewImg.style.display = 'none';
        removeBtn.style.display = 'none';
        uploadArea.classList.remove('has-image');
        
        // Show upload text again
        const uploadIcon = uploadArea.querySelector('.upload-icon');
        const uploadText = uploadArea.querySelector('.upload-text');
        const uploadHint = uploadArea.querySelector('.upload-hint');
        if (uploadIcon) uploadIcon.style.display = 'block';
        if (uploadText) uploadText.style.display = 'block';
        if (uploadHint) uploadHint.style.display = 'block';
        
        fileInput.value = '';
        
        checkFormComplete();
    }
    
    // Validate GCash number
    function validateGCashNumber() {
        const input = document.getElementById('gcashNumber');
        const hint = document.getElementById('gcashHint');
        const value = input.value;
        
        // Allow only digits
        if (value && !/^\d+$/.test(value)) {
            input.value = value.replace(/\D/g, '');
        }
        
        const cleanValue = input.value;
        
        if (cleanValue.length === 0) {
            hint.innerHTML = '';
            hint.className = 'input-hint';
            input.classList.remove('error');
        } else if (cleanValue.length < 11) {
            hint.innerHTML = 'Please enter a complete 11-digit GCash number';
            hint.className = 'input-hint error';
            input.classList.add('error');
        } else if (cleanValue.length === 11 && !cleanValue.startsWith('09')) {
            hint.innerHTML = 'GCash number must start with "09"';
            hint.className = 'input-hint error';
            input.classList.add('error');
        } else if (cleanValue.length === 11 && cleanValue.startsWith('09')) {
            hint.innerHTML = '<i class="fas fa-check-circle"></i> Valid GCash number';
            hint.className = 'input-hint success';
            input.classList.remove('error');
        } else {
            hint.innerHTML = '';
            hint.className = 'input-hint';
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
        submitText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
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
                window.location.href = '/seller/ui/orders.php';
            }, 1500);
        }, 2000);
    }
    
    // Add input event listener for GCash number
    document.getElementById('gcashNumber').addEventListener('input', validateGCashNumber);
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQRModal();
        }
    });
</script>

</body>
</html>