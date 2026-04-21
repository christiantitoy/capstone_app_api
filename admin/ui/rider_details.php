<?php
// /admin/ui/rider_details.php
require_once '../backend/session/auth_admin.php';

$riderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$riderId) {
    header('Location: riders.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Details | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/rider_details.css?v=<?= time() ?>">
</head>
<body>

<div class="rider-details-container">
    <!-- Back Navigation -->
    <div class="page-header">
        <a href="riders.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Riders
        </a>
        <h1>Rider Details</h1>
        <div class="header-actions">
            <div id="actionButtons"></div>
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p>Loading rider details...</p>
    </div>

    <!-- Content -->
    <div id="riderContent" style="display: none;">
        <!-- Rider Profile Card -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="rider-avatar" id="riderAvatar">
                    <div class="avatar-placeholder">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                </div>
                <div class="profile-info">
                    <h2 id="riderName">-</h2>
                    <p id="riderEmail">-</p>
                    <div class="badges-container">
                        <span class="badge" id="verificationStatus">pending</span>
                        <span class="badge" id="riderStatus">offline</span>
                    </div>
                    <!-- Rejection Reason (if rejected) -->
                    <div id="rejectionReasonContainer" style="display: none; margin-top: 15px;">
                        <p style="color: #e74c3c; font-size: 14px; margin-bottom: 5px;">
                            <i class="fas fa-exclamation-circle"></i> Rejection Reason:
                        </p>
                        <p id="rejectionReason" style="background: #fdf0f0; padding: 10px; border-radius: 8px; font-size: 14px; color: #2c3e50;"></p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-item">
                    <div class="stat-value" id="riderId">-</div>
                    <div class="stat-label">Rider ID</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="totalDeliveries">0</div>
                    <div class="stat-label">Total Deliveries</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="memberSince">-</div>
                    <div class="stat-label">Member Since</div>
                </div>
            </div>
        </div>

        <!-- Rider Information -->
        <div class="info-grid">
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-user"></i>
                    <h3>Personal Information</h3>
                </div>
                <div class="card-body" id="personalInfo">
                    <!-- Dynamic content -->
                </div>
            </div>
            
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-id-card"></i>
                    <h3>Account Information</h3>
                </div>
                <div class="card-body" id="accountInfo">
                    <!-- Dynamic content -->
                </div>
            </div>
            
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-chart-bar"></i>
                    <h3>Performance Stats</h3>
                </div>
                <div class="card-body" id="performanceStats">
                    <!-- Dynamic content -->
                </div>
            </div>
        </div>
    </div>

    <!-- Error State -->
    <div id="errorState" class="error-state" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <h3>Failed to load rider details</h3>
        <p id="errorMessage">An error occurred while loading the rider information.</p>
        <a href="riders.php" class="btn btn-primary">Return to Riders</a>
    </div>
</div>

<!-- Status Update Modal (Approve/Reject) -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">
                <i id="modalIcon" class="fas"></i>
                <span id="modalTitleText">Update Rider Status</span>
            </h3>
            <span class="close-modal" onclick="closeStatusModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="modalMessage"></div>
            <form id="statusUpdateForm">
                <input type="hidden" id="modalAction">
                
                <div class="form-group">
                    <label>Rider: <span id="modalRiderName"></span></label>
                </div>
                
                <div class="form-group" id="rejectionReasonGroup" style="display: none;">
                    <label for="rejectionReasonInput">Rejection Reason <span class="required">*</span></label>
                    <textarea id="rejectionReasonInput" class="form-control" rows="4" placeholder="Please provide a reason for rejecting this rider..."></textarea>
                    <small class="form-text">This reason will be recorded and the rider will be notified.</small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeStatusModal()">Cancel</button>
            <button class="btn" id="confirmStatusBtn" onclick="confirmStatusUpdate()">Confirm</button>
        </div>
    </div>
</div>

<!-- Notification Container -->
<div id="notificationContainer"></div>

<script>
    const riderId = <?= $riderId ?>;
    let currentRider = null;
    
    // Load rider details
    async function loadRiderDetails() {
        const loadingState = document.getElementById('loadingState');
        const riderContent = document.getElementById('riderContent');
        const errorState = document.getElementById('errorState');
        
        try {
            const response = await fetch(`../backend/riders/get_rider_details.php?id=${riderId}`);
            const result = await response.json();
            
            if (result.success) {
                currentRider = result.data;
                displayRiderDetails(currentRider);
                updateActionButtons(currentRider);
                
                loadingState.style.display = 'none';
                riderContent.style.display = 'block';
                errorState.style.display = 'none';
            } else {
                throw new Error(result.message || 'Failed to load rider details');
            }
        } catch (error) {
            console.error('Error loading rider details:', error);
            loadingState.style.display = 'none';
            riderContent.style.display = 'none';
            errorState.style.display = 'block';
            document.getElementById('errorMessage').textContent = error.message;
        }
    }
    
    function updateActionButtons(rider) {
        const actionContainer = document.getElementById('actionButtons');
        
        // Only show buttons if rider verification is pending
        if (rider.verification_status === 'pending') {
            actionContainer.innerHTML = `
                <button class="btn btn-success" onclick="openStatusModal('approve')">
                    <i class="fas fa-check-circle"></i> Approve Rider
                </button>
                <button class="btn btn-danger" onclick="openStatusModal('reject')">
                    <i class="fas fa-times-circle"></i> Reject Rider
                </button>
            `;
        } else {
            // Hide buttons for verified or rejected riders
            actionContainer.innerHTML = '';
        }
    }
    
    function openStatusModal(action) {
        const modal = document.getElementById('statusModal');
        const modalTitle = document.getElementById('modalTitleText');
        const modalIcon = document.getElementById('modalIcon');
        const modalMessage = document.getElementById('modalMessage');
        const modalAction = document.getElementById('modalAction');
        const modalRiderName = document.getElementById('modalRiderName');
        const rejectionGroup = document.getElementById('rejectionReasonGroup');
        const confirmBtn = document.getElementById('confirmStatusBtn');
        
        modalAction.value = action;
        modalRiderName.textContent = currentRider.username;
        
        if (action === 'approve') {
            modalTitle.textContent = 'Approve Rider';
            modalIcon.className = 'fas fa-check-circle';
            modalIcon.style.color = '#27ae60';
            modalMessage.innerHTML = `
                <div class="message-box message-success">
                    <i class="fas fa-info-circle"></i>
                    <p>Are you sure you want to approve this rider? They will be able to start accepting deliveries immediately.</p>
                </div>
            `;
            rejectionGroup.style.display = 'none';
            confirmBtn.className = 'btn btn-success';
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Approve Rider';
        } else {
            modalTitle.textContent = 'Reject Rider';
            modalIcon.className = 'fas fa-times-circle';
            modalIcon.style.color = '#e74c3c';
            modalMessage.innerHTML = `
                <div class="message-box message-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Are you sure you want to reject this rider? This action requires a rejection reason.</p>
                </div>
            `;
            rejectionGroup.style.display = 'block';
            document.getElementById('rejectionReasonInput').value = '';
            confirmBtn.className = 'btn btn-danger';
            confirmBtn.innerHTML = '<i class="fas fa-times"></i> Reject Rider';
        }
        
        modal.style.display = 'flex';
        if (action === 'reject') {
            document.getElementById('rejectionReasonInput').focus();
        }
    }
    
    function closeStatusModal() {
        document.getElementById('statusModal').style.display = 'none';
        document.getElementById('rejectionReasonInput').value = '';
    }
    
    async function confirmStatusUpdate() {
        const action = document.getElementById('modalAction').value;
        const status = action === 'approve' ? 'complete' : 'rejected';
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
            const response = await fetch('../backend/riders/update_rider_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    rider_id: riderId,
                    verification_status: status,
                    reason: reason
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                closeStatusModal();
                showNotification('success', `Rider ${action === 'approve' ? 'approved' : 'rejected'} successfully!`);
                
                // Reload the page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('error', 'Failed to update rider status: ' + result.message);
            }
        } catch (error) {
            console.error('Error updating rider:', error);
            showNotification('error', 'An error occurred while updating rider status. Please try again.');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
        }
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
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
    
    function displayRiderDetails(rider) {
        // Update profile
        document.getElementById('riderName').textContent = rider.username;
        document.getElementById('riderEmail').textContent = rider.email;
        document.getElementById('riderId').textContent = '#' + rider.id;
        document.getElementById('memberSince').textContent = new Date(rider.created_at).toLocaleDateString();
        
        // Update badges
        updateBadges(rider);
        
        // Show rejection reason if rejected
        if (rider.verification_status === 'rejected' && rider.rejection_reason) {
            document.getElementById('rejectionReasonContainer').style.display = 'block';
            document.getElementById('rejectionReason').textContent = rider.rejection_reason;
        }
        
        // Personal Information
        document.getElementById('personalInfo').innerHTML = `
            <p><strong>Username:</strong> ${escapeHtml(rider.username)}</p>
            <p><strong>Email:</strong> ${escapeHtml(rider.email)}</p>
            <p><strong>Joined:</strong> ${new Date(rider.created_at).toLocaleString()}</p>
        `;
        
        // Account Information
        document.getElementById('accountInfo').innerHTML = `
            <p><strong>Status:</strong> <span class="status-badge status-${rider.status}">${rider.status}</span></p>
            <p><strong>Verification:</strong> <span class="status-badge status-${rider.verification_status}">${formatVerificationStatus(rider.verification_status)}</span></p>
        `;
        
        // Performance Stats
        document.getElementById('performanceStats').innerHTML = `
            <p><strong>Total Deliveries:</strong> ${rider.total_deliveries || 0}</p>
            <p><strong>Completed Deliveries:</strong> ${rider.completed_deliveries || 0}</p>
            <p><strong>Rating:</strong> ${rider.rating ? rider.rating + ' / 5.0' : 'N/A'}</p>
        `;
    }
    
    function updateBadges(rider) {
        const verificationBadge = document.getElementById('verificationStatus');
        verificationBadge.textContent = formatVerificationStatus(rider.verification_status);
        verificationBadge.className = `badge badge-${rider.verification_status}`;
        
        const statusBadge = document.getElementById('riderStatus');
        statusBadge.textContent = rider.status;
        statusBadge.className = `badge badge-${rider.status}`;
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
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('statusModal');
        if (event.target === modal) {
            closeStatusModal();
        }
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStatusModal();
        }
    });
    
    // Load details on page load
    document.addEventListener('DOMContentLoaded', loadRiderDetails);
</script>

</body>
</html>