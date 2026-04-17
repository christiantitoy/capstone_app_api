<?php
// /seller/ui/products.php
require_once __DIR__ . '/../backend/session/auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/products.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 450px;
            width: 90%;
            padding: 2rem;
            animation: slideUp 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-icon {
            text-align: center;
            font-size: 4rem;
            color: var(--danger);
            margin-bottom: 1rem;
        }

        .modal-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .modal-message {
            text-align: center;
            color: #5f6b7a;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .modal-warning {
            background: #ffebee;
            color: #c62828;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .modal-btn {
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 0.95rem;
        }

        .modal-btn-cancel {
            background: #e0e0e0;
            color: #5f6b7a;
        }

        .modal-btn-cancel:hover {
            background: #d0d0d0;
        }

        .modal-btn-confirm {
            background: var(--danger);
            color: white;
        }

        .modal-btn-confirm:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .modal-btn-confirm:disabled {
            background: var(--gray);
            cursor: not-allowed;
            transform: none;
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 1001;
            animation: slideInRight 0.3s ease;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .toast-success {
            border-left: 4px solid var(--success);
        }

        .toast-error {
            border-left: 4px solid var(--danger);
        }

        .toast i {
            font-size: 1.2rem;
        }

        .toast-success i {
            color: var(--success);
        }

        .toast-error i {
            color: var(--danger);
        }

        .toast-message {
            color: var(--dark);
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="dashboard-container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Seller<span>Dashboard</span></h2>
        </div>
        <nav class="sidebar-nav">
            <a href="/seller/ui/dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="/seller/ui/products.php" class="nav-item active"><i class="fas fa-box"></i><span>Products</span></a>
            <a href="/seller/ui/orders.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Orders</span></a>
            <a href="/seller/ui/employees.php" class="nav-item"><i class="fas fa-users"></i><span>Employees</span></a>
            <a href="#" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar"><?= strtoupper(substr($seller_name, 0, 1)) ?></div>
                <div class="user-info">
                    <h4 class="seller-name"><?= htmlspecialchars($seller_name) ?></h4>
                    <p>Seller Account</p>
                </div>
            </div>
            <button class="logout-btn logout-trigger">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="main-header">
            <div class="header-left">
                <h1>Products Management</h1>
                <p>Manage your products and inventory</p>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search products...">
                </div>
                <div class="date-display" id="dateDisplay"></div>
            </div>
        </header>

        <!-- Product List -->
        <div id="productsGrid" class="products-grid">
            <div class="loading-state">Loading products...</div>
        </div>

        <footer class="main-footer">
            © 2026 Seller Dashboard. All rights reserved.
        </footer>
    </main>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="modal-title">Remove Product</h3>
        <p class="modal-message" id="deleteModalMessage">Are you sure you want to remove this product?</p>
        <div class="modal-warning">
            <i class="fas fa-ban"></i>
            <span>This action cannot be undone!</span>
        </div>
        <div class="modal-actions">
            <button class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button class="modal-btn modal-btn-confirm" id="confirmDeleteBtn">
                <i class="fas fa-trash"></i> Remove Product
            </button>
        </div>
    </div>
</div>

<script>
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
            <div class="product-card" data-product-id="${product.id}">
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
                        <button class="action-btn edit-btn" onclick="editProduct(${product.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="action-btn delete-btn" onclick="showDeleteModal(${product.id}, '${escapeHtml(product.product_name)}')">
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
</script>

</body>
</html>