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
            <a href="/admin/ui/order_payments.php" class="nav-item active">
                <i class="fas fa-credit-card"></i><span>Order Payments</span>
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
                        <input type="text" class="search-field" id="searchPayment" placeholder="Search order or buyer...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="payment_holder">
                    <div class="payment-table-header">
                        <div class="col-proof-id">Proof ID</div>
                        <div class="col-order">Order #</div>
                        <div class="col-buyer">Buyer</div>
                        <div class="col-gcash">GCash Number</div>
                        <div class="col-amount">Amount</div>
                        <div class="col-status">Status</div>
                        <div class="col-date">Submitted</div>
                        <div class="col-action">Action</div>
                    </div>
                    
                    <div class="table-body" id="paymentsTableBody">
                        <div class="loading">Loading payment proofs...</div>
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

<!-- Image Viewer Modal -->
<div id="imageViewerModal" class="image-viewer-modal">
    <span class="close-viewer" onclick="closeImageViewer()">&times;</span>
    <img id="viewerImage" src="" alt="Payment Proof">
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">
                <i id="modalIcon" class="fas"></i>
                <span id="modalTitleText">Update Payment Status</span>
            </h3>
            <span class="close-modal" onclick="closeStatusModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="modalMessage"></div>
            <form id="statusUpdateForm">
                <input type="hidden" id="modalProofId">
                <input type="hidden" id="modalAction">
                
                <div class="form-group">
                    <label>Order: <span id="modalOrderInfo"></span></label>
                </div>
                
                <div class="form-group">
                    <label>Buyer: <span id="modalBuyerInfo"></span></label>
                </div>
                
                <div class="form-group">
                    <label>Amount: <span id="modalAmount"></span></label>
                </div>
                
                <div class="form-group" id="rejectionReasonGroup" style="display: none;">
                    <label for="rejectionReasonInput">Rejection Reason <span class="required">*</span></label>
                    <textarea id="rejectionReasonInput" class="form-control" rows="4" placeholder="Please provide a reason for rejection..."></textarea>
                    <small class="form-text">This reason will be recorded for reference.</small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeStatusModal()">Cancel</button>
            <button class="btn" id="confirmStatusBtn" onclick="confirmStatusUpdate()">Confirm</button>
        </div>
    </div>
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

<!-- Notification Container -->
<div id="notificationContainer"></div>

<script src="/admin/js/logout.js"></script>

