<?php
// /admin/ui/order_payment_details.php
require_once '../backend/session/auth_admin.php';

$proofId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$proofId) {
    header('Location: order_payments.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/payment_details.css?v=<?= time() ?>">
</head>
<body>

<div class="payment-details-container">
    <div class="page-header">
        <a href="order_payments.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Payments
        </a>
        <h1>Payment Details</h1>
        <div class="header-actions">
            <div id="actionButtons"></div>
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p>Loading payment details...</p>
    </div>

    <div id="content" style="display: none;">
        <div class="payment-summary-card">
            <div class="payment-header">
                <div class="payment-title">
                    <h2>Payment Proof #<span id="proofId">-</span></h2>
                    <span class="status-badge" id="paymentStatus">-</span>
                </div>
                <div class="payment-meta">
                    <p><i class="fas fa-calendar"></i> Submitted: <span id="submittedDate">-</span></p>
                </div>
            </div>
            
            <div class="payment-details-grid">
                <div class="detail-item">
                    <span class="label">Order #</span>
                    <span class="value"><a href="order_details.php?id=" id="orderLink">-</a></span>
                </div>
                <div class="detail-item">
                    <span class="label">Buyer</span>
                    <span class="value" id="buyerName">-</span>
                </div>
                <div class="detail-item">
                    <span class="label">Email</span>
                    <span class="value" id="buyerEmail">-</span>
                </div>
                <div class="detail-item">
                    <span class="label">GCash Number</span>
                    <span class="value" id="gcashNumber">-</span>
                </div>
                <div class="detail-item">
                    <span class="label">Payment Amount</span>
                    <span class="value" id="paymentAmount">₱0.00</span>
                </div>
                <div class="detail-item">
                    <span class="label">Order Total</span>
                    <span class="value" id="orderTotal">₱0.00</span>
                </div>
            </div>
            
            <div class="proof-section">
                <h4><i class="fas fa-image"></i> Payment Proof</h4>
                <div class="proof-image" onclick="viewFullImage()">
                    <img id="proofImg" src="" alt="Payment Proof">
                </div>
            </div>
            
            <div id="rejectionSection" style="display: none;" class="rejection-section">
                <h4><i class="fas fa-exclamation-circle"></i> Rejection Reason</h4>
                <p id="rejectionReason"></p>
            </div>
        </div>

        <div class="info-section">
            <div class="section-header">
                <h3><i class="fas fa-shopping-bag"></i> Order Items</h3>
            </div>
            
            <div class="table-container">
                <div class="items-holder">
                    <div class="items-header">
                        <div class="col-product">Product</div>
                        <div class="col-seller">Seller</div>
                        <div class="col-qty">Qty</div>
                        <div class="col-price">Unit Price</div>
                        <div class="col-total">Total</div>
                    </div>
                    
                    <div class="table-body" id="itemsBody"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="errorState" class="error-state" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <h3>Failed to load payment details</h3>
        <p id="errorMessage"></p>
        <a href="order_payments.php" class="btn btn-primary">Return to Payments</a>
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
            <div class="form-group" id="rejectionReasonGroup" style="display: none;">
                <label for="rejectionReasonInput">Rejection Reason <span class="required">*</span></label>
                <textarea id="rejectionReasonInput" class="form-control" rows="4" placeholder="Please provide a reason..."></textarea>
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
    const proofId = <?= $proofId ?>;
    let paymentData = null;
    let proofImageUrl = '';
    
    async function loadDetails() {
        try {
            const response = await fetch(`../backend/payments/get_payment_details.php?id=${proofId}`);
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
                <button class="btn btn-success" onclick="openStatusModal('verify')">
                    <i class="fas fa-check-circle"></i> Verify Payment
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
        const items = data.items;
        
        document.getElementById('proofId').textContent = payment.proof_id;
        document.getElementById('submittedDate').textContent = formatDateTime(payment.submitted_at);
        document.getElementById('orderLink').href = `order_details.php?id=${payment.order_id}`;
        document.getElementById('orderLink').textContent = `#${payment.order_id}`;
        document.getElementById('buyerName').textContent = payment.buyer_name || 'N/A';
        document.getElementById('buyerEmail').textContent = payment.buyer_email || 'N/A';
        document.getElementById('gcashNumber').textContent = payment.gcash_number;
        document.getElementById('paymentAmount').textContent = `₱${formatNumber(payment.amount)}`;
        document.getElementById('orderTotal').textContent = `₱${formatNumber(payment.order_total)}`;
        document.getElementById('proofImg').src = payment.proof_image_url;
        
        const statusBadge = document.getElementById('paymentStatus');
        statusBadge.textContent = formatStatus(payment.payment_status);
        statusBadge.className = `status-badge status-${payment.payment_status}`;
        
        if (payment.rejection_reason) {
            document.getElementById('rejectionSection').style.display = 'block';
            document.getElementById('rejectionReason').textContent = payment.rejection_reason;
        }
        
        displayItems(items);
    }
    
    function displayItems(items) {
        const tbody = document.getElementById('itemsBody');
        
        if (items.length === 0) {
            tbody.innerHTML = '<div class="no-data">No items found</div>';
            return;
        }
        
        let html = '';
        items.forEach(item => {
            html += `
                <div class="item-row">
                    <div class="col-product">
                        <div class="product-info">
                            <strong>${escapeHtml(item.product_name)}</strong>
                        </div>
                    </div>
                    <div class="col-seller">${escapeHtml(item.store_name || item.seller_name || 'N/A')}</div>
                    <div class="col-qty">${item.quantity}</div>
                    <div class="col-price">₱${formatNumber(item.unit_price)}</div>
                    <div class="col-total">₱${formatNumber(item.total_price)}</div>
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
        const reasonGroup = document.getElementById('rejectionReasonGroup');
        const confirmBtn = document.getElementById('confirmStatusBtn');
        
        actionInput.value = action;
        
        if (action === 'verify') {
            title.textContent = 'Verify Payment';
            icon.className = 'fas fa-check-circle';
            icon.style.color = '#27ae60';
            message.innerHTML = '<div class="message-box message-success"><i class="fas fa-info-circle"></i><p>Are you sure you want to verify this payment?</p></div>';
            reasonGroup.style.display = 'none';
            confirmBtn.className = 'btn btn-success';
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Verify';
        } else {
            title.textContent = 'Reject Payment';
            icon.className = 'fas fa-times-circle';
            icon.style.color = '#e74c3c';
            message.innerHTML = '<div class="message-box message-danger"><i class="fas fa-exclamation-triangle"></i><p>Are you sure you want to reject this payment?</p></div>';
            reasonGroup.style.display = 'block';
            document.getElementById('rejectionReasonInput').value = '';
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
        const status = action === 'verify' ? 'verified' : 'rejected';
        const reason = action === 'reject' ? document.getElementById('rejectionReasonInput').value.trim() : '';
        
        if (action === 'reject' && !reason) {
            showNotification('error', 'Please provide a rejection reason');
            return;
        }
        
        const confirmBtn = document.getElementById('confirmStatusBtn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        try {
            const response = await fetch('../backend/payments/update_payment_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ proof_id: proofId, status, reason })
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
    
    function formatStatus(status) {
        return status.charAt(0).toUpperCase() + status.slice(1);
    }
    
    function formatNumber(num) {
        return parseFloat(num || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
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