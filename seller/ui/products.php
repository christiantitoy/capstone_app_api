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
    <link rel="stylesheet" href="../css/error.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/logout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        
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
            <a href="/seller/ui/my_plan.php" class="nav-item"><i class="fas fa-crown"></i><span>My Plan</span></a>
            <a href="#" class="nav-item"><i class="fas fa-cog"></i><span>Sales</span></a>
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

<!-- ── LOGOUT CONFIRMATION MODAL ── -->
<div class="logout-modal-overlay" id="logoutModal">
    <div class="logout-modal-content">
        <div class="logout-modal-header">
            <h3>Sign Out</h3>
            <button class="logout-modal-close" id="closeModal">×</button>
        </div>
        <div class="logout-modal-body">
            <p>Are you sure you want to sign out?</p>
            <p class="logout-text-secondary">You will need to log in again to access your dashboard.</p>
        </div>
        <div class="logout-modal-footer">
            <button class="logout-btn logout-btn-secondary" id="cancelLogout">Cancel</button>
            <a href="/seller/backend/auth/logout.php" class="logout-btn logout-btn-danger">Sign Out</a>
        </div>
    </div>
</div>

<script src="/seller/js/logout.js"></script>

<script src="/seller/js/products.js?v=<?= time() ?>"></script>

</body>
</html>