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
            --info: #17a2b8;
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
            max-width: 1400px;
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

        /* Loading & Error States */
        .loading-state, .error-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
        }

        /* Product Main Card */
        .product-main-card {
            background: white;
            border-radius: 12px;
            margin-bottom: 2rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .product-header {
            display: flex;
            gap: 2rem;
            padding: 2rem;
            flex-wrap: wrap;
            border-bottom: 1px solid #ebedf0;
        }

        .product-gallery {
            flex: 0 0 300px;
        }

        .main-image {
            width: 100%;
            height: 300px;
            background: #f8fafc;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .main-image i {
            font-size: 5rem;
            color: #d1d9e0;
        }

        .thumbnail-images {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .thumbnail {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .thumbnail.active {
            border-color: var(--primary);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            flex: 1;
        }

        .product-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .product-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .category-badge {
            display: inline-block;
            background: rgba(52,152,219,0.1);
            color: var(--primary);
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-approved {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-on_hold {
            background: #fff3e0;
            color: #e65100;
        }

        .status-removed {
            background: #ffebee;
            color: #c62828;
        }

        .product-description {
            color: #5f6b7a;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .info-item {
            display: flex;
            align-items: baseline;
            gap: 10px;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
        }

        .info-label {
            font-weight: 600;
            color: var(--gray);
            font-size: 0.85rem;
        }

        .info-value {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--dark);
        }

        .price-value {
            color: var(--primary);
            font-size: 1.3rem;
        }

        /* Variations Section */
        .variations-section {
            background: white;
            border-radius: 12px;
            margin-bottom: 2rem;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
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
            transition: all 0.2s;
        }

        .variant-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .variant-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        .variant-stat {
            text-align: center;
            padding: 0.5rem;
            background: white;
            border-radius: 6px;
        }

        .variant-stat-label {
            font-size: 0.7rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }

        .variant-stat-value {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .variant-price {
            color: var(--primary);
        }

        .variant-stock {
            color: var(--success);
        }

        .variant-stock.low {
            color: var(--warning);
        }

        .variant-stock.out {
            color: var(--danger);
        }

        /* Additional Info Section */
        .additional-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .info-row {
            display: flex;
            padding: 0.75rem 0;
            border-bottom: 1px solid #ebedf0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row-label {
            width: 180px;
            font-weight: 600;
            color: var(--gray);
        }

        .info-row-value {
            flex: 1;
            color: var(--dark);
        }

        .employee-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .employee-avatar {
            width: 32px;
            height: 32px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .no-variations {
            text-align: center;
            padding: 3rem;
            background: #f8fafc;
            border-radius: 10px;
            color: var(--gray);
        }

        /* Variant Images Styles */
    .variant-images {
        margin: 1rem 0;
        padding: 0.75rem;
        background: white;
        border-radius: 8px;
    }

    .variant-images-label {
        font-size: 0.8rem;
        color: var(--gray);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .variant-thumbnails {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .variant-thumb {
        width: 50px;
        height: 50px;
        border-radius: 6px;
        object-fit: cover;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
    }

    .variant-thumb:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        border-color: var(--primary);
    }

    /* Image Modal for Lightbox */
    .image-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .image-modal.active {
        display: flex;
    }

    .image-modal img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 8px;
    }

    .image-modal-close {
        position: absolute;
        top: 20px;
        right: 30px;
        color: white;
        font-size: 2rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .image-modal-close:hover {
        color: var(--danger);
    }

        @media (max-width: 768px) {
            body { padding: 1rem; }
            .product-header { flex-direction: column; }
            .product-gallery { flex: auto; }
            .info-grid { grid-template-columns: 1fr; }
            .variations-grid { grid-template-columns: 1fr; }
            .info-row { flex-direction: column; gap: 5px; }
            .info-row-label { width: auto; }
        }
    </style>
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
    // Check if modal already exists
    let modal = document.getElementById('imageModal');
    if (!modal) {
        // Create modal
        modal = document.createElement('div');
        modal.id = 'imageModal';
        modal.className = 'image-modal';
        modal.innerHTML = `
            <span class="image-modal-close">&times;</span>
            <img id="modalImage" src="" alt="Full size image">
        `;
        document.body.appendChild(modal);
        
        // Close modal when clicking on background or close button
        modal.addEventListener('click', function(e) {
            if (e.target === modal || e.target.className === 'image-modal-close') {
                modal.classList.remove('active');
            }
        });
        
        // Close with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                modal.classList.remove('active');
            }
        });
    }
    
    // Set image and show modal
    const modalImg = document.getElementById('modalImage');
    modalImg.src = imageUrl;
    modal.classList.add('active');
}

function displayProductDetails(product, variations) {
    const container = document.getElementById('productContainer');
    
    // Show edit button if product is not removed
    if (product.status !== 'removed') {
        document.getElementById('editBtn').style.display = 'inline-flex';
    }
    
    // Parse images from image_urls (comma-separated string)
    let images = [];
    
    // First, try to parse image_urls as comma-separated string
    if (product.image_urls) {
        // Split by comma and trim each URL
        const urls = product.image_urls.split(',').map(url => url.trim());
        images.push(...urls);
    }
    
    // If no images found in image_urls, fallback to main_image_url
    if (images.length === 0 && product.main_image_url) {
        images.push(product.main_image_url);
    }
    
    // Remove duplicates and filter out empty strings
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
            // Escape single quotes in image URL to prevent JavaScript errors
            const escapedImg = img.replace(/'/g, "\\'");
            thumbnailsHtml += `
                <div class="thumbnail ${index === 0 ? 'active' : ''}" onclick="changeMainImage(this, '${escapedImg}')">
                    <img src="${img}" alt="Thumbnail ${index + 1}" onerror="this.src='/seller/image/placeholder.png'">
                </div>
            `;
        });
        thumbnailsHtml += '</div>';
    }

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
    
    // Variations HTML
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
                        
                        // Parse variant images from image_urls (comma-separated string)
                        let variantImages = [];
                        if (variant.image_urls) {
                            // Split by comma and trim each URL
                            const urls = variant.image_urls.split(',').map(url => url.trim());
                            variantImages.push(...urls);
                        }
                        // Remove duplicates and filter out empty strings
                        variantImages = [...new Set(variantImages.filter(img => img && img.trim() !== ''))];
                        
                        // Generate variant images HTML
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
    
    const html = `
        <div class="product-main-card">
            <div class="product-header">
                <div class="product-gallery">
                    <div class="main-image" id="mainImage">
                        ${images.length > 0 ? 
                            `<img src="${images[0]}" alt="${escapeHtml(product.product_name)}" onerror="this.src='/seller/image/placeholder.png'">` : 
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
    // Update main image
    const mainImageDiv = document.getElementById('mainImage');
    const escapedImg = imageUrl.replace(/'/g, "\\'");
    mainImageDiv.innerHTML = `<img src="${imageUrl}" alt="Product Image" onclick="openImageModal('${escapedImg}')" style="cursor: pointer;" onerror="this.src='/seller/image/placeholder.png'">`;
    
    // Update active state on thumbnails
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

// Load data on page load
loadProductData();
</script>
</body>
</html>