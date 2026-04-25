<?php
// /seller/ui/payouts.php
require_once __DIR__ . '/../backend/session/auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payouts - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/payouts.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/logout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Palit<span>Ora</span></h2>
            <button class="sidebar-close-btn" onclick="toggleSidebar()" aria-label="Close navigation">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="sidebar-nav">
            <a href="/seller/ui/dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="/seller/ui/products.php" class="nav-item"><i class="fas fa-box"></i><span>Products</span></a>
            <a href="/seller/ui/orders.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Orders</span></a>
            <a href="/seller/ui/employees.php" class="nav-item"><i class="fas fa-users"></i><span>Employees</span></a>
            <a href="/seller/ui/my_plan.php" class="nav-item"><i class="fas fa-crown"></i><span>My Plan</span></a>
            <a href="/seller/ui/sales.php" class="nav-item"><i class="fas fa-chart-line"></i><span>Sales</span></a>
            <a href="/seller/ui/payouts.php" class="nav-item active"><i class="fas fa-money-bill-wave"></i><span>Payouts</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile" id="userProfile">
                <div class="avatar"><?= strtoupper(substr($seller_name, 0, 1)) ?></div>
                <div class="user-info">
                    <h4 class="seller-name"><?= htmlspecialchars($seller_name) ?></h4>
                    <p>Seller Account</p>
                </div>
            </div>
            <button class="logout-btn logout-trigger" title="Sign out">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div class="header-left">
                <button class="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Open navigation">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Payout History</h1>
                <p>Track your earnings and payouts</p>
            </div>
            <div class="header-right">
                <div class="date-display"><?= date('F j, Y') ?></div>
            </div>
        </header>

        <!-- Stats Cards -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalPayouts">--</h3>
                    <p>Total Payouts</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#27ae6020;color:#27ae60">
                    <i class="fas fa-peso-sign"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalReceived">--</h3>
                    <p>Total Received</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#9b59b620;color:#9b59b6">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3 id="lastPayout">--</h3>
                    <p>Last Payout</p>
                </div>
            </div>
        </section>

        <!-- Payouts List -->
        <div class="full-width-section">
            <div class="section-header">
                <h2>Payout History</h2>
            </div>
            
            <div class="payouts-list" id="payoutsList">
                <div class="loading-state">
                    <div class="spinner"></div>
                    <p>Loading payouts...</p>
                </div>
            </div>
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

<!-- Payout Details Modal -->
<div id="payoutModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3><i class="fas fa-money-bill-wave" style="color: #27ae60;"></i> Payout Details</h3>
            <span class="modal-close" onclick="closePayoutModal()">&times;</span>
        </div>
        <div class="modal-body" id="payoutModalBody">
            <!-- Dynamic content -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closePayoutModal()">Close</button>
        </div>
    </div>
</div>

<!-- Image Viewer Modal -->
<div id="imageViewerModal" class="image-viewer-modal">
    <span class="close-viewer" onclick="closeImageViewer()">&times;</span>
    <img id="viewerImage" src="" alt="Payment Proof">
</div>

