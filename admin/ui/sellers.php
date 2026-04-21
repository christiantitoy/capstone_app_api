<?php
// /admin/ui/sellers.php
require_once '../backend/session/auth_admin.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sellers Management | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/sellers.css?v=<?= time() ?>">
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
            <a href="/admin/ui/sellers.php" class="nav-item active">
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
                <h1>Sellers Management</h1>
                <p>Manage all sellers and their stores</p>
            </div>
            <div class="header-right">
                <div class="date-display" id="currentDate"></div>
            </div>
        </header>

        <!-- STATS CARDS -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db">
                    <i class="fas fa-store"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalSellers">0</h3>
                    <p>Total Sellers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#27ae6020;color:#27ae60">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="approvedSellers">0</h3>
                    <p>Approved Sellers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#f39c1220;color:#f39c12">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="pendingSellers">0</h3>
                    <p>Pending Approval</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e74c3c20;color:#e74c3c">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="rejectedSellers">0</h3>
                    <p>Rejected Sellers</p>
                </div>
            </div>
        </section>

        <!-- SELLERS LIST SECTION -->
        <div class="full-width-section sellers-list">
            <div class="section-header">
                <h2>Sellers List</h2>
                <div class="filter-controls">
                    <select id="statusFilter" class="filter-select">
                        <option value="all">All Status</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <div class="search-container">
                        <input type="text" class="search-field" id="searchSeller" placeholder="Search seller...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="sellers-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Seller Info</th>
                            <th>Store</th>
                            <th>Plan</th>
                            <th>Approval Status</th>
                            <th>Email Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sellersTableBody">
                        <tr>
                            <td colspan="7" style="text-align: center;">Loading sellers...</td>
                        </tr>
                    </tbody>
                </table>
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

<!-- Status Update Modal -->
<div id="statusModal" class="modal status-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Update Seller Status</h3>
            <button class="modal-close" onclick="closeStatusModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="modalMessage"></div>
            <form id="statusUpdateForm">
                <input type="hidden" id="modalSellerId">
                <input type="hidden" id="modalAction">
                
                <div class="form-group">
                    <label>Seller: <span id="modalSellerName"></span></label>
                </div>
                
                <div class="form-group" id="rejectionReasonGroup" style="display: none;">
                    <label for="rejectionReason">Rejection Reason *</label>
                    <textarea id="rejectionReason" class="form-control" rows="3" placeholder="Please provide a reason for rejection..."></textarea>
                    <small class="form-text text-muted">This reason will be visible to the seller.</small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeStatusModal()">Cancel</button>
            <button class="btn btn-primary" id="confirmStatusBtn" onclick="confirmStatusUpdate()">Confirm</button>
        </div>
    </div>
</div>

<script src="/admin/js/logout.js"></script>

