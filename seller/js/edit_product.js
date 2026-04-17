// /seller/js/edit_product.js

let productId = null;
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

// Initialize the page
function initEditProduct(productIdFromPhp) {
    productId = productIdFromPhp;
    
    if (!productId) {
        window.location.href = '/seller/ui/products.php';
        return;
    }
    
    // Load data on page load
    loadProductData();
}