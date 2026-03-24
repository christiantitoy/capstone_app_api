<?php
// /admin/ui/buyers.php
session_start();

// Simple auth check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyers Management | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../admin/css/dashboard.css">
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
            <a href="/admin/ui/buyers.php" class="nav-item active">
                <i class="fas fa-users"></i><span>Buyers</span>
            </a>
            <a href="/admin/ui/sellers.php" class="nav-item">
                <i class="fas fa-store"></i><span>Sellers</span>
            </a>
            <a href="/admin/ui/products.php" class="nav-item">
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
                    <h4>Admin User</h4>
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
                <h1>Buyers Management</h1>
                <p>Manage all buyers on the platform</p>
            </div>
            <div class="header-right">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
                <div class="date-display" id="currentDate"></div>
            </div>
        </header>

        <!-- STATS CARDS -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalBuyers">0</h3>
                    <p>Total Buyers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#27ae6020;color:#27ae60">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h3 id="activeBuyers">0</h3>
                    <p>Active Buyers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e67e2220;color:#e67e22">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalOrders">0</h3>
                    <p>Total Orders</p>
                </div>
            </div>
        </section>

        <!-- BUYERS LIST SECTION -->
        <div class="full-width-section buyers-list">
            <div class="section-header">
                <h2>Buyers List</h2>
                <div class="search-container">
                    <input type="text" class="search-field" id="searchBuyer" placeholder="Search buyer...">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <div class="table-container">
                <div class="buyer_holder">
                    <div class="table-header">
                        <div class="col-id">ID</div>
                        <div class="col-username">Username</div>
                        <div class="col-email">Email</div>
                        <div class="col-avatar">Avatar</div>
                        <div class="col-orders">Orders</div>
                        <div class="col-status">Status</div>
                    </div>
                    
                    <div class="table-body" id="buyersTableBody">
                        <div class="loading">Loading buyers...</div>
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

<script>
    // Display current date
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString(undefined, options);
    
    // Logout function
    document.getElementById('logoutBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = '/admin/backend/logout.php';
        }
    });
</script>

</body>
</html>