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
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sellersTableBody">
                        <tr>
                            <td colspan="6" style="text-align: center;">Loading sellers...</td>
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

<!-- Seller Details Modal -->
<div id="sellerModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 id="modalTitle">Seller Details</h3>
            <span class="close-modal" onclick="closeSellerModal()">&times;</span>
        </div>
        <div class="modal-body" id="sellerModalBody">
            <!-- Dynamic content will be loaded here -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeSellerModal()">Close</button>
            <button class="btn btn-primary" id="modalActionBtn">Approve Seller</button>
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
    let currentSeller = null;
    
    // Display current date
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString(undefined, options);

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
                    // Display filtered sellers in table
                    tableBody.innerHTML = filteredSellers.map(seller => `
                        <tr>
                            <td>#${seller.id}</td>
                            <td>
                                <div class="seller-info">
                                    <strong>${escapeHtml(seller.full_name)}</strong>
                                    <small>${escapeHtml(seller.email)}</small>
                                    ${seller.is_confirmed ? '<span class="badge badge-success">Confirmed</span>' : '<span class="badge badge-warning">Unconfirmed</span>'}
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
                            </td>
                            <td>
                                <span class="status-badge status-${seller.approval_status}">${seller.approval_status}</span>
                            </td>
                            <td>
                                <button class="btn-icon" onclick="viewSellerDetails(${seller.id})" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                ${seller.approval_status === 'pending' ? `
                                    <button class="btn-icon btn-success" onclick="approveSeller(${seller.id})" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn-icon btn-danger" onclick="rejectSeller(${seller.id})" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                ` : ''}
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No sellers found</td></tr>';
                }
            } else {
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Error loading sellers</td></tr>';
            }
        } catch (error) {
            console.error('Error loading sellers:', error);
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Error loading sellers. Please try again.</td></tr>';
        }
    }
    
    // Function to view seller details
    function viewSellerDetails(sellerId) {
        const seller = allSellers.find(s => s.id === sellerId);
        if (!seller) return;
        
        currentSeller = seller;
        
        const modalBody = document.getElementById('sellerModalBody');
        const modalTitle = document.getElementById('modalTitle');
        const actionBtn = document.getElementById('modalActionBtn');
        
        modalTitle.textContent = `Seller Details - ${seller.full_name}`;
        
        // Build modal content
        let html = `
            <div class="seller-details">
                <div class="detail-section">
                    <h4>Seller Information</h4>
                    <p><strong>ID:</strong> ${seller.id}</p>
                    <p><strong>Full Name:</strong> ${escapeHtml(seller.full_name)}</p>
                    <p><strong>Email:</strong> ${escapeHtml(seller.email)}</p>
                    <p><strong>Plan:</strong> ${seller.seller_plan} (${seller.seller_billing})</p>
                    <p><strong>Status:</strong> ${seller.approval_status}</p>
                    <p><strong>Confirmed:</strong> ${seller.is_confirmed ? 'Yes' : 'No'}</p>
                    <p><strong>Joined:</strong> ${new Date(seller.created_at).toLocaleDateString()}</p>
                </div>
        `;
        
        if (seller.store_name) {
            html += `
                <div class="detail-section">
                    <h4>Store Information</h4>
                    <p><strong>Store Name:</strong> ${escapeHtml(seller.store_name)}</p>
                    <p><strong>Category:</strong> ${escapeHtml(seller.category)}</p>
                    <p><strong>Description:</strong> ${escapeHtml(seller.description)}</p>
                    <p><strong>Contact:</strong> ${escapeHtml(seller.contact_number)}</p>
                    <p><strong>Owner:</strong> ${escapeHtml(seller.owner_full_name)}</p>
                    <p><strong>ID Type:</strong> ${escapeHtml(seller.id_type)}</p>
                </div>
            `;
            
            if (seller.valid_id_files && seller.valid_id_files.length > 0) {
                html += `
                    <div class="detail-section">
                        <h4>Valid ID Files</h4>
                        <div class="file-list">
                            ${seller.valid_id_files.map(file => `<a href="${file}" target="_blank" class="file-link">View ID</a>`).join('')}
                        </div>
                    </div>
                `;
            }
        }
        
        html += `</div>`;
        
        modalBody.innerHTML = html;
        
        // Update action button based on status
        if (seller.approval_status === 'pending') {
            actionBtn.textContent = 'Approve Seller';
            actionBtn.className = 'btn btn-success';
            actionBtn.onclick = () => approveSeller(seller.id);
        } else {
            actionBtn.style.display = 'none';
        }
        
        document.getElementById('sellerModal').style.display = 'block';
    }
    
    // Function to approve seller
    function approveSeller(sellerId) {
        if (confirm('Are you sure you want to approve this seller?')) {
            updateSellerStatus(sellerId, 'approved');
        }
    }
    
    // Function to reject seller
    function rejectSeller(sellerId) {
        const reason = prompt('Please provide a reason for rejection:');
        if (reason !== null) {
            updateSellerStatus(sellerId, 'rejected', reason);
        }
    }
    
    // Function to update seller status
    async function updateSellerStatus(sellerId, status, reason = '') {
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
                alert(`Seller ${status} successfully!`);
                closeSellerModal();
                loadSellers(
                    document.getElementById('searchSeller').value.toLowerCase().trim(),
                    document.getElementById('statusFilter').value
                );
            } else {
                alert('Failed to update seller status: ' + result.message);
            }
        } catch (error) {
            console.error('Error updating seller:', error);
            alert('Error updating seller status. Please try again.');
        }
    }
    
    // Function to close modal
    function closeSellerModal() {
        document.getElementById('sellerModal').style.display = 'none';
        currentSeller = null;
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
        const modal = document.getElementById('sellerModal');
        if (event.target === modal) {
            closeSellerModal();
        }
    }
    
    // Load sellers when page loads
    document.addEventListener('DOMContentLoaded', () => {
        loadSellers();
    });
</script>

</body>
</html>