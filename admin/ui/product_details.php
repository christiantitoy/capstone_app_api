<?php
// /admin/ui/product_details.php
require_once '../backend/session/auth_admin.php';

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$productId) {
    header('Location: products.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/product_details.css?v=<?= time() ?>">
</head>
<body>

<div class="product-details-container">
    <!-- Back Navigation -->
    <div class="page-header">
        <a href="products.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
        <h1>Product Details</h1>
        <div class="header-actions">
            <div id="actionButtons"></div>
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p>Loading product details...</p>
    </div>

    <!-- Content -->
    <div id="productContent" style="display: none;">
        <!-- Product Header -->
        <div class="product-header-card">
            <div class="product-gallery">
                <div class="main-image" id="mainImage" onclick="openImageViewer(0, 'product')">
                    <div class="image-placeholder"><i class="fas fa-box"></i></div>
                </div>
                <div class="thumbnail-list" id="thumbnailList"></div>
            </div>
            <div class="product-info">
                <div class="product-title-section">
                    <h2 id="productName">-</h2>
                    <div class="product-badges">
                        <span class="status-badge" id="productStatus">-</span>
                        <span class="category-badge" id="productCategory">-</span>
                    </div>
                </div>
                <div class="product-meta">
                    <p><strong>Product ID:</strong> #<span id="productId">-</span></p>
                    <p><strong>Created:</strong> <span id="productCreated">-</span></p>
                    <p><strong>Last Updated:</strong> <span id="productUpdated">-</span></p>
                </div>
                <div class="product-price-stock">
                    <div class="price-box">
                        <span class="label">Price</span>
                        <span class="value" id="productPrice">₱0.00</span>
                    </div>
                    <div class="stock-box">
                        <span class="label">Stock</span>
                        <span class="value" id="productStock">0</span>
                    </div>
                    <div class="sold-box">
                        <span class="label">Sold</span>
                        <span class="value" id="productSold">0</span>
                    </div>
                </div>
                <!-- Status Reason (if on_hold or removed) -->
                <div id="statusReasonContainer" style="display: none; margin-top: 15px;">
                    <p style="color: #e74c3c; font-size: 14px; margin-bottom: 5px;">
                        <i class="fas fa-exclamation-circle"></i> <span id="statusReasonLabel">Reason:</span>
                    </p>
                    <p id="statusReason" style="background: #fdf0f0; padding: 10px; border-radius: 8px; font-size: 14px; color: #2c3e50;"></p>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-item">
                <i class="fas fa-shopping-cart"></i>
                <div class="stat-info">
                    <span class="stat-value" id="timesOrdered">0</span>
                    <span class="stat-label">Times Ordered</span>
                </div>
            </div>
            <div class="stat-item">
                <i class="fas fa-cubes"></i>
                <div class="stat-info">
                    <span class="stat-value" id="totalQuantitySold">0</span>
                    <span class="stat-label">Total Quantity Sold</span>
                </div>
            </div>
            <div class="stat-item">
                <i class="fas fa-tag"></i>
                <div class="stat-info">
                    <span class="stat-value" id="hasVariants">No</span>
                    <span class="stat-label">Has Variants</span>
                </div>
            </div>
        </div>

        <!-- Product Description -->
        <div class="info-section">
            <div class="section-header">
                <h3><i class="fas fa-align-left"></i> Description</h3>
            </div>
            <div class="description-content" id="productDescription">
                <p>No description available</p>
            </div>
        </div>

        <!-- Seller Information -->
        <div class="info-section">
            <div class="section-header">
                <h3><i class="fas fa-store"></i> Seller Information</h3>
                <a href="#" id="viewSellerLink" class="view-link">View Seller <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="seller-card">
                <div class="seller-logo" id="sellerLogo">
                    <i class="fas fa-store"></i>
                </div>
                <div class="seller-details">
                    <h4 id="sellerStoreName">-</h4>
                    <p id="sellerName">-</p>
                    <p id="sellerEmail">-</p>
                    <div class="seller-badges">
                        <span class="badge" id="sellerPlan">-</span>
                        <span class="badge" id="sellerStatus">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Information (if assigned) -->
        <div class="info-section" id="employeeSection" style="display: none;">
            <div class="section-header">
                <h3><i class="fas fa-user-tie"></i> Assigned Employee</h3>
            </div>
            <div class="employee-card">
                <div class="employee-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="employee-details">
                    <h4 id="employeeName">-</h4>
                    <p id="employeeEmail">-</p>
                    <p id="employeeRole">-</p>
                    <span class="status-badge" id="employeeStatus">-</span>
                </div>
            </div>
        </div>

        <!-- Variants Section -->
        <div class="info-section" id="variantsSection" style="display: none;">
            <div class="section-header">
                <h3><i class="fas fa-layer-group"></i> Product Variants</h3>
                <span class="badge" id="variantCount">0 variants</span>
            </div>
            <div class="table-container">
                <table class="variants-table">
                    <thead>
                        <tr>
                            <th>Variant</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody id="variantsBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Error State -->
    <div id="errorState" class="error-state" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <h3>Failed to load product details</h3>
        <p id="errorMessage">An error occurred while loading the product information.</p>
        <a href="products.php" class="btn btn-primary">Return to Products</a>
    </div>
</div>

<!-- Full Image Viewer Modal -->
<div id="imageViewerModal" class="image-viewer-modal">
    <span class="close-viewer" onclick="closeImageViewer()">&times;</span>
    <button class="viewer-nav prev" onclick="navigateImage(-1)"><i class="fas fa-chevron-left"></i></button>
    <img id="viewerImage" src="" alt="Image">
    <button class="viewer-nav next" onclick="navigateImage(1)"><i class="fas fa-chevron-right"></i></button>
    <div class="image-counter" id="imageCounter">1 / 1</div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">
                <i id="modalIcon" class="fas"></i>
                <span id="modalTitleText">Update Product Status</span>
            </h3>
            <span class="close-modal" onclick="closeStatusModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="modalMessage"></div>
            <form id="statusUpdateForm">
                <input type="hidden" id="modalAction">
                
                <div class="form-group">
                    <label>Product: <span id="modalProductName"></span></label>
                </div>
                
                <div class="form-group" id="statusReasonGroup" style="display: none;">
                    <label for="statusReasonInput">Reason <span class="required">*</span></label>
                    <textarea id="statusReasonInput" class="form-control" rows="4" placeholder="Please provide a reason for this action..."></textarea>
                    <small class="form-text">This reason will be recorded for future reference.</small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeStatusModal()">Cancel</button>
            <button class="btn" id="confirmStatusBtn" onclick="confirmStatusUpdate()">Confirm</button>
        </div>
    </div>
</div>

<!-- Notification Container -->
<div id="notificationContainer"></div>

<script>
    const productId = <?= $productId ?>;
    let allProductImages = [];
    let currentImageIndex = 0;
    let currentImageType = 'product';
    let variantImagesList = [];
    let currentProduct = null;
    
    // Load product details
    async function loadProductDetails() {
        const loadingState = document.getElementById('loadingState');
        const productContent = document.getElementById('productContent');
        const errorState = document.getElementById('errorState');
        
        try {
            const response = await fetch(`../backend/products/get_product_details.php?id=${productId}`);
            const result = await response.json();
            
            if (result.success) {
                const data = result.data;
                currentProduct = data.product;
                displayProductDetails(data);
                updateActionButtons(currentProduct);
                
                loadingState.style.display = 'none';
                productContent.style.display = 'block';
                errorState.style.display = 'none';
            } else {
                throw new Error(result.message || 'Failed to load product details');
            }
        } catch (error) {
            console.error('Error loading product details:', error);
            loadingState.style.display = 'none';
            productContent.style.display = 'none';
            errorState.style.display = 'block';
            document.getElementById('errorMessage').textContent = error.message;
        }
    }
    
    function updateActionButtons(product) {
        const actionContainer = document.getElementById('actionButtons');
        
        let buttons = '';
        
        // Show different buttons based on current status (without On Hold)
        if (product.status === 'on_review') {
            buttons = `
                <button class="btn btn-success" onclick="openStatusModal('approve')">
                    <i class="fas fa-check-circle"></i> Approve
                </button>
                <button class="btn btn-danger" onclick="openStatusModal('remove')">
                    <i class="fas fa-trash"></i> Remove
                </button>
            `;
        } else if (product.status === 'approved') {
            buttons = `
                <button class="btn btn-danger" onclick="openStatusModal('remove')">
                    <i class="fas fa-trash"></i> Remove
                </button>
            `;
        } else if (product.status === 'removed') {
            buttons = `
                <button class="btn btn-success" onclick="openStatusModal('approve')">
                    <i class="fas fa-check-circle"></i> Restore
                </button>
            `;
        }
        
        actionContainer.innerHTML = buttons;
    }
    
    function openStatusModal(action) {
        const modal = document.getElementById('statusModal');
        const modalTitle = document.getElementById('modalTitleText');
        const modalIcon = document.getElementById('modalIcon');
        const modalMessage = document.getElementById('modalMessage');
        const modalAction = document.getElementById('modalAction');
        const modalProductName = document.getElementById('modalProductName');
        const reasonGroup = document.getElementById('statusReasonGroup');
        const confirmBtn = document.getElementById('confirmStatusBtn');
        
        modalAction.value = action;
        modalProductName.textContent = currentProduct.product_name;
        
        const actionConfig = {
            'approve': {
                title: currentProduct.status === 'removed' ? 'Restore Product' : 'Approve Product',
                icon: 'fa-check-circle',
                iconColor: '#27ae60',
                message: currentProduct.status === 'removed' ? 
                    'Are you sure you want to restore this product? It will be visible to customers again.' :
                    'Are you sure you want to approve this product? It will be visible to customers.',
                messageType: 'success',
                showReason: false,
                btnClass: 'btn-success',
                btnText: currentProduct.status === 'removed' ? 'Restore Product' : 'Approve Product'
            },
            'remove': {
                title: 'Remove Product',
                icon: 'fa-trash',
                iconColor: '#e74c3c',
                message: 'Are you sure you want to remove this product? This action requires a reason.',
                messageType: 'danger',
                showReason: true,
                btnClass: 'btn-danger',
                btnText: 'Remove Product'
            }
        };
        
        const config = actionConfig[action];
        
        if (config) {
            modalTitle.textContent = config.title;
            modalIcon.className = `fas ${config.icon}`;
            modalIcon.style.color = config.iconColor;
            modalMessage.innerHTML = `
                <div class="message-box message-${config.messageType}">
                    <i class="fas fa-info-circle"></i>
                    <p>${config.message}</p>
                </div>
            `;
            
            reasonGroup.style.display = config.showReason ? 'block' : 'none';
            if (config.showReason) {
                document.getElementById('statusReasonInput').value = '';
            }
            
            confirmBtn.className = `btn ${config.btnClass}`;
            confirmBtn.innerHTML = `<i class="fas fa-${config.icon.split('-')[1] || 'check'}"></i> ${config.btnText}`;
            
            modal.style.display = 'flex';
            if (config.showReason) {
                document.getElementById('statusReasonInput').focus();
            }
        }
    }
    
    function closeStatusModal() {
        document.getElementById('statusModal').style.display = 'none';
        document.getElementById('statusReasonInput').value = '';
    }
    
    async function confirmStatusUpdate() {
        const action = document.getElementById('modalAction').value;
        const reason = action === 'remove' ? 
            document.getElementById('statusReasonInput').value.trim() : '';
        
        if (action === 'remove' && !reason) {
            showNotification('error', 'Please provide a reason for removing this product.');
            return;
        }
        
        const statusMap = {
            'approve': 'approved',
            'remove': 'removed'
        };
        
        const status = statusMap[action];
        const confirmBtn = document.getElementById('confirmStatusBtn');
        const originalText = confirmBtn.innerHTML;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        try {
            const response = await fetch('../backend/products/update_product_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    status: status,
                    reason: reason
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                closeStatusModal();
                showNotification('success', result.message);
                
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('error', 'Failed to update product status: ' + result.message);
            }
        } catch (error) {
            console.error('Error updating product:', error);
            showNotification('error', 'An error occurred while updating product status.');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
        }
    }
    
    function showNotification(type, message) {
        const container = document.getElementById('notificationContainer');
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        
        notification.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
        `;
        
        container.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
    
    function displayProductDetails(data) {
        const product = data.product;
        const variants = data.variants;
        const orderStats = data.order_stats;
        const employee = data.employee;
        
        // Update product header
        document.getElementById('productName').textContent = product.product_name;
        document.getElementById('productId').textContent = product.id;
        document.getElementById('productCreated').textContent = new Date(product.created_at).toLocaleDateString();
        document.getElementById('productUpdated').textContent = new Date(product.updated_at).toLocaleDateString();
        document.getElementById('productPrice').textContent = `₱${formatNumber(product.price)}`;
        document.getElementById('productStock').textContent = product.stock;
        document.getElementById('productSold').textContent = product.sold;
        document.getElementById('productCategory').textContent = product.category;
        
        // Status badge
        const statusBadge = document.getElementById('productStatus');
        statusBadge.textContent = formatStatus(product.status);
        statusBadge.className = `status-badge status-${product.status}`;
        
        // Show status reason if removed
        if (product.status === 'removed' && product.status_reason) {
            document.getElementById('statusReasonContainer').style.display = 'block';
            document.getElementById('statusReasonLabel').textContent = 'Removal Reason:';
            document.getElementById('statusReason').textContent = product.status_reason;
        }
        
        // Description
        document.getElementById('productDescription').innerHTML = `<p>${escapeHtml(product.product_description) || 'No description available'}</p>`;
        
        // Images
        displayImages(product);
        
        // Quick stats
        document.getElementById('timesOrdered').textContent = orderStats.times_ordered;
        document.getElementById('totalQuantitySold').textContent = orderStats.total_quantity_sold;
        document.getElementById('hasVariants').textContent = product.has_variations == 1 ? 'Yes' : 'No';
        
        // Seller info
        document.getElementById('sellerStoreName').textContent = product.store_name || 'No store setup';
        document.getElementById('sellerName').textContent = product.seller_name || 'Unknown';
        document.getElementById('sellerEmail').textContent = product.seller_email || 'No email';
        document.getElementById('sellerPlan').textContent = product.seller_plan || 'Bronze';
        document.getElementById('sellerStatus').textContent = product.approval_status || 'pending';
        document.getElementById('viewSellerLink').href = `seller_details.php?id=${product.seller_id}`;
        
        if (product.store_logo) {
            document.getElementById('sellerLogo').innerHTML = `<img src="${product.store_logo}" alt="Store Logo" style="width:100%;height:100%;object-fit:cover;">`;
        }
        
        // Employee info
        if (employee) {
            document.getElementById('employeeSection').style.display = 'block';
            document.getElementById('employeeName').textContent = employee.full_name;
            document.getElementById('employeeEmail').textContent = employee.email;
            document.getElementById('employeeRole').textContent = formatRole(employee.role);
            document.getElementById('employeeStatus').textContent = employee.status;
        }
        
        // Variants
        if (product.has_variations == 1 && variants.length > 0) {
            document.getElementById('variantsSection').style.display = 'block';
            document.getElementById('variantCount').textContent = `${variants.length} variant${variants.length !== 1 ? 's' : ''}`;
            displayVariants(variants);
        }
    }
    
    function formatStatus(status) {
        const formats = {
            'approved': 'Approved',
            'on_hold': 'On Hold',
            'removed': 'Removed',
            'on_review': 'On Review'
        };
        return formats[status] || status;
    }
    
    function displayImages(product) {
        const mainImage = document.getElementById('mainImage');
        const thumbnailList = document.getElementById('thumbnailList');
        
        allProductImages = [];
        if (product.main_image_url) {
            allProductImages.push(product.main_image_url);
        }
        if (product.image_urls_array && product.image_urls_array.length > 0) {
            allProductImages.push(...product.image_urls_array);
        }
        
        if (allProductImages.length > 0) {
            mainImage.innerHTML = `<img src="${allProductImages[0]}" alt="${product.product_name}" id="currentMainImage" style="width:100%;height:100%;object-fit:cover;">`;
            mainImage.style.cursor = 'pointer';
            
            if (allProductImages.length > 1) {
                thumbnailList.innerHTML = allProductImages.map((img, index) => `
                    <div class="thumbnail ${index === 0 ? 'active' : ''}" onclick="changeMainImage('${img}', ${index}, event)">
                        <img src="${img}" alt="Thumbnail ${index + 1}" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                `).join('');
            }
        }
    }
    
    function changeMainImage(src, index, event) {
        if (event) event.stopPropagation();
        document.getElementById('currentMainImage').src = src;
        currentImageIndex = index;
        currentImageType = 'product';
        document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
        document.querySelectorAll('.thumbnail')[index].classList.add('active');
    }
    
    function openImageViewer(index, type = 'product', variantImages = null) {
        currentImageType = type;
        
        if (type === 'variant' && variantImages && variantImages.length > 0) {
            variantImagesList = variantImages;
            currentImageIndex = index !== undefined ? index : 0;
        } else {
            if (allProductImages.length === 0) return;
            variantImagesList = [];
            currentImageIndex = index !== undefined ? index : currentImageIndex;
        }
        
        const imageArray = type === 'variant' ? variantImagesList : allProductImages;
        if (imageArray.length === 0) return;
        
        const modal = document.getElementById('imageViewerModal');
        const viewerImage = document.getElementById('viewerImage');
        const counter = document.getElementById('imageCounter');
        
        viewerImage.src = imageArray[currentImageIndex];
        counter.textContent = `${currentImageIndex + 1} / ${imageArray.length}`;
        modal.style.display = 'flex';
        
        document.querySelector('.viewer-nav.prev').style.display = imageArray.length > 1 ? 'flex' : 'none';
        document.querySelector('.viewer-nav.next').style.display = imageArray.length > 1 ? 'flex' : 'none';
    }
    
    function closeImageViewer() {
        document.getElementById('imageViewerModal').style.display = 'none';
        variantImagesList = [];
    }
    
    function navigateImage(direction) {
        const imageArray = currentImageType === 'variant' ? variantImagesList : allProductImages;
        if (imageArray.length === 0) return;
        
        currentImageIndex = (currentImageIndex + direction + imageArray.length) % imageArray.length;
        const viewerImage = document.getElementById('viewerImage');
        const counter = document.getElementById('imageCounter');
        
        viewerImage.src = imageArray[currentImageIndex];
        counter.textContent = `${currentImageIndex + 1} / ${imageArray.length}`;
        
        if (currentImageType === 'product') {
            const mainImg = document.getElementById('currentMainImage');
            if (mainImg) {
                mainImg.src = allProductImages[currentImageIndex];
                document.querySelectorAll('.thumbnail').forEach((thumb, i) => {
                    thumb.classList.toggle('active', i === currentImageIndex);
                });
            }
        }
    }
    
    function displayVariants(variants) {
        const tbody = document.getElementById('variantsBody');
        
        tbody.innerHTML = variants.map((variant, variantIndex) => {
            let variantName = '';
            if (variant.options) {
                variantName = Object.values(variant.options).join(' / ');
            } else if (variant.options_json_value) {
                variantName = variant.options_json_value;
            }
            
            const variantImgs = [];
            if (variant.image_urls_array && variant.image_urls_array.length > 0) {
                variantImgs.push(...variant.image_urls_array);
            }
            
            const hasImage = variantImgs.length > 0;
            const firstImage = hasImage ? variantImgs[0] : '';
            
            return `
                <tr>
                    <td><strong>${escapeHtml(variantName || '-')}</strong></td>
                    <td>${escapeHtml(variant.sku)}</td>
                    <td>₱${formatNumber(variant.price)}</td>
                    <td>${variant.stock}</td>
                    <td>
                        <div class="variant-image ${hasImage ? 'clickable' : ''}" 
                             ${hasImage ? `onclick="openImageViewer(0, 'variant', ${JSON.stringify(variantImgs).replace(/"/g, '&quot;')})"` : ''}>
                            ${hasImage ? 
                                `<img src="${firstImage}" alt="Variant" style="width:100%;height:100%;object-fit:cover;">` : 
                                '<i class="fas fa-image"></i>'
                            }
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    function formatRole(role) {
        const roleMap = {
            'order_manager': 'Order Manager',
            'product_manager': 'Product Manager'
        };
        return roleMap[role] || role;
    }
    
    function formatNumber(num) {
        return parseFloat(num || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const statusModal = document.getElementById('statusModal');
        const imageModal = document.getElementById('imageViewerModal');
        
        if (event.target === statusModal) {
            closeStatusModal();
        }
        if (event.target === imageModal) {
            closeImageViewer();
        }
    }
    
    document.addEventListener('keydown', function(e) {
        const imageModal = document.getElementById('imageViewerModal');
        const statusModal = document.getElementById('statusModal');
        
        if (e.key === 'Escape') {
            if (imageModal.style.display === 'flex') {
                closeImageViewer();
            }
            if (statusModal.style.display === 'flex') {
                closeStatusModal();
            }
        }
        
        if (imageModal.style.display === 'flex') {
            if (e.key === 'ArrowLeft') {
                navigateImage(-1);
            } else if (e.key === 'ArrowRight') {
                navigateImage(1);
            }
        }
    });
    
    document.addEventListener('DOMContentLoaded', loadProductDetails);
</script>

</body>
</html>