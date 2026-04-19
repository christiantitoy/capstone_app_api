<?php
// /seller/ui/sales.php
require_once __DIR__ . '/../backend/session/auth.php';

// Get seller ID from session
$seller_id = $_SESSION['seller_id'] ?? 0;

// Fetch sales data
require_once '/var/www/html/connection/db_connection.php';

try {
    $stmt = $conn->prepare("
        SELECT 
            si.id as sale_id,
            si.created_at as sale_date,
            oi.quantity,
            oi.unit_price,
            oi.total_price,
            i.product_name,
            o.payment_method,
            ba.recipient_name,
            ba.full_address
        FROM sold_items si
        JOIN order_items oi ON si.order_items_id = oi.id
        JOIN items i ON oi.product_id = i.id
        JOIN orders o ON si.orders_id = o.id
        JOIN buyer_addresses ba ON o.address_id = ba.id
        WHERE i.seller_id = ?
        ORDER BY si.created_at DESC
    ");
    $stmt->execute([$seller_id]);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $sales = [];
    $error_message = "Failed to load sales data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/logout.css?v=<?= time() ?>">
    <style>
        :root {
            --primary: #3498db;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gray: #95a5a6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
            min-height: 100vh;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: 240px 1fr;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: white;
            border-right: 1px solid #ebedf0;
            display: flex;
            flex-direction: column;
            padding: 1.5rem 0;
        }

        .sidebar-header {
            padding: 0 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo { font-size: 1.8rem; color: var(--primary); }

        .sidebar-header h2 {
            font-size: 1.4rem;
            font-weight: 600;
        }

        .sidebar-header span { color: var(--primary); }

        .sidebar-nav { flex: 1; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.9rem 1.5rem;
            color: #5f6b7a;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.15s;
        }

        .nav-item:hover, .nav-item.active {
            background: #e8f4fd;
            color: var(--primary);
        }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #ebedf0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
            cursor: pointer;
            border-radius: 8px;
            padding: 4px 8px;
            transition: background 0.2s;
        }

        .user-profile:hover {
            background: #f0f2f5;
        }

        .user-info {
            flex: 1;
            min-width: 0;
            overflow: hidden;
        }

        .seller-name {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-profile p {
            font-size: 0.75rem;
            margin: 0;
            color: var(--gray);
        }

        .avatar {
            width: 38px; height: 38px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: grid; place-items: center;
            font-weight: bold; font-size: 1.1rem;
        }

        .logout-btn {
            background: none;
            border: none;
            color: #e74c3c;
            font-size: 1.3rem;
            cursor: pointer;
            flex-shrink: 0;
            padding: 8px;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .logout-btn:hover {
            background: #fee;
        }

        /* Main Content */
        .main-content {
            overflow-y: auto;
            padding: 1.5rem 2.5rem;
        }

        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .header-left h1 { 
            font-size: 1.8rem; 
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header-left p { color: #7f8c8d; margin-top: 0.25rem; }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            flex-wrap: wrap;
        }

        .date-display { color: #7f8c8d; font-size: 0.95rem; white-space: nowrap; }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.blue { background: #e8f4fd; color: var(--primary); }
        .stat-icon.green { background: #e8f5e9; color: var(--success); }
        .stat-icon.orange { background: #fff3e0; color: var(--warning); }

        .stat-info h3 {
            font-size: 0.85rem;
            color: var(--gray);
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .stat-info .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
        }

        /* Sales Table */
        .sales-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .card-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #eef2f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-header h2 {
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-header h2 i {
            color: var(--primary);
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f5f7fa;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }

        .search-box i {
            color: var(--gray);
        }

        .search-box input {
            border: none;
            background: none;
            outline: none;
            font-size: 0.9rem;
            min-width: 200px;
        }

        .table-container {
            overflow-x: auto;
        }

        .sales-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sales-table th {
            text-align: left;
            padding: 1rem 1.5rem;
            background: #fafbfc;
            color: var(--gray);
            font-weight: 500;
            font-size: 0.85rem;
            border-bottom: 1px solid #eef2f6;
            white-space: nowrap;
        }

        .sales-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #eef2f6;
            font-size: 0.9rem;
        }

        .sales-table tbody tr {
            cursor: pointer;
            transition: background 0.15s;
        }

        .sales-table tbody tr:hover {
            background: #f0f7ff;
        }

        .buyer-cell {
            display: flex;
            flex-direction: column;
        }

        .buyer-name {
            font-weight: 500;
            margin-bottom: 0.15rem;
        }

        .buyer-address {
            font-size: 0.8rem;
            color: var(--gray);
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .payment-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            background: #e8f4fd;
            color: var(--primary);
        }

        .amount-cell {
            font-weight: 600;
            color: var(--success);
        }

        .no-sales {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .no-sales i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Footer */
        footer.main-footer {
            text-align: center;
            padding: 2rem 0;
            color: #95a5a6;
            font-size: 0.9rem;
            border-top: 1px solid #ebedf0;
            margin-top: 2rem;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 900px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
        }

        @media (max-width: 600px) {
            .main-content {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box input {
                min-width: 150px;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-store logo"></i>
            <h2>Seller<span>Dashboard</span></h2>
        </div>
        <nav class="sidebar-nav">
            <a href="/seller/ui/dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="/seller/ui/products.php" class="nav-item"><i class="fas fa-box"></i><span>Products</span></a>
            <a href="/seller/ui/orders.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Orders</span></a>
            <a href="/seller/ui/employees.php" class="nav-item"><i class="fas fa-users"></i><span>Employees</span></a>
            <a href="/seller/ui/my_plan.php" class="nav-item"><i class="fas fa-crown"></i><span>My Plan</span></a>
            <a href="/seller/ui/sales.php" class="nav-item active"><i class="fas fa-chart-line"></i><span>Sales</span></a>
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
                <h1>
                    <i class="fas fa-chart-line" style="color: var(--primary);"></i>
                    Sales Report
                </h1>
                <p>View and track all your sold products</p>
            </div>
            <div class="header-right">
                <div class="date-display"><?= date('F j, Y') ?></div>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Sales</h3>
                    <div class="stat-value"><?= count($sales) ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-peso-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Revenue</h3>
                    <div class="stat-value">
                        ₱<?= number_format(array_sum(array_column($sales, 'total_price')), 2) ?>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3>Items Sold</h3>
                    <div class="stat-value">
                        <?= array_sum(array_column($sales, 'quantity')) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="sales-card">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Recent Sales</h2>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search products or buyers...">
                </div>
            </div>
            <div class="table-container">
                <?php if (empty($sales)): ?>
                    <div class="no-sales">
                        <i class="fas fa-box-open"></i>
                        <h3>No Sales Yet</h3>
                        <p>Your sold products will appear here once you make a sale.</p>
                    </div>
                <?php else: ?>
                    <table class="sales-table" id="salesTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Buyer</th>
                                <th>Unit Price</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                                <tr onclick="window.location.href='/seller/ui/sales_details.php?sale_id=<?= $sale['sale_id'] ?>'">
                                    <td>
                                        <strong><?= htmlspecialchars($sale['product_name']) ?></strong>
                                        <br>
                                        <small style="color: var(--gray);">Qty: <?= $sale['quantity'] ?></small>
                                    </td>
                                    <td>
                                        <div class="buyer-cell">
                                            <span class="buyer-name"><?= htmlspecialchars($sale['recipient_name']) ?></span>
                                            <span class="buyer-address" title="<?= htmlspecialchars($sale['full_address']) ?>">
                                                <?= htmlspecialchars($sale['full_address']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="amount-cell">₱<?= number_format($sale['unit_price'], 2) ?></td>
                                    <td>
                                        <span class="payment-badge">
                                            <?= htmlspecialchars($sale['payment_method']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <footer class="main-footer">
            <p>© <?= date('Y') ?> Seller Dashboard. All rights reserved.</p>
        </footer>
    </main>
</div>

<!-- Logout Modal -->
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
// Fix user profile redirect
document.addEventListener('DOMContentLoaded', function() {
    const userProfile = document.getElementById('userProfile');
    if (userProfile) {
        const newProfile = userProfile.cloneNode(true);
        userProfile.parentNode.replaceChild(newProfile, userProfile);
        
        newProfile.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/seller/ui/seller_profile.php';
        });
        
        newProfile.style.cursor = 'pointer';
    }
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const table = document.getElementById('salesTable');
            if (table) {
                const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                
                for (let row of rows) {
                    const productName = row.cells[0]?.textContent.toLowerCase() || '';
                    const buyerName = row.cells[1]?.textContent.toLowerCase() || '';
                    
                    if (productName.includes(filter) || buyerName.includes(filter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            }
        });
    }
});
</script>

</body>
</html>