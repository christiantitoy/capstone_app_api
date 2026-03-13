<?php
// /seller/ui/dashboard.php
session_start();
// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /seller/ui/login.php");
    exit;
}
// Get seller info from session
$seller_name = $_SESSION['seller_name'] ?? 'Seller';
$seller_email = $_SESSION['seller_email'] ?? '';
$seller_id = $_SESSION['seller_id'] ?? '';
// You would typically fetch real data from database here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/error.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Seller<span>Dashboard</span></h2>
        </div>
        <nav class="sidebar-nav">
            <a href="/seller/ui/dashboard.php" class="nav-item active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="/seller/ui/products.php" class="nav-item"><i class="fas fa-box"></i><span>Products</span></a>
            <a href="/seller/ui/orders.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Orders</span></a>
            <a href="/seller/ui/analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i><span>Employees</span></a>
            <a href="/seller/ui/analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
            <a href="#" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar"><?= strtoupper(substr($seller_name, 0, 1)) ?></div>
                <div>
                    <h4><?= htmlspecialchars($seller_name) ?></h4>
                    <p>Seller Account</p>
                </div>
            </div>
            <!-- Changed to button that opens modal -->
            <button class="logout-btn logout-trigger" title="Sign out">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </aside>

    <main class="main-content">
        
        <header class="main-header">
            <div class="header-left">
                <h1>Dashboard Overview</h1>
                <p>Welcome back, <?= htmlspecialchars(explode(' ', $seller_name)[0]) ?>!</p>
            </div>
            <div class="header-right">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
                <div class="date-display"><?= date('F j, Y') ?></div>
            </div>
        </header>

        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db"><i class="fas fa-box"></i></div>
                <div class="stat-info"><h3>24</h3><p>Total Products</p></div>
                <div class="stat-trend positive"><i class="fas fa-arrow-up"></i> 12%</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e67e2220;color:#e67e22"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-info"><h3>342</h3><p>Total Orders</p></div>
                <div class="stat-trend positive"><i class="fas fa-arrow-up"></i> 8%</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-info"><h3>$18,420.50</h3><p>Total Revenue</p></div>
                <div class="stat-trend positive"><i class="fas fa-arrow-up"></i> 15%</div>
            </div>
        </section>

        <!-- Recent Orders - full width -->
        <div class="full-width-section recent-orders">
            <div class="section-header">
                <h2>Recent Orders</h2>
                <a href="/seller/ui/orders.php" class="view-all">View All</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>#00345</td><td>Maria Santos</td><td>Wireless Earbuds Pro</td><td>$39.99</td><td><span class="status-badge status-delivered">Delivered</span></td></tr>
                        <tr><td>#00344</td><td>Juan Dela Cruz</td><td>Smart LED Bulb (4-pack)</td><td>$23.99</td><td><span class="status-badge status-shipped">Shipped</span></td></tr>
                        <tr><td>#00343</td><td>Ana Reyes</td><td>Phone Fast Charger 65W</td><td>$21.00</td><td><span class="status-badge status-pending">Pending</span></td></tr>
                        <tr><td>#00342</td><td>Pedro Gomez</td><td>Reusable Water Bottle 1L</td><td>$17.00</td><td><span class="status-badge status-delivered">Delivered</span></td></tr>
                        <tr><td>#00341</td><td>Luz Fernandez</td><td>Portable Power Bank 20000mAh</td><td>$28.00</td><td><span class="status-badge status-shipped">Shipped</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions + Top Products - same height, side by side -->
        <div class="side-by-side">

            <div class="top-products">
                <div class="section-header"><h2>Top Selling Products</h2></div>
                <div class="products-list">
                    <div class="product-item">
                        <div class="product-info"><h4>Wireless Earbuds Pro</h4><p>148 sold</p></div>
                        <div class="product-progress">
                            <div class="progress-bar"><div class="progress-fill" style="width:92%;background:#3498db;"></div></div>
                            <span>92%</span>
                        </div>
                    </div>
                    <div class="product-item">
                        <div class="product-info"><h4>Smart LED Bulb (4-pack)</h4><p>97 sold</p></div>
                        <div class="product-progress">
                            <div class="progress-bar"><div class="progress-fill" style="width:76%;background:#3498db;"></div></div>
                            <span>76%</span>
                        </div>
                    </div>
                    <div class="product-item">
                        <div class="product-info"><h4>Phone Fast Charger 65W</h4><p>82 sold</p></div>
                        <div class="product-progress">
                            <div class="progress-bar"><div class="progress-fill" style="width:68%;background:#3498db;"></div></div>
                            <span>68%</span>
                        </div>
                    </div>
                    <div class="product-item">
                        <div class="product-info"><h4>Reusable Water Bottle 1L</h4><p>65 sold</p></div>
                        <div class="product-progress">
                            <div class="progress-bar"><div class="progress-fill" style="width:54%;background:#3498db;"></div></div>
                            <span>54%</span>
                        </div>
                    </div>
                    <div class="product-item">
                        <div class="product-info"><h4>Portable Power Bank 20000mAh</h4><p>51 sold</p></div>
                        <div class="product-progress">
                            <div class="progress-bar"><div class="progress-fill" style="width:42%;background:#3498db;"></div></div>
                            <span>42%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <p>© <?= date('Y') ?> Seller Dashboard. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a> •
                <a href="#">Terms of Service</a> •
                <a href="#">Help Center</a>
            </div>
        </footer>

    </main>
</div>

<!-- ── LOGOUT CONFIRMATION MODAL ── -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Sign Out</h3>
            <button class="modal-close" id="closeModal">×</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to sign out?</p>
            <p class="text-secondary">You will need to log in again to access your dashboard.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelLogout">Cancel</button>
            <a href="/seller/backend/auth/logout.php" class="btn btn-danger">Sign Out</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('logoutModal');
    const trigger = document.querySelector('.logout-trigger');
    const closeBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelLogout');

    function openModal() {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (trigger) trigger.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    // Click outside to close
    modal.addEventListener('click', e => {
        if (e.target === modal) closeModal();
    });

    // Escape key to close
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });
});
</script>

</body>
</html>