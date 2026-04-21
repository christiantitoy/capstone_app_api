<?php
// /admin/ui/deliveries.php
require_once '../backend/session/auth_admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deliveries Management | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/deliveries.css?v=<?= time() ?>">
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
            <a href="/admin/ui/deliveries.php" class="nav-item active">
                <i class="fas fa-truck"></i><span>Deliveries</span>
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
                <h1>Deliveries Management</h1>
                <p>Monitor and manage all deliveries</p>
            </div>
            <div class="header-right">
                <div class="date-display" id="currentDate"></div>
            </div>
        </header>

        <!-- STATS CARDS -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalDeliveries">0</h3>
                    <p>Total Deliveries</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e67e2220;color:#e67e22">
                    <i class="fas fa-motorcycle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="activeDeliveries">0</h3>
                    <p>Active Deliveries</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#27ae6020;color:#27ae60">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="completedDeliveries">0</h3>
                    <p>Completed</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e74c3c20;color:#e74c3c">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="cancelledDeliveries">0</h3>
                    <p>Cancelled</p>
                </div>
            </div>
        </section>

        <!-- DELIVERIES LIST SECTION -->
        <div class="full-width-section deliveries-list">
            <div class="section-header">
                <h2>All Deliveries</h2>
                <div class="filter-container">
                    <select id="statusFilter" class="filter-select">
                        <option value="all">All Deliveries</option>
                        <option value="active">Active Deliveries</option>
                        <option value="assigned">Assigned</option>
                        <option value="picked_up">Picked Up</option>
                        <option value="delivering">Delivering</option>
                        <option value="completed">Completed</option>
                        <option value="abandoned">Abandoned</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <div class="search-container">
                        <input type="text" class="search-field" id="searchDelivery" placeholder="Search delivery...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="delivery_holder">
                    <div class="delivery-table-header">
                        <div class="col-delivery-id">Delivery ID</div>
                        <div class="col-order-id">Order #</div>
                        <div class="col-rider">Rider</div>
                        <div class="col-buyer">Buyer</div>
                        <div class="col-status">Status</div>
                        <div class="col-timeline">Timeline</div>
                    </div>
                    
                    <div class="table-body" id="deliveriesTableBody">
                        <div class="loading">Loading deliveries...</div>
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
    let allDeliveries = [];
    
    // Display current date
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString(undefined, options);
    
    // Load deliveries from backend
    async function loadDeliveries() {
        try {
            const response = await fetch('/admin/backend/deliveries/getAllDeliveries.php');
            const result = await response.json();
            
            if (result.success) {
                allDeliveries = result.data;
                
                // Calculate stats
                const total = allDeliveries.length;
                const completed = allDeliveries.filter(d => d.delivery_status === 'completed').length;
                const cancelled = allDeliveries.filter(d => d.delivery_status === 'cancelled').length;
                const abandoned = allDeliveries.filter(d => d.delivery_status === 'abandoned').length;
                // Active = not completed, not cancelled, not abandoned
                const active = total - completed - cancelled - abandoned;
                
                // Update stats
                document.getElementById('totalDeliveries').textContent = total;
                document.getElementById('activeDeliveries').textContent = active;
                document.getElementById('completedDeliveries').textContent = completed;
                document.getElementById('cancelledDeliveries').textContent = cancelled + abandoned;
                
                // Display deliveries
                displayDeliveries(allDeliveries);
            } else {
                document.getElementById('deliveriesTableBody').innerHTML = '<div class="error">Failed to load deliveries</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('deliveriesTableBody').innerHTML = '<div class="error">Error loading deliveries</div>';
        }
    }
    
    // Display deliveries in table
    function displayDeliveries(deliveries) {
        const tbody = document.getElementById('deliveriesTableBody');
        
        if (deliveries.length === 0) {
            tbody.innerHTML = '<div class="no-data">No deliveries found</div>';
            return;
        }
        
        let html = '';
        deliveries.forEach(delivery => {
            // Set status badge class
            let statusClass = getStatusClass(delivery.delivery_status);
            let statusText = formatStatusText(delivery.delivery_status);
            
            // Format timeline
            let timelineHtml = formatTimeline(delivery);
            
            html += `
                <div class="delivery-row" onclick="viewDelivery(${delivery.delivery_id})">
                    <div class="col-delivery-id">#${delivery.delivery_id}</div>
                    <div class="col-order-id">
                        <span class="order-link">#${delivery.order_id}</span>
                    </div>
                    <div class="col-rider">
                        <div class="rider-info">
                            <a href="rider_details.php?id=${delivery.rider_id}" class="rider-link" onclick="event.stopPropagation()">
                                <strong>${escapeHtml(delivery.rider_name || 'N/A')}</strong>
                            </a>
                            <small>ID: #${delivery.rider_id || 'N/A'}</small>
                        </div>
                    </div>
                    <div class="col-buyer">
                        <div class="buyer-info">
                            <strong>${escapeHtml(delivery.buyer_name || 'N/A')}</strong>
                            <small>${escapeHtml(delivery.buyer_email || 'No email')}</small>
                        </div>
                    </div>
                    <div class="col-status">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                    <div class="col-timeline">
                        ${timelineHtml}
                    </div>
                </div>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    // Format timeline for display
    function formatTimeline(delivery) {
        let timeline = [];
        
        if (delivery.assigned_at) {
            timeline.push(`<span class="timeline-item"><i class="fas fa-user-plus"></i> ${formatTime(delivery.assigned_at)}</span>`);
        }
        if (delivery.picked_up_at) {
            timeline.push(`<span class="timeline-item"><i class="fas fa-box"></i> ${formatTime(delivery.picked_up_at)}</span>`);
        }
        if (delivery.completed_at) {
            timeline.push(`<span class="timeline-item completed"><i class="fas fa-check-circle"></i> ${formatTime(delivery.completed_at)}</span>`);
        }
        if (delivery.cancelled_at) {
            timeline.push(`<span class="timeline-item cancelled"><i class="fas fa-times-circle"></i> ${formatTime(delivery.cancelled_at)}</span>`);
        }
        if (delivery.abandoned_at) {
            timeline.push(`<span class="timeline-item abandoned"><i class="fas fa-exclamation-circle"></i> ${formatTime(delivery.abandoned_at)}</span>`);
        }
        
        if (timeline.length === 0) {
            return '<span class="timeline-item">—</span>';
        }
        
        return timeline.slice(0, 2).join('');
    }
    
    function formatTime(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }
    
    // Get status class for styling
    function getStatusClass(status) {
        switch(status) {
            case 'assigned': return 'status-assigned';
            case 'picked_up': return 'status-picked-up';
            case 'delivering': return 'status-delivering';
            case 'completed': return 'status-completed';
            case 'abandoned': return 'status-abandoned';
            case 'cancelled': return 'status-cancelled';
            default: return 'status-assigned';
        }
    }
    
    // Format status text for display
    function formatStatusText(status) {
        const formats = {
            'assigned': 'Assigned',
            'picked_up': 'Picked Up',
            'delivering': 'Delivering',
            'completed': 'Completed',
            'abandoned': 'Abandoned',
            'cancelled': 'Cancelled'
        };
        return formats[status] || status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Filter and search functionality
    function filterDeliveries() {
        const statusFilter = document.getElementById('statusFilter').value;
        const searchTerm = document.getElementById('searchDelivery').value.toLowerCase();
        
        let filteredDeliveries = [...allDeliveries];
        
        // Filter by status
        if (statusFilter !== 'all') {
            if (statusFilter === 'active') {
                // Active = not completed, not cancelled, not abandoned
                filteredDeliveries = filteredDeliveries.filter(delivery => 
                    delivery.delivery_status !== 'completed' && 
                    delivery.delivery_status !== 'cancelled' && 
                    delivery.delivery_status !== 'abandoned'
                );
            } else {
                filteredDeliveries = filteredDeliveries.filter(delivery => delivery.delivery_status === statusFilter);
            }
        }
        
        // Filter by search term
        if (searchTerm) {
            filteredDeliveries = filteredDeliveries.filter(delivery => 
                delivery.delivery_id.toString().includes(searchTerm) ||
                delivery.order_id.toString().includes(searchTerm) ||
                delivery.rider_id.toString().includes(searchTerm) ||
                (delivery.rider_name && delivery.rider_name.toLowerCase().includes(searchTerm)) ||
                (delivery.buyer_name && delivery.buyer_name.toLowerCase().includes(searchTerm))
            );
        }
        
        displayDeliveries(filteredDeliveries);
    }

    function viewDelivery(id) {
        window.location.href = `delivery_details.php?id=${id}`;
    }
    
    // Event listeners for filters
    document.getElementById('statusFilter').addEventListener('change', filterDeliveries);
    document.getElementById('searchDelivery').addEventListener('input', filterDeliveries);
    
    // Load deliveries when page loads
    loadDeliveries();
</script>

</body>
</html>