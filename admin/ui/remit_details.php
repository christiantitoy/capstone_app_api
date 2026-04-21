<?php
// /admin/ui/remit_details.php
require_once '../backend/session/auth_admin.php';

$remitId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$remitId) {
    header('Location: rider_remittances.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remittance Details | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/remit_details.css?v=<?= time() ?>">
</head>
<body>

<div class="remit-details-container">
    <div class="page-header">
        <a href="rider_remittances.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Remittances
        </a>
        <h1>Remittance Details</h1>
        <div class="header-actions">
            <div id="actionButtons"></div>
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p>Loading remittance details...</p>
    </div>

    <div id="remitContent" style="display: none;">
        <div class="remit-summary-card">
            <div class="remit-header">
                <div class="remit-title">
                    <h2>Remittance #<span id="remitId">-</span></h2>
                    <span class="status-badge" id="remitStatus">-</span>
                </div>
                <div class="remit-meta">
                    <p><i class="fas fa-calendar"></i> Submitted: <span id="submittedDate">-</span></p>
                </div>
            </div>
            
            <div class="remit-details-grid">
                <div class="detail-item">
                    <span class="label">Rider</span>
                    <span class="value" id="riderName">-</span>
                </div>
                <div class="detail-item">
                    <span class="label">Email</span>
                    <span class="value" id="riderEmail">-</span>
                </div>
                <div class="detail-item">
                    <span class="label">GCash Number</span>
                    <span class="value" id="gcashNumber">-</span>
                </div>
                <div class="detail-item">
                    <span class="label">Rider Status</span>
                    <span class="value" id="riderStatus">-</span>
                </div>
            </div>
            
            <div class="proof-section">
                <h4><i class="fas fa-image"></i> Payment Proof</h4>
                <div class="proof-image" id="proofImage" onclick="viewFullImage()">
                    <img id="proofImg" src="" alt="Payment Proof">
                </div>
            </div>
            
            <div class="amount-summary">
                <div class="summary-item">
                    <span class="label">Total COD Amount</span>
                    <span class="value" id="totalCODAmount">₱0.00</span>
                </div>
                <div class="summary-item">
                    <span class="label">Remitted Amount</span>
                    <span class="value" id="remittedAmount">₱0.00</span>
                </div>
                <div class="summary-item">
                    <span class="label">Total Earnings</span>
                    <span class="value" id="totalEarnings">0 orders</span>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="section-header">
                <h3><i class="fas fa-list"></i> Earnings Included</h3>
            </div>
            
            <div class="table-container">
                <div class="earnings-holder">
                    <div class="earnings-header">
                        <div class="col-order">Order #</div>
                        <div class="col-buyer">Buyer</div>
                        <div class="col-shipping">Shipping Fee</div>
                        <div class="col-cod">COD Amount</div>
                        <div class="col-status">Status</div>
                        <div class="col-date">Date</div>
                    </div>
                    
                    <div class="table-body" id="earningsBody"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="errorState" class="error-state" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <h3>Failed to load remittance details</h3>
        <p id="errorMessage"></p>
        <a href="rider_remittances.php" class="btn btn-primary">Return to Remittances</a>
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
    const remitId = <?= $remitId ?>;
    let remitData = null;
    let proofImageUrl = '';
    
    async function loadRemitDetails() {
        try {
            const response = await fetch(`../backend/remittances/get_remit_details.php?id=${remitId}`);
            const result = await response.json();
            
            if (result.success) {
                remitData = result.data;
                proofImageUrl = remitData.remit_proof.proof_image_url;
                displayRemitDetails(remitData);
                updateActionButtons(remitData.remit_proof);
                
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('remitContent').style.display = 'block';
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('errorState').style.display = 'block';
            document.getElementById('errorMessage').textContent = error.message;
        }
    }
    
    function updateActionButtons(remit) {
        const container = document.getElementById('actionButtons');
        
        if (remit.remit_status === 'pending') {
            container.innerHTML = `
                <button class="btn btn-success" onclick="openStatusModal('confirm')">
                    <i class="fas fa-check-circle"></i> Confirm Remittance
                </button>
                <button class="btn btn-danger" onclick="openStatusModal('reject')">
                    <i class="fas fa-times-circle"></i> Reject
                </button>
            `;
        } else {
            container.innerHTML = '';
        }
    }
    
    function displayRemitDetails(data) {
        const remit = data.remit_proof;
        const earnings = data.earnings;
        const summary = data.summary;
        
        document.getElementById('remitId').textContent = remit.remit_id;
        document.getElementById('submittedDate').textContent = formatDateTime(remit.submitted_at);
        document.getElementById('riderName').textContent = remit.rider_name;
        document.getElementById('riderEmail').textContent = remit.rider_email;
        document.getElementById('gcashNumber').textContent = remit.gcash_number;
        document.getElementById('riderStatus').textContent = formatStatus(remit.rider_status);
        document.getElementById('totalCODAmount').textContent = `₱${formatNumber(summary.total_cod_amount)}`;
        document.getElementById('remittedAmount').textContent = `₱${formatNumber(summary.remitted_amount)}`;
        document.getElementById('totalEarnings').textContent = `${summary.total_earnings} orders`;
        document.getElementById('proofImg').src = remit.proof_image_url;
        
        const statusBadge = document.getElementById('remitStatus');
        statusBadge.textContent = formatRemitStatus(remit.remit_status);
        statusBadge.className = `status-badge status-${remit.remit_status}`;
        
        displayEarnings(earnings);
    }
    
    function displayEarnings(earnings) {
    const tbody = document.getElementById('earningsBody');
    
    if (earnings.length === 0) {
        tbody.innerHTML = '<div class="no-data">No earnings found</div>';
        return;
    }
    
    let html = '';
    earnings.forEach(earning => {
        // COD amount = subtotal + platform_fee (excluding shipping_fee)
        const codAmount = earning.calculated_cod_amount || 
                         (parseFloat(earning.subtotal || 0) + parseFloat(earning.platform_fee || 0));
        
        html += `
            <div class="earning-row">
                <div class="col-order">
                    <a href="order_details.php?id=${earning.order_id}" class="order-link">#${earning.order_id}</a>
                </div>
                <div class="col-buyer">
                    <div class="buyer-info">
                        <strong>${escapeHtml(earning.buyer_name || 'N/A')}</strong>
                        <small>${escapeHtml(earning.buyer_email || '')}</small>
                    </div>
                </div>
                <div class="col-shipping">₱${formatNumber(earning.shipping_fee)}</div>
                <div class="col-cod">₱${formatNumber(codAmount)}</div>
                <div class="col-status">
                    <span class="status-badge ${earning.is_remitted ? 'status-confirmed' : 'status-pending'}">
                        ${earning.is_remitted ? 'Remitted' : 'Pending'}
                    </span>
                </div>
                <div class="col-date">${formatDate(earning.created_at)}</div>
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
        
        if (action === 'confirm') {
            title.textContent = 'Confirm Remittance';
            icon.className = 'fas fa-check-circle';
            icon.style.color = '#27ae60';
            message.innerHTML = '<div class="message-box message-success"><i class="fas fa-info-circle"></i><p>Are you sure you want to confirm this remittance?</p></div>';
            reasonGroup.style.display = 'none';
            confirmBtn.className = 'btn btn-success';
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirm';
        } else {
            title.textContent = 'Reject Remittance';
            icon.className = 'fas fa-times-circle';
            icon.style.color = '#e74c3c';
            message.innerHTML = '<div class="message-box message-danger"><i class="fas fa-exclamation-triangle"></i><p>Are you sure you want to reject this remittance?</p></div>';
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
        const status = action === 'confirm' ? 'confirmed' : 'rejected';
        const reason = action === 'reject' ? document.getElementById('rejectionReasonInput').value.trim() : '';
        
        if (action === 'reject' && !reason) {
            showNotification('error', 'Please provide a rejection reason');
            return;
        }
        
        const confirmBtn = document.getElementById('confirmStatusBtn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        try {
            const response = await fetch('../backend/remittances/update_remit_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ remit_id: remitId, status, reason })
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
    
    function formatRemitStatus(status) {
        return status.charAt(0).toUpperCase() + status.slice(1);
    }
    
    function formatStatus(status) {
        return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
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
    document.addEventListener('DOMContentLoaded', loadRemitDetails);
</script>

</body>
</html>