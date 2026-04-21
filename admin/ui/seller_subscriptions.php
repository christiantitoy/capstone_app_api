<?php
// /admin/ui/seller_subscriptions.php
require_once '../backend/session/auth_admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Subscriptions | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/subscriptions.css?v=<?= time() ?>">
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
            <a href="/admin/ui/seller_subscriptions.php" class="nav-item active">
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
                <h1>Seller Subscriptions</h1>
                <p>Manage seller plan subscription payments</p>
            </div>
            <div class="header-right">
                <div class="date-display" id="currentDate"></div>
            </div>
        </header>

        <!-- STATS CARDS -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalPending">0</h3>
                    <p>Pending Subscriptions</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#27ae6020;color:#27ae60">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalAmount">₱0</h3>
                    <p>Total Pending Amount</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#cd7f3220;color:#cd7f32">
                    <i class="fas fa-medal"></i>
                </div>
                <div class="stat-info">
                    <h3 id="bronzeCount">0</h3>
                    <p>Bronze Plans</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#ffd70020;color:#ffd700">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="stat-info">
                    <h3 id="goldCount">0</h3>
                    <p>Gold/Silver Plans</p>
                </div>
            </div>
        </section>

        <!-- SUBSCRIPTIONS LIST -->
        <div class="full-width-section subscriptions-list">
            <div class="section-header">
                <h2>Pending Subscription Payments</h2>
                <div class="filter-container">
                    <select id="planFilter" class="filter-select">
                        <option value="all">All Plans</option>
                        <option value="bronze">Bronze</option>
                        <option value="silver">Silver</option>
                        <option value="gold">Gold</option>
                    </select>
                    <div class="search-container">
                        <input type="text" class="search-field" id="searchSubscription" placeholder="Search seller...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="subscription_holder">
                    <div class="subscription-table-header">
                        <div class="col-payment-id">Payment ID</div>
                        <div class="col-seller">Seller</div>
                        <div class="col-plan">Plan</div>
                        <div class="col-billing">Billing</div>
                        <div class="col-amount">Amount</div>
                        <div class="col-gcash">GCash</div>
                        <div class="col-date">Submitted</div>
                    </div>
                    
                    <div class="table-body" id="subscriptionsTableBody">
                        <div class="loading">Loading subscriptions...</div>
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
    let allSubscriptions = [];
    let planBreakdown = [];
    
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString(undefined, options);
    
    async function loadSubscriptions() {
        try {
            const response = await fetch('/admin/backend/subscriptions/get_subscriptions.php');
            const result = await response.json();
            
            if (result.success) {
                allSubscriptions = result.data;
                planBreakdown = result.plan_breakdown;
                
                document.getElementById('totalPending').textContent = result.status_counts.total_pending;
                document.getElementById('totalAmount').textContent = `₱${formatNumber(result.status_counts.total_amount)}`;
                
                // Update plan counts
                const bronze = planBreakdown.find(p => p.plan === 'bronze');
                const silver = planBreakdown.find(p => p.plan === 'silver');
                const gold = planBreakdown.find(p => p.plan === 'gold');
                
                document.getElementById('bronzeCount').textContent = bronze?.count || 0;
                document.getElementById('goldCount').textContent = (silver?.count || 0) + (gold?.count || 0);
                
                displaySubscriptions(allSubscriptions);
            } else {
                document.getElementById('subscriptionsTableBody').innerHTML = '<div class="error">Failed to load subscriptions</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('subscriptionsTableBody').innerHTML = '<div class="error">Error loading subscriptions</div>';
        }
    }
    
    function displaySubscriptions(subscriptions) {
        const tbody = document.getElementById('subscriptionsTableBody');
        
        if (subscriptions.length === 0) {
            tbody.innerHTML = '<div class="no-data">No pending subscriptions found</div>';
            return;
        }
        
        let html = '';
        subscriptions.forEach(sub => {
            const planClass = getPlanClass(sub.plan);
            
            html += `
                <div class="subscription-row clickable" onclick="viewSubscriptionDetails(${sub.payment_id})">
                    <div class="col-payment-id">#${sub.payment_id}</div>
                    <div class="col-seller">
                        <div class="seller-info">
                            <strong>${escapeHtml(sub.seller_name || 'N/A')}</strong>
                            <small>${escapeHtml(sub.store_name || 'No store')}</small>
                        </div>
                    </div>
                    <div class="col-plan">
                        <span class="plan-badge ${planClass}">${capitalize(sub.plan)}</span>
                    </div>
                    <div class="col-billing">
                        <span class="billing-badge">${capitalize(sub.billing)}</span>
                    </div>
                    <div class="col-amount">
                        <span class="amount">₱${formatNumber(sub.amount)}</span>
                    </div>
                    <div class="col-gcash">
                        <span class="gcash-number">${escapeHtml(sub.gcash_number)}</span>
                    </div>
                    <div class="col-date">${formatDate(sub.submitted_at)}</div>
                </div>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    function viewSubscriptionDetails(paymentId) {
        window.location.href = `subscription_details.php?id=${paymentId}`;
    }
    
    function filterSubscriptions() {
        const planFilter = document.getElementById('planFilter').value;
        const searchTerm = document.getElementById('searchSubscription').value.toLowerCase();
        
        let filtered = [...allSubscriptions];
        
        if (planFilter !== 'all') {
            filtered = filtered.filter(s => s.plan === planFilter);
        }
        
        if (searchTerm) {
            filtered = filtered.filter(s => 
                (s.seller_name && s.seller_name.toLowerCase().includes(searchTerm)) ||
                (s.store_name && s.store_name.toLowerCase().includes(searchTerm)) ||
                (s.seller_email && s.seller_email.toLowerCase().includes(searchTerm))
            );
        }
        
        displaySubscriptions(filtered);
    }
    
    function getPlanClass(plan) {
        switch(plan) {
            case 'bronze': return 'plan-bronze';
            case 'silver': return 'plan-silver';
            case 'gold': return 'plan-gold';
            default: return 'plan-bronze';
        }
    }
    
    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    function formatNumber(num) {
        return parseFloat(num || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    document.getElementById('planFilter').addEventListener('change', filterSubscriptions);
    document.getElementById('searchSubscription').addEventListener('input', filterSubscriptions);
    
    loadSubscriptions();
</script>

</body>
</html>