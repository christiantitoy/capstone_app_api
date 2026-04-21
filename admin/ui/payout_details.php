<?php
// /admin/ui/payout_details.php
require_once '../backend/session/auth_admin.php';

$sellerId = isset($_GET['seller_id']) ? (int) $_GET['seller_id'] : 0;
if (!$sellerId) {
    header('Location: process_payouts.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payout Details | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/payout_details.css?v=<?= time() ?>">
</head>
<body>

<div class="payout-details-container">
    <!-- Back Navigation -->
    <div class="page-header">
        <a href="process_payouts.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Payouts
        </a>
        <h1>Payout Details</h1>
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
        <p>Loading payout details...</p>
    </div>

    <!-- Content -->
    <div id="payoutContent" style="display: none;">
        <!-- Seller Summary Card -->
        <div class="seller-summary-card">
            <div class="seller-header">
                <div class="seller-title">
                    <h2 id="sellerName">-</h2>
                    <span class="status-badge" id="payoutStatus">-</span>
                </div>
                <div class="seller-meta">
                    <p><i class="fas fa-store"></i> <span id="storeName">-</span></p>
                    <p><i class="fas fa-envelope"></i> <span id="sellerEmail">-</span></p>
                </div>
            </div>
            
            <div class="payout-summary">
                <div class="summary-item">
                    <span class="label">Total Items</span>
                    <span class="value" id="totalItems">0</span>
                </div>
                <div class="summary-item">
                    <span class="label">Total Amount</span>
                    <span class="value" id="totalAmount">₱0.00</span>
                </div>
                <div class="summary-item unpaid">
                    <span class="label">Unpaid Amount</span>
                    <span class="value" id="unpaidAmount">₱0.00</span>
                </div>
                <div class="summary-item paid">
                    <span class="label">Paid Amount</span>
                    <span class="value" id="paidAmount">₱0.00</span>
                </div>
            </div>
        </div>

        <!-- Sold Items List -->
        <div class="info-section">
            <div class="section-header">
                <h3><i class="fas fa-box"></i> Sold Items</h3>
                <div class="filter-container">
                    <select id="itemStatusFilter" class="filter-select">
                        <option value="all">All Items</option>
                        <option value="Unpaid">Unpaid</option>
                        <option value="Paid">Paid</option>
                    </select>
                </div>
            </div>
            
            <div class="table-container">
                <div class="items-holder">
                    <div class="items-header">
                        <div class="col-order">Order #</div>
                        <div class="col-product">Product</div>
                        <div class="col-qty">Qty</div>
                        <div class="col-price">Unit Price</div>
                        <div class="col-total">Total</div>
                        <div class="col-status">Status</div>
                        <div class="col-date">Date</div>
                    </div>
                    
                    <div class="table-body" id="itemsBody">
                        <!-- Dynamic content -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error State -->
    <div id="errorState" class="error-state" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <h3>Failed to load payout details</h3>
        <p id="errorMessage">An error occurred while loading the payout information.</p>
        <a href="process_payouts.php" class="btn btn-primary">Return to Payouts</a>
    </div>
</div>

<!-- Notification Container -->
<div id="notificationContainer"></div>

<script>
    const sellerId = <?= $sellerId ?>;
    let allItems = [];
    let sellerInfo = null;
    
    // Load payout details
    async function loadPayoutDetails() {
        const loadingState = document.getElementById('loadingState');
        const payoutContent = document.getElementById('payoutContent');
        const errorState = document.getElementById('errorState');
        
        try {
            const response = await fetch(`../backend/payouts/get_seller_payouts.php?seller_id=${sellerId}`);
            const result = await response.json();
            
            if (result.success) {
                sellerInfo = result.data.seller;
                allItems = result.data.items;
                
                displayPayoutDetails(sellerInfo, allItems);
                updateActionButtons(sellerInfo);
                
                loadingState.style.display = 'none';
                payoutContent.style.display = 'block';
                errorState.style.display = 'none';
            } else {
                throw new Error(result.message || 'Failed to load payout details');
            }
        } catch (error) {
            console.error('Error loading payout details:', error);
            loadingState.style.display = 'none';
            payoutContent.style.display = 'none';
            errorState.style.display = 'block';
            document.getElementById('errorMessage').textContent = error.message;
        }
    }
    
    function updateActionButtons(seller) {
        const actionContainer = document.getElementById('actionButtons');
        
        // Only show Mark as Paid button if there are unpaid items
        if (seller.unpaid_amount > 0) {
            actionContainer.innerHTML = `
                <button class="btn btn-success" onclick="markAllAsPaid()">
                    <i class="fas fa-check-circle"></i> Mark All as Paid
                </button>
            `;
        } else {
            actionContainer.innerHTML = '';
        }
    }
    
    function displayPayoutDetails(seller, items) {
        // Seller info
        document.getElementById('sellerName').textContent = seller.seller_name;
        document.getElementById('storeName').textContent = seller.store_name || 'No store';
        document.getElementById('sellerEmail').textContent = seller.seller_email || 'No email';
        
        // Status badge
        const statusBadge = document.getElementById('payoutStatus');
        statusBadge.textContent = seller.paid_status;
        statusBadge.className = `status-badge status-${seller.paid_status.toLowerCase()}`;
        
        // Summary
        document.getElementById('totalItems').textContent = seller.total_items;
        document.getElementById('totalAmount').textContent = `₱${formatNumber(seller.total_amount)}`;
        document.getElementById('unpaidAmount').textContent = `₱${formatNumber(seller.unpaid_amount)}`;
        document.getElementById('paidAmount').textContent = `₱${formatNumber(seller.paid_amount)}`;
        
        // Display items
        displayItems(items);
    }
    
    function displayItems(items) {
        const tbody = document.getElementById('itemsBody');
        
        if (items.length === 0) {
            tbody.innerHTML = '<div class="no-data">No items found</div>';
            return;
        }
        
        let html = '';
        items.forEach(item => {
            const statusClass = item.paid_status === 'paid' ? 'status-processed' : 'status-pending';
            const statusText = item.paid_status === 'paid' ? 'Paid' : 'Unpaid';
            
            html += `
                <div class="item-row">
                    <div class="col-order">
                        <a href="order_details.php?id=${item.orders_id}" class="order-link">#${item.orders_id}</a>
                    </div>
                    <div class="col-product">
                        <div class="product-info">
                            <strong>${escapeHtml(item.product_name)}</strong>
                        </div>
                    </div>
                    <div class="col-qty">${item.quantity}</div>
                    <div class="col-price">₱${formatNumber(item.unit_price)}</div>
                    <div class="col-total">₱${formatNumber(item.item_total)}</div>
                    <div class="col-status">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                    <div class="col-date">${formatDate(item.sold_date)}</div>
                </div>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    async function markAllAsPaid() {
        if (!confirm(`Mark all unpaid items (₱${formatNumber(sellerInfo.unpaid_amount)}) as paid for ${sellerInfo.seller_name}?`)) {
            return;
        }
        
        try {
            const response = await fetch('../backend/payouts/mark_seller_paid.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    seller_id: sellerId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('success', result.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('error', 'Failed to mark as paid: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('error', 'Error marking payout as paid');
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
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
    
    // Filter items by status
    document.getElementById('itemStatusFilter').addEventListener('change', function(e) {
        const filter = e.target.value;
        let filteredItems = [...allItems];
        
        if (filter !== 'all') {
            const isPaid = filter === 'Paid';
            filteredItems = allItems.filter(item => 
                isPaid ? item.paid_status === 'paid' : item.paid_status !== 'paid'
            );
        }
        
        displayItems(filteredItems);
    });
    
    function formatNumber(num) {
        return parseFloat(num || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    document.addEventListener('DOMContentLoaded', loadPayoutDetails);
</script>

</body>
</html>