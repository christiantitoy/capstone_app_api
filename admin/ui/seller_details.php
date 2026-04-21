<?php
// /admin/ui/seller_details.php
require_once '../backend/session/auth_admin.php';

$sellerId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$sellerId) {
    header('Location: sellers.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Details | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/seller_details.css?v=<?= time() ?>">
</head>
<body>

<div class="seller-details-container">
    <!-- Back Navigation -->
    <div class="page-header">
        <a href="sellers.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Sellers
        </a>
        <h1>Seller Details</h1>
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
        <p>Loading seller details...</p>
    </div>

    <!-- Content -->
    <div id="sellerContent" style="display: none;">
        <!-- Banner Image -->
        <div class="banner-container" id="bannerContainer">
            <div class="banner-placeholder" id="bannerPlaceholder">
                <i class="fas fa-image"></i>
            </div>
            <img id="bannerImage" src="" alt="Store Banner" style="display: none;" onclick="openBannerViewer()">
        </div>

        <!-- Seller Profile Card -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="store-logo" id="storeLogo">
                    <div class="logo-placeholder">
                        <i class="fas fa-store"></i>
                    </div>
                </div>
                <div class="profile-info">
                    <h2 id="storeName">-</h2>
                    <p id="sellerName">-</p>
                    <div class="badges-container">
                        <span class="badge" id="sellerPlan">Bronze</span>
                        <span class="badge" id="approvalStatus">pending</span>
                        <span class="badge" id="emailStatus">Unconfirmed</span>
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
                    <div class="stat-value" id="totalProducts">0</div>
                    <div class="stat-label">Total Products</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="totalOrders">0</div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="totalRevenue">₱0</div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="uniqueCustomers">0</div>
                    <div class="stat-label">Customers</div>
                </div>
            </div>
        </div>

        <!-- Store Information -->
        <div class="info-grid">
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i>
                    <h3>Store Information</h3>
                </div>
                <div class="card-body" id="storeInfo">
                    <!-- Dynamic content -->
                </div>
            </div>
            
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-user"></i>
                    <h3>Owner Information</h3>
                </div>
                <div class="card-body" id="ownerInfo">
                    <!-- Dynamic content -->
                </div>
            </div>
            
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-clock"></i>
                    <h3>Business Hours</h3>
                </div>
                <div class="card-body" id="businessHours">
                    <!-- Dynamic content -->
                </div>
            </div>
            
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Location</h3>
                </div>
                <div class="card-body" id="locationInfo">
                    <!-- Dynamic content -->
                </div>
            </div>
        </div>

        <!-- Employees Section -->
        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-users"></i> Employees</h2>
                <span class="badge" id="employeeCount">0 employees</span>
            </div>
            <div class="table-container">
                <table class="employees-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody id="employeesBody">
                        <!-- Employees will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Error State -->
    <div id="errorState" class="error-state" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <h3>Failed to load seller details</h3>
        <p id="errorMessage">An error occurred while loading the seller information.</p>
        <a href="sellers.php" class="btn btn-primary">Return to Sellers</a>
    </div>
</div>

<!-- Full Image Viewer Modal for Banner -->
<div id="bannerViewerModal" class="image-viewer-modal">
    <span class="close-viewer" onclick="closeBannerViewer()">&times;</span>
    <img id="viewerBannerImage" src="" alt="Store Banner">
</div>

<!-- Status Update Modal (Approve/Reject) -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">
                <i id="modalIcon" class="fas"></i>
                <span id="modalTitleText">Update Seller Status</span>
            </h3>
            <span class="close-modal" onclick="closeStatusModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="modalMessage"></div>
            <form id="statusUpdateForm">
                <input type="hidden" id="modalAction">
                
                <div class="form-group">
                    <label>Seller: <span id="modalSellerName"></span></label>
                </div>
                
                <div class="form-group" id="rejectionReasonGroup" style="display: none;">
                    <label for="rejectionReasonInput">Rejection Reason <span class="required">*</span></label>
                    <textarea id="rejectionReasonInput" class="form-control" rows="4" placeholder="Please provide a reason for rejecting this seller..."></textarea>
                    <small class="form-text">This reason will be recorded and the seller will be notified.</small>
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
    const sellerId = <?= $sellerId ?>;
    let bannerUrl = '';
    let currentSeller = null;
    
    // Load seller details
    async function loadSellerDetails() {
        const loadingState = document.getElementById('loadingState');
        const sellerContent = document.getElementById('sellerContent');
        const errorState = document.getElementById('errorState');
        
        try {
            const response = await fetch(`../backend/sellers/get_seller_details.php?id=${sellerId}`);
            const result = await response.json();
            
            if (result.success) {
                const data = result.data;
                currentSeller = data.seller;
                displaySellerDetails(data);
                updateActionButtons(currentSeller);
                
                loadingState.style.display = 'none';
                sellerContent.style.display = 'block';
                errorState.style.display = 'none';
            } else {
                throw new Error(result.message || 'Failed to load seller details');
            }
        } catch (error) {
            console.error('Error loading seller details:', error);
            loadingState.style.display = 'none';
            sellerContent.style.display = 'none';
            errorState.style.display = 'block';
            document.getElementById('errorMessage').textContent = error.message;
        }
    }
    
    function updateActionButtons(seller) {
        const actionContainer = document.getElementById('actionButtons');
        
        // Show appropriate buttons based on status
        if (seller.approval_status === 'pending') {
            actionContainer.innerHTML = `
                <button class="btn btn-success" onclick="openStatusModal('approve')">
                    <i class="fas fa-check-circle"></i> Approve Seller
                </button>
                <button class="btn btn-danger" onclick="openStatusModal('reject')">
                    <i class="fas fa-times-circle"></i> Reject Seller
                </button>
            `;
        } else if (seller.approval_status === 'approved') {
            actionContainer.innerHTML = `
                <button class="btn btn-danger" onclick="openStatusModal('reject')">
                    <i class="fas fa-times-circle"></i> Reject Seller
                </button>
            `;
        } else if (seller.approval_status === 'rejected') {
            actionContainer.innerHTML = `
                <button class="btn btn-success" onclick="openStatusModal('approve')">
                    <i class="fas fa-check-circle"></i> Approve Seller
                </button>
            `;
        } else {
            actionContainer.innerHTML = '';
        }
    }
    
    function openStatusModal(action) {
        const modal = document.getElementById('statusModal');
        const modalTitle = document.getElementById('modalTitleText');
        const modalIcon = document.getElementById('modalIcon');
        const modalMessage = document.getElementById('modalMessage');
        const modalAction = document.getElementById('modalAction');
        const modalSellerName = document.getElementById('modalSellerName');
        const rejectionGroup = document.getElementById('rejectionReasonGroup');
        const confirmBtn = document.getElementById('confirmStatusBtn');
        
        modalAction.value = action;
        modalSellerName.textContent = currentSeller.full_name;
        
        if (action === 'approve') {
            modalTitle.textContent = 'Approve Seller';
            modalIcon.className = 'fas fa-check-circle';
            modalIcon.style.color = '#27ae60';
            modalMessage.innerHTML = `
                <div class="message-box message-success">
                    <i class="fas fa-info-circle"></i>
                    <p>Are you sure you want to approve this seller? They will be able to start selling on the platform immediately.</p>
                </div>
            `;
            rejectionGroup.style.display = 'none';
            confirmBtn.className = 'btn btn-success';
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Approve Seller';
        } else {
            modalTitle.textContent = 'Reject Seller';
            modalIcon.className = 'fas fa-times-circle';
            modalIcon.style.color = '#e74c3c';
            modalMessage.innerHTML = `
                <div class="message-box message-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Are you sure you want to reject this seller? This action requires a rejection reason.</p>
                </div>
            `;
            rejectionGroup.style.display = 'block';
            document.getElementById('rejectionReasonInput').value = '';
            confirmBtn.className = 'btn btn-danger';
            confirmBtn.innerHTML = '<i class="fas fa-times"></i> Reject Seller';
        }
        
        modal.style.display = 'flex';
        document.getElementById('rejectionReasonInput').focus();
    }
    
    function closeStatusModal() {
        document.getElementById('statusModal').style.display = 'none';
        document.getElementById('rejectionReasonInput').value = '';
    }
    
    async function confirmStatusUpdate() {
        const action = document.getElementById('modalAction').value;
        const status = action === 'approve' ? 'approved' : 'rejected';
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
            const response = await fetch('../backend/sellers/update_seller_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    seller_id: sellerId,
                    status: status,
                    reason: reason
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                closeStatusModal();
                showNotification('success', `Seller ${status} successfully!`);
                
                // Reload the page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('error', 'Failed to update seller status: ' + result.message);
            }
        } catch (error) {
            console.error('Error updating seller:', error);
            showNotification('error', 'An error occurred while updating seller status. Please try again.');
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
    
    function displaySellerDetails(data) {
        const seller = data.seller;
        const employees = data.employees;
        const productStats = data.product_stats;
        const orderStats = data.order_stats;
        
        // Update banner
        bannerUrl = seller.banner_url;
        if (bannerUrl) {
            document.getElementById('bannerPlaceholder').style.display = 'none';
            const bannerImg = document.getElementById('bannerImage');
            bannerImg.src = bannerUrl;
            bannerImg.style.display = 'block';
            bannerImg.style.cursor = 'pointer';
        }
        
        // Update profile
        document.getElementById('storeName').textContent = seller.store_name || 'No store setup';
        document.getElementById('sellerName').textContent = seller.full_name;
        
        // Update badges with colors
        updateBadges(seller);
        
        // Show rejection reason if rejected
        if (seller.approval_status === 'rejected' && seller.rejection_reason) {
            document.getElementById('rejectionReasonContainer').style.display = 'block';
            document.getElementById('rejectionReason').textContent = seller.rejection_reason;
        }
        
        // Update store logo
        const logoContainer = document.getElementById('storeLogo');
        if (seller.logo_url) {
            logoContainer.innerHTML = `<img src="${seller.logo_url}" alt="${seller.store_name}" class="logo-img" style="width:100%;height:100%;object-fit:cover;">`;
        } else {
            logoContainer.innerHTML = `<div class="logo-placeholder"><i class="fas fa-store"></i></div>`;
        }
        
        // Update quick stats
        document.getElementById('totalProducts').textContent = productStats.total_products;
        document.getElementById('totalOrders').textContent = orderStats.total_orders;
        document.getElementById('totalRevenue').textContent = `₱${formatNumber(orderStats.total_revenue)}`;
        document.getElementById('uniqueCustomers').textContent = orderStats.unique_customers;
        
        // Update store information
        document.getElementById('storeInfo').innerHTML = `
            <p><strong>Store Name:</strong> ${escapeHtml(seller.store_name || 'Not set')}</p>
            <p><strong>Category:</strong> ${escapeHtml(seller.category || 'Not set')}</p>
            <p><strong>Contact:</strong> ${escapeHtml(seller.contact_number || 'Not set')}</p>
            <p><strong>Description:</strong> ${escapeHtml(seller.description || 'No description')}</p>
            <p><strong>Joined:</strong> ${new Date(seller.created_at).toLocaleDateString()}</p>
        `;
        
        // Update owner information
        document.getElementById('ownerInfo').innerHTML = `
            <p><strong>Full Name:</strong> ${escapeHtml(seller.owner_full_name || seller.full_name)}</p>
            <p><strong>Email:</strong> ${escapeHtml(seller.email)}</p>
            <p><strong>ID Type:</strong> ${escapeHtml(seller.id_type || 'Not provided')}</p>
            ${seller.valid_id_files && seller.valid_id_files.length > 0 ? 
                `<div class="file-links">${seller.valid_id_files.map(file => 
                    `<a href="${file}" target="_blank" class="file-link">View ID</a>`
                ).join('')}</div>` : ''}
        `;
        
        // Update business hours
        document.getElementById('businessHours').innerHTML = `
            <p><strong>Open:</strong> ${seller.open_time || 'Not set'}</p>
            <p><strong>Close:</strong> ${seller.close_time || 'Not set'}</p>
        `;
        
        // Update location
        document.getElementById('locationInfo').innerHTML = `
            <p><strong>Plus Code:</strong> ${escapeHtml(seller.plus_code || 'Not set')}</p>
            ${seller.latitude && seller.longitude ? 
                `<p><strong>Coordinates:</strong> ${seller.latitude}, ${seller.longitude}</p>` : ''}
        `;
        
        // Display employees
        displayEmployees(employees);
    }
    
    function updateBadges(seller) {
        // Seller Plan Badge
        const planBadge = document.getElementById('sellerPlan');
        planBadge.textContent = seller.seller_plan;
        planBadge.className = `badge badge-plan badge-${seller.seller_plan.toLowerCase()}`;
        
        // Approval Status Badge
        const statusBadge = document.getElementById('approvalStatus');
        statusBadge.textContent = seller.approval_status;
        statusBadge.className = `badge badge-status badge-${seller.approval_status}`;
        
        // Email Status Badge
        const emailBadge = document.getElementById('emailStatus');
        emailBadge.textContent = seller.is_confirmed ? 'Confirmed' : 'Unconfirmed';
        emailBadge.className = `badge badge-email ${seller.is_confirmed ? 'badge-confirmed' : 'badge-unconfirmed'}`;
    }
    
    function displayEmployees(employees) {
        const employeeCount = document.getElementById('employeeCount');
        const tbody = document.getElementById('employeesBody');
        
        employeeCount.textContent = `${employees.length} employee${employees.length !== 1 ? 's' : ''}`;
        
        if (employees.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No employees found</td></tr>';
            return;
        }
        
        tbody.innerHTML = employees.map(emp => {
            const statusClass = emp.status === 'active' ? 'status-active' : 
                               emp.status === 'on_hold' ? 'status-onhold' : 'status-inactive';
            
            return `
                <tr>
                    <td><strong>#${emp.id}</strong></td>
                    <td>${escapeHtml(emp.full_name)}</td>
                    <td>${escapeHtml(emp.email)}</td>
                    <td><span class="role-badge role-${emp.role}">${formatRole(emp.role)}</span></td>
                    <td><span class="status-badge ${statusClass}">${emp.status}</span></td>
                    <td>${emp.last_login ? new Date(emp.last_login).toLocaleString() : 'Never'}</td>
                    <td>${new Date(emp.created_at).toLocaleDateString()}</td>
                </tr>
            `;
        }).join('');
    }
    
    function openBannerViewer() {
        if (bannerUrl) {
            const modal = document.getElementById('bannerViewerModal');
            const viewerImg = document.getElementById('viewerBannerImage');
            viewerImg.src = bannerUrl;
            modal.style.display = 'flex';
        }
    }
    
    function closeBannerViewer() {
        document.getElementById('bannerViewerModal').style.display = 'none';
    }
    
    function formatRole(role) {
        const roleMap = {
            'order_manager': 'Order Manager',
            'product_manager': 'Product Manager'
        };
        return roleMap[role] || role;
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
        const statusModal = document.getElementById('statusModal');
        const bannerModal = document.getElementById('bannerViewerModal');
        
        if (event.target === statusModal) {
            closeStatusModal();
        }
        if (event.target === bannerModal) {
            closeBannerViewer();
        }
    }

    // Update keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const statusModal = document.getElementById('statusModal');
            const bannerModal = document.getElementById('bannerViewerModal');
            
            if (statusModal.style.display === 'flex') {
                closeStatusModal();
            }
            if (bannerModal.style.display === 'flex') {
                closeBannerViewer();
            }
        }
    });
    
    // Load details on page load
    document.addEventListener('DOMContentLoaded', loadSellerDetails);
</script>

</body>
</html>