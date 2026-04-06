<?php
// /admin/ui/orders.php
require_once '../backend/session/auth_admin.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/orders.css?v=<?= time() ?>">
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
            <a href="/admin/ui/orders.php" class="nav-item active">
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
                <h1>Orders Management</h1>
                <p>Monitor and manage all orders</p>
            </div>
            <div class="header-right">
                <div class="date-display" id="currentDate"></div>
            </div>
        </header>

        <!-- STATS CARDS -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalOrders">0</h3>
                    <p>Total Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e67e2220;color:#e67e22">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="pendingOrders">0</h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#00ff00;color:#00ff00">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-info">
                    <h3 id="shippedOrders">0</h3>
                    <p>Shipped</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#27ae6020;color:#27ae60">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="deliveredOrders">0</h3>
                    <p>Delivered</p>
                </div>
            </div>
        </section>

        <!-- ORDERS LIST SECTION -->
        <div class="full-width-section orders-list">
            <div class="section-header">
                <h2>All Orders</h2>
                <div class="filter-container">
                    <select id="statusFilter" class="filter-select">
                        <option value="all">All Orders</option>
                        <option value="pending">Pending</option>
                        <option value="packed">Packed</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <div class="search-container">
                        <input type="text" class="search-field" id="searchOrder" placeholder="Search order...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="order_holder">
                    <div class="order-table-header">
                        <div class="col-id">Order ID</div>
                        <div class="col-customer">Customer</div>
                        <div class="col-product-ids">Product IDs</div>
                        <div class="col-amount">Amount</div>
                        <div class="col-status">Status</div>
                        <div class="col-date">Date</div>
                    </div>
                    
                    <div class="table-body" id="ordersTableBody">
                        <div class="loading">Loading orders...</div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <p>© 2024 Admin Dashboard. All rights reserved.</p>
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
    let allOrders = [];
    
    // Display current date
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString(undefined, options);
    
    // Load orders from backend
    async function loadOrders() {
        try {
            const response = await fetch('/admin/backend/orders/getAllOrders.php');
            const result = await response.json();
            
            if (result.success) {
                allOrders = result.data;
                
                // Update stats
                document.getElementById('totalOrders').textContent = result.status_counts.total;
                document.getElementById('pendingOrders').textContent = result.status_counts.pending;
                document.getElementById('shippedOrders').textContent = result.status_counts.shipped;
                document.getElementById('deliveredOrders').textContent = result.status_counts.delivered;
                
                // Display orders
                displayOrders(allOrders);
            } else {
                document.getElementById('ordersTableBody').innerHTML = '<div class="error">Failed to load orders</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('ordersTableBody').innerHTML = '<div class="error">Error loading orders</div>';
        }
    }
    
    // Display orders in table
    function displayOrders(orders) {
        const tbody = document.getElementById('ordersTableBody');
        
        if (orders.length === 0) {
            tbody.innerHTML = '<div class="no-data">No orders found</div>';
            return;
        }
        
        let html = '';
        orders.forEach(order => {
            // Set status badge class
            let statusClass = getStatusClass(order.status);
            let statusText = order.status.charAt(0).toUpperCase() + order.status.slice(1);
            
            // Format date
            const orderDate = new Date(order.created_at);
            const formattedDate = orderDate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
            
            html += `
                <div class="order-row" onclick="viewOrder(${order.order_id})">
                    <div class="col-id">#${order.order_id}</div>
                    <div class="col-customer">
                        <div class="customer-info">
                            <strong>${escapeHtml(order.customer_name || 'N/A')}</strong>
                            <small>${escapeHtml(order.customer_email || 'No email')}</small>
                        </div>
                    </div>
                    <div class="col-product-ids">
                        <div class="product-ids">
                            ${order.product_ids ? order.product_ids.split(',').map(id => 
                                `<span class="product-id-badge">${id.trim()}</span>`
                            ).join('') : 'No products'}
                        </div>
                        <small>${order.total_items} item(s)</small>
                    </div>
                    <div class="col-amount">₱${parseFloat(order.total_amount).toFixed(2)}</div>
                    <div class="col-status">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                    <div class="col-date">${formattedDate}</div>
                </div>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    // Get status class for styling
    function getStatusClass(status) {
        switch(status) {
            case 'pending': return 'status-pending';
            case 'packed': return 'status-packed';
            case 'shipped': return 'status-shipped';
            case 'delivered': return 'status-delivered';
            case 'cancelled': return 'status-cancelled';
            case 'complete': return 'status-complete';
            case 'locked': return 'status-locked';
            case 'assigned': return 'status-assigned';
            default: return 'status-pending';
        }
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // View order function (for future development)
    function viewOrder(id) {
        alert('Order details will be implemented soon. Order ID: ' + id);
    }
    
    // Filter and search functionality
    function filterOrders() {
        const statusFilter = document.getElementById('statusFilter').value;
        const searchTerm = document.getElementById('searchOrder').value.toLowerCase();
        
        let filteredOrders = [...allOrders];
        
        // Filter by status
        if (statusFilter !== 'all') {
            filteredOrders = filteredOrders.filter(order => order.status === statusFilter);
        }
        
        // Filter by search term (order ID or customer name)
        if (searchTerm) {
            filteredOrders = filteredOrders.filter(order => 
                order.order_id.toString().includes(searchTerm) ||
                (order.customer_name && order.customer_name.toLowerCase().includes(searchTerm)) ||
                (order.customer_email && order.customer_email.toLowerCase().includes(searchTerm))
            );
        }
        
        displayOrders(filteredOrders);
    }
    
    // Event listeners for filters
    document.getElementById('statusFilter').addEventListener('change', filterOrders);
    document.getElementById('searchOrder').addEventListener('input', filterOrders);
    
    // Load orders when page loads
    loadOrders();
</script>

</body>
</html>