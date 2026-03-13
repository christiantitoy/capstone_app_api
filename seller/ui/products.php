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

    <!-- Sidebar ── identical style to dashboard & analytics -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Seller<span>Dashboard</span></h2>
        </div>

        <nav class="sidebar-nav">
            <a href="/seller/ui/dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="/seller/ui/products.php" class="nav-item active"><i class="fas fa-box"></i><span>Products</span></a>
            <a href="/seller/ui/orders.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Orders</span></a>
            <a href="/seller/ui/employees.php" class="nav-item"><i class="fas fa-users"></i><span>Employees</span></a>
            <a href="/seller/ui/analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
            <a href="#" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar">T</div>
                <div>
                    <h4>Titoy</h4>
                    <p>Seller Account</p>
                </div>
            </div>
            <a href="#" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
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
                    <input type="text" placeholder="Search products...">
                </div>
                <div class="date-display">March 9, 2026</div>
            </div>
        </header>

        <div style="margin-bottom: 2rem; display: flex; justify-content: flex-end;">
            <a href="#" class="add-product-btn">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>

        <!-- Product List -->
        <div class="products-grid">
            <div class="product-card">
                <div class="product-image"><i class="fas fa-headphones"></i></div>
                <div class="product-info">
                    <h3 class="product-title">Wireless Earbuds Pro</h3>
                    <span class="product-category">Electronics</span>
                    <div class="product-price">₱1,299.00</div>
                    <div class="product-stock">
                        <span>Stock:</span>
                        <span class="stock-badge stock-ok">In Stock (124)</span>
                    </div>
                    <div class="product-actions">
                        <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</button>
                        <button class="action-btn delete-btn"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </div>
            </div>

            <div class="product-card">
                <div class="product-image"><i class="fas fa-lightbulb"></i></div>
                <div class="product-info">
                    <h3 class="product-title">Smart LED Bulb (4-pack)</h3>
                    <span class="product-category">Home & Living</span>
                    <div class="product-price">₱899.00</div>
                    <div class="product-stock">
                        <span>Stock:</span>
                        <span class="stock-badge stock-low">Low Stock (8)</span>
                    </div>
                    <div class="product-actions">
                        <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</button>
                        <button class="action-btn delete-btn"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </div>
            </div>

            <div class="product-card">
                <div class="product-image"><i class="fas fa-charging-station"></i></div>
                <div class="product-info">
                    <h3 class="product-title">65W Fast Charger</h3>
                    <span class="product-category">Accessories</span>
                    <div class="product-price">₱599.00</div>
                    <div class="product-stock">
                        <span>Stock:</span>
                        <span class="stock-badge stock-ok">In Stock (67)</span>
                    </div>
                    <div class="product-actions">
                        <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</button>
                        <button class="action-btn delete-btn"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </div>
            </div>

            <div class="product-card">
                <div class="product-image"><i class="fas fa-bottle-water"></i></div>
                <div class="product-info">
                    <h3 class="product-title">1L Reusable Water Bottle</h3>
                    <span class="product-category">Eco Products</span>
                    <div class="product-price">₱349.00</div>
                    <div class="product-stock">
                        <span>Stock:</span>
                        <span class="stock-badge stock-ok">In Stock (189)</span>
                    </div>
                    <div class="product-actions">
                        <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</button>
                        <button class="action-btn delete-btn"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </div>
            </div>

            <div class="product-card">
                <div class="product-image"><i class="fas fa-battery-full"></i></div>
                <div class="product-info">
                    <h3 class="product-title">20000mAh Power Bank</h3>
                    <span class="product-category">Electronics</span>
                    <div class="product-price">₱1,099.00</div>
                    <div class="product-stock">
                        <span>Stock:</span>
                        <span class="stock-badge stock-low">Low Stock (12)</span>
                    </div>
                    <div class="product-actions">
                        <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</button>
                        <button class="action-btn delete-btn"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- If no products -->
        <!--
        <div class="no-products">
            <i class="fas fa-box-open"></i>
            <h3>No Products Yet</h3>
            <p>Start by adding your first product to sell</p>
            <a href="#" class="add-product-btn" style="margin-top: 1.5rem;">
                <i class="fas fa-plus"></i> Add Your First Product
            </a>
        </div>
        -->

        <footer class="main-footer" style="margin-top: 3rem; text-align: center; padding: 2rem 0; color: #95a5a6; font-size: 0.9rem; border-top: 1px solid #ebedf0;">
            © 2026 Seller Dashboard. All rights reserved.
        </footer>

    </main>
</div>

</body>
</html>