<!-- LOGOUT MODAL -->
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
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;
        sidebar.classList.toggle('active');
    }

    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        if (!sidebar || !sidebar.classList.contains('active')) return;
        if (!sidebar.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) sidebar.classList.remove('active');
        }
    });

    let currentPayouts = [];
    
    // Load payouts
    async function loadPayouts() {
        try {
            const response = await fetch('/seller/backend/payouts/get_seller_payouts.php');
            const result = await response.json();
            
            if (result.success) {
                currentPayouts = result.data.payouts;
                displayStats(result.data.stats);
                displayPayouts(result.data.payouts);
            } else {
                document.getElementById('payoutsList').innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Failed to load payouts</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('payoutsList').innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Error loading payouts</p>
                </div>
            `;
        }
    }
    
    function displayStats(stats) {
        document.getElementById('totalPayouts').textContent = stats.total_payouts || 0;
        document.getElementById('totalReceived').textContent = `₱${formatNumber(stats.total_received)}`;
        document.getElementById('lastPayout').textContent = stats.last_payout_date || 'Never';
    }
    
    function displayPayouts(payouts) {
        const container = document.getElementById('payoutsList');
        
        if (!payouts || payouts.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>No payouts yet</h3>
                    <p>Your earnings will appear here once payouts are processed.</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        payouts.forEach(payout => {
            html += `
                <div class="payout-card" onclick="viewPayoutDetails(${payout.payout_id})">
                    <div class="payout-header">
                        <div class="payout-title">
                            <span class="payout-id">Payout #${payout.payout_id}</span>
                            <span class="payout-date">${payout.paid_at_formatted || payout.created_at_formatted}</span>
                        </div>
                        <div class="payout-amount">₱${formatNumber(payout.total_amount)}</div>
                    </div>
                    <div class="payout-body">
                        <div class="payout-info">
                            <span><i class="fas fa-shopping-cart"></i> ${payout.order_count} order${payout.order_count !== 1 ? 's' : ''}</span>
                            <span><i class="fas fa-box"></i> ${payout.total_items} item${payout.total_items !== 1 ? 's' : ''}</span>
                            <span><i class="fas fa-mobile-alt"></i> ${escapeHtml(payout.gcash_number)}</span>
                        </div>
                        <div class="payout-proof" onclick="event.stopPropagation(); viewProofImage('${payout.proof_url}')">
                            <i class="fas fa-image"></i> View Proof
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    async function viewPayoutDetails(payoutId) {
        try {
            const response = await fetch(`/seller/backend/payouts/get_payout_details.php?id=${payoutId}`);
            const result = await response.json();
            
            if (result.success) {
                showPayoutModal(result.data);
            } else {
                alert('Failed to load payout details');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error loading payout details');
        }
    }
    
    function showPayoutModal(payout) {
        const modalBody = document.getElementById('payoutModalBody');
        
        let itemsHtml = '';
        if (payout.items && payout.items.length > 0) {
            itemsHtml = `
                <div class="modal-items-list">
                    ${payout.items.map(item => `
                        <div class="modal-item">
                            <div class="item-info">
                                <strong>${escapeHtml(item.product_name)}</strong>
                                <span>Order #${item.orders_id} • ${item.quantity} × ₱${formatNumber(item.unit_price)}</span>
                            </div>
                            <div class="item-total">₱${formatNumber(item.item_total)}</div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        modalBody.innerHTML = `
            <div class="payout-detail-summary">
                <div class="detail-row">
                    <span class="label">Payout ID:</span>
                    <span class="value">#${payout.payout_id}</span>
                </div>
                <div class="detail-row">
                    <span class="label">GCash Number:</span>
                    <span class="value">${escapeHtml(payout.gcash_number)}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Total Amount:</span>
                    <span class="value" style="color: #27ae60; font-weight: 700;">₱${formatNumber(payout.total_amount)}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Paid On:</span>
                    <span class="value">${payout.paid_at_formatted || 'Pending'}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Payment Proof:</span>
                    <span class="value">
                        <button class="btn-link" onclick="viewProofImage('${payout.proof_url}')">
                            <i class="fas fa-image"></i> View Proof
                        </button>
                    </span>
                </div>
            </div>
            
            <div class="modal-section">
                <h4>Items in this Payout</h4>
                ${itemsHtml || '<p class="text-muted">No items found</p>'}
            </div>
        `;
        
        document.getElementById('payoutModal').style.display = 'flex';
    }
    
    function closePayoutModal() {
        document.getElementById('payoutModal').style.display = 'none';
    }
    
    function viewProofImage(url) {
        document.getElementById('viewerImage').src = url;
        document.getElementById('imageViewerModal').style.display = 'flex';
    }
    
    function closeImageViewer() {
        document.getElementById('imageViewerModal').style.display = 'none';
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
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('payoutModal');
        const imageModal = document.getElementById('imageViewerModal');
        if (event.target === modal) closePayoutModal();
        if (event.target === imageModal) closeImageViewer();
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePayoutModal();
            closeImageViewer();
        }
    });
    
    // Load on page load
    document.addEventListener('DOMContentLoaded', loadPayouts);
</script>

</body>
</html>