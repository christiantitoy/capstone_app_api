<?php
require_once __DIR__ . '/../backend/session/auth.php';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/product_details.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>Product Details</h1>
            <p>View complete product information</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <a href="/seller/ui/edit_product.php?id=<?= $product_id ?>" class="back-btn" id="editBtn" style="display: none;">
                <i class="fas fa-edit"></i> Edit Product
            </a>
            <a href="/seller/ui/products.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
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

// Load product data
function loadProductData() {
    fetch(`/seller/backend/products_backend/get_product_details.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayProductDetails(data.product, data.variations);
            } else {
                showError(data.message || 'Failed to load product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error loading product details');
        });
}

function openImageModal(imageUrl) {
    let modal = document.getElementById('imageModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'imageModal';
        modal.className = 'image-modal';
        modal.innerHTML = `
            <span class="image-modal-close">&times;</span>
            <img id="modalImage" src="" alt="Full size image">
        `;
        document.body.appendChild(modal);
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal || e.target.className === 'image-modal-close') {
                modal.classList.remove('active');
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                modal.classList.remove('active');
            }
        });
    }
    
    const modalImg = document.getElementById('modalImage');
    modalImg.src = imageUrl;
    modal.classList.add('active');
}

function getVariantOptionsHtml(optionsJson) {
    if (!optionsJson) return '<span class="variant-option">No options</span>';
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

function changeMainImage(thumbnailElement, imageUrl) {
    const mainImageDiv = document.getElementById('mainImage');
    const escapedImg = imageUrl.replace(/'/g, "\\'");
    mainImageDiv.innerHTML = `<img src="${imageUrl}" alt="Product Image" onclick="openImageModal('${escapedImg}')" style="cursor: pointer;" onerror="this.src='/seller/image/placeholder.png'">`;
    
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    thumbnailElement.classList.add('active');
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
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
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function displayProductDetails(product, variations) {
    const container = document.getElementById('productContainer');
    
    // Show edit button if product is not removed
    if (product.status !== 'removed') {
        document.getElementById('editBtn').style.display = 'inline-flex';
    }
    
    // Parse images from image_urls (comma-separated string)
    let images = [];
    if (product.image_urls) {
        const urls = product.image_urls.split(',').map(url => url.trim());
        images.push(...urls);
    }
    if (images.length === 0 && product.main_image_url) {
        images.push(product.main_image_url);
    }
    images = [...new Set(images.filter(img => img && img.trim() !== ''))];
    
    // Status badge class
    let statusClass = '';
    let statusText = '';
    switch(product.status) {
        case 'approved':
            statusClass = 'status-approved';
            statusText = 'Approved';
            break;
        case 'on_hold':
            statusClass = 'status-on_hold';
            statusText = 'On Hold';
            break;
        case 'removed':
            statusClass = 'status-removed';
            statusText = 'Removed';
            break;
        default:
            statusClass = 'status-approved';
            statusText = product.status;
    }
    
    // Stock status
    let stockClass = 'variant-stock';
    let stockText = `${product.stock} units`;
    if (product.stock <= 0) {
        stockClass += ' out';
        stockText = 'Out of Stock';
    } else if (product.stock <= 10) {
        stockClass += ' low';
        stockText = `${product.stock} units (Low Stock)`;
    }
    
    // Generate thumbnail images HTML
    let thumbnailsHtml = '';
    if (images.length > 0) {
        thumbnailsHtml = '<div class="thumbnail-images">';
        images.forEach((img, index) => {
            const escapedImg = img.replace(/'/g, "\\'");
            thumbnailsHtml += `
                <div class="thumbnail ${index === 0 ? 'active' : ''}" onclick="changeMainImage(this, '${escapedImg}')">
                    <img src="${img}" alt="Thumbnail ${index + 1}" onerror="this.src='/seller/image/placeholder.png'">
                </div>
            `;
        });
        thumbnailsHtml += '</div>';
    }
    
    // Build variations HTML
    let variationsHtml = '';
    if (product.has_variations == 1 && variations.length > 0) {
        variationsHtml = `
            <div class="variations-section">
                <div class="section-title">
                    <i class="fas fa-code-branch"></i>
                    <span>Product Variations (${variations.length})</span>
                </div>
                <div class="variations-grid">
                    ${variations.map(variant => {
                        let variantStockClass = 'variant-stock';
                        let variantStockText = `${variant.stock} units`;
                        if (variant.stock <= 0) {
                            variantStockClass += ' out';
                            variantStockText = 'Out of Stock';
                        } else if (variant.stock <= 10) {
                            variantStockClass += ' low';
                            variantStockText = `${variant.stock} units (Low)`;
                        }
                        
                        let variantImages = [];
                        if (variant.image_urls) {
                            const urls = variant.image_urls.split(',').map(url => url.trim());
                            variantImages.push(...urls);
                        }
                        variantImages = [...new Set(variantImages.filter(img => img && img.trim() !== ''))];
                        
                        let variantImagesHtml = '';
                        if (variantImages.length > 0) {
                            variantImagesHtml = `
                                <div class="variant-images">
                                    <div class="variant-images-label">
                                        <i class="fas fa-images"></i> Images (${variantImages.length})
                                    </div>
                                    <div class="variant-thumbnails">
                                        ${variantImages.map(img => `
                                            <img src="${img}" alt="Variant image" class="variant-thumb" onclick="event.stopPropagation(); openImageModal('${img.replace(/'/g, "\\'")}')">
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }
                        
                        return `
                            <div class="variant-card">
                                <div class="variant-options">
                                    ${getVariantOptionsHtml(variant.options_json)}
                                </div>
                                <div class="variant-sku">
                                    <i class="fas fa-barcode"></i> SKU: ${escapeHtml(variant.sku)}
                                </div>
                                ${variantImagesHtml}
                                <div class="variant-stats">
                                    <div class="variant-stat">
                                        <div class="variant-stat-label">Price</div>
                                        <div class="variant-stat-value variant-price">₱${parseFloat(variant.price).toFixed(2)}</div>
                                    </div>
                                    <div class="variant-stat">
                                        <div class="variant-stat-label">Stock</div>
                                        <div class="variant-stat-value ${variantStockClass}">${variantStockText}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
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
    
    // Employee info
    let employeeHtml = '<span>Not assigned</span>';
    if (product.employee_id && product.employee_name) {
        employeeHtml = `
            <div class="employee-info">
                <div class="employee-avatar">${escapeHtml(product.employee_name.charAt(0).toUpperCase())}</div>
                <div>
                    <div><strong>${escapeHtml(product.employee_name)}</strong></div>
                    <div style="font-size: 0.8rem; color: var(--gray);">Employee ID: ${product.employee_id}</div>
                </div>
            </div>
        `;
    }
    
    // Final HTML assembly
    const html = `
        <div class="product-main-card">
            <div class="product-header">
                <div class="product-gallery">
                    <div class="main-image" id="mainImage">
                        ${images.length > 0 ? 
                            `<img src="${images[0]}" alt="${escapeHtml(product.product_name)}" onclick="openImageModal('${images[0].replace(/'/g, "\\'")}')" style="cursor: pointer;" onerror="this.src='/seller/image/placeholder.png'">` : 
                            `<i class="fas fa-box"></i>`
                        }
                    </div>
                    ${thumbnailsHtml}
                </div>
                <div class="product-info">
                    <h1 class="product-title">${escapeHtml(product.product_name)}</h1>
                    <div class="product-meta">
                        <span class="category-badge"><i class="fas fa-tag"></i> ${escapeHtml(product.category)}</span>
                        <span class="status-badge ${statusClass}"><i class="fas fa-circle"></i> ${statusText}</span>
                    </div>
                    <div class="product-description">
                        <i class="fas fa-align-left" style="margin-right: 8px; color: var(--gray);"></i>
                        ${escapeHtml(product.product_description)}
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-tag"></i> Price:</span>
                            <span class="info-value price-value">₱${parseFloat(product.price).toFixed(2)}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-boxes"></i> Stock:</span>
                            <span class="info-value ${stockClass}">${stockText}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-code-branch"></i> Type:</span>
                            <span class="info-value">${product.has_variations == 1 ? 'With Variations' : 'Simple Product'}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        ${variationsHtml}
        
        <div class="additional-section">
            <div class="section-title">
                <i class="fas fa-info-circle"></i>
                <span>Additional Information</span>
            </div>
            <div class="info-row">
                <div class="info-row-label">Product ID:</div>
                <div class="info-row-value">#${product.id}</div>
            </div>
            <div class="info-row">
                <div class="info-row-label">Created Date:</div>
                <div class="info-row-value">${formatDate(product.created_at)}</div>
            </div>
            <div class="info-row">
                <div class="info-row-label">Last Updated:</div>
                <div class="info-row-value">${formatDate(product.updated_at)}</div>
            </div>
            <div class="info-row">
                <div class="info-row-label">Assigned Employee:</div>
                <div class="info-row-value">${employeeHtml}</div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

// Load data on page load
loadProductData();
</script>
</body>
</html>