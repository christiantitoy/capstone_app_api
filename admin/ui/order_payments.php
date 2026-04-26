<?php
// /admin/ui/order_payments.php
require_once '../backend/session/auth_admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Payments | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/order_payments.css?v=<?= time() ?>">
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
                <h1>Order Payments</h1>
                <p>Manage GCash payment proofs from buyers</p>
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
                    <h3 id="totalPayments">0</h3>
                    <p>Total Payments</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e67e2220;color:#e67e22">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="pendingPayments">0</h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#27ae6020;color:#27ae60">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="verifiedPayments">0</h3>
                    <p>Verified</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e74c3c20;color:#e74c3c">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="rejectedPayments">0</h3>
                    <p>Rejected</p>
                </div>
            </div>
        </section>

        <!-- PAYMENT PROOFS LIST -->
        <div class="full-width-section payments-list">
            <div class="section-header">
                <h2>Payment Proofs</h2>
                <div class="filter-container">
                    <select id="statusFilter" class="filter-select">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="verified">Verified</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <div class="search-container">
                        <input type="text" class="search-field" id="searchPayment" placeholder="Search buyer or GCash...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="payment_holder">
                    <div class="payment-table-header">
                        <div class="col-proof-id">Proof ID</div>
                        <div class="col-buyer">Buyer</div>
                        <div class="col-gcash">GCash Number</div>
                        <div class="col-amount">Amount</div>
                        <div class="col-status">Status</div>
                        <div class="col-date">Submitted</div>
                    </div>
                    
                    <div class="table-body" id="paymentsTableBody">
                        <div class="loading">Loading payment proofs...</div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <p>© 2026 PalitOra Admin. All rights reserved.</p>
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
    let allPaymentProofs = [];
    
    // Toggle dropdown
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
    
    // Display current date
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString(undefined, options);
    
    // Load payment proofs from backend
    async function loadPaymentProofs() {
        try {
            const response = await fetch('/admin/backend/payments/get_payment_proofs.php');
            const result = await response.json();
            
            if (result.success) {
                allPaymentProofs = result.data;
                
                document.getElementById('totalPayments').textContent = result.status_counts.total;
                document.getElementById('pendingPayments').textContent = result.status_counts.pending;
                document.getElementById('verifiedPayments').textContent = result.status_counts.verified;
                document.getElementById('rejectedPayments').textContent = result.status_counts.rejected;
                
                displayPaymentProofs(allPaymentProofs);
            } else {
                document.getElementById('paymentsTableBody').innerHTML = '<div class="error">Failed to load payment proofs</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('paymentsTableBody').innerHTML = '<div class="error">Error loading payment proofs</div>';
        }
    }
    
    function displayPaymentProofs(proofs) {
        const tbody = document.getElementById('paymentsTableBody');
        
        if (proofs.length === 0) {
            tbody.innerHTML = '<div class="no-data">No payment proofs found</div>';
            return;
        }
        
        let html = '';
        proofs.forEach(proof => {
            const statusClass = getStatusClass(proof.payment_status);
            const statusText = formatStatus(proof.payment_status);
            
            html += `
                <div class="payment-row clickable" onclick="viewPaymentDetails(${proof.proof_id})">
                    <div class="col-proof-id">#${proof.proof_id}</div>
                    <div class="col-buyer">
                        <div class="buyer-info">
                            <strong>${escapeHtml(proof.buyer_name || 'N/A')}</strong>
                            <small>${escapeHtml(proof.buyer_email || 'No email')}</small>
                        </div>
                    </div>
                    <div class="col-gcash">
                        <span class="gcash-number">${escapeHtml(proof.gcash_number)}</span>
                    </div>
                    <div class="col-amount">
                        <span class="payment-amount">₱${formatNumber(proof.amount)}</span>
                    </div>
                    <div class="col-status">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                    <div class="col-date">${formatDateTime(proof.submitted_at)}</div>
                </div>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    function viewPaymentDetails(proofId) {
        window.location.href = `order_payment_details.php?id=${proofId}`;
    }
    
    function filterPayments() {
        const statusFilter = document.getElementById('statusFilter').value;
        const searchTerm = document.getElementById('searchPayment').value.toLowerCase();
        
        let filtered = [...allPaymentProofs];
        
        if (statusFilter !== 'all') {
            filtered = filtered.filter(p => p.payment_status === statusFilter);
        }
        
        if (searchTerm) {
            filtered = filtered.filter(p => 
                (p.buyer_name && p.buyer_name.toLowerCase().includes(searchTerm)) ||
                (p.buyer_email && p.buyer_email.toLowerCase().includes(searchTerm)) ||
                p.gcash_number.includes(searchTerm) ||
                p.proof_id.toString().includes(searchTerm)
            );
        }
        
        displayPaymentProofs(filtered);
    }
    
    function getStatusClass(status) {
        switch(status) {
            case 'pending': return 'status-pending';
            case 'verified': return 'status-verified';
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
    
    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleString('en-US', { 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    document.getElementById('statusFilter').addEventListener('change', filterPayments);
    document.getElementById('searchPayment').addEventListener('input', filterPayments);
    
    loadPaymentProofs();
</script>

</body>
</html>