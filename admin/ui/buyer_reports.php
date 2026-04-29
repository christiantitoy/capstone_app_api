<?php
// /admin/ui/buyer_reports.php
require_once '../backend/session/auth_admin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Reports | Admin Panel</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/buyer_reports.css?v=<?= time() ?>">
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
            <a href="/admin/ui/products.php" class="nav-item">
                <i class="fas fa-box"></i><span>Products</span>
            </a>
            <a href="/admin/ui/riders.php" class="nav-item">
                <i class="fas fa-motorcycle"></i><span>Riders</span>
            </a>
            <a href="/admin/ui/orders.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i><span>Orders</span>
            </a>
            <a href="/admin/ui/deliveries.php" class="nav-item">
                <i class="fas fa-truck"></i><span>Deliveries</span>
            </a>
            <a href="/admin/ui/buyer_reports.php" class="nav-item active">
                <i class="fas fa-flag"></i><span>Buyer Reports</span>
            </a>
            <a href="/admin/ui/process_payouts.php" class="nav-item">
                <i class="fas fa-money-bill-wave"></i><span>Process Payouts</span>
            </a>
            <a href="/admin/ui/order_payments.php" class="nav-item">
                <i class="fas fa-credit-card"></i><span>Order Payments</span>
            </a>
            <a href="/admin/ui/rider_remittances.php" class="nav-item">
                <i class="fas fa-hand-holding-usd"></i><span>Rider Remittances</span>
            </a>
            <a href="/admin/ui/seller_subscriptions.php" class="nav-item">
                <i class="fas fa-crown"></i><span>Seller Subscriptions</span>
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
                <h1>Buyer Reports</h1>
                <p>Manage and resolve buyer issues</p>
            </div>
            <div class="header-right">
                <div class="date-display" id="currentDate"></div>
            </div>
        </header>

        <!-- Stats Cards -->
        <section class="stats-cards" id="statsCards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ff980020;color:#ff9800">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="pendingCount">0</h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#2196f320;color:#2196f3">
                    <i class="fas fa-search"></i>
                </div>
                <div class="stat-info">
                    <h3 id="reviewingCount">0</h3>
                    <p>Reviewing</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#4caf5020;color:#4caf50">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="resolvedCount">0</h3>
                    <p>Resolved</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#f4433620;color:#f44336">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="closedCount">0</h3>
                    <p>Closed</p>
                </div>
            </div>
        </section>

        <!-- Filters -->
        <div class="full-width-section">
            <div class="filters-container">
                <div class="filter-group">
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="reviewing">Reviewing</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                    <input type="text" id="searchInput" class="filter-search" placeholder="Search by issue type or delivery ID...">
                    <button class="btn-refresh" onclick="fetchReports()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="full-width-section">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Delivery ID</th>
                            <th>Buyer ID</th>
                            <th>Issue Type</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody id="reportsTableBody">
                        <tr>
                            <td colspan="6" style="text-align: center;">
                                <i class="fas fa-spinner fa-spin"></i> Loading reports...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-container" id="paginationContainer">
            <button class="page-btn" id="prevPage" disabled>
                <i class="fas fa-chevron-left"></i> Previous
            </button>
            <div class="page-info" id="pageInfo">Page 1 of 1</div>
            <button class="page-btn" id="nextPage" disabled>
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <footer class="main-footer">
            <p>© 2026 PalitOra Admin. All rights reserved.</p>
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
<script src="/admin/js/buyer_reports.js?v=<?= time() ?>"></script>

</body>
</html>