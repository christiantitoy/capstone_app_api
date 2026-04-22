<?php
// /admin/ui/delivery_details.php
require_once '../backend/session/auth_admin.php';

$deliveryId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$deliveryId) {
    header('Location: deliveries.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Details | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/delivery_details.css?v=<?= time() ?>">
</head>
<body>

<div class="delivery-details-container">
    <!-- Back Navigation -->
    <div class="page-header">
        <a href="deliveries.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Deliveries
        </a>
        <h1>Delivery Details</h1>
        <div class="header-actions">
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p>Loading delivery details...</p>
    </div>

    <!-- Content -->
    <div id="deliveryContent" style="display: none;">
        <!-- Delivery Summary Card -->
        <div class="delivery-summary-card">
            <div class="delivery-header">
                <div class="delivery-title">
                    <h2>Delivery #<span id="deliveryId">-</span></h2>
                    <span class="status-badge" id="deliveryStatus">-</span>
                </div>
                <div class="delivery-meta">
                    <p><i class="fas fa-shopping-cart"></i> Order #<a href="order_details.php?id=" id="orderLink">-</a></p>
                    <p><i class="far fa-calendar-alt"></i> Created: <span id="deliveryCreated">-</span></p>
                </div>
            </div>
            
            <!-- Delivery Timeline -->
            <div class="delivery-timeline">
                <div class="timeline-step" id="stepAssigned">
                    <div class="step-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="step-content">
                        <span class="step-label">Assigned</span>
                        <span class="step-time" id="assignedTime">-</span>
                    </div>
                </div>
                <div class="timeline-connector"></div>
                <div class="timeline-step" id="stepPickedUp">
                    <div class="step-icon"><i class="fas fa-box"></i></div>
                    <div class="step-content">
                        <span class="step-label">Picked Up</span>
                        <span class="step-time" id="pickedUpTime">-</span>
                    </div>
                </div>
                <div class="timeline-connector"></div>
                <div class="timeline-step" id="stepCompleted">
                    <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="step-content">
                        <span class="step-label">Completed</span>
                        <span class="step-time" id="completedTime">-</span>
                    </div>
                </div>
            </div>
            
            <!-- Cancelled/Abandoned info -->
            <div id="cancelledInfo" style="display: none;" class="cancelled-info">
                <i class="fas fa-times-circle"></i>
                <span id="cancelledText">-</span>
            </div>
            
            <div id="abandonedInfo" style="display: none;" class="abandoned-info">
                <i class="fas fa-exclamation-circle"></i>
                <span id="abandonedText">-</span>
            </div>
            
            <!-- Delivery Duration -->
            <div id="durationInfo" style="display: none;" class="duration-info">
                <i class="fas fa-clock"></i>
                <span>Delivery Duration: <strong id="deliveryDuration">-</strong></span>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-motorcycle"></i>
                    <h3>Rider Information</h3>
                </div>
                <div class="card-body" id="riderInfo">
                    <!-- Dynamic content -->
                </div>
            </div>
            
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
            
            <div class="info-card">
                <div class="card-header">
                    <i class="fas fa-credit-card"></i>
                    <h3>Payment Summary</h3>
                </div>
                <div class="card-body" id="paymentInfo">
                    <!-- Dynamic content -->
                </div>
            </div>
        </div>

        <!-- Delivery Proof Section -->
        <div id="deliveryProofSection" class="info-section" style="display: none;">
            <div class="section-header">
                <h3><i class="fas fa-camera"></i> Delivery Proof</h3>
            </div>
            <div class="delivery-proof-container" id="deliveryProofContainer">
                <!-- Dynamic content -->
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
        <h3>Failed to load delivery details</h3>
        <p id="errorMessage">An error occurred while loading the delivery information.</p>
        <a href="deliveries.php" class="btn btn-primary">Return to Deliveries</a>
    </div>
</div>

<style>
/* Delivery Proof Styles */
.delivery-proof-container {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.proof-image-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 15px;
}

.proof-image {
    max-width: 100%;
    max-height: 400px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: transform 0.3s;
}

.proof-image:hover {
    transform: scale(1.02);
}

.proof-meta {
    display: flex;
    justify-content: center;
    gap: 20px;
    color: #7f8c8d;
    font-size: 14px;
}

.proof-meta i {
    margin-right: 5px;
    color: #3498db;
}

.no-proof {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}

.no-proof i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

/* Image Modal */
.image-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
    justify-content: center;
    align-items: center;
}

.image-modal.active {
    display: flex;
}

.modal-image {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
}

.close-modal-btn {
    position: absolute;
    top: 20px;
    right: 35px;
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}

.close-modal-btn:hover {
    color: #bbb;
}
</style>

<!-- Image Modal -->
<div id="imageModal" class="image-modal" onclick="closeImageModal()">
    <span class="close-modal-btn">&times;</span>
    <img id="modalImage" class="modal-image">
</div>

<script>
    const deliveryId = <?= $deliveryId ?>;
    
    // Load delivery details
    async function loadDeliveryDetails() {
        const loadingState = document.getElementById('loadingState');
        const deliveryContent = document.getElementById('deliveryContent');
        const errorState = document.getElementById('errorState');
        
        try {
            const response = await fetch(`../backend/deliveries/get_delivery_details.php?id=${deliveryId}`);
            const result = await response.json();
            
            if (result.success) {
                const data = result.data;
                displayDeliveryDetails(data);
                
                loadingState.style.display = 'none';
                deliveryContent.style.display = 'block';
                errorState.style.display = 'none';
            } else {
                throw new Error(result.message || 'Failed to load delivery details');
            }
        } catch (error) {
            console.error('Error loading delivery details:', error);
            loadingState.style.display = 'none';
            deliveryContent.style.display = 'none';
            errorState.style.display = 'block';
            document.getElementById('errorMessage').textContent = error.message;
        }
    }
    
    function displayDeliveryDetails(data) {
        const delivery = data.delivery;
        const items = data.items;
        
        // Delivery header
        document.getElementById('deliveryId').textContent = delivery.delivery_id;
        document.getElementById('deliveryCreated').textContent = formatDateTime(delivery.created_at);
        document.getElementById('orderLink').href = `order_details.php?id=${delivery.order_id}`;
        document.getElementById('orderLink').textContent = delivery.order_id;
        
        // Status badge
        const statusBadge = document.getElementById('deliveryStatus');
        statusBadge.textContent = formatDeliveryStatus(delivery.delivery_status);
        statusBadge.className = `status-badge status-${delivery.delivery_status}`;
        
        // Timeline
        document.getElementById('assignedTime').textContent = delivery.assigned_at ? formatDateTime(delivery.assigned_at) : 'Not assigned';
        document.getElementById('pickedUpTime').textContent = delivery.picked_up_at ? formatDateTime(delivery.picked_up_at) : 'Not picked up';
        document.getElementById('completedTime').textContent = delivery.completed_at ? formatDateTime(delivery.completed_at) : 'Not completed';
        
        // Update timeline step statuses
        updateTimelineStatus(delivery);
        
        // Cancelled info
        if (delivery.cancelled_at) {
            document.getElementById('cancelledInfo').style.display = 'flex';
            document.getElementById('cancelledText').textContent = `Cancelled on ${formatDateTime(delivery.cancelled_at)}`;
        }
        
        // Abandoned info
        if (delivery.abandoned_at) {
            document.getElementById('abandonedInfo').style.display = 'flex';
            document.getElementById('abandonedText').textContent = `Abandoned on ${formatDateTime(delivery.abandoned_at)}`;
        }
        
        // Duration
        if (data.delivery_duration) {
            document.getElementById('durationInfo').style.display = 'flex';
            document.getElementById('deliveryDuration').textContent = data.delivery_duration;
        }
        
        // Delivery Proof
        displayDeliveryProof(data.delivery_proofs);
        
        // Rider info
        document.getElementById('riderInfo').innerHTML = `
            <p><strong>Name:</strong> <a href="rider_details.php?id=${delivery.rider_id}" class="rider-link">${escapeHtml(delivery.rider_name || 'N/A')}</a></p>
            <p><strong>Email:</strong> ${escapeHtml(delivery.rider_email || 'N/A')}</p>
            <p><strong>Status:</strong> <span class="status-badge status-${delivery.rider_status}">${delivery.rider_status || 'N/A'}</span></p>
            <p><strong>Verification:</strong> ${formatVerificationStatus(delivery.rider_verification)}</p>
        `;
        
        // Customer info
        document.getElementById('customerInfo').innerHTML = `
            <p><strong>Username:</strong> ${escapeHtml(delivery.buyer_name || 'N/A')}</p>
            <p><strong>Email:</strong> ${escapeHtml(delivery.buyer_email || 'N/A')}</p>
        `;
        
        // Address info
        let addressHtml = '';
        if (delivery.recipient_name) {
            addressHtml += `<p><strong>Recipient:</strong> ${escapeHtml(delivery.recipient_name)}</p>`;
        }
        if (delivery.phone_number) {
            addressHtml += `<p><strong>Phone:</strong> ${escapeHtml(delivery.phone_number)}</p>`;
        }
        if (delivery.full_address) {
            addressHtml += `<p><strong>Address:</strong> ${escapeHtml(delivery.full_address)}</p>`;
        }
        if (delivery.gps_location) {
            addressHtml += `<p><strong>GPS/Plus Code:</strong> ${escapeHtml(delivery.gps_location)}</p>`;
        }
        document.getElementById('addressInfo').innerHTML = addressHtml || '<p>No address information available</p>';
        
        // Payment info
        document.getElementById('paymentInfo').innerHTML = `
            <p><strong>Payment Method:</strong> ${formatPaymentMethod(delivery.payment_method)}</p>
            <p><strong>Subtotal:</strong> ₱${formatNumber(delivery.subtotal)}</p>
            <p><strong>Shipping Fee:</strong> ₱${formatNumber(delivery.shipping_fee)}</p>
            <p><strong>Platform Fee:</strong> ₱${formatNumber(delivery.platform_fee)}</p>
            <p><strong>Total Amount:</strong> <span style="color: #27ae60; font-weight: 700;">₱${formatNumber(delivery.total_amount)}</span></p>
            <p><strong>Order Status:</strong> <span class="status-badge status-${delivery.order_status}">${formatOrderStatus(delivery.order_status)}</span></p>
        `;
        
        // Order items
        document.getElementById('itemCount').textContent = `${items.length} item${items.length !== 1 ? 's' : ''}`;
        displayOrderItems(items);
    }
    
    function displayDeliveryProof(proofs) {
        const proofSection = document.getElementById('deliveryProofSection');
        const proofContainer = document.getElementById('deliveryProofContainer');
        
        if (!proofs || proofs.length === 0) {
            proofSection.style.display = 'none';
            return;
        }
        
        proofSection.style.display = 'block';
        
        let html = '';
        proofs.forEach(proof => {
            html += `
                <div class="delivery-proof-container">
                    <div class="proof-image-wrapper">
                        <img src="${escapeHtml(proof.proof_image_path)}" 
                             alt="Delivery Proof" 
                             class="proof-image"
                             onclick="openImageModal('${escapeHtml(proof.proof_image_path)}')">
                    </div>
                    <div class="proof-meta">
                        <span><i class="far fa-calendar-alt"></i> Uploaded: ${formatDateTime(proof.created_at)}</span>
                    </div>
                </div>
            `;
        });
        
        proofContainer.innerHTML = html;
    }
    
    function openImageModal(imageSrc) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        modal.classList.add('active');
        modalImg.src = imageSrc;
    }
    
    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.remove('active');
    }
    
    function updateTimelineStatus(delivery) {
        const stepAssigned = document.getElementById('stepAssigned');
        const stepPickedUp = document.getElementById('stepPickedUp');
        const stepCompleted = document.getElementById('stepCompleted');
        
        // Reset classes
        stepAssigned.classList.remove('completed', 'active');
        stepPickedUp.classList.remove('completed', 'active');
        stepCompleted.classList.remove('completed', 'active');
        
        if (delivery.completed_at) {
            stepAssigned.classList.add('completed');
            stepPickedUp.classList.add('completed');
            stepCompleted.classList.add('completed');
        } else if (delivery.picked_up_at) {
            stepAssigned.classList.add('completed');
            stepPickedUp.classList.add('completed');
            stepCompleted.classList.add('active');
        } else if (delivery.assigned_at) {
            stepAssigned.classList.add('completed');
            stepPickedUp.classList.add('active');
        } else {
            stepAssigned.classList.add('active');
        }
        
        // Handle cancelled/abandoned
        if (delivery.cancelled_at || delivery.abandoned_at) {
            stepAssigned.classList.add('completed');
            stepPickedUp.classList.remove('active', 'completed');
            stepCompleted.classList.remove('active', 'completed');
        }
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
            const optionsDisplay = item.selected_options || item.options_json_value || '';
            
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
                                <i class="fas fa-external-link-alt"></i> View
                            </a>
                        </div>
                        ${item.store_name ? `<p class="item-store"><i class="fas fa-store"></i> ${escapeHtml(item.store_name)}</p>` : ''}
                        ${optionsDisplay ? `<p class="item-variant">Variant: ${escapeHtml(optionsDisplay)}</p>` : ''}
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
    
    function formatOrderStatus(status) {
        const formats = {
            'pending': 'Pending',
            'pending_payment': 'Pending Payment',
            'packed': 'Packed',
            'ready_for_pickup': 'Ready for Pickup',
            'shipped': 'Shipped',
            'delivered': 'Delivered',
            'cancelled': 'Cancelled',
            'complete': 'Complete'
        };
        return formats[status] || status;
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
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
    
    document.addEventListener('DOMContentLoaded', loadDeliveryDetails);
</script>

</body>
</html>