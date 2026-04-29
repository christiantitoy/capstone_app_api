<?php
// /admin/ui/report_details.php
require_once '../backend/session/auth_admin.php';
require_once '/var/www/html/connection/db_connection.php';

$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$reportId) {
    header('Location: buyer_reports.php');
    exit;
}

// Fetch report details
$reportSql = "SELECT id, delivery_id, buyer_id, issue_type, status, created_at, updated_at 
              FROM buyer_reports WHERE id = :id";
$reportStmt = $conn->prepare($reportSql);
$reportStmt->execute([':id' => $reportId]);
$report = $reportStmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    header('Location: buyer_reports.php');
    exit;
}

// Fetch buyer details
$buyerSql = "SELECT id, username, email, avatar_url FROM buyers WHERE id = :id";
$buyerStmt = $conn->prepare($buyerSql);
$buyerStmt->execute([':id' => $report['buyer_id']]);
$buyer = $buyerStmt->fetch(PDO::FETCH_ASSOC);

// Fetch delivery details
$deliverySql = "SELECT id, order_id, rider_id, status, assigned_at, picked_up_at, 
                       completed_at, abandoned_at, cancelled_at, created_at, updated_at 
                FROM order_deliveries WHERE id = :id";
$deliveryStmt = $conn->prepare($deliverySql);
$deliveryStmt->execute([':id' => $report['delivery_id']]);
$delivery = $deliveryStmt->fetch(PDO::FETCH_ASSOC);

