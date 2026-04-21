<?php
// /admin/ui/riders.php
require_once '../backend/session/auth_admin.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riders Management | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/riders.css?v=<?= time() ?>">
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
            <a href="/admin/ui/products.php" class="nav-item">
                <i class="fas fa-box"></i><span>Products</span>
            </a>
            <a href="/admin/ui/riders.php" class="nav-item active">
                <i class="fas fa-motorcycle"></i><span>Riders</span>
            </a>
            <a href="/admin/ui/orders.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i><span>Orders</span>
            </a>
            <a href="/admin/ui/deliveries.php" class="nav-item">
                <i class="fas fa-truck"></i><span>Deliveries</span>
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
                <h1>Riders Management</h1>
                <p>Manage delivery riders on the platform</p>
            </div>
            <div class="header-right">
                <div class="date-display" id="currentDate"></div>
            </div>
        </header>

        <!-- STATS CARDS -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db">
                    <i class="fas fa-motorcycle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalRiders">0</h3>
                    <p>Total Riders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#27ae6020;color:#27ae60">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="approvedRiders">0</h3>
                    <p>Approved Riders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#f39c1220;color:#f39c12">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="pendingRiders">0</h3>
                    <p>Pending Approval</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#2ecc7120;color:#2ecc71">
                    <i class="fas fa-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="activeRiders">0</h3>
                    <p>Active Now</p>
                </div>
            </div>
        </section>

        <!-- RIDERS LIST SECTION -->
        <div class="full-width-section riders-list">
            <div class="section-header">
                <h2>Riders List</h2>
                <div class="filter-controls">
                    <select id="statusFilter" class="filter-select">
                        <option value="all">All Riders</option>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                        <option value="delivering">Delivering</option>
                    </select>
                    <div class="search-container">
                        <input type="text" class="search-field" id="searchRider" placeholder="Search rider...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="rider_holder">
                    <div class="rider-table-header">
                        <div class="col-id">ID</div>
                        <div class="col-name">Name</div>
                        <div class="col-contact">Contact</div>
                        <div class="col-verification">Verification</div>
                        <div class="col-status">Status</div>
                    </div>
                    
                    <div class="table-body" id="ridersTableBody">
                        <div class="loading">Loading riders...</div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <p>© 2026 Admin Dashboard. All rights reserved.</p>
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
    // Store original riders data for filtering
    let allRiders = [];
    
    // Display current date
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString(undefined, options);
    
    // Function to view rider details
    function viewRider(riderId) {
        window.location.href = `rider_details.php?id=${riderId}`;
    }
    
    // Load riders from backend
    async function loadRiders() {
        const tableBody = document.getElementById('ridersTableBody');
        
        try {
            const response = await fetch('../backend/riders/get_riders.php');
            const result = await response.json();
            
            if (result.success) {
                allRiders = result.data;
                const stats = result.statistics;
                
                // Update statistics
                document.getElementById('totalRiders').textContent = stats.total;
                document.getElementById('approvedRiders').textContent = stats.approved;
                document.getElementById('pendingRiders').textContent = stats.pending;
                document.getElementById('activeRiders').textContent = stats.online + stats.delivering;
                
                // Display riders
                displayRiders(allRiders);
            } else {
                tableBody.innerHTML = '<div class="loading">Failed to load riders</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            tableBody.innerHTML = '<div class="loading">Error loading riders</div>';
        }
    }
    
    function displayRiders(riders) {
        const tbody = document.getElementById('ridersTableBody');
        
        if (riders.length === 0) {
            tbody.innerHTML = '<div class="loading">No riders found</div>';
            return;
        }
        
        let html = '';
        riders.forEach(rider => {
            const statusClass = getStatusClass(rider.status);
            const verificationClass = getVerificationClass(rider.verification_status);
            
            html += `
                <div class="rider-row" onclick="viewRider(${rider.id})">
                    <div class="col-id">#${rider.id}</div>
                    <div class="col-name">
                        <strong>${escapeHtml(rider.username)}</strong>
                    </div>
                    <div class="col-contact">
                        <div style="font-size: 13px; color: #7f8c8d;">
                            <i class="fas fa-envelope" style="margin-right: 5px;"></i>
                            ${escapeHtml(rider.email)}
                        </div>
                    </div>
                    <div class="col-verification">
                        <span class="status-badge ${verificationClass}">${formatVerificationStatus(rider.verification_status)}</span>
                    </div>
                    <div class="col-status">
                        <span class="status-badge ${statusClass}">
                            <i class="fas ${getStatusIcon(rider.status)}" style="margin-right: 5px;"></i>
                            ${rider.status}
                        </span>
                    </div>
                </div>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    function getStatusClass(status) {
        const classes = {
            'online': 'status-active',
            'offline': 'status-offline',
            'delivering': 'status-busy'
        };
        return classes[status] || 'status-pending';
    }
    
    function getStatusIcon(status) {
        const icons = {
            'online': 'fa-circle',
            'offline': 'fa-circle',
            'delivering': 'fa-motorcycle'
        };
        return icons[status] || 'fa-circle';
    }
    
    function getVerificationClass(verificationStatus) {
        const classes = {
            'complete': 'status-approved',
            'pending': 'status-pending',
            'rejected': 'status-rejected',
            'none': 'status-offline'
        };
        return classes[verificationStatus] || 'status-offline';
    }
    
    function formatVerificationStatus(status) {
        const formats = {
            'complete': 'Verified',
            'pending': 'Pending',
            'rejected': 'Rejected',
            'none': 'Unverified'
        };
        return formats[status] || status;
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Search and filter functionality
    function filterRiders() {
        const searchTerm = document.getElementById('searchRider').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        
        let filtered = allRiders;
        
        if (statusFilter !== 'all') {
            filtered = filtered.filter(r => r.status === statusFilter);
        }
        
        if (searchTerm) {
            filtered = filtered.filter(rider => 
                rider.username.toLowerCase().includes(searchTerm) ||
                rider.email.toLowerCase().includes(searchTerm) ||
                rider.id.toString().includes(searchTerm)
            );
        }
        
        displayRiders(filtered);
    }
    
    document.getElementById('searchRider').addEventListener('input', filterRiders);
    document.getElementById('statusFilter').addEventListener('change', filterRiders);
    
    // Load riders when page loads
    loadRiders();
</script>

</body>
</html>