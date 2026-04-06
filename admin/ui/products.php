<?php
// /admin/ui/products.php
require_once '../backend/session/auth_admin.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/products.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/logout.css?v=<?= time() ?>">
</head>
<body>

<div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Admin<span>Dashboard</span></h2>
        </div>
        <nav class="sidebar-nav">
            <a href="/admin/ui/dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
            <a href="/admin/ui/buyers.php" class="nav-item">
                <i class="fas fa-users"></i><span>Buyers</span>
            </a>
            <a href="/admin/ui/sellers.php" class="nav-item">
                <i class="fas fa-store"></i><span>Sellers</span>
            </a>
            <a href="/admin/ui/products.php" class="nav-item active">
                <i class="fas fa-box"></i><span>Products</span>
            </a>
            <a href="/admin/ui/riders.php" class="nav-item">
                <i class="fas fa-motorcycle"></i><span>Riders</span>
            </a>
            <a href="/admin/ui/orders.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i><span>Orders</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar">A</div>
                <div>
                    <h4><?php echo $_SESSION['admin_name']; ?></h4>
                    <p>Administrator</p>
                </div>
            </div>
            <button class="logout-btn" id="logoutBtn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <header class="main-header">
            <div class="header-left">
                <h1>Products Management</h1>
                <p>Manage all products on the platform</p>
            </div>
            <div class="header-right">
                <div class="date-display" id="currentDate"></div>
            </div>
        </header>

        <!-- STATS CARDS -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalProducts">0</h3>
                    <p>Total Products</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#27ae6020;color:#27ae60">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="approvedProducts">0</h3>
                    <p>Approved</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e67e2220;color:#e67e22">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="pendingProducts">0</h3>
                    <p>On Hold</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e74c3c20;color:#e74c3c">
                    <i class="fas fa-trash"></i>
                </div>
                <div class="stat-info">
                    <h3 id="removedProducts">0</h3>
                    <p>Removed</p>
                </div>
            </div>
        </section>

        <!-- PRODUCTS LIST SECTION -->
        <div class="full-width-section products-list">
            <div class="section-header">
                <h2>Products List</h2>
                <div class="search-container">
                    <input type="text" class="search-field" id="searchProduct" placeholder="Search product...">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <div class="table-container">
                <div class="product_holder">
                    <div class="product-table-header">
                        <div class="col-id">ID</div>
                        <div class="col-name">Product Name</div>
                        <div class="col-price">Price</div>
                        <div class="col-stock">Stock</div>
                        <div class="col-status">Status</div>
                        <div class="col-actions">Actions</div>
                    </div>
                    
                    <div class="table-body" id="productsTableBody">
                        <div class="loading">Loading products...</div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <p>© 2024 Admin Dashboard. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a> •
                <a href="#">Terms of Service</a> •
                <a href="#">Help Center</a>
            </div>
        </footer>
    </main>
</div>

<!-- Logout Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Logout</h3>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to logout?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-cancel" id="cancelLogoutBtn">Cancel</button>
            <button class="btn btn-logout" id="confirmLogoutBtn">Logout</button>
        </div>
    </div>
</div>

<script src="/admin/js/logout.js"></script>

<script>
    // Display current date
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString(undefined, options);

    // Load products from backend
    async function loadProducts() {
        try {
            const response = await fetch('/admin/backend/products/getAllproducts.php');
            const result = await response.json();
            
            if (result.success) {
                // Update stats
                const products = result.data;
                const totalCount = result.total_count;
                
                // Calculate stats
                const approved = products.filter(p => p.status === 'approved').length;
                const onHold = products.filter(p => p.status === 'on_hold').length;
                const removed = products.filter(p => p.status === 'removed').length;
                
                document.getElementById('totalProducts').textContent = totalCount;
                document.getElementById('approvedProducts').textContent = approved;
                document.getElementById('pendingProducts').textContent = onHold;
                document.getElementById('removedProducts').textContent = removed;
                
                // Display products
                displayProducts(products);
            } else {
                document.getElementById('productsTableBody').innerHTML = '<div class="error">Failed to load products</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('productsTableBody').innerHTML = '<div class="error">Error loading products</div>';
        }
    }
    
    // Display products in table
    function displayProducts(products) {
        const tbody = document.getElementById('productsTableBody');
        
        if (products.length === 0) {
            tbody.innerHTML = '<div class="no-data">No products found</div>';
            return;
        }
        
        let html = '';
        products.forEach(product => {
            // Set status badge class
            let statusClass = '';
            let statusText = product.status;
            if (product.status === 'approved') statusClass = 'status-approved';
            else if (product.status === 'on_hold') statusClass = 'status-hold';
            else if (product.status === 'removed') statusClass = 'status-removed';
            
            html += `
                <div class="product-row">
                    <div class="col-id">${product.id}</div>
                    <div class="col-name">
                        <div class="product-info">
                            <strong>${escapeHtml(product.product_name)}</strong>
                            <small>${escapeHtml(product.category)}</small>
                        </div>
                    </div>
                    <div class="col-price">₱${parseFloat(product.price).toFixed(2)}</div>
                    <div class="col-stock">${product.stock}</div>
                    <div class="col-status">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                    <div class="col-actions">
                        <button class="action-btn view-btn" onclick="viewProduct(${product.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn edit-btn" onclick="editProduct(${product.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Search functionality
    document.getElementById('searchProduct').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('.product-row');
        
        rows.forEach(row => {
            const productName = row.querySelector('.col-name strong').textContent.toLowerCase();
            if (productName.includes(searchTerm)) {
                row.style.display = 'flex';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // View product function
    function viewProduct(id) {
        alert('View product ID: ' + id + ' - Feature coming soon');
    }
    
    // Edit product function
    function editProduct(id) {
        alert('Edit product ID: ' + id + ' - Feature coming soon');
    }
    
    // Load products when page loads
    loadProducts();
</script>

</body>
</html>