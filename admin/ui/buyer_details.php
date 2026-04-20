<?php
// /admin/ui/buyer_details.php
require_once '../backend/session/auth_admin.php';

$buyerId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$buyerId) {
    header('Location: buyers.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Details | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/buyer_details.css?v=<?= time() ?>">
</head>
<body>

<div class="buyer-details-container">
    <!-- Back Navigation -->
    <div class="page-header">
        <a href="buyers.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Buyers
        </a>
        <h1>Buyer Details</h1>
        <div class="header-actions">
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p>Loading buyer details...</p>
    </div>

    <!-- Content -->
    <div id="buyerContent" style="display: none;">
        <!-- Buyer Profile Card -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar" id="buyerAvatar">
                    <div class="avatar-placeholder">B</div>
                </div>
                <div class="profile-info">
                    <h2 id="buyerName">-</h2>
                    <p id="buyerEmail">-</p>
                    <span class="badge" id="buyerSince">Member since -</span>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-item">
                    <div class="stat-value" id="totalOrders">0</div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="totalSpent">₱0</div>
                    <div class="stat-label">Total Spent</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="activeOrders">0</div>
                    <div class="stat-label">Active Orders</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="completedOrders">0</div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
        </div>

        <!-- Order Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-shopping-bag"></i>
                <div class="stat-content">
                    <h3 id="statTotalOrders">0</h3>
                    <p>Total Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-hourglass-half"></i>
                <div class="stat-content">
                    <h3 id="statActiveOrders">0</h3>
                    <p>Active Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <div class="stat-content">
                    <h3 id="statCompletedOrders">0</h3>
                    <p>Completed Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-times-circle"></i>
                <div class="stat-content">
                    <h3 id="statCancelledOrders">0</h3>
                    <p>Cancelled Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <div class="stat-content">
                    <h3 id="statLastOrder">-</h3>
                    <p>Last Order</p>
                </div>
            </div>
        </div>

        <!-- Addresses Section -->
        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-map-marker-alt"></i> Shipping Addresses</h2>
                <span class="badge" id="addressCount">0 addresses</span>
            </div>
            <div id="addressesList" class="addresses-grid">
                <!-- Addresses will be loaded here -->
            </div>
        </div>

        <!-- Recent Orders Section -->
        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-history"></i> Recent Orders</h2>
                <a href="orders.php?buyer_id=<?= $buyerId ?>" class="view-all-link">
                    View All Orders <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            <div class="table-container">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody id="recentOrdersBody">
                        <!-- Orders will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Error State -->
    <div id="errorState" class="error-state" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <h3>Failed to load buyer details</h3>
        <p id="errorMessage">An error occurred while loading the buyer information.</p>
        <a href="buyers.php" class="btn btn-primary">Return to Buyers</a>
    </div>
</div>

<script>
    const buyerId = <?= $buyerId ?>;
    
    // Load buyer details
    async function loadBuyerDetails() {
        const loadingState = document.getElementById('loadingState');
        const buyerContent = document.getElementById('buyerContent');
        const errorState = document.getElementById('errorState');
        
        try {
            const response = await fetch(`../backend/buyers/get_buyer_details.php?id=${buyerId}`);
            const result = await response.json();
            
            if (result.success) {
                const data = result.data;
                displayBuyerDetails(data);
                
                loadingState.style.display = 'none';
                buyerContent.style.display = 'block';
                errorState.style.display = 'none';
            } else {
                throw new Error(result.message || 'Failed to load buyer details');
            }
        } catch (error) {
            console.error('Error loading buyer details:', error);
            loadingState.style.display = 'none';
            buyerContent.style.display = 'none';
            errorState.style.display = 'block';
            document.getElementById('errorMessage').textContent = error.message;
        }
    }
    
    function displayBuyerDetails(data) {
        const buyer = data.buyer;
        const orderStats = data.order_stats;
        const addresses = data.addresses;
        const recentOrders = data.recent_orders;
        const buyerSince = data.buyer_since;
        
        // Update profile
        document.getElementById('buyerName').textContent = buyer.username;
        document.getElementById('buyerEmail').textContent = buyer.email;
        
        if (buyerSince) {
            const sinceDate = new Date(buyerSince).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById('buyerSince').textContent = `Member since ${sinceDate}`;
        } else {
            document.getElementById('buyerSince').textContent = 'No orders yet';
        }
        
        // Update avatar
        const avatarContainer = document.getElementById('buyerAvatar');
        if (buyer.avatar_url) {
            avatarContainer.innerHTML = `<img src="${buyer.avatar_url}" alt="${buyer.username}" class="avatar-img">`;
        } else {
            avatarContainer.innerHTML = `<div class="avatar-placeholder">${buyer.username.charAt(0).toUpperCase()}</div>`;
        }
        
        // Update quick stats
        document.getElementById('totalOrders').textContent = orderStats.total_orders;
        document.getElementById('totalSpent').textContent = `₱${formatNumber(orderStats.total_spent)}`;
        document.getElementById('activeOrders').textContent = orderStats.active_orders;
        document.getElementById('completedOrders').textContent = orderStats.completed_orders;
        
        // Update stat cards
        document.getElementById('statTotalOrders').textContent = orderStats.total_orders;
        document.getElementById('statActiveOrders').textContent = orderStats.active_orders;
        document.getElementById('statCompletedOrders').textContent = orderStats.completed_orders;
        document.getElementById('statCancelledOrders').textContent = orderStats.cancelled_orders;
        
        if (orderStats.last_order_date) {
            const lastOrder = new Date(orderStats.last_order_date).toLocaleDateString();
            document.getElementById('statLastOrder').textContent = lastOrder;
        } else {
            document.getElementById('statLastOrder').textContent = 'No orders';
        }
        
        // Display addresses
        displayAddresses(addresses);
        
        // Display recent orders
        displayRecentOrders(recentOrders);
    }
    
    function displayAddresses(addresses) {
        const addressCount = document.getElementById('addressCount');
        const addressesList = document.getElementById('addressesList');
        
        addressCount.textContent = `${addresses.length} address${addresses.length !== 1 ? 'es' : ''}`;
        
        if (addresses.length === 0) {
            addressesList.innerHTML = '<div class="no-data">No addresses found</div>';
            return;
        }
        
        addressesList.innerHTML = addresses.map(addr => {
            const isDefault = addr.is_default == 1;
            return `
                <div class="address-card ${isDefault ? 'default' : ''}">
                    ${isDefault ? '<span class="default-badge"><i class="fas fa-star"></i> Default</span>' : ''}
                    <div class="address-header">
                        <i class="fas fa-user"></i>
                        <strong>${escapeHtml(addr.recipient_name)}</strong>
                    </div>
                    <div class="address-detail">
                        <i class="fas fa-phone"></i>
                        <span>${escapeHtml(addr.phone_number)}</span>
                    </div>
                    <div class="address-detail">
                        <i class="fas fa-map-pin"></i>
                        <span>${escapeHtml(addr.gps_location)}</span>
                    </div>
                    <div class="address-detail full-address">
                        <i class="fas fa-location-dot"></i>
                        <span>${escapeHtml(addr.full_address || 'No full address provided')}</span>
                    </div>
                    <div class="address-footer">
                        <small>Added: ${new Date(addr.created_at).toLocaleDateString()}</small>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    function displayRecentOrders(orders) {
        const tbody = document.getElementById('recentOrdersBody');
        
        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No orders found</td></tr>';
            return;
        }
        
        tbody.innerHTML = orders.map(order => {
            const statusClass = getStatusClass(order.status);
            return `
                <tr>
                    <td><strong>#${order.id}</strong></td>
                    <td>${new Date(order.created_at).toLocaleDateString()}</td>
                    <td>₱${formatNumber(order.total_amount)}</td>
                    <td><span class="status-badge ${statusClass}">${order.status}</span></td>
                    <td>${new Date(order.updated_at).toLocaleString()}</td>
                </tr>
            `;
        }).join('');
    }
    
    function getStatusClass(status) {
        const statusMap = {
            'pending': 'status-pending',
            'pending_payment': 'status-pending',
            'packed': 'status-processing',
            'ready_for_pickup': 'status-processing',
            'shipped': 'status-shipped',
            'assigned': 'status-shipped',
            'reassigned': 'status-shipped',
            'delivered': 'status-delivered',
            'complete': 'status-delivered',
            'cancelled': 'status-cancelled',
            'locked': 'status-locked'
        };
        return statusMap[status] || 'status-default';
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
    
    // Load details on page load
    document.addEventListener('DOMContentLoaded', loadBuyerDetails);
</script>

</body>
</html>