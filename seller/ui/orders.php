<?php
// /seller/ui/orders.php
require_once __DIR__ . '/../backend/session/auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/orders.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/error.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/logout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional styles for clickable rows and modal */
        .clickable-row {
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .clickable-row:hover {
            background: #f0f7ff !important;
        }
        
        /* Order Details Modal */
        .order-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .order-modal.active {
            display: flex;
        }
        
        .order-modal-content {
            background: white;
            border-radius: 12px;
            max-width: 800px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .order-modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #ebedf0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
        }
        
        .order-modal-header h2 {
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
            transition: color 0.2s;
        }
        
        .close-modal:hover {
            color: var(--danger);
        }
        
        .order-modal-body {
            padding: 1.5rem;
        }
        
        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #ebedf0;
        }
        
        .order-info-item {
            background: #f8fafc;
            padding: 0.75rem;
            border-radius: 8px;
        }
        
        .order-info-label {
            font-size: 0.75rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }
        
        .order-info-value {
            font-weight: 600;
            color: var(--dark);
        }
        
        .items-list {
            margin: 1.5rem 0;
        }
        
        .items-list h4 {
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .order-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 0.75rem;
        }
        
        .order-item-image {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .order-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .order-item-image i {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #d1d9e0;
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .order-item-variant {
            font-size: 0.75rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }
        
        .order-item-price {
            font-size: 0.85rem;
            color: var(--primary);
        }
        
        .order-item-quantity {
            text-align: right;
            min-width: 80px;
        }
        
        .order-item-quantity .quantity {
            font-weight: 600;
        }
        
        .order-item-quantity .total {
            font-size: 0.85rem;
            color: var(--primary);
        }
        
        .order-summary {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
        }
        
        .summary-row.total {
            font-weight: 700;
            font-size: 1.1rem;
            border-top: 1px solid #e2e8f0;
            margin-top: 0.5rem;
            padding-top: 0.75rem;
        }
        
        .status-badge {
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending { background: #fff3e0; color: #ef6c00; }
        .status-pending_payment { background: #fff3e0; color: #ef6c00; }
        .status-packed { background: #e3f2fd; color: #1565c0; }
        .status-shipped { background: #e3f2fd; color: #1565c0; }
        .status-delivered { background: #e8f5e9; color: #2e7d32; }
        .status-cancelled { background: #ffebee; color: #c62828; }
        .status-complete { background: #e8f5e9; color: #2e7d32; }
        .status-locked { background: #f3e5f5; color: #6a1b9a; }
        
        .badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .badge-info {
            background: #e3f2fd;
            color: #1565c0;
        }
        
        .loading-state {
            text-align: center;
            padding: 3rem;
        }
        
        .no-orders {
            text-align: center;
            padding: 4rem;
        }
        
        .no-orders i {
            font-size: 4rem;
            color: #d1d9e0;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .order-item {
                flex-direction: column;
            }
            .order-item-quantity {
                text-align: left;
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Palit<span>Ora</span></h2>
            <button class="sidebar-close-btn" onclick="toggleSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="sidebar-nav">
            <a href="/seller/ui/dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="/seller/ui/products.php" class="nav-item"><i class="fas fa-box"></i><span>Products</span></a>
            <a href="/seller/ui/orders.php" class="nav-item active"><i class="fas fa-shopping-cart"></i><span>Orders</span></a>
            <a href="/seller/ui/employees.php" class="nav-item"><i class="fas fa-users"></i><span>Employees</span></a>
            <a href="/seller/ui/my_plan.php" class="nav-item"><i class="fas fa-crown"></i><span>My Plan</span></a>
            <a href="/seller/ui/sales.php" class="nav-item"><i class="fas fa-chart-line"></i><span>Sales</span></a>
            <a href="/seller/ui/payouts.php" class="nav-item"><i class="fas fa-money-bill-wave"></i><span>Payouts</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile" id="userProfile">
                <div class="avatar"><?= strtoupper(substr($seller_name, 0, 1)) ?></div>
                <div class="user-info">
                    <h4 class="seller-name"><?= htmlspecialchars($seller_name) ?></h4>
                    <p>Seller Account</p>
                </div>
            </div>
            <button class="logout-btn logout-trigger" title="Sign out">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="main-header">
            <div class="header-left">
                <button class="mobile-menu-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Orders Management</h1>
                <p>Manage customer orders and fulfillment</p>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search orders...">
                </div>
                <div class="date-display" id="dateDisplay"></div>
            </div>
        </header>

        <div class="orders-header">
            <h2 id="ordersCount">Customer Orders (0)</h2>
            <select class="status-select" id="statusFilter">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="pending_payment">Pending Payment</option>
                <option value="packed">Packed</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="complete">Complete</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="table-container">
            <table id="ordersTable">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="ordersBody">
                    <tr>
                        <td colspan="5">
                            <div class="loading-state">
                                <i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i>
                                <p>Loading orders...</p>
                            </div>
                        <\/td>
                    </tr>
                </tbody>
            </table>
        </div>

        <footer class="main-footer">
            <p>© <?= date('Y') ?> PalitOra. All rights reserved.</p>
            <div class="footer-links">
                <a href="privacy.html">Privacy Policy</a> •
                <a href="terms.html">Terms of Service</a>
            </div>
        </footer>
    </main>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="order-modal">
    <div class="order-modal-content">
        <div class="order-modal-header">
            <h2>Order Details</h2>
            <button class="close-modal" onclick="closeOrderModal()">&times;</button>
        </div>
        <div class="order-modal-body" id="orderModalBody">
            <div class="loading-state">Loading...</div>
        </div>
    </div>
</div>

<!-- ── LOGOUT CONFIRMATION MODAL ── -->
<div class="logout-modal-overlay" id="logoutModal">
    <div class="logout-modal-content">
        <div class="logout-modal-header">
            <h3>Sign Out</h3>
            <button class="logout-modal-close" id="closeModal">×</button>
        </div>
        <div class="logout-modal-body">
            <p>Are you sure you want to sign out?</p>
            <p class="logout-text-secondary">You will need to log in again to access your dashboard.</p>
        </div>
        <div class="logout-modal-footer">
            <button class="logout-btn2 logout-btn2-secondary" id="cancelLogout">Cancel</button>
            <a href="/seller/backend/auth/logout.php" class="logout-btn2 logout-btn2-danger">Sign Out</a>
        </div>
    </div>
</div>

<script src="/seller/js/logout.js"></script>

<script>
// Mobile sidebar toggle functionality
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

// Close sidebar when tapping outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    if (!sidebar.contains(event.target) && !mobileMenuBtn.contains(event.target) && sidebar.classList.contains('active')) {
        sidebar.classList.remove('active');
    }
});

// Close sidebar with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('active');
    }
});

// Store orders data globally
let ordersData = [];

// Set current date
document.getElementById('dateDisplay').textContent = new Date().toLocaleDateString('en-US', { 
    year: 'numeric', month: 'long', day: 'numeric' 
});

// Load orders when page loads
loadOrders();

// Search functionality
let searchTimeout;
document.getElementById('searchInput').addEventListener('keyup', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadOrders();
    }, 500);
});

// Status filter
document.getElementById('statusFilter').addEventListener('change', function() {
    loadOrders();
});

function loadOrders() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    
    let url = `/seller/backend/orders/get_orders.php?`;
    if (search) url += `search=${encodeURIComponent(search)}&`;
    if (status) url += `status=${encodeURIComponent(status)}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.orders.length > 0) {
                ordersData = data.orders;
                displayOrders(ordersData);
                document.getElementById('ordersCount').innerHTML = `Customer Orders (${data.orders.length})`;
            } else {
                showNoOrders();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError();
        });
}

function displayOrders(orders) {
    let html = '';
    
    orders.forEach(order => {
        let statusClass = getStatusClass(order.status);
        let statusText = formatStatus(order.status);
        
        html += `
            <tr class="clickable-row" onclick="viewOrderDetails(${order.id})">
                <td><strong>#${escapeHtml(String(order.order_number))}</strong></td>
                <td>${escapeHtml(order.customer_name)}</td>
                <td><span class="badge badge-info">${order.item_count} item(s)</span></td>
                <td><strong>${order.subtotal_formatted}</strong></td>
                <td><span class="status-badge ${statusClass}">${statusText}</span></td>
             </tr>
        `;
    });
    
    document.getElementById('ordersBody').innerHTML = html;
}

function viewOrderDetails(orderId) {
    // Find the order from stored data
    const order = ordersData.find(o => o.id == orderId);
    
    if (!order) {
        showErrorInModal('Order not found');
        return;
    }
    
    const modal = document.getElementById('orderModal');
    const modalBody = document.getElementById('orderModalBody');
    
    // Generate items HTML from the order's items
    let itemsHtml = '';
    if (order.items && order.items.length > 0) {
        order.items.forEach(item => {
            let variantHtml = '';
            if (item.variant_options && item.variant_options !== '[]' && item.variant_options !== null) {
                try {
                    const options = typeof item.variant_options === 'string' ? JSON.parse(item.variant_options) : item.variant_options;
                    if (Array.isArray(options) && options.length > 0) {
                        variantHtml = `<div class="order-item-variant">${escapeHtml(options.join(', '))}</div>`;
                    } else if (typeof options === 'object') {
                        const opts = Object.entries(options).map(([k,v]) => `${k}: ${v}`).join(', ');
                        variantHtml = `<div class="order-item-variant">${escapeHtml(opts)}</div>`;
                    }
                } catch(e) {}
            }
            
            itemsHtml += `
                <div class="order-item">
                    <div class="order-item-image">
                        ${item.main_image_url ? 
                            `<img src="${item.main_image_url}" alt="${escapeHtml(item.product_name)}">` : 
                            `<i class="fas fa-box"></i>`
                        }
                    </div>
                    <div class="order-item-details">
                        <div class="order-item-name">${escapeHtml(item.product_name)}</div>
                        ${variantHtml}
                        <div class="order-item-price">₱${parseFloat(item.price).toFixed(2)} each</div>
                    </div>
                    <div class="order-item-quantity">
                        <div class="quantity">Qty: ${item.quantity}</div>
                        <div class="total">₱${parseFloat(item.total_price).toFixed(2)}</div>
                    </div>
                </div>
            `;
        });
    } else {
        itemsHtml = '<p>No items found for this order.</p>';
    }
    
    const html = `
        <div class="order-info-grid">
            <div class="order-info-item">
                <div class="order-info-label">Order Number</div>
                <div class="order-info-value">#${escapeHtml(order.order_number)}</div>
            </div>
            <div class="order-info-item">
                <div class="order-info-label">Order Date</div>
                <div class="order-info-value">${escapeHtml(order.created_datetime)}</div>
            </div>
            <div class="order-info-item">
                <div class="order-info-label">Order Status</div>
                <div class="order-info-value">
                    <span class="status-badge ${getStatusClass(order.status)}">${formatStatus(order.status)}</span>
                </div>
            </div>
            <div class="order-info-item">
                <div class="order-info-label">Customer Name</div>
                <div class="order-info-value">${escapeHtml(order.customer_name)}</div>
            </div>
            <div class="order-info-item">
                <div class="order-info-label">Phone Number</div>
                <div class="order-info-value">${escapeHtml(order.phone_number || 'N/A')}</div>
            </div>
            <div class="order-info-item">
                <div class="order-info-label">Shipping Address</div>
                <div class="order-info-value">${escapeHtml(order.shipping_address || 'N/A')}</div>
            </div>
        </div>
        
        <div class="items-list">
            <h4>Order Items (${order.items ? order.items.length : 0})</h4>
            ${itemsHtml}
        </div>
        
        <div class="order-summary">
            <div class="summary-row">
                <span>Subtotal</span>
                <span>${order.subtotal_formatted}</span>
            </div>
            <div class="summary-row">
                <span>Shipping Fee</span>
                <span>₱${parseFloat(order.shipping_fee || 0).toFixed(2)}</span>
            </div>
            <div class="summary-row">
                <span>Platform Fee</span>
                <span>₱${parseFloat(order.platform_fee || 0).toFixed(2)}</span>
            </div>
            <div class="summary-row total">
                <span>Total Amount</span>
                <span>₱${parseFloat(order.total_amount || order.subtotal).toFixed(2)}</span>
            </div>
        </div>
    `;
    
    modalBody.innerHTML = html;
    modal.classList.add('active');
}

function showErrorInModal(message) {
    const modalBody = document.getElementById('orderModalBody');
    modalBody.innerHTML = `<div class="error-state">${escapeHtml(message)}</div>`;
}

function getStatusClass(status) {
    const classes = {
        'pending': 'status-pending',
        'pending_payment': 'status-pending_payment',
        'packed': 'status-packed',
        'shipped': 'status-shipped',
        'delivered': 'status-delivered',
        'complete': 'status-complete',
        'cancelled': 'status-cancelled',
        'locked': 'status-locked'
    };
    return classes[status] || 'status-pending';
}

function formatStatus(status) {
    if (!status) return 'Unknown';
    return status.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
}

function showNoOrders() {
    document.getElementById('ordersBody').innerHTML = `
        <tr>
            <td colspan="5">
                <div class="no-orders">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>No Orders Found</h3>
                    <p>No orders match your search criteria.</p>
                </div>
             </td>
         </tr>
    `;
    document.getElementById('ordersCount').innerHTML = `Customer Orders (0)`;
}

function showError() {
    document.getElementById('ordersBody').innerHTML = `
        <tr>
            <td colspan="5">
                <div class="no-orders">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error Loading Orders</h3>
                    <p>Please refresh the page to try again.</p>
                </div>
             </td>
         </tr>
    `;
}

function closeOrderModal() {
    document.getElementById('orderModal').classList.remove('active');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Close modal when clicking outside
document.getElementById('orderModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeOrderModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeOrderModal();
    }
});
</script>

</body>
</html>