<script>
    // Store original sellers data for filtering
    let allSellers = [];
    let sellerStatistics = {
        total: 0,
        approved: 0,
        pending: 0,
        rejected: 0
    };
    
    // Display current date
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString(undefined, options);

    // Function to navigate to seller details
    function viewSeller(sellerId) {
        window.location.href = `seller_details.php?id=${sellerId}`;
    }

    // Modal functions
    function openStatusModal(sellerId, sellerName, currentStatus, action) {
        const modal = document.getElementById('statusModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalSellerId = document.getElementById('modalSellerId');
        const modalSellerName = document.getElementById('modalSellerName');
        const modalAction = document.getElementById('modalAction');
        const rejectionGroup = document.getElementById('rejectionReasonGroup');
        const modalMessage = document.getElementById('modalMessage');
        
        modalSellerId.value = sellerId;
        modalSellerName.textContent = sellerName;
        modalAction.value = action;
        
        if (action === 'approve') {
            modalTitle.textContent = 'Approve Seller';
            modalMessage.innerHTML = '<p class="text-success"><i class="fas fa-check-circle"></i> Are you sure you want to approve this seller?</p>';
            rejectionGroup.style.display = 'none';
        } else if (action === 'reject') {
            modalTitle.textContent = 'Reject Seller';
            modalMessage.innerHTML = '<p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Are you sure you want to reject this seller?</p>';
            rejectionGroup.style.display = 'block';
            document.getElementById('rejectionReason').value = '';
        }
        
        modal.style.display = 'flex';
    }
    
    function closeStatusModal() {
        document.getElementById('statusModal').style.display = 'none';
        document.getElementById('rejectionReason').value = '';
    }
    
    async function confirmStatusUpdate() {
        const sellerId = document.getElementById('modalSellerId').value;
        const action = document.getElementById('modalAction').value;
        const status = action === 'approve' ? 'approved' : 'rejected';
        const reason = action === 'reject' ? document.getElementById('rejectionReason').value : null;
        
        if (action === 'reject' && !reason) {
            alert('Please provide a rejection reason');
            return;
        }
        
        const confirmBtn = document.getElementById('confirmStatusBtn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        
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
                // Show success message
                showNotification('success', result.message);
                // Reload sellers list
                const searchTerm = document.getElementById('searchSeller').value.toLowerCase().trim();
                const statusFilter = document.getElementById('statusFilter').value;
                loadSellers(searchTerm, statusFilter);
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error updating seller status:', error);
            alert('An error occurred while updating seller status');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Confirm';
        }
    }
    
    // Notification function
    function showNotification(type, message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Function to fetch and display sellers
    async function loadSellers(searchTerm = '', statusFilter = 'all') {
        const tableBody = document.getElementById('sellersTableBody');
        
        try {
            const response = await fetch('../backend/sellers/get_sellers.php');
            const result = await response.json();
            
            if (result.success) {
                // Store all sellers
                allSellers = result.data || [];
                
                // Store statistics
                if (result.statistics) {
                    sellerStatistics = result.statistics;
                }
                
                // Filter sellers based on search term and status
                let filteredSellers = allSellers;
                
                if (statusFilter !== 'all') {
                    filteredSellers = filteredSellers.filter(seller => 
                        seller.approval_status === statusFilter
                    );
                }
                
                if (searchTerm) {
                    filteredSellers = filteredSellers.filter(seller => 
                        seller.full_name.toLowerCase().includes(searchTerm) || 
                        seller.email.toLowerCase().includes(searchTerm) ||
                        (seller.store_name && seller.store_name.toLowerCase().includes(searchTerm))
                    );
                }
                
                // Update statistics cards
                document.getElementById('totalSellers').textContent = sellerStatistics.total;
                document.getElementById('approvedSellers').textContent = sellerStatistics.approved;
                document.getElementById('pendingSellers').textContent = sellerStatistics.pending;
                document.getElementById('rejectedSellers').textContent = sellerStatistics.rejected;
                
                if (filteredSellers.length > 0) {
                    // Display filtered sellers in table with clickable rows
                    tableBody.innerHTML = filteredSellers.map(seller => {
                        // Determine email confirmation status
                        const emailStatus = seller.is_confirmed ? 
                            '<span class="status-badge status-confirmed"><i class="fas fa-check-circle"></i> Confirmed</span>' : 
                            '<span class="status-badge status-unconfirmed"><i class="fas fa-clock"></i> Unconfirmed</span>';
                        
                        // Action buttons based on current status
                        let actionButtons = '';
                        if (seller.approval_status === 'pending') {
                            actionButtons = `
                                <button class="btn-action btn-approve" onclick="event.stopPropagation(); openStatusModal(${seller.id}, '${escapeHtml(seller.full_name)}', '${seller.approval_status}', 'approve')" title="Approve Seller">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn-action btn-reject" onclick="event.stopPropagation(); openStatusModal(${seller.id}, '${escapeHtml(seller.full_name)}', '${seller.approval_status}', 'reject')" title="Reject Seller">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                        } else if (seller.approval_status === 'approved') {
                            actionButtons = `
                                <button class="btn-action btn-reject" onclick="event.stopPropagation(); openStatusModal(${seller.id}, '${escapeHtml(seller.full_name)}', '${seller.approval_status}', 'reject')" title="Reject Seller">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                        } else if (seller.approval_status === 'rejected') {
                            actionButtons = `
                                <button class="btn-action btn-approve" onclick="event.stopPropagation(); openStatusModal(${seller.id}, '${escapeHtml(seller.full_name)}', '${seller.approval_status}', 'approve')" title="Approve Seller">
                                    <i class="fas fa-check"></i>
                                </button>
                            `;
                        }
                        
                        return `
                        <tr class="clickable-row" onclick="viewSeller(${seller.id})">
                            <td><strong>#${seller.id}</strong></td>
                            <td>
                                <div class="seller-info">
                                    <strong>${escapeHtml(seller.full_name)}</strong>
                                    <small>${escapeHtml(seller.email)}</small>
                                </div>
                            </td>
                            <td>
                                <div class="store-info">
                                    ${seller.store_name ? 
                                        `<strong>${escapeHtml(seller.store_name)}</strong><br>
                                         <small>${escapeHtml(seller.category || 'No category')}</small>` : 
                                        '<span class="text-muted">No store setup</span>'
                                    }
                                </div>
                            </td>
                            <td>
                                <span class="plan-badge plan-${seller.seller_plan.toLowerCase()}">${seller.seller_plan}</span>
                                <br>
                                <small class="billing-type">${seller.seller_billing}</small>
                            </td>
                            <td>
                                <span class="status-badge status-${seller.approval_status}">${seller.approval_status}</span>
                                ${seller.rejection_reason ? `<br><small class="rejection-reason" title="${escapeHtml(seller.rejection_reason)}">Reason: ${escapeHtml(seller.rejection_reason.substring(0, 30))}${seller.rejection_reason.length > 30 ? '...' : ''}</small>` : ''}
                            </td>
                            <td>
                                ${emailStatus}
                            </td>
                            <td onclick="event.stopPropagation()">
                                <div class="action-buttons">
                                    ${actionButtons}
                                </div>
                            </td>
                        </tr>
                        `;
                    }).join('');
                } else {
                    tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No sellers found</td></tr>';
                }
            } else {
                tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Error loading sellers</td></tr>';
            }
        } catch (error) {
            console.error('Error loading sellers:', error);
            tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Error loading sellers. Please try again.</td></tr>';
        }
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Event listeners
    document.getElementById('searchSeller').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        const statusFilter = document.getElementById('statusFilter').value;
        loadSellers(searchTerm, statusFilter);
    });
    
    document.getElementById('statusFilter').addEventListener('change', function(e) {
        const statusFilter = e.target.value;
        const searchTerm = document.getElementById('searchSeller').value.toLowerCase().trim();
        loadSellers(searchTerm, statusFilter);
    });
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('statusModal');
        if (event.target === modal) {
            closeStatusModal();
        }
    }
    
    // Load sellers when page loads
    document.addEventListener('DOMContentLoaded', () => {
        loadSellers();
    });
</script>

</body>
</html>