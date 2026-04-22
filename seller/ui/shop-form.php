<?php
// /seller/ui/shop-form.php
session_start();
if (!isset($_SESSION['seller_id'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Setup</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/shop-form.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/error.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/openlocationcode/latest/openlocationcode.min.js"></script>
    <style>
        .upload-preview {
            max-width: 160px;
            margin-top: 8px;
            border-radius: 6px;
            border: 1px solid #ddd;
            display: block;
        }
        .multi-preview {
            margin-top: 8px;
            font-size: 0.9rem;
            color: #555;
        }
        .multi-preview li {
            margin-bottom: 4px;
        }
        
        /* Disabled submit button */
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #95a5a6 !important;
        }
        
        .submit-btn:disabled:hover {
            background: #95a5a6 !important;
            transform: none !important;
        }
        
        /* Upload indicator - fixed animation */
        .uploading-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: 10px;
            color: #3498db;
            font-size: 13px;
        }
        
        .uploading-indicator .fa-spinner {
            animation: spin 1s linear infinite;
        }
        
        .uploading-indicator .fa-check-circle,
        .uploading-indicator .fa-times-circle {
            animation: none !important;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Upload status message */
        .upload-status {
            margin-top: 20px;
            padding: 12px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 14px;
            color: #7f8c8d;
            text-align: center;
            transition: all 0.3s;
        }
        
        .upload-status.has-uploads {
            background: #e8f5e9;
            color: #27ae60;
        }
        
        .upload-status.uploading {
            background: #fff3e0;
            color: #e67e22;
        }
    </style>
</head>
<body>

    <section class="shop-setup">
        <div class="container">
            <div class="shop-card">
                <div class="shop-header">
                    <h2>Set Up Your Shop</h2>
                    <p>Complete your shop profile to start selling on the platform.</p>
                </div>

                <form id="shopSetupForm" method="POST" action="/seller/backend/shop-form/process-shop-setup.php">

                    <!-- Shop Information -->
                    <div class="form-section">
                        <h3>Shop Information</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="store_name">Store Name *</label>
                                <input type="text" id="store_name" name="store_name" placeholder="e.g. Mang Kiko's Milk Tea" required>
                                <div class="help-text">Use your brand or shop name — this will appear in search results</div>
                            </div>
                            <div class="form-group">
                                <label for="category">Store Category *</label>
                                <select id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option>Electronics</option>
                                    <option>Fashion & Clothing</option>
                                    <option>Home & Garden</option>
                                    <option>Sports & Outdoors</option>
                                    <option>Books & Media</option>
                                    <option>Beauty & Cosmetics</option>
                                    <option>Toys & Games</option>
                                    <option>Automotive</option>
                                    <option>Health</option>
                                    <option>Art & Crafts</option>
                                </select>
                                <div class="help-text">Choose the main category of products you sell</div>
                            </div>
                            <div class="form-group full">
                                <label for="description">Store Description *</label>
                                <textarea id="description" name="description" placeholder="Tell customers what makes your shop special..." required></textarea>
                                <div class="help-text">Keep it short but attractive (80–300 characters recommended)</div>
                            </div>
                            <div class="form-group">
                                <label for="contact">Contact Number *</label>
                                <input type="tel" id="contact" name="contact" placeholder="09123456789" required>
                                <div class="help-text">We'll use this for order updates and verification</div>
                            </div>
                            <div class="form-group">
                                <label for="open_time">Open Time</label>
                                <input type="time" id="open_time" name="open_time">
                                <div class="help-text">When do you usually open?</div>
                            </div>
                            <div class="form-group">
                                <label for="close_time">Close Time</label>
                                <input type="time" id="close_time" name="close_time">
                                <div class="help-text">When do you usually close?</div>
                            </div>
                        </div>
                    </div>

                    <!-- Shop Location -->
                    <div class="form-section">
                        <h3>Shop Location</h3>
                        <div class="form-grid">
                            <div class="form-group full">
                                <label for="gps_display">Detect Shop Location (GPS) *</label>
                                <input type="text" id="gps_display" readonly placeholder="Click here to auto-detect using GPS" style="cursor:pointer;">
                                <div class="help-text">
                                    Click to use your device's GPS (allow permission when prompted).<br>
                                    This fills latitude/longitude and attempts to generate the Plus Code.
                                </div>
                                <input type="hidden" id="latitude" name="latitude" required>
                                <input type="hidden" id="longitude" name="longitude" required>
                            </div>
                            <div class="form-group full">
                                <label for="plus_code">Google Plus Code *</label>
                                <div class="plus-code-wrapper">
                                    <input type="text" id="plus_code" name="plus_code" placeholder="e.g. 6QX58844+P7" required>
                                    <button type="button" id="copy_plus_code">Copy</button>
                                </div>
                                <div class="help-text">
                                    Automatically filled from GPS if possible.<br>
                                    If it doesn't fill: In Google Maps, long-press your shop location → Share → copy the Plus Code (e.g. XXXXXX+XX).
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GCash Information -->
                    <div class="form-section">
                        <h3>GCash Information (For Payouts)</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="gcash_name">GCash Account Name</label>
                                <input type="text" id="gcash_name" name="gcash_name" placeholder="e.g. Juan Dela Cruz">
                                <div class="help-text">Enter the name registered to your GCash account</div>
                            </div>
                            <div class="form-group">
                                <label for="gcash_number">GCash Number</label>
                                <input type="tel" id="gcash_number" name="gcash_number" placeholder="09123456789" pattern="09[0-9]{9}">
                                <div class="help-text">Your 11-digit GCash mobile number (09XXXXXXXXX)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Shop Media -->
                    <div class="form-section">
                        <h3>Shop Media</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="logo">Store Logo</label>
                                <input type="file" id="logo" accept="image/*">
                                <div class="help-text">Recommended: 500×500 px, PNG or JPG</div>
                                <img id="logo_preview" class="upload-preview" style="display:none;" alt="Logo preview">
                                <input type="hidden" name="logo_url" id="logo_url">
                                <span id="logo_status" class="uploading-indicator" style="display:none;"></span>
                            </div>

                            <div class="form-group">
                                <label for="banner">Store Banner</label>
                                <input type="file" id="banner" accept="image/*">
                                <div class="help-text">Best size: 1200×400 px or wider</div>
                                <img id="banner_preview" class="upload-preview" style="display:none;" alt="Banner preview">
                                <input type="hidden" name="banner_url" id="banner_url">
                                <span id="banner_status" class="uploading-indicator" style="display:none;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Shop Verification -->
                    <div class="form-section">
                        <h3>Shop Verification</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="owner_name">Owner Full Name *</label>
                                <input type="text" id="owner_name" name="owner_name" placeholder="Juan Dela Cruz" required>
                                <div class="help-text">Must match the name on your valid ID</div>
                            </div>
                            <div class="form-group">
                                <label for="id_type">Valid ID Type *</label>
                                <select id="id_type" name="id_type" required>
                                    <option value="">Select ID Type</option>
                                    <option>National ID</option>
                                    <option>Driver's License</option>
                                    <option>Passport</option>
                                    <option>Student ID</option>
                                    <option>SSS / UMID</option>
                                    <option>Others</option>
                                </select>
                                <div class="help-text">Choose the government-issued ID you will upload</div>
                            </div>
                            <div class="form-group">
                                <label for="valid_id">Upload Valid ID (Front & Back) *</label>
                                <input type="file" id="valid_id" accept="image/*,.pdf" multiple>
                                <div class="help-text">
                                    <strong>Required:</strong> Upload clear photos/scans of both <strong>front</strong> and <strong>back</strong> sides.<br>
                                    You can select multiple files at once (max 4 recommended).
                                </div>
                                <ul id="valid_id_list" class="multi-preview"></ul>
                                <input type="hidden" name="valid_id_urls" id="valid_id_urls">
                                <span id="valid_id_status" class="uploading-indicator" style="display:none;"></span>
                            </div>
                            <div class="form-group">
                                <label for="store_photos">Upload Store Photos *</label>
                                <input type="file" id="store_photos" accept="image/*" multiple>
                                <div class="help-text">Upload 2–6 clear photos of your shop (inside, outside, products, etc.)</div>
                                <ul id="store_photos_list" class="multi-preview"></ul>
                                <input type="hidden" name="store_photo_urls" id="store_photo_urls">
                                <span id="store_photos_status" class="uploading-indicator" style="display:none;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Status -->
                    <div id="uploadStatus" class="upload-status">
                        <span id="uploadStatusText">Ready to submit</span>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        Save Shop Information
                    </button>
                </form>
            </div>
        </div>
    </section>

    <script>
        const UPLOAD_ENDPOINT = '/connection/upload_apis/upload-seller-media.php';
        
        // Track upload states
        let activeUploads = 0;
        const submitBtn = document.getElementById('submitBtn');
        const uploadStatusText = document.getElementById('uploadStatusText');
        const uploadStatusDiv = document.getElementById('uploadStatus');
        
        // Update submit button state
        function updateSubmitButton() {
            if (activeUploads > 0) {
                submitBtn.disabled = true;
                uploadStatusDiv.className = 'upload-status uploading';
                uploadStatusText.textContent = `Uploading ${activeUploads} file(s)... Please wait`;
            } else {
                submitBtn.disabled = false;
                uploadStatusDiv.className = 'upload-status has-uploads';
                uploadStatusText.textContent = 'All uploads complete. Ready to submit.';
            }
        }
        
        // Check if any required images are missing
        function validateRequiredImages() {
            const validIdUrls = document.getElementById('valid_id_urls').value;
            const storePhotoUrls = document.getElementById('store_photo_urls').value;
            
            if (!validIdUrls || validIdUrls === '[]') {
                alert('Please upload your valid ID (front and back)');
                return false;
            }
            
            if (!storePhotoUrls || storePhotoUrls === '[]') {
                alert('Please upload at least one store photo');
                return false;
            }
            
            return true;
        }

        // Single file upload helper - FIXED
        async function uploadSingleFile(file, type, previewId, hiddenInputId, statusId) {
            if (!file) return;

            const statusEl = document.getElementById(statusId);
            activeUploads++;
            updateSubmitButton();
            
            if (statusEl) {
                statusEl.style.display = 'inline-flex';
                statusEl.innerHTML = '<i class="fas fa-spinner"></i> Uploading...';
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', type);

            try {
                const res = await fetch(UPLOAD_ENDPOINT, {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();

                if (!data.success) {
                    alert(`Upload failed (${type}): ${data.error || 'Unknown error'}`);
                    if (statusEl) {
                        statusEl.innerHTML = '<i class="fas fa-times-circle" style="color: #e74c3c; animation: none;"></i> Failed';
                    }
                    return;
                }

                const url = data.files[0]?.url;
                if (url) {
                    document.getElementById(hiddenInputId).value = url;

                    const preview = document.getElementById(previewId);
                    preview.src = url;
                    preview.style.display = 'block';
                    
                    if (statusEl) {
                        statusEl.innerHTML = '<i class="fas fa-check-circle" style="color: #27ae60; animation: none;"></i> Uploaded';
                    }
                }
            } catch (err) {
                console.error(err);
                alert(`Network error uploading ${type}`);
                if (statusEl) {
                    statusEl.innerHTML = '<i class="fas fa-times-circle" style="color: #e74c3c; animation: none;"></i> Error';
                }
            } finally {
                activeUploads--;
                updateSubmitButton();
            }
        }

        // Multiple files upload helper - FIXED
        async function uploadMultipleFiles(files, type, listId, hiddenInputId, statusId) {
            if (files.length === 0) return;

            const statusEl = document.getElementById(statusId);
            activeUploads++;
            updateSubmitButton();
            
            if (statusEl) {
                statusEl.style.display = 'inline-flex';
                statusEl.innerHTML = '<i class="fas fa-spinner"></i> Uploading...';
            }

            const formData = new FormData();
            for (let file of files) {
                formData.append('files[]', file);
            }
            formData.append('type', type);

            try {
                const res = await fetch(UPLOAD_ENDPOINT, {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();

                if (!data.success) {
                    alert(`Upload failed (${type}): ${data.error || 'Unknown error'}`);
                    if (statusEl) {
                        statusEl.innerHTML = '<i class="fas fa-times-circle" style="color: #e74c3c; animation: none;"></i> Failed';
                    }
                    return;
                }

                const urls = data.files.map(f => f.url);
                document.getElementById(hiddenInputId).value = JSON.stringify(urls);

                const list = document.getElementById(listId);
                list.innerHTML = '';
                urls.forEach((url, i) => {
                    const li = document.createElement('li');
                    li.textContent = `File ${i+1} uploaded ✓`;
                    list.appendChild(li);
                });
                
                if (statusEl) {
                    statusEl.innerHTML = '<i class="fas fa-check-circle" style="color: #27ae60; animation: none;"></i> Uploaded';
                }
            } catch (err) {
                console.error(err);
                alert(`Network error uploading ${type} files`);
                if (statusEl) {
                    statusEl.innerHTML = '<i class="fas fa-times-circle" style="color: #e74c3c; animation: none;"></i> Error';
                }
            } finally {
                activeUploads--;
                updateSubmitButton();
            }
        }

        // Event listeners
        document.getElementById('logo').addEventListener('change', (e) => {
            if (e.target.files[0]) {
                uploadSingleFile(e.target.files[0], 'logo', 'logo_preview', 'logo_url', 'logo_status');
            }
        });

        document.getElementById('banner').addEventListener('change', (e) => {
            if (e.target.files[0]) {
                uploadSingleFile(e.target.files[0], 'banner', 'banner_preview', 'banner_url', 'banner_status');
            }
        });

        document.getElementById('valid_id').addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                uploadMultipleFiles(e.target.files, 'valid_id', 'valid_id_list', 'valid_id_urls', 'valid_id_status');
            }
        });

        document.getElementById('store_photos').addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                uploadMultipleFiles(e.target.files, 'store_photos', 'store_photos_list', 'store_photo_urls', 'store_photos_status');
            }
        });

        // GCash number validation
        document.getElementById('gcash_number').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }
        });

        // Form submit validation
        document.getElementById('shopSetupForm').addEventListener('submit', function(e) {
            if (activeUploads > 0) {
                e.preventDefault();
                alert('Please wait for all images to finish uploading.');
                return false;
            }
            
            const gcashNumber = document.getElementById('gcash_number').value;
            
            if (gcashNumber && !/^09[0-9]{9}$/.test(gcashNumber)) {
                e.preventDefault();
                alert('GCash number must be 11 digits starting with 09 (e.g., 09123456789)');
                return false;
            }
            
            if (!validateRequiredImages()) {
                e.preventDefault();
                return false;
            }
        });

        // GPS + Plus Code logic
        const gpsInput = document.getElementById('gps_display');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const plusCodeInput = document.getElementById('plus_code');
        const copyBtn = document.getElementById('copy_plus_code');

        if (typeof OpenLocationCode === 'undefined') {
            console.error("OpenLocationCode library failed to load.");
            plusCodeInput.value = "Library issue – paste short code from Google Maps";
        } else {
            console.log("OpenLocationCode loaded OK.");
        }

        const dumagueteRef = { lat: 9.3064, lng: 123.3054 };

        gpsInput.addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert("Geolocation not supported.");
                return;
            }
            gpsInput.value = "Detecting... allow permission";
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    gpsInput.value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                    latInput.value = lat;
                    lngInput.value = lng;

                    if (typeof OpenLocationCode !== 'undefined') {
                        try {
                            const fullCode = OpenLocationCode.encode(lat, lng, 11);
                            console.log("Full Plus Code:", fullCode);

                            let shortCode = fullCode;
                            try {
                                shortCode = OpenLocationCode.shorten(fullCode, dumagueteRef.lat, dumagueteRef.lng);
                                console.log("Shortened Plus Code:", shortCode);
                            } catch (shortenErr) {
                                console.warn("Shortening failed (possibly out of area):", shortenErr);
                            }

                            plusCodeInput.value = `${shortCode} Dumaguete City`;

                            const mapsUrl = `https://plus.codes/${shortCode},Dumaguete City`;
                            window.open(mapsUrl, '_blank');
                        } catch (err) {
                            console.error("Plus Code error:", err);
                            plusCodeInput.value = "Auto-generation failed – copy short code from Google Maps";
                        }
                    } else {
                        plusCodeInput.value = "Library not ready – copy from Google Maps";
                    }
                },
                (error) => {
                    let msg = "Location error.";
                    if (error.code === 1) msg = "Permission denied – allow access.";
                    gpsInput.value = msg;
                    alert(msg + "\nPaste short Plus Code from Google Maps.");
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        });

        copyBtn.addEventListener('click', () => {
            if (!plusCodeInput.value.trim() || plusCodeInput.value.includes('failed')) {
                alert("No valid code to copy.");
                return;
            }
            navigator.clipboard.writeText(plusCodeInput.value)
                .then(() => alert('Copied! (Include the city name when sharing)'))
                .catch(() => alert('Copy failed – select manually.'));
        });
        
        // Initialize
        updateSubmitButton();
    </script>
</body>
</html>