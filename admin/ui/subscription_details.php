<?php
// /admin/ui/subscription_details.php
require_once '../backend/session/auth_admin.php';

$paymentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$paymentId) {
    header('Location: seller_subscriptions.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Details | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/subscription_details.css?v=<?= time() ?>">
</head>
<body>

<div class="subscription-details-container">
    <div class="page-header">
        <a href="seller_subscriptions.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Subscriptions
        </a>
        <h1>Subscription Details</h1>
        <div class="header-actions">
            <div id="actionButtons"></div>
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p>Loading subscription details...</p>
    </div>

    <div id="content" style="display: none;">
        <div class="payment-summary-card">
            <div class="payment-header">
                <div class="payment-title">
                    <h2>Payment #<span id="paymentId">-</span></h2>
                    <span class="status-badge" id="paymentStatus">-</span>
                </div>
                <div class="payment-meta">
                    <p><i class="fas fa-calendar"></i> Submitted: <span id="submittedDate">-</span></p>
                    <p><i class="fas fa-check-circle"></i> Reviewed: <span id="reviewedDate">-</span></p>
                </div>
            </div>
            
            <div class="payment-details-grid">
                <div class="detail-item">
                    <span class="label">Seller</span>
                    <span class="value" id="sellerName">-</span>
                </div>
                <div class="detail-item">
                    <span class="label">Store</span>
                    <span class="value" id="storeName">-</span>
                </div>
                <div class="detail-item">
                    <span class="label">Email</span>
                    <span class="value" id="sellerEmail">-</span>
                </div>
                <div class="detail-item">
                    <span class="label">Plan</span>
                    <span class="value" id="planInfo">-</span>
                </div>
                <div class="detail-item">
                    <span class="label">Billing</span>
                    <span class="value" id="billingInfo">-</span>
                </div>
                <div class="detail-item">
                    <span class="label">GCash Number</span>
                    <span class="value" id="gcashNumber">-</span>
                </div>
                <div class="detail-item">
                    <span class="label">Amount</span>
                    <span class="value" id="amount">₱0.00</span>
                </div>
                <div class="detail-item">
                    <span class="label"></span>
                    <span class="value">
                        <a href="#" id="viewSellerBtn" class="btn btn-outline btn-small">
                            <i class="fas fa-store"></i> View Seller
                        </a>
                    </span>
                </div>
            </div>
            
            <div class="proof-section">
                <h4><i class="fas fa-image"></i> Payment Proof</h4>
                <div class="proof-image" id="proofImage" onclick="viewFullImage()">
                    <img id="proofImg" src="" alt="Payment Proof">
                </div>
            </div>
            
            <div id="notesSection" style="display: none;" class="notes-section">
                <h4><i class="fas fa-sticky-note"></i> Notes</h4>
                <p id="notesText"></p>
            </div>
        </div>

        <div class="info-section">
            <div class="section-header">
                <h3><i class="fas fa-history"></i> Subscription History</h3>
            </div>
            
            <div class="table-container">
                <div class="history-holder">
                    <div class="history-header">
                        <div class="col-payment">Payment ID</div>
                        <div class="col-plan">Plan</div>
                        <div class="col-billing">Billing</div>
                        <div class="col-amount">Amount</div>
                        <div class="col-status">Status</div>
                        <div class="col-date">Date</div>
                    </div>
                    
                    <div class="table-body" id="historyBody"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="errorState" class="error-state" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <h3>Failed to load subscription details</h3>
        <p id="errorMessage"></p>
        <a href="seller_subscriptions.php" class="btn btn-primary">Return to Subscriptions</a>
    </div>
</div>

<div id="imageViewerModal" class="image-viewer-modal">
    <span class="close-viewer" onclick="closeImageViewer()">&times;</span>
    <img id="viewerImage" src="" alt="Payment Proof">
</div>

<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle"><i id="modalIcon" class="fas"></i> <span id="modalTitleText"></span></h3>
            <span class="close-modal" onclick="closeStatusModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="modalMessage"></div>
            <input type="hidden" id="modalAction">
            <div class="form-group" id="notesGroup" style="display: none;">
                <label for="notesInput">Notes <span class="required">*</span></label>
                <textarea id="notesInput" class="form-control" rows="4" placeholder="Please provide notes..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeStatusModal()">Cancel</button>
            <button class="btn" id="confirmStatusBtn" onclick="confirmStatusUpdate()">Confirm</button>
        </div>
    </div>
</div>

<div id="notificationContainer"></div>

<script>
    const paymentId = <?= $paymentId ?>;
    let paymentData = null;
    let proofImageUrl = '';
    
    async function loadDetails() {
        try {
            const response = await fetch(`../backend/subscriptions/get_subscription_details.php?id=${paymentId}`);
            const result = await response.json();
            
            if (result.success) {
                paymentData = result.data;
                proofImageUrl = paymentData.payment.proof_image_url;
                displayDetails(paymentData);
                updateActionButtons(paymentData.payment);
                
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('content').style.display = 'block';
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('errorState').style.display = 'block';
            document.getElementById('errorMessage').textContent = error.message;
        }
    }
    
    function updateActionButtons(payment) {
        const container = document.getElementById('actionButtons');
        
        if (payment.payment_status === 'pending') {
            container.innerHTML = `
                <button class="btn btn-success" onclick="openStatusModal('confirm')">
                    <i class="fas fa-check-circle"></i> Confirm Payment
                </button>
                <button class="btn btn-danger" onclick="openStatusModal('reject')">
                    <i class="fas fa-times-circle"></i> Reject
                </button>
            `;
        } else {
            container.innerHTML = '';
        }
    }
    
    function displayDetails(data) {
        const payment = data.payment;
        const history = data.history;
        
        document.getElementById('paymentId').textContent = payment.payment_id;
        document.getElementById('submittedDate').textContent = formatDateTime(payment.submitted_at);
        document.getElementById('reviewedDate').textContent = payment.reviewed_at ? formatDateTime(payment.reviewed_at) : 'Not reviewed';
        document.getElementById('sellerName').textContent = payment.seller_name || 'N/A';
        document.getElementById('storeName').textContent = payment.store_name || 'No store';
        document.getElementById('sellerEmail').textContent = payment.seller_email || 'N/A';
        document.getElementById('planInfo').innerHTML = `<span class="plan-badge plan-${payment.plan}">${capitalize(payment.plan)}</span>`;
        document.getElementById('billingInfo').textContent = capitalize(payment.billing);
        document.getElementById('gcashNumber').textContent = payment.gcash_number;
        document.getElementById('amount').textContent = `₱${formatNumber(payment.amount)}`;
        document.getElementById('proofImg').src = payment.proof_image_url;
        
        // Set seller details link
        document.getElementById('viewSellerBtn').href = `seller_details.php?id=${payment.seller_id}`;
        
        const statusBadge = document.getElementById('paymentStatus');
        statusBadge.textContent = capitalize(payment.payment_status);
        statusBadge.className = `status-badge status-${payment.payment_status}`;
        
        if (payment.notes) {
            document.getElementById('notesSection').style.display = 'block';
            document.getElementById('notesText').textContent = payment.notes;
        }
        
        displayHistory(history);
    }
    
    function displayHistory(history) {
        const tbody = document.getElementById('historyBody');
        
        if (history.length === 0) {
            tbody.innerHTML = '<div class="no-data">No subscription history</div>';
            return;
        }
        
        let html = '';
        history.forEach(h => {
            html += `
                <div class="history-row">
                    <div class="col-payment">#${h.payment_id}</div>
                    <div class="col-plan"><span class="plan-badge plan-${h.plan}">${capitalize(h.plan)}</span></div>
                    <div class="col-billing">${capitalize(h.billing)}</div>
                    <div class="col-amount">₱${formatNumber(h.amount)}</div>
                    <div class="col-status">
                        <span class="status-badge status-${h.payment_status}">${capitalize(h.payment_status)}</span>
                    </div>
                    <div class="col-date">${formatDate(h.submitted_at)}</div>
                </div>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    function viewFullImage() {
        document.getElementById('viewerImage').src = proofImageUrl;
        document.getElementById('imageViewerModal').style.display = 'flex';
    }
    
    function closeImageViewer() {
        document.getElementById('imageViewerModal').style.display = 'none';
    }
    
    function openStatusModal(action) {
        const modal = document.getElementById('statusModal');
        const title = document.getElementById('modalTitleText');
        const icon = document.getElementById('modalIcon');
        const message = document.getElementById('modalMessage');
        const actionInput = document.getElementById('modalAction');
        const notesGroup = document.getElementById('notesGroup');
        const confirmBtn = document.getElementById('confirmStatusBtn');
        
        actionInput.value = action;
        
        if (action === 'confirm') {
            title.textContent = 'Confirm Payment';
            icon.className = 'fas fa-check-circle';
            icon.style.color = '#27ae60';
            message.innerHTML = '<div class="message-box message-success"><i class="fas fa-info-circle"></i><p>Confirm this subscription payment? The seller\'s plan will be activated.</p></div>';
            notesGroup.style.display = 'none';
            confirmBtn.className = 'btn btn-success';
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirm';
        } else {
            title.textContent = 'Reject Payment';
            icon.className = 'fas fa-times-circle';
            icon.style.color = '#e74c3c';
            message.innerHTML = '<div class="message-box message-danger"><i class="fas fa-exclamation-triangle"></i><p>Reject this subscription payment? Please provide notes.</p></div>';
            notesGroup.style.display = 'block';
            document.getElementById('notesInput').value = '';
            confirmBtn.className = 'btn btn-danger';
            confirmBtn.innerHTML = '<i class="fas fa-times"></i> Reject';
        }
        
        modal.style.display = 'flex';
    }
    
    function closeStatusModal() {
        document.getElementById('statusModal').style.display = 'none';
    }
    
    async function confirmStatusUpdate() {
        const action = document.getElementById('modalAction').value;
        const status = action === 'confirm' ? 'confirmed' : 'rejected';
        const notes = action === 'reject' ? document.getElementById('notesInput').value.trim() : '';
        
        if (action === 'reject' && !notes) {
            showNotification('error', 'Please provide notes for rejection');
            return;
        }
        
        const confirmBtn = document.getElementById('confirmStatusBtn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        try {
            const response = await fetch('../backend/subscriptions/update_subscription_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ payment_id: paymentId, status, notes })
            });
            
            const result = await response.json();
            
            if (result.success) {
                closeStatusModal();
                showNotification('success', result.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('error', result.message);
            }
        } catch (error) {
            showNotification('error', 'Error updating status');
        } finally {
            confirmBtn.disabled = false;
        }
    }
    
    function showNotification(type, message) {
        const container = document.getElementById('notificationContainer');
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i><span>${message}</span><button class="notification-close" onclick="this.parentElement.remove()">&times;</button>`;
        container.appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    }
    
    function capitalize(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    function formatNumber(num) {
        return parseFloat(num || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
    
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    
    window.onclick = function(e) {
        if (e.target === document.getElementById('statusModal')) closeStatusModal();
        if (e.target === document.getElementById('imageViewerModal')) closeImageViewer();
    }
    
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeStatusModal(); closeImageViewer(); } });
    document.addEventListener('DOMContentLoaded', loadDetails);
</script>

</body>
</html>