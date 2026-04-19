<?php
// /seller/ui/dashboard.php
require_once __DIR__ . '/../backend/session/auth.php';

$seller_id = $_SESSION['seller_id'] ?? null;

if (!$seller_id) {
    header("Location: /seller/ui/login.php");
    exit;
}

// Fetch top selling products
require_once '/var/www/html/connection/db_connection.php';

$topProducts = [];
try {
    $stmt = $conn->prepare("
        SELECT 
            i.id,
            i.product_name,
            i.sold as total_sold,
            i.main_image_url,
            (i.sold * 100.0 / NULLIF((SELECT MAX(sold) FROM items WHERE seller_id = ? AND sold > 0), 0)) as percentage
        FROM items i
        WHERE i.seller_id = ? AND i.sold > 0
        GROUP BY i.id, i.product_name, i.sold, i.main_image_url
        ORDER BY i.sold DESC, i.id
        LIMIT 5
    ");
    $stmt->execute([$seller_id, $seller_id]);
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate max sold for percentage
    if (!empty($topProducts)) {
        $maxSold = $topProducts[0]['total_sold'];
        foreach ($topProducts as &$product) {
            $product['percentage'] = $maxSold > 0 ? round(($product['total_sold'] / $maxSold) * 100) : 0;
        }
    }
} catch (PDOException $e) {
    $topProducts = [];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/error.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/logout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Seller<span>Dashboard</span></h2>
        </div>
        <nav class="sidebar-nav">
            <a href="/seller/ui/dashboard.php" class="nav-item active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="/seller/ui/products.php" class="nav-item"><i class="fas fa-box"></i><span>Products</span></a>
            <a href="/seller/ui/orders.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Orders</span></a>
            <a href="/seller/ui/employees.php" class="nav-item"><i class="fas fa-users"></i><span>Employees</span></a>
            <a href="/seller/ui/my_plan.php" class="nav-item"><i class="fas fa-crown"></i><span>My Plan</span></a>
            <a href="/seller/ui/sales.php" class="nav-item"><i class="fas fa-chart-line"></i><span>Sales</span></a>
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

    <main class="main-content">
        
        <header class="main-header">
            <div class="header-left">
                <h1>Dashboard Overview</h1>
                <p>Welcome back, <?= htmlspecialchars(explode(' ', $seller_name)[0]) ?>!</p>

                <?php
                // Define plan styles
                $plan_styles = [
                    'bronze' => [
                        'background' => '#b45309',
                        'hover' => '#d97706',
                        'icon' => 'fa-medal'
                    ],
                    'silver' => [
                        'background' => '#6b7280',
                        'hover' => '#9ca3af',
                        'icon' => 'fa-star'
                    ],
                    'gold' => [
                        'background' => '#fbbf24',
                        'hover' => '#fcd34d',
                        'icon' => 'fa-crown'
                    ]
                ];
                
                $current_plan = strtolower($seller_plan ?? 'bronze');
                $plan_style = $plan_styles[$current_plan] ?? $plan_styles['bronze'];
                ?>
                
                <a href="#" class="plan-badge" style="background: <?= $plan_style['background']; ?>;">
                    <i class="fas <?= $plan_style['icon']; ?>" style="margin-right: 6px;"></i>
                    <?= ucfirst($current_plan); ?> Seller
                </a>
            </div>
            <div class="header-right">
                <div class="date-display"><?= date('F j, Y') ?></div>
            </div>
        </header>

        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db"><i class="fas fa-box"></i></div>
                <div class="stat-info"><h3 id="products-count">--</h3><p>Total Products</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e67e2220;color:#e67e22"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-info"><h3 id="orders-count">--</h3><p>Total Orders</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#27ae6020;color:#27ae60"><i class="fas fa-peso-sign"></i></div>
                <div class="stat-info"><h3 id="revenue-count">--</h3><p>Total Revenue</p></div>
            </div>
        </section>

        <!-- Recent Orders Section -->
        <div class="full-width-section recent-orders">
            <div class="section-header">
                <h2>Recent Orders</h2>
                <a href="/seller/ui/orders.php" class="view-all">View All</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="recent-orders-body">
                        <tr>
                            <td colspan="5" style="text-align: center;">Loading orders...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions + Top Products -->
        <div class="side-by-side">
            <div class="top-products">
                <div class="section-header"><h2>Top Selling Products</h2></div>
                <div class="products-list">
                    <?php if (empty($topProducts)): ?>
                        <div class="product-item" style="justify-content: center; padding: 2rem;">
                            <p style="color: #95a5a6;">No sales data yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($topProducts as $product): ?>
                            <div class="product-item">
                                <div class="product-info">
                                    <h4><?= htmlspecialchars($product['product_name']) ?></h4>
                                    <p><?= $product['total_sold'] ?> sold</p>
                                </div>
                                <div class="product-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width:<?= $product['percentage'] ?>%;background:#3498db;"></div>
                                    </div>
                                    <span><?= $product['percentage'] ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <p>© <?= date('Y') ?> Seller Dashboard. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a> •
                <a href="#">Terms of Service</a> •
                <a href="#">Help Center</a>
            </div>
        </footer>

    </main>
</div>

<!-- LOGOUT MODAL -->
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
    const sellerId = <?php echo json_encode($seller_id); ?>;
    
    async function loadData() {
        try {
            const response = await fetch(`/seller/backend/dashboard_backends/count_products.php?seller_id=${sellerId}`);
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('products-count').textContent = data.products_count;
                document.getElementById('orders-count').textContent = data.orders_count;
                document.getElementById('revenue-count').textContent = '₱' + parseFloat(data.total_revenue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }
    
    loadData();

    // Recent orders
    fetch(`/seller/backend/dashboard_backends/get_recent_orders.php?seller_id=${sellerId}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('recent-orders-body');
            
            if (data.success && data.orders.length > 0) {
                tbody.innerHTML = '';
                
                data.orders.forEach(order => {
                    let statusClass = 'status-badge';
                    if (order.status === 'delivered' || order.status === 'complete') {
                        statusClass += ' status-delivered';
                    } else if (order.status === 'shipped' || order.status === 'ready_for_pickup') {
                        statusClass += ' status-shipped';
                    } else {
                        statusClass += ' status-pending';
                    }
                    
                    tbody.innerHTML += `
                        <tr>
                            <td>#${String(order.order_id).padStart(5, '0')}</td>
                            <td>${order.customer_name || 'Guest'}</td>
                            <td>${order.products}</td>
                            <td>₱${parseFloat(order.total_amount).toFixed(2)}</td>
                            <td><span class="${statusClass}">
                                ${order.status
                                    .split('_')
                                    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                                    .join(' ')}
                                </span>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No orders found</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('recent-orders-body').innerHTML = '<tr><td colspan="5" style="text-align: center;">Error loading orders</td></tr>';
        });
</script>

</body>
</html>