<?php
// /admin/ui/rider_remittances.php
require_once '../backend/session/auth_admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Remittances | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/remittances.css?v=<?= time() ?>">
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
            <a href="/admin/ui/riders.php" class="nav-item">
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
                <h1>Rider Remittances</h1>
                <p>Manage COD remittances from riders</p>
            </div>
            <div class="header-right">
                <div class="date-display" id="currentDate"></div>
            </div>
        </header>

        <!-- STATS CARDS -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalRemittances">0</h3>
                    <p>Total Remittances</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e67e2220;color:#e67e22">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="pendingRemittances">0</h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#27ae6020;color:#27ae60">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="confirmedRemittances">0</h3>
                    <p>Confirmed</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#9b59b620;color:#9b59b6">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h3 id="pendingAmount">₱0</h3>
                    <p>Pending Amount</p>
                </div>
            </div>
        </section>

        <!-- REMITTANCES LIST -->
        <div class="full-width-section remittances-list">
            <div class="section-header">
                <h2>Remittance Proofs</h2>
                <div class="filter-container">
                    <select id="statusFilter" class="filter-select">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <div class="search-container">
                        <input type="text" class="search-field" id="searchRemittance" placeholder="Search rider...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="remittance_holder">
                    <div class="remittance-table-header">
                        <div class="col-remit-id">Remit ID</div>
                        <div class="col-rider">Rider</div>
                        <div class="col-gcash">GCash Number</div>
                        <div class="col-earnings">Earnings</div>
                        <div class="col-amount">Amount</div>
                        <div class="col-status">Status</div>
                    </div>
                    
                    <div class="table-body" id="remittancesTableBody">
                        <div class="loading">Loading remittances...</div>
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
    let allRemittances = [];
    
    function toggleDropdown(element) {
        const dropdown = element.closest('.nav-dropdown');
        dropdown.classList.toggle('open');
        localStorage.setItem('onlinePaymentsOpen', dropdown.classList.contains('open'));
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const dropdown = document.querySelector('.nav-dropdown');
        const isOpen = localStorage.getItem('onlinePaymentsOpen') === 'true';
        const hasActive = dropdown?.querySelector('.dropdown-item.active');
        if (isOpen || hasActive) {
            dropdown?.classList.add('open');
        }
    });
    
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString(undefined, options);
    
    async function loadRemittances() {
        try {
            const response = await fetch('/admin/backend/remittances/get_remit_proofs.php');
            const result = await response.json();
            
            if (result.success) {
                allRemittances = result.data;
                
                document.getElementById('totalRemittances').textContent = result.status_counts.total;
                document.getElementById('pendingRemittances').textContent = result.status_counts.pending;
                document.getElementById('confirmedRemittances').textContent = result.status_counts.confirmed;
                document.getElementById('pendingAmount').textContent = `₱${formatNumber(result.status_counts.pending_amount)}`;
                
                displayRemittances(allRemittances);
            } else {
                document.getElementById('remittancesTableBody').innerHTML = '<div class="error">Failed to load remittances</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('remittancesTableBody').innerHTML = '<div class="error">Error loading remittances</div>';
        }
    }
    
    function displayRemittances(remittances) {
        const tbody = document.getElementById('remittancesTableBody');
        
        if (remittances.length === 0) {
            tbody.innerHTML = '<div class="no-data">No remittances found</div>';
            return;
        }
        
        let html = '';
        remittances.forEach(remit => {
            const statusClass = getStatusClass(remit.remit_status);
            const statusText = formatStatus(remit.remit_status);
            
            html += `
                <div class="remittance-row clickable" onclick="viewRemitDetails(${remit.remit_id})">
                    <div class="col-remit-id">#${remit.remit_id}</div>
                    <div class="col-rider">
                        <div class="rider-info">
                            <strong>${escapeHtml(remit.rider_name || 'N/A')}</strong>
                            <small>${escapeHtml(remit.rider_email || 'No email')}</small>
                        </div>
                    </div>
                    <div class="col-gcash">
                        <span class="gcash-number">${escapeHtml(remit.gcash_number)}</span>
                    </div>
                    <div class="col-earnings">
                        <span class="earnings-count">${remit.total_earnings_count} orders</span>
                    </div>
                    <div class="col-amount">
                        <span class="remit-amount">₱${formatNumber(remit.amount)}</span>
                    </div>
                    <div class="col-status">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                </div>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    function viewRemitDetails(remitId) {
        window.location.href = `remit_details.php?id=${remitId}`;
    }
    
    function filterRemittances() {
        const statusFilter = document.getElementById('statusFilter').value;
        const searchTerm = document.getElementById('searchRemittance').value.toLowerCase();
        
        let filtered = [...allRemittances];
        
        if (statusFilter !== 'all') {
            filtered = filtered.filter(r => r.remit_status === statusFilter);
        }
        
        if (searchTerm) {
            filtered = filtered.filter(r => 
                (r.rider_name && r.rider_name.toLowerCase().includes(searchTerm)) ||
                (r.rider_email && r.rider_email.toLowerCase().includes(searchTerm)) ||
                r.gcash_number.includes(searchTerm)
            );
        }
        
        displayRemittances(filtered);
    }
    
    function getStatusClass(status) {
        switch(status) {
            case 'pending': return 'status-pending';
            case 'confirmed': return 'status-confirmed';
            case 'rejected': return 'status-rejected';
            default: return 'status-pending';
        }
    }
    
    function formatStatus(status) {
        return status.charAt(0).toUpperCase() + status.slice(1);
    }
    
    function formatNumber(num) {
        return parseFloat(num || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    document.getElementById('statusFilter').addEventListener('change', filterRemittances);
    document.getElementById('searchRemittance').addEventListener('input', filterRemittances);
    
    loadRemittances();
</script>

</body>
</html>