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

        <!-- Rejection Reason (if rejected) -->
        <div class="info-section" id="rejectionSection" style="display: none;">
            <div class="section-header">
                <h3><i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i> Rejection Information</h3>
            </div>
            <div class="rejection-card">
                <p id="rejectionReason">-</p>
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

<!-- Rejection Modal -->
<div id="rejectionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-times-circle" style="color: #e74c3c;"></i> Reject Rider</h3>
            <span class="close-modal" onclick="closeRejectionModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Please provide a reason for rejecting this rider:</p>
            <textarea id="rejectionReasonInput" rows="4" placeholder="Enter rejection reason..."></textarea>
            <p class="modal-note">This reason will be recorded and the rider will be notified.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeRejectionModal()">Cancel</button>
            <button class="btn btn-danger" onclick="confirmReject()">Confirm Rejection</button>
        </div>
    </div>
</div>

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
        
        if (rider.verification_status === 'pending') {
            actionContainer.innerHTML = `
                <button class="btn btn-success" onclick="approveRider()">
                    <i class="fas fa-check-circle"></i> Approve
                </button>
                <button class="btn btn-danger" onclick="openRejectionModal()">
                    <i class="fas fa-times-circle"></i> Reject
                </button>
            `;
        } else {
            actionContainer.innerHTML = '';
        }
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
            document.getElementById('rejectionSection').style.display = 'block';
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
        
        // Performance Stats (placeholder - update when delivery data is available)
        document.getElementById('performanceStats').innerHTML = `
            <p><strong>Total Deliveries:</strong> 0</p>
            <p><strong>Completed Deliveries:</strong> 0</p>
            <p><strong>Rating:</strong> N/A</p>
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
    
    function approveRider() {
        if (confirm('Are you sure you want to approve this rider?')) {
            updateRiderStatus('complete');
        }
    }
    
    function openRejectionModal() {
        document.getElementById('rejectionModal').style.display = 'flex';
        document.getElementById('rejectionReasonInput').value = '';
        document.getElementById('rejectionReasonInput').focus();
    }
    
    function closeRejectionModal() {
        document.getElementById('rejectionModal').style.display = 'none';
    }
    
    function confirmReject() {
        const reason = document.getElementById('rejectionReasonInput').value.trim();
        if (!reason) {
            alert('Please provide a reason for rejection.');
            return;
        }
        updateRiderStatus('rejected', reason);
    }
    
    async function updateRiderStatus(status, reason = '') {
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
                alert(`Rider ${status} successfully!`);
                closeRejectionModal();
                location.reload();
            } else {
                alert('Failed to update rider status: ' + result.message);
            }
        } catch (error) {
            console.error('Error updating rider:', error);
            alert('Error updating rider status. Please try again.');
        }
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('rejectionModal');
        if (event.target === modal) {
            closeRejectionModal();
        }
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRejectionModal();
        }
    });
    
    // Load details on page load
    document.addEventListener('DOMContentLoaded', loadRiderDetails);
</script>

</body>
</html>