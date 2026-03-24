<?php
// /seller/ui/dashboard.php
require_once __DIR__ . '/../backend/session/auth.php';

$seller_id = $_SESSION['seller_id'] ?? null;

if (!$seller_id) {
    header("Location: /seller/ui/login.php");
    exit;
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
            <a href="/seller/ui/analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
            <a href="#" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar"><?= strtoupper(substr($seller_name, 0, 1)) ?></div>
                <div>
                    <h4><?= htmlspecialchars($seller_name) ?></h4>
                    <p>Seller Account</p>
                </div>
            </div>
            <!-- Changed to button that opens modal -->
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
                        'background' => '#b45309', // Brown/orange
                        'hover' => '#d97706',
                        'icon' => 'fa-medal'
                    ],
                    'silver' => [
                        'background' => '#6b7280', // Gray
                        'hover' => '#9ca3af',
                        'icon' => 'fa-star'
                    ],
                    'gold' => [
                        'background' => '#fbbf24', // Gold
                        'hover' => '#fcd34d',
                        'icon' => 'fa-crown'
                    ]
                ];
                
                // Get current plan (default to bronze if not set)
                $current_plan = strtolower($seller_plan ?? 'bronze');
                $plan_style = $plan_styles[$current_plan] ?? $plan_styles['bronze'];
                ?>
                
                <a href="#" class="plan-badge" style="background: <?= $plan_style['background']; ?>;">
                    <i class="fas <?= $plan_style['icon']; ?>" style="margin-right: 6px;"></i>
                    <?= ucfirst($current_plan); ?> Seller
                </a>
            </div>
            <div class="header-right">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
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
                <div class="stat-icon" style="background:#3498db20;color:#3498db"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-info"><h3>$18,420.50</h3><p>Total Revenue</p></div>
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

        <!-- Quick Actions + Top Products - same height, side by side -->
        <div class="side-by-side">

            <div class="top-products">
                <div class="section-header"><h2>Top Selling Products</h2></div>
                <div class="products-list">
                    <div class="product-item">
                        <div class="product-info"><h4>Wireless Earbuds Pro</h4><p>148 sold</p></div>
                        <div class="product-progress">
                            <div class="progress-bar"><div class="progress-fill" style="width:92%;background:#3498db;"></div></div>
                            <span>92%</span>
                        </div>
                    </div>
                    <div class="product-item">
                        <div class="product-info"><h4>Smart LED Bulb (4-pack)</h4><p>97 sold</p></div>
                        <div class="product-progress">
                            <div class="progress-bar"><div class="progress-fill" style="width:76%;background:#3498db;"></div></div>
                            <span>76%</span>
                        </div>
                    </div>
                    <div class="product-item">
                        <div class="product-info"><h4>Phone Fast Charger 65W</h4><p>82 sold</p></div>
                        <div class="product-progress">
                            <div class="progress-bar"><div class="progress-fill" style="width:68%;background:#3498db;"></div></div>
                            <span>68%</span>
                        </div>
                    </div>
                    <div class="product-item">
                        <div class="product-info"><h4>Reusable Water Bottle 1L</h4><p>65 sold</p></div>
                        <div class="product-progress">
                            <div class="progress-bar"><div class="progress-fill" style="width:54%;background:#3498db;"></div></div>
                            <span>54%</span>
                        </div>
                    </div>
                    <div class="product-item">
                        <div class="product-info"><h4>Portable Power Bank 20000mAh</h4><p>51 sold</p></div>
                        <div class="product-progress">
                            <div class="progress-bar"><div class="progress-fill" style="width:42%;background:#3498db;"></div></div>
                            <span>42%</span>
                        </div>
                    </div>
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

<!-- ── LOGOUT CONFIRMATION MODAL ── -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Sign Out</h3>
            <button class="modal-close" id="closeModal">×</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to sign out?</p>
            <p class="text-secondary">You will need to log in again to access your dashboard.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelLogout">Cancel</button>
            <a href="/seller/backend/auth/logout.php" class="btn btn-danger">Sign Out</a>
        </div>
    </div>
</div>

<script src="/seller/js/logout.js"></script>


 <script>

    // --------------------------------------------------------------------------------------------------
    // JS code to fetch and display products and orders count on the dashboard cards. 
    // This runs only once when the page loads.
        const sellerId = <?php echo json_encode($seller_id); ?>;
        
        async function loadData() {
            try {
                const response = await fetch(`/seller/backend/dashboard_backends/count_products.php?seller_id=${sellerId}`);
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('products-count').textContent = data.products_count;
                    document.getElementById('orders-count').textContent = data.orders_count;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        // Load data only once when page loads
        loadData();

// --------------------------------------------------------------------------------------------------
    // JS code to fetch and display recent orders in the "Recent Orders" table.
    //  This also runs once on page load.
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
                        } else if (order.status === 'shipped') {
                            statusClass += ' status-shipped';
                        } else {
                            statusClass += ' status-pending';
                        }
                        
                        tbody.innerHTML += `
                            <tr>
                                <td>#${String(order.order_id).padStart(5, '0')}</td>
                                <td>${order.customer_name || 'Guest'}</td>
                                <td>${order.products}</td>
                                <td>$${parseFloat(order.total_amount).toFixed(2)}</td>
                                <td><span class="${statusClass}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></td>
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
// -------------------------------------------------------------------------------------------------------------------

    </script>

</body>
</html>