// Fetch order items with product and store info
$orderItems = [];
if ($delivery) {
    $itemsSql = "SELECT 
                    oi.id as order_item_id,
                    oi.order_id,
                    oi.product_id,
                    oi.variation_id,
                    oi.selected_options,
                    oi.quantity,
                    oi.unit_price,
                    oi.total_price,
                    oi.is_shipped,
                    i.product_name,
                    i.product_description,
                    i.category as product_category,
                    i.price as current_price,
                    i.stock,
                    i.main_image_url,
                    i.status as product_status,
                    i.has_variations,
                    i.sold,
                    s.store_name,
                    s.seller_id,
                    s.category as store_category,
                    s.logo_url as store_logo
                 FROM order_items oi
                 LEFT JOIN items i ON oi.product_id = i.id
                 LEFT JOIN stores s ON i.seller_id = s.seller_id
                 WHERE oi.order_id = :order_id
                 ORDER BY oi.id ASC";
    
    $itemsStmt = $conn->prepare($itemsSql);
    $itemsStmt->execute([':order_id' => $delivery['order_id']]);
    $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report #<?= $reportId ?> Details | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/images/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
            min-height: 100vh;
        }

        .report-details-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }

        /* Page Header */
        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 15px;
            transition: color 0.3s;
        }

        .back-btn:hover {
            color: #2980b9;
        }

        .page-header h1 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .page-header .subtitle {
            color: #7f8c8d;
            font-size: 14px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-primary:hover { background: #2980b9; }

        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-success:hover { background: #219a52; }

        .btn-warning {
            background: #f39c12;
            color: white;
        }
        .btn-warning:hover { background: #e67e22; }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover { background: #c0392b; }

        .btn-outline {
            background: white;
            color: #7f8c8d;
            border: 1px solid #ddd;
        }
        .btn-outline:hover {
            background: #f8f9fa;
            border-color: #3498db;
            color: #3498db;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header i {
            font-size: 20px;
            color: #3498db;
        }

        .card-header h3 {
            font-size: 18px;
            color: #2c3e50;
        }

        .card-body {
            padding: 25px;
        }

        /* Report Status Header */
        .report-status-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .report-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .report-id {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background: #fff3e0;
            color: #e67e22;
            border: 1px solid #ffcc80;
        }

        .status-reviewing {
            background: #e3f2fd;
            color: #2196f3;
            border: 1px solid #90caf9;
        }

        .status-resolved {
            background: #e8f5e9;
            color: #4caf50;
            border: 1px solid #a5d6a7;
        }

        .status-closed {
            background: #fce4ec;
            color: #f44336;
            border: 1px solid #ef9a9a;
        }

        .status-assigned { background: #e3f2fd; color: #1976d2; }
        .status-picked_up { background: #fff3e0; color: #f57c00; }
        .status-delivering { background: #e8f5e9; color: #388e3c; }
        .status-completed { background: #e8f5e9; color: #2e7d32; }
        .status-abandoned,
        .status-cancelled { background: #fce4ec; color: #c62828; }

        .status-approved { background: #e8f5e9; color: #2e7d32; }
        .status-on_hold { background: #fff3e0; color: #e67e22; }
        .status-removed { background: #fce4ec; color: #c62828; }
        .status-on_review { background: #e3f2fd; color: #1976d2; }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #7f8c8d;
            font-size: 14px;
        }

        .info-value {
            color: #2c3e50;
            font-size: 14px;
            font-weight: 500;
        }

        /* Timestamps */
        .timestamps {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }

        .timestamp-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .timestamp-label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .timestamp-value {
            font-size: 14px;
            color: #2c3e50;
            font-weight: 500;
        }

        /* Product Items */
        .product-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .product-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            border: 1px solid #e9ecef;
            transition: all 0.2s;
        }

        .product-item:hover {
            border-color: #3498db;
            box-shadow: 0 2px 8px rgba(52,152,219,0.1);
        }

        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .product-name {
            font-size: 15px;
            font-weight: 600;
            color: #2c3e50;
        }

        .product-store {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 2px;
        }

        .product-price {
            font-size: 15px;
            font-weight: 600;
            color: #27ae60;
        }

        .product-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 8px;
        }

        .product-detail-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .product-detail-label {
            font-size: 11px;
            color: #7f8c8d;
            text-transform: uppercase;
        }

        .product-detail-value {
            font-size: 13px;
            color: #2c3e50;
            font-weight: 500;
        }

        .shipped-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .shipped-yes {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .shipped-no {
            background: #fff3e0;
            color: #e67e22;
        }

        .product-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            background: #e9ecef;
        }

        .order-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            border: 1px solid #e9ecef;
        }

        .order-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .order-summary-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 16px;
            color: #2c3e50;
        }

        .order-summary-label {
            color: #7f8c8d;
            font-size: 14px;
        }

        .order-summary-value {
            color: #2c3e50;
            font-size: 14px;
            font-weight: 500;
        }

        /* Notification */
        #notificationContainer {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .notification {
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 300px;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .notification-success { background: #27ae60; }
        .notification-error { background: #e74c3c; }

        .notification-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            margin-left: auto;
            font-size: 18px;
            opacity: 0.8;
        }

        .notification-close:hover { opacity: 1; }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .report-details-container { padding: 15px; }
            .info-grid { grid-template-columns: 1fr; }
            .report-status-header { flex-direction: column; align-items: flex-start; }
            .header-actions { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
            .product-header { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="report-details-container">
    <!-- Back Navigation -->
    <div class="page-header">
        <a href="buyer_reports.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
        <h1>Report #<?= $reportId ?> Details</h1>
        <p class="subtitle">View and manage buyer report information</p>
        <div class="header-actions">
            <?php if ($report['status'] === 'pending'): ?>
                <button class="btn btn-warning" onclick="quickStatusUpdate('reviewing')">
                    <i class="fas fa-search"></i> Mark as Reviewing
                </button>
            <?php endif; ?>
            
            <?php if ($report['status'] === 'reviewing'): ?>
                <button class="btn btn-success" onclick="quickStatusUpdate('resolved')">
                    <i class="fas fa-check-circle"></i> Mark as Resolved
                </button>
            <?php endif; ?>
            
            <?php if ($report['status'] !== 'closed'): ?>
                <button class="btn btn-danger" onclick="quickStatusUpdate('closed')">
                    <i class="fas fa-times-circle"></i> Close Report
                </button>
            <?php endif; ?>
            
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Report Status Card -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-flag"></i>
            <h3>Report Information</h3>
        </div>
        <div class="card-body">
            <div class="report-status-header">
                <div class="report-meta">
                    <span class="report-id">Report #<?= $report['id'] ?></span>
                    <span class="status-badge status-<?= $report['status'] ?>"><?= htmlspecialchars($report['status']) ?></span>
                </div>
            </div>
            <div class="timestamps">
                <div class="timestamp-item">
                    <span class="timestamp-label">Submitted</span>
                    <span class="timestamp-value"><?= date('M d, Y h:i A', strtotime($report['created_at'])) ?></span>
                </div>
                <div class="timestamp-item">
                    <span class="timestamp-label">Last Updated</span>
                    <span class="timestamp-value"><?= date('M d, Y h:i A', strtotime($report['updated_at'])) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <!-- Issue Details -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i>
                <h3>Issue Details</h3>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <span class="info-label">Report ID</span>
                    <span class="info-value">#<?= $report['id'] ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Issue Type</span>
                    <span class="info-value"><?= htmlspecialchars($report['issue_type']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="status-badge status-<?= $report['status'] ?>"><?= htmlspecialchars($report['status']) ?></span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Delivery ID</span>
                    <span class="info-value">#<?= $report['delivery_id'] ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Buyer ID</span>
                    <span class="info-value">#<?= $report['buyer_id'] ?></span>
                </div>
            </div>
        </div>

        <!-- Buyer Information -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user"></i>
                <h3>Buyer Information</h3>
            </div>
            <div class="card-body">
                <?php if ($buyer): ?>
                    <div class="info-item">
                        <span class="info-label">Buyer ID</span>
                        <span class="info-value">#<?= $buyer['id'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Username</span>
                        <span class="info-value"><?= htmlspecialchars($buyer['username']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?= htmlspecialchars($buyer['email']) ?></span>
                    </div>
                <?php else: ?>
                    <div class="info-item">
                        <span class="info-label">Buyer ID</span>
                        <span class="info-value">#<?= $report['buyer_id'] ?></span>
                    </div>
                    <p style="color: #7f8c8d; margin-top: 10px; font-style: italic;">Buyer details not available</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-truck"></i>
                <h3>Delivery Information</h3>
            </div>
            <div class="card-body">
                <?php if ($delivery): ?>
                    <div class="info-item">
                        <span class="info-label">Delivery ID</span>
                        <span class="info-value">#<?= $delivery['id'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Order ID</span>
                        <span class="info-value">#<?= $delivery['order_id'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Rider ID</span>
                        <span class="info-value">#<?= $delivery['rider_id'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="status-badge status-<?= $delivery['status'] ?>">
                                <?= htmlspecialchars(str_replace('_', ' ', $delivery['status'])) ?>
                            </span>
                        </span>
                    </div>
                    <?php if ($delivery['assigned_at']): ?>
                    <div class="info-item">
                        <span class="info-label">Assigned At</span>
                        <span class="info-value"><?= date('M d, Y h:i A', strtotime($delivery['assigned_at'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($delivery['picked_up_at']): ?>
                    <div class="info-item">
                        <span class="info-label">Picked Up At</span>
                        <span class="info-value"><?= date('M d, Y h:i A', strtotime($delivery['picked_up_at'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($delivery['completed_at']): ?>
                    <div class="info-item">
                        <span class="info-label">Completed At</span>
                        <span class="info-value"><?= date('M d, Y h:i A', strtotime($delivery['completed_at'])) ?></span>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="info-item">
                        <span class="info-label">Delivery ID</span>
                        <span class="info-value">#<?= $report['delivery_id'] ?></span>
                    </div>
                    <p style="color: #7f8c8d; margin-top: 10px; font-style: italic;">Delivery details not available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Product Information Card (Full Width) -->
    <?php if (!empty($orderItems)): ?>
    <div class="card">
        <div class="card-header">
            <i class="fas fa-box"></i>
            <h3>Product Information</h3>
            <span style="margin-left: auto; font-size: 13px; color: #7f8c8d;">
                Order #<?= $delivery['order_id'] ?> • <?= count($orderItems) ?> item(s)
            </span>
        </div>
        <div class="card-body">
            <div class="product-list">
                <?php foreach ($orderItems as $item): ?>
                <div class="product-item">
                    <div class="product-header">
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <?php if ($item['main_image_url']): ?>
                                <img src="<?= htmlspecialchars($item['main_image_url']) ?>" 
                                     alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                     class="product-image"
                                     onerror="this.style.display='none'">
                            <?php else: ?>
                                <div class="product-image" style="display: flex; align-items: center; justify-content: center; color: #bdc3c7;">
                                    <i class="fas fa-box"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div class="product-name"><?= htmlspecialchars($item['product_name'] ?? 'Unknown Product') ?></div>
                                <div class="product-store">
                                    <i class="fas fa-store"></i>
                                    <?= htmlspecialchars($item['store_name'] ?? 'Unknown Store') ?>
                                </div>
                            </div>
                        </div>
                        <div class="product-price">₱<?= number_format($item['total_price'], 2) ?></div>
                    </div>
                    
                    <div class="product-details">
                        <div class="product-detail-item">
                            <span class="product-detail-label">Unit Price</span>
                            <span class="product-detail-value">₱<?= number_format($item['unit_price'], 2) ?></span>
                        </div>
                        <div class="product-detail-item">
                            <span class="product-detail-label">Quantity</span>
                            <span class="product-detail-value"><?= (int)$item['quantity'] ?></span>
                        </div>
                        <div class="product-detail-item">
                            <span class="product-detail-label">Product ID</span>
                            <span class="product-detail-value">#<?= $item['product_id'] ?></span>
                        </div>
                        <div class="product-detail-item">
                            <span class="product-detail-label">Seller ID</span>
                            <span class="product-detail-value">#<?= $item['seller_id'] ?></span>
                        </div>
                        <div class="product-detail-item">
                            <span class="product-detail-label">Category</span>
                            <span class="product-detail-value"><?= htmlspecialchars($item['product_category'] ?? 'N/A') ?></span>
                        </div>
                        <div class="product-detail-item">
                            <span class="product-detail-label">Stock</span>
                            <span class="product-detail-value"><?= (int)($item['stock'] ?? 0) ?></span>
                        </div>
                        <div class="product-detail-item">
                            <span class="product-detail-label">Sold</span>
                            <span class="product-detail-value"><?= (int)($item['sold'] ?? 0) ?></span>
                        </div>
                        <div class="product-detail-item">
                            <span class="product-detail-label">Product Status</span>
                            <span class="product-detail-value">
                                <span class="status-badge status-<?= $item['product_status'] ?>">
                                    <?= htmlspecialchars(str_replace('_', ' ', $item['product_status'] ?? 'unknown')) ?>
                                </span>
                            </span>
                        </div>
                        <div class="product-detail-item">
                            <span class="product-detail-label">Shipped</span>
                            <span class="product-detail-value">
                                <span class="shipped-badge <?= $item['is_shipped'] ? 'shipped-yes' : 'shipped-no' ?>">
                                    <?= $item['is_shipped'] ? 'Yes' : 'No' ?>
                                </span>
                            </span>
                        </div>
                        <?php if ($item['variation_id']): ?>
                        <div class="product-detail-item">
                            <span class="product-detail-label">Variation ID</span>
                            <span class="product-detail-value">#<?= $item['variation_id'] ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($item['selected_options']): ?>
                        <div class="product-detail-item" style="grid-column: 1 / -1;">
                            <span class="product-detail-label">Selected Options</span>
                            <span class="product-detail-value"><?= htmlspecialchars($item['selected_options']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="order-summary-row">
                    <span class="order-summary-label">Subtotal</span>
                    <span class="order-summary-value">
                        ₱<?= number_format(array_sum(array_column($orderItems, 'total_price')), 2) ?>
                    </span>
                </div>
                <div class="order-summary-row">
                    <span class="order-summary-label">Total Items</span>
                    <span class="order-summary-value"><?= count($orderItems) ?></span>
                </div>
                <div class="order-summary-row" style="font-weight: 700; font-size: 16px;">
                    <span class="order-summary-label">Order Total</span>
                    <span class="order-summary-value" style="color: #27ae60;">
                        ₱<?= number_format(array_sum(array_column($orderItems, 'total_price')), 2) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php elseif ($delivery): ?>
    <div class="card">
        <div class="card-header">
            <i class="fas fa-box"></i>
            <h3>Product Information</h3>
        </div>
        <div class="card-body">
            <div style="text-align: center; padding: 30px; color: #7f8c8d;">
                <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 15px; color: #bdc3c7;"></i>
                <p>No order items found for Order #<?= $delivery['order_id'] ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Notification Container -->
<div id="notificationContainer"></div>

<script>
    const reportId = <?= $reportId ?>;

    async function quickStatusUpdate(newStatus) {
        try {
            const response = await fetch('/admin/backend/reports/update_report_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    report_id: reportId,
                    status: newStatus
                })
            });

            const result = await response.json();

            if (result.status === 'success') {
                showNotification('success', `Report marked as ${newStatus}!`);
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('error', 'Failed to update status: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('error', 'Network error occurred');
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
</script>

</body>
</html>