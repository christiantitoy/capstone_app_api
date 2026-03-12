<?php
// /seller/ui/add-product.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /seller/ui/login.php");
    exit;
}

$seller_name = $_SESSION['seller_name'] ?? 'Seller';
$current_step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }

        body {
            background: #f5f7fb;
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }

        .add-product-wrapper {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
        }

        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-header-left h1 {
            font-size: 28px;
            color: #2d3748;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .page-header-left p {
            color: #718096;
            font-size: 14px;
        }

        .close-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #718096;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 18px;
        }

        .close-btn:hover {
            background: #f7fafc;
            color: #e53e3e;
            border-color: #e53e3e;
        }

        /* Add Product Container */
        .add-product-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        /* Progress Bar */
        .progress-bar-container {
            padding: 30px 40px 20px;
            border-bottom: 1px solid #edf2f7;
            background: #fafbfc;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 60px;
            right: 60px;
            height: 2px;
            background: #e2e8f0;
            z-index: 1;
        }

        .step {
            position: relative;
            z-index: 2;
            background: #fafbfc;
            padding: 0 10px;
            text-align: center;
        }

        .step-number {
            width: 48px;
            height: 48px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: 600;
            color: #a0aec0;
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .step.active .step-number {
            background: #3498db;
            border-color: #3498db;
            color: white;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }

        .step.completed .step-number {
            background: #48bb78;
            border-color: #48bb78;
            color: white;
        }

        .step-label {
            font-size: 13px;
            color: #a0aec0;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .step.active .step-label {
            color: #3498db;
            font-weight: 600;
        }

        .step.completed .step-label {
            color: #48bb78;
        }

        /* Form Content */
        .form-content {
            padding: 40px;
        }

        .form-section {
            margin-bottom: 40px;
            border-bottom: 1px solid #edf2f7;
            padding-bottom: 30px;
        }

        .form-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .form-section h2 {
            font-size: 20px;
            color: #2d3748;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .form-section h2 i {
            color: #3498db;
            margin-right: 10px;
            font-size: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            color: #4a5568;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group label .required {
            color: #e53e3e;
            margin-left: 4px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.2s;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-control:hover {
            border-color: #cbd5e0;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .character-count {
            text-align: right;
            font-size: 12px;
            color: #a0aec0;
            margin-top: 6px;
        }

        /* Image Upload */
        .image-upload-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
        }

        .image-upload-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            margin-top: 16px;
        }

        .image-upload-box {
            aspect-ratio: 1;
            border: 2px dashed #cbd5e0;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }

        .image-upload-box:hover {
            border-color: #3498db;
            background: #ebf8ff;
        }

        .image-upload-box i {
            font-size: 24px;
            color: #a0aec0;
            margin-bottom: 8px;
        }

        .image-upload-box p {
            font-size: 12px;
            color: #718096;
            font-weight: 500;
        }

        .image-note {
            font-size: 13px;
            color: #718096;
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .image-note i {
            color: #3498db;
        }

        /* Variations Grid */
        .variation-types-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
        }

        .variation-types-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }

        .variation-type-item {
            border: 1.5px solid #e2e8f0;
            border-radius: 30px;
            padding: 10px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 13px;
            font-weight: 500;
            background: white;
        }

        .variation-type-item:hover {
            border-color: #3498db;
            background: #ebf8ff;
            color: #3498db;
        }

        .variation-type-item.selected {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .selected-variations {
            background: white;
            border-radius: 10px;
            padding: 16px;
            margin-top: 20px;
        }

        .selected-variations strong {
            display: block;
            margin-bottom: 12px;
            color: #2d3748;
            font-size: 14px;
        }

        .selected-variation-tag {
            display: inline-flex;
            align-items: center;
            background: #ebf8ff;
            border: 1px solid #bee3f8;
            border-radius: 30px;
            padding: 6px 14px;
            margin: 0 8px 8px 0;
            font-size: 13px;
            color: #2c5282;
        }

        .selected-variation-tag i {
            margin-left: 8px;
            color: #e53e3e;
            cursor: pointer;
            font-size: 12px;
        }

        .selected-variation-tag i:hover {
            color: #c53030;
        }

        /* Variation Values */
        .variation-values-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }

        .variation-values-section h3 {
            font-size: 16px;
            color: #2d3748;
            margin-bottom: 16px;
            font-weight: 600;
        }

        .value-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }

        .value-input-group input {
            flex: 1;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
        }

        .value-input-group input:focus {
            outline: none;
            border-color: #3498db;
        }

        .value-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .value-tag {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 30px;
            padding: 6px 14px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
        }

        .value-tag i {
            margin-left: 8px;
            color: #e53e3e;
            cursor: pointer;
            font-size: 12px;
        }

        .add-value-btn {
            background: white;
            color: #3498db;
            border: 1.5px dashed #3498db;
            padding: 12px;
            border-radius: 10px;
            width: 100%;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .add-value-btn:hover {
            background: #ebf8ff;
            border-style: solid;
        }

        /* Variant SKU Table */
        .variants-table-container {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            overflow-x: auto;
        }

        .variants-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .variants-table th {
            text-align: left;
            padding: 16px 12px;
            background: #edf2f7;
            color: #2d3748;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .variants-table th:first-child {
            border-radius: 8px 0 0 8px;
        }

        .variants-table th:last-child {
            border-radius: 0 8px 8px 0;
        }

        .variants-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .variant-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .variant-name {
            font-weight: 600;
            color: #2d3748;
        }

        .variant-details {
            font-size: 12px;
            color: #718096;
        }

        .variant-input {
            width: 100%;
            padding: 8px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 6px;
            font-size: 13px;
        }

        .variant-input:focus {
            outline: none;
            border-color: #3498db;
        }

        /* Review Summary */
        .review-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .review-header h3 {
            font-size: 18px;
            color: #2d3748;
            font-weight: 600;
        }

        .review-badge {
            background: #48bb78;
            color: white;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
        }

        .review-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .review-item span:first-child {
            color: #718096;
        }

        .review-item strong {
            color: #2d3748;
            font-weight: 600;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 24px;
        }

        .stat-item {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
        }

        .stat-label {
            font-size: 13px;
            color: #718096;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
        }

        .stat-value.success {
            color: #48bb78;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: space-between;
            padding: 24px 40px;
            background: #f8fafc;
            border-top: 1px solid #edf2f7;
        }

        .btn {
            padding: 12px 28px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #3498db;
            color: white;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.4);
        }

        .btn-secondary {
            background: white;
            border: 1.5px solid #e2e8f0;
            color: #718096;
        }

        .btn-secondary:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
            color: #2d3748;
        }

        .btn-danger {
            background: white;
            border: 1.5px solid #e53e3e;
            color: #e53e3e;
        }

        .btn-danger:hover {
            background: #fff5f5;
        }

        /* Step visibility */
        .step-content {
            display: none;
        }

        .step-content.active {
            display: block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 20px 10px;
            }

            .form-content {
                padding: 20px;
            }

            .progress-steps::before {
                left: 30px;
                right: 30px;
            }

            .step-number {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .step-label {
                font-size: 11px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .image-upload-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .form-actions {
                padding: 20px;
                flex-direction: column-reverse;
                gap: 10px;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="add-product-wrapper">
        <!-- Simple Header with Close Button -->
        <div class="page-header">
            <div class="page-header-left">
                <h1>Add Product</h1>
                <p>Create a new product listing for your store</p>
            </div>
            <a href="/seller/ui/products.php" class="close-btn">
                <i class="fas fa-times"></i>
            </a>
        </div>

        <!-- Main Container -->
        <div class="add-product-container">
            <!-- Progress Bar -->
            <div class="progress-bar-container">
                <div class="progress-steps">
                    <div class="step <?= $current_step >= 1 ? 'active' : '' ?> <?= $current_step > 1 ? 'completed' : '' ?>">
                        <div class="step-number">1</div>
                        <div class="step-label">Select Types</div>
                    </div>
                    <div class="step <?= $current_step >= 2 ? 'active' : '' ?> <?= $current_step > 2 ? 'completed' : '' ?>">
                        <div class="step-number">2</div>
                        <div class="step-label">Define Values</div>
                    </div>
                    <div class="step <?= $current_step >= 3 ? 'active' : '' ?> <?= $current_step > 3 ? 'completed' : '' ?>">
                        <div class="step-number">3</div>
                        <div class="step-label">Set Details</div>
                    </div>
                    <div class="step <?= $current_step >= 4 ? 'active' : '' ?> <?= $current_step > 4 ? 'completed' : '' ?>">
                        <div class="step-number">4</div>
                        <div class="step-label">Assign SKU</div>
                    </div>
                    <div class="step <?= $current_step >= 5 ? 'active' : '' ?>">
                        <div class="step-number">5</div>
                        <div class="step-label">Review</div>
                    </div>
                </div>
            </div>

            <!-- Step 1: Basic Info & Variation Types -->
            <div class="step-content <?= $current_step == 1 ? 'active' : '' ?>" id="step1">
                <div class="form-content">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                        <div class="form-group">
                            <label>Product Name <span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="e.g. Wireless Bluetooth Earbuds" maxlength="120">
                            <div class="character-count">0/120</div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" placeholder="Describe your product features, specifications, and benefits..." maxlength="3000"></textarea>
                            <div class="character-count">0/3000</div>
                        </div>

                        <div class="form-group">
                            <label>Product Category</label>
                            <select class="form-control">
                                <option value="">Select a category</option>
                                <option>Electronics</option>
                                <option>Fashion</option>
                                <option>Home & Living</option>
                                <option>Beauty & Personal Care</option>
                                <option>Sports & Outdoors</option>
                                <option>Toys & Games</option>
                            </select>
                        </div>
                    </div>

                    <!-- Product Images -->
                    <div class="form-section">
                        <h2><i class="fas fa-images"></i> Product Images</h2>
                        <div class="image-upload-section">
                            <div class="image-upload-grid">
                                <div class="image-upload-box">
                                    <i class="fas fa-camera"></i>
                                    <p>Add Photo</p>
                                </div>
                                <div class="image-upload-box">
                                    <i class="fas fa-camera"></i>
                                    <p>Add Photo</p>
                                </div>
                                <div class="image-upload-box">
                                    <i class="fas fa-camera"></i>
                                    <p>Add Photo</p>
                                </div>
                                <div class="image-upload-box">
                                    <i class="fas fa-camera"></i>
                                    <p>Add Photo</p>
                                </div>
                                <div class="image-upload-box">
                                    <i class="fas fa-camera"></i>
                                    <p>Add Photo</p>
                                </div>
                            </div>
                            <div class="image-note">
                                <i class="fas fa-info-circle"></i>
                                Upload at least 1 image. The first image will be your main product image.
                            </div>
                        </div>
                    </div>

                    <!-- Price and Stock -->
                    <div class="form-section">
                        <h2><i class="fas fa-tag"></i> Pricing & Inventory</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Product Price</label>
                                <input type="text" class="form-control" placeholder="₱ 0.00">
                            </div>
                            <div class="form-group">
                                <label>Product Stock</label>
                                <input type="text" class="form-control" placeholder="Enter quantity">
                            </div>
                        </div>
                    </div>

                    <!-- Variation Types -->
                    <div class="form-section">
                        <h2><i class="fas fa-layer-group"></i> Product Variations</h2>
                        <div class="variation-types-section">
                            <p style="color: #4a5568; margin-bottom: 16px;">Choose the types of variations your product has:</p>
                            
                            <div class="variation-types-grid">
                                <div class="variation-type-item" onclick="toggleVariation(this, 'Size')">Size</div>
                                <div class="variation-type-item" onclick="toggleVariation(this, 'Color')">Color</div>
                                <div class="variation-type-item" onclick="toggleVariation(this, 'Style')">Style</div>
                                <div class="variation-type-item" onclick="toggleVariation(this, 'Specification')">Specification</div>
                                <div class="variation-type-item" onclick="toggleVariation(this, 'Storage')">Storage</div>
                                <div class="variation-type-item" onclick="toggleVariation(this, 'RAM')">RAM</div>
                                <div class="variation-type-item" onclick="toggleVariation(this, 'Power')">Power</div>
                                <div class="variation-type-item" onclick="toggleVariation(this, 'Capacity')">Capacity</div>
                                <div class="variation-type-item" onclick="toggleVariation(this, 'Others')">Others</div>
                            </div>

                            <div class="selected-variations">
                                <strong>Selected Variations:</strong>
                                <div id="selectedVariations">
                                    <span class="selected-variation-tag">Color <i class="fas fa-times" onclick="removeVariation('Color')"></i></span>
                                    <span class="selected-variation-tag">Specification <i class="fas fa-times" onclick="removeVariation('Specification')"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-secondary" onclick="window.location.href='/seller/ui/products.php'">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button class="btn btn-primary" onclick="nextStep(2)">
                        Continue to Define Values <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Step 2: Define Variation Values -->
            <div class="step-content <?= $current_step == 2 ? 'active' : '' ?>" id="step2">
                <div class="form-content">
                    <div class="variation-values-section">
                        <h3>Color Values</h3>
                        <div class="value-input-group">
                            <input type="text" placeholder="Enter color value (e.g. White, Black, Red)" value="White">
                            <button class="btn btn-secondary" style="padding: 12px 24px;">Add</button>
                        </div>
                        <div class="value-tags">
                            <span class="value-tag">White <i class="fas fa-times"></i></span>
                        </div>
                    </div>

                    <div class="variation-values-section">
                        <h3>Specification Values</h3>
                        <div class="value-input-group">
                            <input type="text" placeholder="Enter specification value (e.g. Nice, Premium, Basic)" value="Nice">
                            <button class="btn btn-secondary" style="padding: 12px 24px;">Add</button>
                        </div>
                        <div class="value-tags">
                            <span class="value-tag">Nice <i class="fas fa-times"></i></span>
                        </div>
                    </div>

                    <button class="add-value-btn">
                        <i class="fas fa-plus"></i> Add Another Variation Type
                    </button>
                </div>
                <div class="form-actions">
                    <button class="btn btn-secondary" onclick="prevStep(1)">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button class="btn btn-primary" onclick="nextStep(3)">
                        Continue to Details <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Step 3: Set Details -->
            <div class="step-content <?= $current_step == 3 ? 'active' : '' ?>" id="step3">
                <div class="form-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Base Price</label>
                            <input type="text" class="form-control" placeholder="₱ 0.00" value="50.00">
                        </div>
                        <div class="form-group">
                            <label>Base Stock</label>
                            <input type="text" class="form-control" placeholder="Enter quantity" value="60">
                        </div>
                    </div>

                    <div class="form-section">
                        <h2><i class="fas fa-layer-group"></i> Variation Details</h2>
                        <div class="variants-table-container">
                            <table class="variants-table">
                                <thead>
                                    <tr>
                                        <th>Variant</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>SKU</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="variant-info">
                                                <span class="variant-name">White - Nice</span>
                                                <span class="variant-details">Color: White, Specification: Nice</span>
                                            </div>
                                        </td>
                                        <td><input type="text" class="variant-input" value="50.00"></td>
                                        <td><input type="text" class="variant-input" value="60"></td>
                                        <td><input type="text" class="variant-input" placeholder="Auto-generate"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top: 16px; text-align: right;">
                            <span style="background: #edf2f7; padding: 8px 16px; border-radius: 30px; font-size: 14px;">
                                Total variants: <strong>1</strong>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-secondary" onclick="prevStep(2)">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button class="btn btn-primary" onclick="nextStep(4)">
                        Continue to SKU <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Step 4: Assign SKU -->
            <div class="step-content <?= $current_step == 4 ? 'active' : '' ?>" id="step4">
                <div class="form-content">
                    <h3 style="font-size: 18px; margin-bottom: 12px; color: #2d3748;">Assign SKU</h3>
                    <p style="color: #718096; margin-bottom: 24px;">Enter SKU for each variant combination. Empty fields will have a generated SKU.</p>
                    
                    <div class="variants-table-container">
                        <table class="variants-table">
                            <thead>
                                <tr>
                                    <th>Variant</th>
                                    <th>Details</th>
                                    <th>SKU</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="variant-name">White - Nice</span>
                                    </td>
                                    <td>
                                        <div class="variant-details">
                                            Price: ₱50.00<br>
                                            Stock: 60 pcs
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="variant-input" placeholder="Enter SKU">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 20px; text-align: right;">
                        <span style="background: #edf2f7; padding: 8px 16px; border-radius: 30px; font-size: 14px;">
                            Total variants: <strong>1</strong>
                        </span>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-secondary" onclick="prevStep(3)">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button class="btn btn-primary" onclick="nextStep(5)">
                        Continue to Review <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Step 5: Review Summary -->
            <div class="step-content <?= $current_step == 5 ? 'active' : '' ?>" id="step5">
                <div class="form-content">
                    <h3 style="font-size: 18px; margin-bottom: 12px; color: #2d3748;">Review Summary</h3>
                    <p style="color: #718096; margin-bottom: 24px;">Check your product variations before finishing.</p>
                    
                    <div class="review-card">
                        <div class="review-header">
                            <h3>White - Nice</h3>
                            <span class="review-badge">Ready</span>
                        </div>
                        <div class="review-item">
                            <span>Price:</span>
                            <strong>₱50.00</strong>
                        </div>
                        <div class="review-item">
                            <span>Stock:</span>
                            <strong>60 pcs</strong>
                        </div>
                        <div class="review-item">
                            <span>SKU:</span>
                            <strong>PRD:4KWEO6VNPW</strong>
                        </div>
                        <div class="review-item">
                            <span>Images:</span>
                            <strong>1 image(s)</strong>
                        </div>
                    </div>

                    <div class="summary-stats">
                        <div class="stat-item">
                            <div class="stat-label">Total Variants</div>
                            <div class="stat-value">1</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">With Price</div>
                            <div class="stat-value success">1/1</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">With Stock</div>
                            <div class="stat-value success">1/1</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">With Images</div>
                            <div class="stat-value success">1/1</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">With SKU</div>
                            <div class="stat-value success">1/1</div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-secondary" onclick="prevStep(4)">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button class="btn btn-primary" onclick="finishProduct()">
                        <i class="fas fa-check"></i> Finish
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleVariation(element, type) {
            element.classList.toggle('selected');
            updateSelectedVariations();
        }

        function updateSelectedVariations() {
            const selected = document.querySelectorAll('.variation-type-item.selected');
            const container = document.getElementById('selectedVariations');
            container.innerHTML = '';
            
            selected.forEach(item => {
                const type = item.textContent;
                const tag = document.createElement('span');
                tag.className = 'selected-variation-tag';
                tag.innerHTML = `${type} <i class="fas fa-times" onclick="removeVariation('${type}')"></i>`;
                container.appendChild(tag);
            });
        }

        function removeVariation(type) {
            const items = document.querySelectorAll('.variation-type-item');
            items.forEach(item => {
                if (item.textContent === type) {
                    item.classList.remove('selected');
                }
            });
            updateSelectedVariations();
        }

        function nextStep(step) {
            window.location.href = `?step=${step}`;
        }

        function prevStep(step) {
            window.location.href = `?step=${step}`;
        }

        function finishProduct() {
            if (confirm('Are you sure you want to create this product?')) {
                alert('Product created successfully!');
                window.location.href = '/seller/ui/products.php';
            }
        }

        // Initialize selected variations
        document.addEventListener('DOMContentLoaded', function() {
            const items = document.querySelectorAll('.variation-type-item');
            items.forEach(item => {
                if (item.textContent === 'Color' || item.textContent === 'Specification') {
                    item.classList.add('selected');
                }
            });
            updateSelectedVariations();
        });
    </script>
</body>
</html>