
let currentProductId = null;
let currentProductName = null;

// Set current date
document.getElementById('dateDisplay').textContent = new Date().toLocaleDateString('en-US', { 
    year: 'numeric', month: 'long', day: 'numeric' 
});

// Load products when page loads
loadProducts();

// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    filterProducts();
});

function loadProducts() {
    fetch('/seller/backend/products_backend/get_seller_items.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.products.length > 0) {
                displayProducts(data.products);
            } else {
                showNoProducts();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError();
        });
}

function viewProductDetails(productId) {
    window.location.href = `/seller/ui/product_details.php?id=${productId}`;
}

function displayProducts(products) {
    let html = '';
    
    products.forEach(product => {
        // Skip removed products
        if (product.status === 'removed') return;
        
        // Determine stock badge class
        let stockClass = 'stock-ok';
        let stockText = `In Stock (${product.stock})`;
        
        if (product.stock <= 0) {
            stockClass = 'stock-out';
            stockText = 'Out of Stock';
        } else if (product.stock <= 10) {
            stockClass = 'stock-low';
            stockText = `Low Stock (${product.stock})`;
        }
        
        // Variations display
        let variationsHtml = '';
        if (product.has_variations == 1 && product.variations_count > 0) {
            variationsHtml = `<div class="product-variations">
                <i class="fas fa-code-branch"></i>
                <span>${product.variations_count} Variation${product.variations_count !== 1 ? 's' : ''}</span>
            </div>`;
        }
        
        html += `
            <div class="product-card" data-product-id="${product.id}" style="cursor: pointer;" onclick="viewProductDetails(${product.id})">
                <div class="product-image">
                    ${product.main_image_url ? 
                        `<img src="${product.main_image_url}" alt="${escapeHtml(product.product_name)}">` : 
                        `<i class="fas fa-box"></i>`
                    }
                </div>
                <div class="product-info">
                    <h3 class="product-title">${escapeHtml(product.product_name)}</h3>
                    <span class="product-category">${escapeHtml(product.category)}</span>
                    <div class="product-price">${product.price_formatted}</div>
                    <div class="product-stock">
                        <span>Stock:</span>
                        <span class="stock-badge ${stockClass}">${stockText}</span>
                    </div>
                    ${variationsHtml}
                    <div class="product-actions">
                        <button class="action-btn edit-btn" onclick="event.stopPropagation(); editProduct(${product.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="action-btn delete-btn" onclick="event.stopPropagation(); showDeleteModal(${product.id}, '${escapeHtml(product.product_name)}')">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    if (html === '') {
        showNoProducts();
    } else {
        document.getElementById('productsGrid').innerHTML = html;
    }
}

function showDeleteModal(productId, productName) {
    currentProductId = productId;
    currentProductName = productName;
    
    const modal = document.getElementById('deleteModal');
    const messageElement = document.getElementById('deleteModalMessage');
    messageElement.innerHTML = `Are you sure you want to remove "<strong>${escapeHtml(productName)}</strong>"?`;
    
    modal.classList.add('active');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('active');
    currentProductId = null;
    currentProductName = null;
}

function deleteProduct() {
    if (!currentProductId) return;
    
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Removing...';
    confirmBtn.disabled = true;
    
    fetch('/seller/backend/products_backend/delete_product.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: currentProductId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message || 'Product removed successfully');
            closeDeleteModal();
            loadProducts(); // Refresh the product list
        } else {
            showToast('error', data.message || 'Error removing product');
            closeDeleteModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error removing product');
        closeDeleteModal();
    })
    .finally(() => {
        confirmBtn.innerHTML = originalText;
        confirmBtn.disabled = false;
    });
}

function showToast(type, message) {
    // Remove existing toast
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span class="toast-message">${escapeHtml(message)}</span>
    `;
    
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

function filterProducts() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const products = document.querySelectorAll('.product-card');
    
    products.forEach(product => {
        const title = product.querySelector('.product-title').textContent.toLowerCase();
        if (title.includes(searchTerm)) {
            product.style.display = '';
        } else {
            product.style.display = 'none';
        }
    });
}

function showNoProducts() {
    document.getElementById('productsGrid').innerHTML = `
        <div class="no-products">
            <i class="fas fa-box-open"></i>
            <h3>No Products Yet</h3>
            <p>Start by adding your first product to sell</p>
            <a href="#" class="add-product-btn">
                <i class="fas fa-plus"></i> Add Your First Product
            </a>
        </div>
    `;
}

function showError() {
    document.getElementById('productsGrid').innerHTML = `
        <div class="no-products">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Error Loading Products</h3>
            <p>Please refresh the page to try again</p>
        </div>
    `;
}

function editProduct(productId) {
    window.location.href = `/seller/ui/edit_product.php?id=${productId}`;
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

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Confirm delete button
document.getElementById('confirmDeleteBtn').addEventListener('click', deleteProduct);

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});