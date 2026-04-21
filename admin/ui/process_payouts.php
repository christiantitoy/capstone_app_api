<?php
// /admin/ui/process_payouts.php
require_once '../backend/session/auth_admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Payouts | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/payouts.css?v=<?= time() ?>">
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
                <h1>Process Payouts</h1>
                <p>GCash - Rider Delivery payments for seller payout</p>
            </div>
            <div class="header-right">
                <div class="date-display" id="currentDate"></div>
            </div>
        </header>

        <!-- STATS CARDS -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db">
                    <i class="fas fa-store"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalSellers">0</h3>
                    <p>Total Sellers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e67e2220;color:#e67e22">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="unpaidSellers">0</h3>
                    <p>Unpaid Sellers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#27ae6020;color:#27ae60">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="paidSellers">0</h3>
                    <p>Paid Sellers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#9b59b620;color:#9b59b6">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalPayout">₱0</h3>
                    <p>Total Payout Amount</p>
                </div>
            </div>
        </section>

        <!-- SELLER PAYOUTS LIST -->
        <div class="full-width-section payouts-list">
            <div class="section-header">
                <h2>Seller Payouts</h2>
                <div class="filter-container">
                    <select id="statusFilter" class="filter-select">
                        <option value="all">All Status</option>
                        <option value="Unpaid">Unpaid</option>
                        <option value="Paid">Paid</option>
                    </select>
                    <div class="search-container">
                        <input type="text" class="search-field" id="searchSeller" placeholder="Search seller...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                    <button class="btn btn-primary" onclick="exportPayouts()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>

            <div class="table-container">
                <div class="payout_holder">
                    <div class="payout-table-header">
                        <div class="col-seller">Seller</div>
                        <div class="col-store">Store</div>
                        <div class="col-items">Items</div>
                        <div class="col-amount">Payout Amount</div>
                        <div class="col-status">Status</div>
                        <div class="col-date">Last Updated</div>
                    </div>
                    
                    <div class="table-body" id="payoutsTableBody">
                        <div class="loading">Loading payouts...</div>
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
    let allSellerSummary = [];
    
    // Display current date
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString(undefined, options);
    
    // Load payouts from backend
    async function loadPayouts() {
        try {
            const response = await fetch('/admin/backend/payouts/get_all_payouts.php');
            const result = await response.json();
            
            if (result.success) {
                allSellerSummary = result.data.seller_summary;
                
                // Update stats
                const totalSellers = allSellerSummary.length;
                const unpaidSellers = allSellerSummary.filter(s => s.paid_status === 'Unpaid').length;
                const paidSellers = allSellerSummary.filter(s => s.paid_status === 'Paid').length;
                const totalPayout = allSellerSummary.reduce((sum, s) => sum + s.total_amount, 0);
                
                document.getElementById('totalSellers').textContent = totalSellers;
                document.getElementById('unpaidSellers').textContent = unpaidSellers;
                document.getElementById('paidSellers').textContent = paidSellers;
                document.getElementById('totalPayout').textContent = `₱${formatNumber(totalPayout)}`;
                
                // Display payouts
                displayPayouts(allSellerSummary);
            } else {
                document.getElementById('payoutsTableBody').innerHTML = '<div class="error">Failed to load payouts</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('payoutsTableBody').innerHTML = '<div class="error">Error loading payouts</div>';
        }
    }
    
    // Display payouts in table
    function displayPayouts(sellers) {
        const tbody = document.getElementById('payoutsTableBody');
        
        if (sellers.length === 0) {
            tbody.innerHTML = '<div class="no-data">No payouts found</div>';
            return;
        }
        
        let html = '';
        sellers.forEach(seller => {
            const statusClass = seller.paid_status === 'Paid' ? 'status-processed' : 'status-pending';
            
            html += `
                <div class="payout-row clickable" onclick="viewPayoutDetails(${seller.seller_id})">
                    <div class="col-seller">
                        <div class="seller-info">
                            <strong>${escapeHtml(seller.seller_name || 'N/A')}</strong>
                            <small>${escapeHtml(seller.seller_email || 'No email')}</small>
                        </div>
                    </div>
                    <div class="col-store">
                        <span>${escapeHtml(seller.store_name || 'No store')}</span>
                    </div>
                    <div class="col-items">
                        <span class="item-count">${seller.total_items}</span>
                    </div>
                    <div class="col-amount">
                        <span class="payout-amount">₱${formatNumber(seller.total_amount)}</span>
                    </div>
                    <div class="col-status">
                        <span class="status-badge ${statusClass}">${seller.paid_status}</span>
                    </div>
                    <div class="col-date">
                        ${formatDate(seller.last_updated || seller.sold_items[0]?.sold_date)}
                    </div>
                </div>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    function viewPayoutDetails(sellerId) {
        window.location.href = `payout_details.php?seller_id=${sellerId}`;
    }
    
    function exportPayouts() {
        const statusFilter = document.getElementById('statusFilter').value;
        let sellersToExport = allSellerSummary;
        
        if (statusFilter !== 'all') {
            sellersToExport = allSellerSummary.filter(s => s.paid_status === statusFilter);
        }
        
        let csv = 'Seller Name,Seller Email,Store Name,Total Items,Payout Amount,Status\n';
        
        sellersToExport.forEach(seller => {
            csv += `"${seller.seller_name}","${seller.seller_email}","${seller.store_name || ''}",${seller.total_items},${seller.total_amount.toFixed(2)},"${seller.paid_status}"\n`;
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'seller_payouts.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }
    
    // Filter functionality
    function filterPayouts() {
        const statusFilter = document.getElementById('statusFilter').value;
        const searchTerm = document.getElementById('searchSeller').value.toLowerCase();
        
        let filtered = [...allSellerSummary];
        
        if (statusFilter !== 'all') {
            filtered = filtered.filter(seller => seller.paid_status === statusFilter);
        }
        
        if (searchTerm) {
            filtered = filtered.filter(seller => 
                (seller.seller_name && seller.seller_name.toLowerCase().includes(searchTerm)) ||
                (seller.seller_email && seller.seller_email.toLowerCase().includes(searchTerm)) ||
                (seller.store_name && seller.store_name.toLowerCase().includes(searchTerm))
            );
        }
        
        displayPayouts(filtered);
    }
    
    document.getElementById('statusFilter').addEventListener('change', filterPayouts);
    document.getElementById('searchSeller').addEventListener('input', filterPayouts);
    
    function formatNumber(num) {
        return parseFloat(num || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Load payouts when page loads
    loadPayouts();
</script>

</body>
</html>