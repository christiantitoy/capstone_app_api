<?php
require_once __DIR__ . '/../backend/session/auth.php';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gray: #95a5a6;
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .header p {
            color: #7f8c8d;
            margin-top: 0.25rem;
        }

        .back-btn {
            background: white;
            color: var(--dark);
            padding: 0.7rem 1.4rem;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.2s;
            border: 1px solid #d1d9e0;
        }

        .back-btn:hover {
            background: #f0f2f5;
        }

        /* Product Card */
        .product-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .product-header {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .product-image {
            width: 200px;
            height: 200px;
            background: #f8fafc;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-image i {
            font-size: 4rem;
            color: #d1d9e0;
        }

        .product-details {
            flex: 1;
        }

        .product-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .product-category {
            display: inline-block;
            background: rgba(52,152,219,0.1);
            color: var(--primary);
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .product-description {
            color: #5f6b7a;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        input, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d9e0;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }

        input[readonly] {
            background: #f8fafc;
            cursor: not-allowed;
        }

        /* Variations Section */
        .variations-section {
            margin-top: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .variations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .variant-card {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
        }

        .variant-options {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .variant-option {
            display: inline-block;
            background: white;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.85rem;
            margin: 0.25rem;
            border: 1px solid #d1d9e0;
        }

        .variant-sku {
            font-size: 0.8rem;
            color: var(--gray);
            margin-bottom: 1rem;
            font-family: monospace;
        }

        .variant-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .variant-form-group {
            margin-bottom: 0;
        }

        .variant-form-group label {
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }

        .save-btn {
            background: var(--primary);
            color: white;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .save-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .save-btn:disabled {
            background: var(--gray);
            cursor: not-allowed;
            transform: none;
        }

        .save-all-btn {
            background: var(--success);
            margin-top: 1rem;
            width: 100%;
            justify-content: center;
        }

        .loading-state, .error-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .no-variations {
            text-align: center;
            padding: 3rem;
            background: #f8fafc;
            border-radius: 10px;
            color: var(--gray);
        }

        @media (max-width: 768px) {
            body { padding: 1rem; }
            .form-row { grid-template-columns: 1fr; }
            .variations-grid { grid-template-columns: 1fr; }
            .product-header { flex-direction: column; align-items: center; text-align: center; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>Edit Product</h1>
            <p>Update product price, stock, and variations</p>
        </div>
        <a href="/seller/ui/products.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <div id="productContainer">
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i>
            <p>Loading product details...</p>
        </div>
    </div>
</div>

<script>
const productId = <?= $product_id ?>;

if (!productId) {
    window.location.href = '/seller/ui/products.php';
}

let originalProductData = null;

// Load product data
function loadProductData() {
    fetch(`/seller/backend/products_backend/get_product_details.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                originalProductData = data;
                displayProductForm(data.product, data.variations);
            } else {
                showError(data.message || 'Failed to load product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error loading product details');
        });
}

function displayProductForm(product, variations) {
    const container = document.getElementById('productContainer');
    
    let variationsHtml = '';
    if (product.has_variations == 1 && variations.length > 0) {
        variationsHtml = `
            <div class="variations-section">
                <div class="section-title">
                    <i class="fas fa-code-branch"></i>
                    <span>Product Variations (${variations.length})</span>
                </div>
                <div class="variations-grid" id="variationsGrid">
                    ${variations.map(variant => `
                        <div class="variant-card" data-variant-id="${variant.id}">
                            <div class="variant-options">
                                ${getVariantOptionsHtml(variant.options_json)}
                            </div>
                            <div class="variant-sku">
                                <i class="fas fa-barcode"></i> SKU: ${escapeHtml(variant.sku)}
                            </div>
                            <div class="variant-form-row">
                                <div class="variant-form-group">
                                    <label>Price (₱)</label>
                                    <input type="number" step="0.01" class="variant-price" data-variant-id="${variant.id}" value="${variant.price}">
                                </div>
                                <div class="variant-form-group">
                                    <label>Stock</label>
                                    <input type="number" class="variant-stock" data-variant-id="${variant.id}" value="${variant.stock}">
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <button class="save-btn save-all-btn" id="saveAllBtn">
                    <i class="fas fa-save"></i> Save All Changes
                </button>
            </div>
        `;
    } else if (product.has_variations == 1 && variations.length === 0) {
        variationsHtml = `
            <div class="variations-section">
                <div class="no-variations">
                    <i class="fas fa-info-circle"></i>
                    <p>No variations found for this product</p>
                </div>
            </div>
        `;
    }
    
    const html = `
        <div class="product-card">
            <div class="product-header">
                <div class="product-image">
                    ${product.main_image_url ? 
                        `<img src="${product.main_image_url}" alt="${escapeHtml(product.product_name)}">` : 
                        `<i class="fas fa-box"></i>`
                    }
                </div>
                <div class="product-details">
                    <h2 class="product-title">${escapeHtml(product.product_name)}</h2>
                    <span class="product-category">${escapeHtml(product.category)}</span>
                    <p class="product-description">${escapeHtml(product.product_description)}</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Price (₱)</label>
                            <input type="number" step="0.01" id="mainPrice" value="${product.price}">
                        </div>
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" id="mainStock" value="${product.stock}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ${variationsHtml}
    `;
    
    container.innerHTML = html;
    
    // Add save all button listener if variations exist
    const saveAllBtn = document.getElementById('saveAllBtn');
    if (saveAllBtn) {
        saveAllBtn.addEventListener('click', saveAllChanges);
    }
}

function getVariantOptionsHtml(optionsJson) {
    if (!optionsJson) return '';
    try {
        const options = typeof optionsJson === 'string' ? JSON.parse(optionsJson) : optionsJson;
        if (Array.isArray(options)) {
            return options.map(opt => `<span class="variant-option">${escapeHtml(opt)}</span>`).join('');
        } else if (typeof options === 'object') {
            return Object.entries(options).map(([key, value]) => 
                `<span class="variant-option">${escapeHtml(key)}: ${escapeHtml(value)}</span>`
            ).join('');
        }
        return `<span class="variant-option">${escapeHtml(String(options))}</span>`;
    } catch(e) {
        return `<span class="variant-option">${escapeHtml(String(optionsJson))}</span>`;
    }
}

function saveAllChanges() {
    // Collect main product data
    const mainPrice = document.getElementById('mainPrice').value;
    const mainStock = document.getElementById('mainStock').value;
    
    // Collect variations data
    const variations = [];
    document.querySelectorAll('.variant-card').forEach(card => {
        const variantId = card.dataset.variantId;
        const price = card.querySelector('.variant-price').value;
        const stock = card.querySelector('.variant-stock').value;
        variations.push({ id: variantId, price, stock });
    });
    
    // Check if there are any changes
    const hasMainChanges = mainPrice != originalProductData.product.price || 
                          mainStock != originalProductData.product.stock;
    
    let hasVariantChanges = false;
    if (variations.length > 0 && originalProductData.variations) {
        hasVariantChanges = variations.some(variant => {
            const originalVariant = originalProductData.variations.find(v => v.id == variant.id);
            return originalVariant && (variant.price != originalVariant.price || variant.stock != originalVariant.stock);
        });
    }
    
    if (!hasMainChanges && !hasVariantChanges) {
        showMessage('No changes to save', 'error');
        return;
    }
    
    const saveBtn = document.getElementById('saveAllBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving all changes...';
    saveBtn.disabled = true;
    
    // Prepare payload
    const payload = {
        product_id: productId,
        main_price: hasMainChanges ? parseFloat(mainPrice) : null,
        main_stock: hasMainChanges ? parseInt(mainStock) : null,
        variations: variations
    };
    
    fetch('/seller/backend/products_backend/update_product_prices_stocks.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message || 'All changes saved successfully!', 'success');
            // Reload data to update original values
            loadProductData();
        } else {
            showMessage(data.message || 'Error saving changes', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error saving changes', 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function showMessage(message, type) {
    const container = document.getElementById('productContainer');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    
    const firstChild = container.firstChild;
    container.insertBefore(alertDiv, firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

function showError(message) {
    const container = document.getElementById('productContainer');
    container.innerHTML = `
        <div class="error-state">
            <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: var(--danger);"></i>
            <h3>Error</h3>
            <p>${escapeHtml(message)}</p>
            <a href="/seller/ui/products.php" class="back-btn" style="margin-top: 1rem;">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
    `;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Load data on page load
loadProductData();
</script>
</body>
</html>