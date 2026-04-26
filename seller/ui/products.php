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
        /* Product Header with Status Badge */
        .product-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 4px;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Status Badge Styles */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .status-badge i {
            font-size: 0.65rem;
        }

        /* Approved Status */
        .status-approved {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        /* On Hold Status */
        .status-onhold {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        /* Removed Status */
        .status-removed {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        /* Pending/Default Status */
        .status-pending {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #90caf9;
        }

        /* Make product card handle the new layout */
        .product-card .product-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        /* Ensure actions stay at bottom */
        .product-actions {
            margin-top: 8px;
            display: flex;
            gap: 8px;
            border-top: 1px solid #eef2f6;
            padding-top: 10px;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .product-header {
                flex-wrap: wrap;
            }
            
            .status-badge {
                padding: 2px 6px;
                font-size: 0.65rem;
            }
        }
    </style>
</head>
<body>
    

<div class="dashboard-container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Palit<span>Ora</span></h2>
            <button class="sidebar-close-btn" onclick="toggleSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="sidebar-nav">
            <a href="/seller/ui/dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="/seller/ui/products.php" class="nav-item active"><i class="fas fa-box"></i><span>Products</span></a>
            <a href="/seller/ui/orders.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Orders</span></a>
            <a href="/seller/ui/employees.php" class="nav-item"><i class="fas fa-users"></i><span>Employees</span></a>
            <a href="/seller/ui/my_plan.php" class="nav-item"><i class="fas fa-crown"></i><span>My Plan</span></a>
            <a href="/seller/ui/sales.php" class="nav-item"><i class="fas fa-chart-line"></i><span>Sales</span></a>
            <a href="/seller/ui/payouts.php" class="nav-item"><i class="fas fa-money-bill-wave"></i><span>Payouts</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile" id="userProfile">
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
                <button class="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Open sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Products Management</h1>
                <p>Manage your products and inventory</p>
            </div>
            <div class="header-right">
                <div class="search-row">
                    <div class="date-display" id="dateDisplay"></div>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search products...">
                    </div>
                </div>
            </div>
        </header>

        <!-- Product List -->
        <div id="productsGrid" class="products-grid">
            <div class="loading-state">Loading products...</div>
        </div>

        <footer class="main-footer">
            <p>© <?= date('Y') ?> PalitOra. All rights reserved.</p>
            <div class="footer-links">
                <a href="privacy.html">Privacy Policy</a> •
                <a href="terms.html">Terms of Service</a>
            </div>
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
            <button class="logout-btn2 logout-btn2-secondary" id="cancelLogout">Cancel</button>
            <a href="/seller/backend/auth/logout.php" class="logout-btn2 logout-btn2-danger">Sign Out</a>
        </div>
    </div>
</div>

<script src="/seller/js/logout.js"></script>

<script>
// Mobile sidebar toggle functionality
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

// Close sidebar when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    
    if (!sidebar.contains(event.target) && !mobileMenuBtn.contains(event.target) && sidebar.classList.contains('active')) {
        sidebar.classList.remove('active');
    }
});

// Close sidebar with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('active');
    }
});
</script>

<script src="/seller/js/products.js?v=<?= time() ?>"></script>

</body>
</html>