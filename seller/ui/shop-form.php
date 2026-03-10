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

                <!-- Form now submits text + URLs to the processing script -->
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
                                    <option>Select Category</option>
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
                                <div class="help-text">We’ll use this for order updates and verification</div>
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

                    <!-- Shop Media – now with Cloudinary upload -->
                    <div class="form-section">
                        <h3>Shop Media</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="logo">Store Logo</label>
                                <input type="file" id="logo" accept="image/*">
                                <div class="help-text">Recommended: 500×500 px, PNG or JPG</div>
                                <img id="logo_preview" class="upload-preview" style="display:none;" alt="Logo preview">
                                <!-- Hidden field for Cloudinary URL -->
                                <input type="hidden" name="logo_url" id="logo_url">
                            </div>

                            <div class="form-group">
                                <label for="banner">Store Banner</label>
                                <input type="file" id="banner" accept="image/*">
                                <div class="help-text">Best size: 1200×400 px or wider</div>
                                <img id="banner_preview" class="upload-preview" style="display:none;" alt="Banner preview">
                                <input type="hidden" name="banner_url" id="banner_url">
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
                            </div>
                            <div class="form-group">
                                <label for="store_photos">Upload Store Photos *</label>
                                <input type="file" id="store_photos" accept="image/*" multiple>
                                <div class="help-text">Upload 2–6 clear photos of your shop (inside, outside, products, etc.)</div>
                                <ul id="store_photos_list" class="multi-preview"></ul>
                                <input type="hidden" name="store_photo_urls" id="store_photo_urls">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        Save Shop Information
                    </button>
                </form>
            </div>
        </div>
    </section>

    <script>
        const UPLOAD_ENDPOINT = '/connection/upload_apis/upload-seller-media.php';  // ← your Cloudinary endpoint

        // ────────────────────────────────────────────────
        // Single file upload helper (logo, banner)
        // ────────────────────────────────────────────────
        async function uploadSingleFile(file, type, previewId, hiddenInputId) {
            if (!file) return;

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
                    return;
                }

                const url = data.files[0]?.url;
                if (url) {
                    document.getElementById(hiddenInputId).value = url;

                    const preview = document.getElementById(previewId);
                    preview.src = url;
                    preview.style.display = 'block';
                }
            } catch (err) {
                console.error(err);
                alert(`Network error uploading ${type}`);
            }
        }

        // ────────────────────────────────────────────────
        // Multiple files upload helper (valid_id, store_photos)
        // ────────────────────────────────────────────────
        async function uploadMultipleFiles(files, type, listId, hiddenInputId) {
            if (files.length === 0) return;

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
                    return;
                }

                const urls = data.files.map(f => f.url);
                document.getElementById(hiddenInputId).value = JSON.stringify(urls);

                // Show list of uploaded filenames
                const list = document.getElementById(listId);
                list.innerHTML = '';
                urls.forEach((url, i) => {
                    const li = document.createElement('li');
                    li.textContent = `File ${i+1} uploaded`;
                    list.appendChild(li);
                });

            } catch (err) {
                console.error(err);
                alert(`Network error uploading ${type} files`);
            }
        }

        // ────────────────────────────────────────────────
        // Event listeners for file inputs
        // ────────────────────────────────────────────────

        document.getElementById('logo').addEventListener('change', (e) => {
            uploadSingleFile(e.target.files[0], 'logo', 'logo_preview', 'logo_url');
        });

        document.getElementById('banner').addEventListener('change', (e) => {
            uploadSingleFile(e.target.files[0], 'banner', 'banner_preview', 'banner_url');
        });

        document.getElementById('valid_id').addEventListener('change', (e) => {
            uploadMultipleFiles(e.target.files, 'valid_id', 'valid_id_list', 'valid_id_urls');
        });

        document.getElementById('store_photos').addEventListener('change', (e) => {
            uploadMultipleFiles(e.target.files, 'store_photos', 'store_photos_list', 'store_photo_urls');
        });

        // ────────────────────────────────────────────────
        // Your original GPS + Plus Code logic (unchanged)
        // ────────────────────────────────────────────────
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
    </script>
</body>
</html>