<script>
    let allPaymentProofs = [];
    let currentProof = null;
    
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
                
                // Update stats
                document.getElementById('totalPayments').textContent = result.status_counts.total;
                document.getElementById('pendingPayments').textContent = result.status_counts.pending;
                document.getElementById('verifiedPayments').textContent = result.status_counts.verified;
                document.getElementById('rejectedPayments').textContent = result.status_counts.rejected;
                
                // Display payment proofs
                displayPaymentProofs(allPaymentProofs);
            } else {
                document.getElementById('paymentsTableBody').innerHTML = '<div class="error">Failed to load payment proofs</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('paymentsTableBody').innerHTML = '<div class="error">Error loading payment proofs</div>';
        }
    }
    
    // Display payment proofs in table
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
            
            let actionButtons = '';
            if (proof.payment_status === 'pending') {
                actionButtons = `
                    <button class="btn-action btn-view" onclick="viewProofImage(event, '${proof.proof_image_url}')" title="View Proof">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-action btn-verify" onclick="openStatusModal(event, ${proof.proof_id}, 'verify')" title="Verify">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn-action btn-reject" onclick="openStatusModal(event, ${proof.proof_id}, 'reject')" title="Reject">
                        <i class="fas fa-times"></i>
                    </button>
                `;
            } else {
                actionButtons = `
                    <button class="btn-action btn-view" onclick="viewProofImage(event, '${proof.proof_image_url}')" title="View Proof">
                        <i class="fas fa-eye"></i>
                    </button>
                `;
            }
            
            html += `
                <div class="payment-row" onclick="viewOrder(${proof.order_id})">
                    <div class="col-proof-id">#${proof.proof_id}</div>
                    <div class="col-order">
                        <span class="order-link">#${proof.order_id}</span>
                    </div>
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
                        <small>Order: ₱${formatNumber(proof.order_total)}</small>
                    </div>
                    <div class="col-status">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                    <div class="col-date">${formatDateTime(proof.submitted_at)}</div>
                    <div class="col-action" onclick="event.stopPropagation()">
                        ${actionButtons}
                    </div>
                </div>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    function viewProofImage(event, imageUrl) {
        event.stopPropagation();
        const modal = document.getElementById('imageViewerModal');
        const viewerImage = document.getElementById('viewerImage');
        viewerImage.src = imageUrl;
        modal.style.display = 'flex';
    }
    
    function closeImageViewer() {
        document.getElementById('imageViewerModal').style.display = 'none';
    }
    
    function openStatusModal(event, proofId, action) {
        event.stopPropagation();
        
        const proof = allPaymentProofs.find(p => p.proof_id === proofId);
        if (!proof) return;
        
        currentProof = proof;
        
        const modal = document.getElementById('statusModal');
        const modalTitle = document.getElementById('modalTitleText');
        const modalIcon = document.getElementById('modalIcon');
        const modalMessage = document.getElementById('modalMessage');
        const modalProofId = document.getElementById('modalProofId');
        const modalAction = document.getElementById('modalAction');
        const modalOrderInfo = document.getElementById('modalOrderInfo');
        const modalBuyerInfo = document.getElementById('modalBuyerInfo');
        const modalAmount = document.getElementById('modalAmount');
        const rejectionGroup = document.getElementById('rejectionReasonGroup');
        const confirmBtn = document.getElementById('confirmStatusBtn');
        
        modalProofId.value = proofId;
        modalAction.value = action;
        modalOrderInfo.textContent = `#${proof.order_id}`;
        modalBuyerInfo.textContent = proof.buyer_name;
        modalAmount.textContent = `₱${formatNumber(proof.amount)}`;
        
        if (action === 'verify') {
            modalTitle.textContent = 'Verify Payment';
            modalIcon.className = 'fas fa-check-circle';
            modalIcon.style.color = '#27ae60';
            modalMessage.innerHTML = `
                <div class="message-box message-success">
                    <i class="fas fa-info-circle"></i>
                    <p>Are you sure you want to verify this payment proof?</p>
                </div>
            `;
            rejectionGroup.style.display = 'none';
            confirmBtn.className = 'btn btn-success';
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Verify Payment';
        } else {
            modalTitle.textContent = 'Reject Payment';
            modalIcon.className = 'fas fa-times-circle';
            modalIcon.style.color = '#e74c3c';
            modalMessage.innerHTML = `
                <div class="message-box message-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Are you sure you want to reject this payment proof? Please provide a reason.</p>
                </div>
            `;
            rejectionGroup.style.display = 'block';
            document.getElementById('rejectionReasonInput').value = '';
            confirmBtn.className = 'btn btn-danger';
            confirmBtn.innerHTML = '<i class="fas fa-times"></i> Reject Payment';
        }
        
        modal.style.display = 'flex';
        if (action === 'reject') {
            document.getElementById('rejectionReasonInput').focus();
        }
    }
    
    function closeStatusModal() {
        document.getElementById('statusModal').style.display = 'none';
        document.getElementById('rejectionReasonInput').value = '';
        currentProof = null;
    }
    
    async function confirmStatusUpdate() {
        const proofId = document.getElementById('modalProofId').value;
        const action = document.getElementById('modalAction').value;
        const status = action === 'verify' ? 'verified' : 'rejected';
        const reason = action === 'reject' ? document.getElementById('rejectionReasonInput').value.trim() : '';
        
        if (action === 'reject' && !reason) {
            showNotification('error', 'Please provide a reason for rejection.');
            return;
        }
        
        const confirmBtn = document.getElementById('confirmStatusBtn');
        const originalText = confirmBtn.innerHTML;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        try {
            const response = await fetch('../backend/payments/update_payment_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    proof_id: proofId,
                    status: status,
                    reason: reason
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                closeStatusModal();
                showNotification('success', result.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('error', 'Failed to update status: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('error', 'Error updating payment status');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
        }
    }
    
    function viewOrder(orderId) {
        window.location.href = `order_details.php?id=${orderId}`;
    }
    
    function showNotification(type, message) {
        const container = document.getElementById('notificationContainer');
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        
        notification.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
        `;
        
        container.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
    
    // Filter functionality
    function filterPayments() {
        const statusFilter = document.getElementById('statusFilter').value;
        const searchTerm = document.getElementById('searchPayment').value.toLowerCase();
        
        let filtered = [...allPaymentProofs];
        
        if (statusFilter !== 'all') {
            filtered = filtered.filter(p => p.payment_status === statusFilter);
        }
        
        if (searchTerm) {
            filtered = filtered.filter(p => 
                p.order_id.toString().includes(searchTerm) ||
                (p.buyer_name && p.buyer_name.toLowerCase().includes(searchTerm)) ||
                p.gcash_number.includes(searchTerm)
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
    
    // Event listeners
    document.getElementById('statusFilter').addEventListener('change', filterPayments);
    document.getElementById('searchPayment').addEventListener('input', filterPayments);
    
    window.onclick = function(event) {
        const statusModal = document.getElementById('statusModal');
        const imageModal = document.getElementById('imageViewerModal');
        
        if (event.target === statusModal) {
            closeStatusModal();
        }
        if (event.target === imageModal) {
            closeImageViewer();
        }
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStatusModal();
            closeImageViewer();
        }
    });
    
    // Load payment proofs when page loads
    loadPaymentProofs();
</script>

</body>
</html>