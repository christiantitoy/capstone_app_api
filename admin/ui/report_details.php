<?php
// /admin/ui/report_details.php
require_once '../backend/session/auth_admin.php';

$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$reportId) {
    header('Location: buyer_reports.php');
    exit;
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
        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-success:hover {
            background: #219a52;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }
        .btn-warning:hover {
            background: #e67e22;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
        }

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

        /* Loading State */
        .loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e9ecef;
            border-top-color: #3498db;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-state p {
            color: #7f8c8d;
            font-size: 16px;
        }

        /* Error State */
        .error-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }

        .error-state i {
            font-size: 48px;
            color: #e74c3c;
            margin-bottom: 20px;
        }

        .error-state h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .error-state p {
            color: #7f8c8d;
            margin-bottom: 20px;
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

        /* Delivery Timeline */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 25px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -26px;
            top: 4px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: white;
            border: 3px solid #3498db;
            z-index: 1;
        }

        .timeline-dot.completed {
            background: #27ae60;
            border-color: #27ae60;
        }

        .timeline-dot.active {
            background: #f39c12;
            border-color: #f39c12;
        }

        .timeline-content h4 {
            font-size: 15px;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .timeline-content p {
            font-size: 13px;
            color: #7f8c8d;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 18px;
            color: #2c3e50;
        }

        .close-modal {
            font-size: 24px;
            color: #7f8c8d;
            cursor: pointer;
            background: none;
            border: none;
        }

        .close-modal:hover {
            color: #2c3e50;
        }

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
            font-size: 14px;
        }

        .form-select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            color: #2c3e50;
            background: white;
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
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

        .notification-success {
            background: #27ae60;
        }

        .notification-error {
            background: #e74c3c;
        }

        .notification-info {
            background: #3498db;
        }

        .notification-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            margin-left: auto;
            font-size: 18px;
            opacity: 0.8;
        }

        .notification-close:hover {
            opacity: 1;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .report-details-container {
                padding: 15px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .report-status-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media print {
            .header-actions,
            .back-btn,
            .modal,
            #notificationContainer {
                display: none !important;
            }
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
        <div class="header-actions" id="headerActions"></div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p>Loading report details...</p>
    </div>

    <!-- Content -->
    <div id="reportContent" style="display: none;">
        <!-- Report Status Card -->
        <div class="card" id="reportStatusCard">
            <div class="card-header">
                <i class="fas fa-flag"></i>
                <h3>Report Information</h3>
            </div>
            <div class="card-body">
                <div class="report-status-header">
                    <div class="report-meta">
                        <span class="report-id">Report #<?= $reportId ?></span>
                        <span class="status-badge" id="reportStatus">-</span>
                    </div>
                </div>
                <div class="timestamps" id="reportTimestamps"></div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <!-- Report Details -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i>
                    <h3>Issue Details</h3>
                </div>
                <div class="card-body" id="issueDetails"></div>
            </div>

            <!-- Buyer Information -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user"></i>
                    <h3>Buyer Information</h3>
                </div>
                <div class="card-body" id="buyerInfo"></div>
            </div>

            <!-- Delivery Information -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-truck"></i>
                    <h3>Delivery Information</h3>
                </div>
                <div class="card-body" id="deliveryInfo"></div>
            </div>
        </div>
    </div>

    <!-- Error State -->
    <div id="errorState" class="error-state" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <h3>Failed to load report details</h3>
        <p id="errorMessage">An error occurred while loading the report information.</p>
        <a href="buyer_reports.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Return to Reports
        </a>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Report Status</h3>
            <button class="close-modal" onclick="closeStatusModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="color: #7f8c8d; margin-bottom: 15px;">
                Change status for Report #<?= $reportId ?>
            </p>
            <div class="form-group">
                <label for="newStatusSelect">New Status</label>
                <select id="newStatusSelect" class="form-select">
                    <option value="pending">Pending</option>
                    <option value="reviewing">Reviewing</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeStatusModal()">Cancel</button>
            <button class="btn btn-primary" id="confirmStatusBtn" onclick="updateReportStatus()">
                <i class="fas fa-check"></i> Update Status
            </button>
        </div>
    </div>
</div>

<!-- Notification Container -->
<div id="notificationContainer"></div>

<script>
    const reportId = <?= $reportId ?>;
    let currentReport = null;

    // Load report details on page load
    document.addEventListener('DOMContentLoaded', loadReportDetails);

    async function loadReportDetails() {
        const loadingState = document.getElementById('loadingState');
        const reportContent = document.getElementById('reportContent');
        const errorState = document.getElementById('errorState');

        try {
            // Fetch report details
            const reportResponse = await fetch(`/admin/backend/reports/get_buyer_reports.php?page=1`);
            const reportResult = await reportResponse.json();

            if (!reportResult.success) {
                throw new Error('Failed to load report');
            }

            // Find the specific report
            const report = reportResult.data.reports.find(r => r.id == reportId);
            if (!report) {
                throw new Error('Report not found');
            }

            currentReport = report;

            // Fetch buyer details
            let buyerData = null;
            try {
                const buyerResponse = await fetch(`/admin/backend/buyers/get_buyer.php?id=${report.buyer_id}`);
                const buyerResult = await buyerResponse.json();
                if (buyerResult.success) {
                    buyerData = buyerResult.data;
                }
            } catch (e) {
                console.warn('Could not load buyer details:', e);
            }

            // Fetch delivery details
            let deliveryData = null;
            try {
                const deliveryResponse = await fetch(`/admin/backend/deliveries/get_delivery.php?id=${report.delivery_id}`);
                const deliveryResult = await deliveryResponse.json();
                if (deliveryResult.success) {
                    deliveryData = deliveryResult.data;
                }
            } catch (e) {
                console.warn('Could not load delivery details:', e);
            }

            // Display the data
            displayReportDetails(report, buyerData, deliveryData);

            loadingState.style.display = 'none';
            reportContent.style.display = 'block';
            errorState.style.display = 'none';

        } catch (error) {
            console.error('Error loading report:', error);
            loadingState.style.display = 'none';
            reportContent.style.display = 'none';
            errorState.style.display = 'flex';
            document.getElementById('errorMessage').textContent = error.message;
        }
    }

    function displayReportDetails(report, buyer, delivery) {
        // Update status badge
        const statusBadge = document.getElementById('reportStatus');
        statusBadge.textContent = report.status;
        statusBadge.className = `status-badge status-${report.status}`;

        // Update timestamps
        document.getElementById('reportTimestamps').innerHTML = `
            <div class="timestamp-item">
                <span class="timestamp-label">Submitted</span>
                <span class="timestamp-value">${formatDate(report.created_at)}</span>
            </div>
            <div class="timestamp-item">
                <span class="timestamp-label">Last Updated</span>
                <span class="timestamp-value">${formatDate(report.updated_at)}</span>
            </div>
        `;

        // Issue Details
        document.getElementById('issueDetails').innerHTML = `
            <div class="info-item">
                <span class="info-label">Report ID</span>
                <span class="info-value">#${report.id}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Issue Type</span>
                <span class="info-value">${escapeHtml(report.issue_type)}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status</span>
                <span class="info-value">
                    <span class="status-badge status-${report.status}">${report.status}</span>
                </span>
            </div>
        `;

        // Buyer Information
        if (buyer) {
            document.getElementById('buyerInfo').innerHTML = `
                <div class="info-item">
                    <span class="info-label">Buyer ID</span>
                    <span class="info-value">#${buyer.id}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Username</span>
                    <span class="info-value">${escapeHtml(buyer.username)}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value">${escapeHtml(buyer.email)}</span>
                </div>
            `;
        } else {
            document.getElementById('buyerInfo').innerHTML = `
                <div class="info-item">
                    <span class="info-label">Buyer ID</span>
                    <span class="info-value">#${report.buyer_id}</span>
                </div>
                <p style="color: #7f8c8d; margin-top: 10px; font-style: italic;">Buyer details not available</p>
            `;
        }

        // Delivery Information
        if (delivery) {
            document.getElementById('deliveryInfo').innerHTML = `
                <div class="info-item">
                    <span class="info-label">Delivery ID</span>
                    <span class="info-value">#${delivery.id}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Order ID</span>
                    <span class="info-value">#${delivery.order_id}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Rider ID</span>
                    <span class="info-value">#${delivery.rider_id}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value">${formatStatus(delivery.status)}</span>
                </div>
                ${delivery.assigned_at ? `
                    <div class="info-item">
                        <span class="info-label">Assigned At</span>
                        <span class="info-value">${formatDate(delivery.assigned_at)}</span>
                    </div>
                ` : ''}
                ${delivery.picked_up_at ? `
                    <div class="info-item">
                        <span class="info-label">Picked Up At</span>
                        <span class="info-value">${formatDate(delivery.picked_up_at)}</span>
                    </div>
                ` : ''}
                ${delivery.completed_at ? `
                    <div class="info-item">
                        <span class="info-label">Completed At</span>
                        <span class="info-value">${formatDate(delivery.completed_at)}</span>
                    </div>
                ` : ''}
                ${delivery.created_at ? `
                    <div class="info-item">
                        <span class="info-label">Created At</span>
                        <span class="info-value">${formatDate(delivery.created_at)}</span>
                    </div>
                ` : ''}
            `;
        } else {
            document.getElementById('deliveryInfo').innerHTML = `
                <div class="info-item">
                    <span class="info-label">Delivery ID</span>
                    <span class="info-value">#${report.delivery_id}</span>
                </div>
                <p style="color: #7f8c8d; margin-top: 10px; font-style: italic;">Delivery details not available</p>
            `;
        }

        // Update header actions
        updateHeaderActions(report);
    }

    function updateHeaderActions(report) {
        const actionsContainer = document.getElementById('headerActions');
        let html = '';

        if (report.status === 'pending') {
            html += `
                <button class="btn btn-warning" onclick="quickStatusUpdate('reviewing')">
                    <i class="fas fa-search"></i> Mark as Reviewing
                </button>
            `;
        }

        if (report.status === 'reviewing') {
            html += `
                <button class="btn btn-success" onclick="quickStatusUpdate('resolved')">
                    <i class="fas fa-check-circle"></i> Mark as Resolved
                </button>
            `;
        }

        if (report.status !== 'closed') {
            html += `
                <button class="btn btn-danger" onclick="quickStatusUpdate('closed')">
                    <i class="fas fa-times-circle"></i> Close Report
                </button>
            `;
        }

        html += `
            <button class="btn btn-primary" onclick="openStatusModal()">
                <i class="fas fa-edit"></i> Change Status
            </button>
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        `;

        actionsContainer.innerHTML = html;
    }

    function openStatusModal() {
        const modal = document.getElementById('statusModal');
        document.getElementById('newStatusSelect').value = currentReport.status;
        modal.style.display = 'flex';
    }

    function closeStatusModal() {
        document.getElementById('statusModal').style.display = 'none';
    }

    async function updateReportStatus() {
        const newStatus = document.getElementById('newStatusSelect').value;
        const confirmBtn = document.getElementById('confirmStatusBtn');
        const originalHTML = confirmBtn.innerHTML;

        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

        try {
            const response = await fetch('/admin/backend/reports/get_buyer_reports.php', {
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
                closeStatusModal();
                showNotification('success', 'Report status updated successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('error', 'Failed to update status: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('error', 'Network error occurred');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalHTML;
        }
    }

    async function quickStatusUpdate(newStatus) {
        try {
            const response = await fetch('/admin/backend/reports/get_buyer_reports.php', {
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
                showNotification('error', 'Failed to update status');
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

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const options = { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit' 
        };
        return date.toLocaleDateString('en-US', options);
    }

    function formatStatus(status) {
        if (!status) return 'N/A';
        return status.charAt(0).toUpperCase() + status.slice(1).replace(/_/g, ' ');
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Close modal on outside click
    window.onclick = function(event) {
        const modal = document.getElementById('statusModal');
        if (event.target === modal) {
            closeStatusModal();
        }
    };

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStatusModal();
        }
    });
</script>

</body>
</html>