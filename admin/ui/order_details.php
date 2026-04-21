<?php
// /admin/ui/order_details.php
require_once '../backend/session/auth_admin.php';

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$orderId) {
    header('Location: orders.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/order_details.css?v=<?= time() ?>">
</head>
<body>

<div class="order-details-container">
    <!-- Back Navigation -->
    <div class="page-header">
        <a href="orders.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        <h1>Order Details</h1>
        <div class="header-actions">
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p>Loading order details...</p>
    </div>

    <!-- Content -->
    <div id="orderContent" style="display: none;">
        <!-- Order Summary Card -->
        <div class="order-summary-card">
            <div class="order-header">
                <div class="order-title">
                    <h2>Order #<span id="orderId">-</span></h2>
                    <span class="status-badge" id="orderStatus">-</span>
                </div>
                <div class="order-dates">
                    <p><i class="far fa-calendar-alt"></i> Created: <span id="orderCreated">-</span></p>
                    <p><i class="far fa-clock"></i> Updated: <span id="orderUpdated">-</span></p>
                </div>
            </div>
            
            <div class="order-financials">
                <div class="financial-item">
                    <span class="label">Subtotal</span>
                    <span class="value" id="orderSubtotal">₱0.00</span>
                </div>
                <div class="financial-item">
                    <span class="label">Shipping Fee</span>
                    <span class="value" id="orderShipping">₱0.00</span>
                </div>
                <div class="financial-item">
                    <span class="label">Platform Fee</span>
                    <span class="value" id="orderPlatformFee">₱0.00</span>
                </div>
                <div class="financial-item total">
                    <span class="label">Total Amount</span>
                    <span class="value" id="orderTotal">₱0.00</span>
                </div>
            </div>
            
            <div class="payment-method">
                <i class="fas fa-credit-card"></i>
                <span id="orderPaymentMethod">-</span>
            </div>
        </div>

        <!-- Customer & Delivery Info -->
        <div class="info-grid">
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-user"></i>
                    <h3>Customer Information</h3>
                </div>
                <div class="card-body" id="customerInfo">
                    <!-- Dynamic content -->
                </div>
            </div>
            
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Delivery Address</h3>
                </div>
                <div class="card-body" id="addressInfo">
                    <!-- Dynamic content -->
                </div>
            </div>
        </div>

        <!-- Rider/Delivery Information -->
        <div class="info-section" id="deliverySection" style="display: none;">
            <div class="section-header">
                <h3><i class="fas fa-motorcycle"></i> Delivery Information</h3>
            </div>
            <div class="delivery-card">
                <div class="delivery-status-row">
                    <span class="delivery-status-badge" id="deliveryStatus">-</span>
                </div>
                <div class="delivery-details" id="deliveryDetails">
                    <!-- Dynamic content -->
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="info-section">
            <div class="section-header">
                <h3><i class="fas fa-shopping-bag"></i> Order Items</h3>
                <span class="badge" id="itemCount">0 items</span>
            </div>
            <div class="order-items-list" id="orderItemsList">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>

    <!-- Error State -->
    <div id="errorState" class="error-state" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <h3>Failed to load order details</h3>
        <p id="errorMessage">An error occurred while loading the order information.</p>
        <a href="orders.php" class="btn btn-primary">Return to Orders</a>
    </div>
</div>

<script>
    const orderId = <?= $orderId ?>;
    
    // Load order details
    async function loadOrderDetails() {
        const loadingState = document.getElementById('loadingState');
        const orderContent = document.getElementById('orderContent');
        const errorState = document.getElementById('errorState');
        
        try {
            const response = await fetch(`../backend/orders/get_order_details.php?id=${orderId}`);
            const result = await response.json();
            
            if (result.success) {
                const data = result.data;
                displayOrderDetails(data);
                
                loadingState.style.display = 'none';
                orderContent.style.display = 'block';
                errorState.style.display = 'none';
            } else {
                throw new Error(result.message || 'Failed to load order details');
            }
        } catch (error) {
            console.error('Error loading order details:', error);
            loadingState.style.display = 'none';
            orderContent.style.display = 'none';
            errorState.style.display = 'block';
            document.getElementById('errorMessage').textContent = error.message;
        }
    }
    
    function displayOrderDetails(data) {
        const order = data.order;
        const items = data.items;
        const delivery = data.delivery;
        
        // Order header
        document.getElementById('orderId').textContent = order.id;
        document.getElementById('orderCreated').textContent = formatDateTime(order.created_at);
        document.getElementById('orderUpdated').textContent = formatDateTime(order.updated_at);
        document.getElementById('orderPaymentMethod').textContent = formatPaymentMethod(order.payment_method);
        
        // Status badge
        const statusBadge = document.getElementById('orderStatus');
        statusBadge.textContent = formatStatus(order.status);
        statusBadge.className = `status-badge status-${order.status}`;
        
        // Financials
        document.getElementById('orderSubtotal').textContent = `₱${formatNumber(order.subtotal)}`;
        document.getElementById('orderShipping').textContent = `₱${formatNumber(order.shipping_fee)}`;
        document.getElementById('orderPlatformFee').textContent = `₱${formatNumber(order.platform_fee)}`;
        document.getElementById('orderTotal').textContent = `₱${formatNumber(order.total_amount)}`;
        
        // Customer info - from buyers table
        document.getElementById('customerInfo').innerHTML = `
            <p><strong>Username:</strong> ${escapeHtml(order.buyer_name || 'N/A')}</p>
            <p><strong>Email:</strong> ${escapeHtml(order.buyer_email || 'N/A')}</p>
        `;
        
        // Address info - from buyer_addresses table
        let addressHtml = '';
        if (order.recipient_name) {
            addressHtml += `<p><strong>Recipient:</strong> ${escapeHtml(order.recipient_name)}</p>`;
        }
        if (order.buyer_phone) {
            addressHtml += `<p><strong>Phone:</strong> ${escapeHtml(order.buyer_phone)}</p>`;
        }
        if (order.full_address) {
            addressHtml += `<p><strong>Address:</strong> ${escapeHtml(order.full_address)}</p>`;
        }
        if (order.plus_code) {
            addressHtml += `<p><strong>GPS Location / Plus Code:</strong> ${escapeHtml(order.plus_code)}</p>`;
        }
        
        document.getElementById('addressInfo').innerHTML = addressHtml || '<p>No address information available</p>';
        
        // Delivery info
        if (delivery) {
            document.getElementById('deliverySection').style.display = 'block';
            const deliveryStatus = document.getElementById('deliveryStatus');
            deliveryStatus.textContent = formatDeliveryStatus(delivery.delivery_status);
            deliveryStatus.className = `delivery-status-badge status-${delivery.delivery_status}`;
            
            let deliveryHtml = '';
            if (delivery.rider_name) {
                deliveryHtml += `
                    <p><strong>Rider:</strong> <a href="rider_details.php?id=${delivery.rider_id}" class="rider-link">${escapeHtml(delivery.rider_name)}</a></p>
                `;
            }
            if (delivery.rider_email) {
                deliveryHtml += `<p><strong>Rider Email:</strong> ${escapeHtml(delivery.rider_email)}</p>`;
            }
            if (delivery.assigned_at) {
                deliveryHtml += `<p><strong>Assigned:</strong> ${formatDateTime(delivery.assigned_at)}</p>`;
            }
            if (delivery.picked_up_at) {
                deliveryHtml += `<p><strong>Picked Up:</strong> ${formatDateTime(delivery.picked_up_at)}</p>`;
            }
            if (delivery.completed_at) {
                deliveryHtml += `<p><strong>Completed:</strong> ${formatDateTime(delivery.completed_at)}</p>`;
            }
            if (delivery.cancelled_at) {
                deliveryHtml += `<p><strong>Cancelled:</strong> ${formatDateTime(delivery.cancelled_at)}</p>`;
            }
            
            document.getElementById('deliveryDetails').innerHTML = deliveryHtml || '<p>No delivery details available</p>';
        } else {
            document.getElementById('deliverySection').style.display = 'none';
        }
        
        // Order items
        document.getElementById('itemCount').textContent = `${items.length} item${items.length !== 1 ? 's' : ''}`;
        displayOrderItems(items);
    }
    
    function displayOrderItems(items) {
        const container = document.getElementById('orderItemsList');
        
        if (items.length === 0) {
            container.innerHTML = '<div class="no-items">No items found</div>';
            return;
        }
        
        let html = '';
        items.forEach(item => {
            const productImage = item.main_image_url || '';
            const optionsDisplay = item.options_display || (item.selected_options || '');
            
            html += `
                <div class="order-item">
                    <div class="item-image">
                        ${productImage ? 
                            `<img src="${productImage}" alt="${escapeHtml(item.product_name)}">` : 
                            '<i class="fas fa-box"></i>'
                        }
                    </div>
                    <div class="item-details">
                        <div class="item-header">
                            <h4>${escapeHtml(item.product_name)}</h4>
                            <a href="product_details.php?id=${item.product_id}" class="view-product-btn">
                                <i class="fas fa-external-link-alt"></i> View Product
                            </a>
                        </div>
                        ${item.store_name ? `<p class="item-store"><i class="fas fa-store"></i> ${escapeHtml(item.store_name)}</p>` : ''}
                        ${item.seller_name ? `<p class="item-seller">Seller: ${escapeHtml(item.seller_name)}</p>` : ''}
                        ${optionsDisplay ? `<p class="item-variant"><i class="fas fa-tag"></i> Variant: ${escapeHtml(optionsDisplay)}</p>` : ''}
                        ${item.sku ? `<p class="item-sku">SKU: ${escapeHtml(item.sku)}</p>` : ''}
                        <div class="item-price-qty">
                            <span class="item-price">₱${formatNumber(item.unit_price)} × ${item.quantity}</span>
                            <span class="item-total">₱${formatNumber(item.total_price)}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    function formatStatus(status) {
        const formats = {
            'pending': 'Pending',
            'pending_payment': 'Pending Payment',
            'packed': 'Packed',
            'ready_for_pickup': 'Ready for Pickup',
            'shipped': 'Shipped',
            'delivered': 'Delivered',
            'cancelled': 'Cancelled',
            'complete': 'Complete',
            'locked': 'Locked',
            'assigned': 'Assigned',
            'reassigned': 'Reassigned'
        };
        return formats[status] || status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
    
    function formatDeliveryStatus(status) {
        const formats = {
            'assigned': 'Assigned',
            'picked_up': 'Picked Up',
            'delivering': 'Delivering',
            'completed': 'Completed',
            'abandoned': 'Abandoned',
            'cancelled': 'Cancelled'
        };
        return formats[status] || status;
    }
    
    function formatPaymentMethod(method) {
        const formats = {
            'cod': 'Cash on Delivery',
            'gcash': 'GCash',
            'card': 'Credit/Debit Card'
        };
        return formats[method] || method;
    }
    
    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
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
    document.addEventListener('DOMContentLoaded', loadOrderDetails);
</script>

</body>
</html>