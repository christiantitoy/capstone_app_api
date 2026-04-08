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

        <div style="margin-bottom: 2rem; display: flex; justify-content: flex-end;">
            <a href="#" class="add-product-btn">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>

        <!-- Product List -->
        <div id="productsGrid" class="products-grid">
            <div style="text-align: center; padding: 3rem;">Loading products...</div>
        </div>

        <footer class="main-footer">
            © 2026 Seller Dashboard. All rights reserved.
        </footer>
    </main>
</div>

<script>
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
        
        html += `
            <div class="product-card" data-product-id="${product.id}">
                <div class="product-image">
                    ${product.main_image_url ? 
                        `<img src="${product.main_image_url}" alt="${product.product_name}" style="width: 100%; height: 100%; object-fit: cover;">` : 
                        `<i class="fas fa-box" style="font-size: 3rem; color: #bdc3c7;"></i>`
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
                    <div class="product-actions">
                        <button class="action-btn edit-btn" onclick="editProduct(${product.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="action-btn delete-btn" onclick="deleteProduct(${product.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    document.getElementById('productsGrid').innerHTML = html;
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
            <a href="#" class="add-product-btn" style="margin-top: 1.5rem; display: inline-block;">
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

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        fetch('/seller/backend/products_backend/delete_product.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product deleted successfully');
                loadProducts(); // Reload the list
            } else {
                alert('Error deleting product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting product');
        });
    }
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
</script>

<style>
/* Add missing stock-out style that might be needed */
.stock-out {
    background: #e74c3c !important;
    color: white !important;
}
</style>

</body>
</html>