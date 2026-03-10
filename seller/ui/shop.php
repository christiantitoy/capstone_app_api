<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Setup</title>
    <!-- Reliable CDN from official recommendation (jsDelivr latest) -->
    <script src="https://cdn.jsdelivr.net/openlocationcode/latest/openlocationcode.min.js"></script>
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #e67e22;
            --secondary-dark: #d35400;
            --success: #2ecc71;
            --dark: #2c3e50;
            --gray: #7f8c8d;
            --light: #ecf0f1;
            --bg-light: #f9fafb;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg-light);
            color: var(--dark);
            line-height: 1.5;
        }
        .container { max-width:1000px; margin:auto; padding:2rem; }
        .shop-card {
            background:white;
            padding:3rem;
            border-radius:14px;
            box-shadow:0 10px 30px rgba(0,0,0,0.08);
        }
        .shop-header { text-align:center; margin-bottom:2.5rem; }
        .shop-header h2 { font-size:2rem; margin-bottom:0.4rem; }
        .shop-header p { color:var(--gray); }
        .form-section { margin-bottom:2.8rem; }
        .form-section h3 { margin-bottom:1.1rem; color:var(--primary-dark); }
        .form-grid {
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(240px,1fr));
            gap:1.3rem;
        }
        .form-group { display:flex; flex-direction:column; }
        .form-group.full { grid-column:1/-1; }
        label { font-weight:600; margin-bottom:0.4rem; }
        input, select, textarea {
            padding:0.75rem;
            border-radius:6px;
            border:1px solid #d1d5db;
            font-size:0.97rem;
        }
        textarea { min-height:100px; resize:vertical; }
        input:focus, select:focus, textarea:focus {
            border-color:var(--primary);
            outline:none;
            box-shadow:0 0 0 3px rgba(52,152,219,0.15);
        }
        .help-text { font-size:0.82rem; color:#6b7280; margin-top:0.35rem; }
        .submit-btn {
            width:100%;
            padding:1rem;
            border:none;
            border-radius:8px;
            background:var(--secondary);
            color:white;
            font-weight:600;
            font-size:1.1rem;
            cursor:pointer;
            margin-top:1.5rem;
        }
        .submit-btn:hover { background:var(--secondary-dark); }
        #gps_display {
            background:#e3f2fd;
            border:2px dashed var(--primary);
            text-align:center;
            font-weight:500;
            cursor:pointer;
        }
        #gps_display:hover { background:#bbdefb; }
        .plus-code-wrapper { position:relative; }
        #plus_code {
            width: 100%;
            max-width: 500px;           /* Made noticeably larger */
            padding: 0.75rem 100px 0.75rem 0.75rem;  /* Extra right padding for button */
            font-family: monospace;     /* Easier to read codes */
            font-size: 1.05rem;
        }
        #copy_plus_code {
            position:absolute;
            right:8px;
            top:50%;
            transform:translateY(-50%);
            padding:8px 14px;
            font-size:0.9rem;
            background:var(--primary);
            color:white;
            border:none;
            border-radius:4px;
            cursor:pointer;
        }
        #copy_plus_code:hover { background:var(--primary-dark); }
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
                <form method="POST" enctype="multipart/form-data">

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
                                    <option>Food</option>
                                    <option>Groceries</option>
                                    <option>Milk Tea</option>
                                    <option>Bakery</option>
                                    <option>Pharmacy</option>
                                    <option>Hardware</option>
                                    <option>Others</option>
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

                    <!-- Shop Media -->
                    <div class="form-section">
                        <h3>Shop Media</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="logo">Store Logo</label>
                                <input type="file" id="logo" name="logo" accept="image/*">
                                <div class="help-text">Recommended: 500×500 px, PNG or JPG</div>
                            </div>
                            <div class="form-group">
                                <label for="banner">Store Banner</label>
                                <input type="file" id="banner" name="banner" accept="image/*">
                                <div class="help-text">Best size: 1200×400 px or wider</div>
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
                                <input type="file" id="valid_id" name="valid_id[]" accept="image/*,.pdf" multiple required>
                                <div class="help-text">
                                    <strong>Required:</strong> Upload clear photos/scans of both <strong>front</strong> and <strong>back</strong> sides.<br>
                                    You can select multiple files at once (max 4 recommended).
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="store_photos">Upload Store Photos *</label>
                                <input type="file" id="store_photos" name="store_photos[]" accept="image/*" multiple required>
                                <div class="help-text">Upload 2–6 clear photos of your shop (inside, outside, products, etc.)</div>
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
        const gpsInput      = document.getElementById('gps_display');
        const latInput      = document.getElementById('latitude');
        const lngInput      = document.getElementById('longitude');
        const plusCodeInput = document.getElementById('plus_code');
        const copyBtn       = document.getElementById('copy_plus_code');

        // Check if library loaded
        if (typeof OpenLocationCode === 'undefined') {
            console.error("OpenLocationCode library failed to load.");
            plusCodeInput.value = "Library issue – paste short code from Google Maps";
        } else {
            console.log("OpenLocationCode loaded OK.");
        }

        // Approximate Dumaguete city center for shortening (adjust if your shops are far outside)
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
                            // Step 1: Generate full Plus Code (high precision)
                            const fullCode = OpenLocationCode.encode(lat, lng, 11);
                            console.log("Full Plus Code:", fullCode);

                            // Step 2: Shorten it using Dumaguete reference
                            let shortCode = fullCode;
                            try {
                                shortCode = OpenLocationCode.shorten(fullCode, dumagueteRef.lat, dumagueteRef.lng);
                                console.log("Shortened Plus Code:", shortCode);
                            } catch (shortenErr) {
                                console.warn("Shortening failed (possibly out of area):", shortenErr);
                                // Fallback to full if shortening not possible
                            }

                            // Display the short version (add city name for user-friendliness)
                            plusCodeInput.value = `${shortCode} Dumaguete City`;
                            // If you prefer just the short code without city: plusCodeInput.value = shortCode;

                            // Optional: open in Maps to confirm (uses short format)
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

        // Copy button (now copies whatever is in the field)
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