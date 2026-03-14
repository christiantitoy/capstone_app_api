<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/orders.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>

<div class="dashboard-container">

    <!-- Sidebar ── identical to your other pages -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Seller<span>Dashboard</span></h2>
        </div>

        <nav class="sidebar-nav">
            <a href="/seller/ui/dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="/seller/ui/products.php" class="nav-item"><i class="fas fa-box"></i><span>Products</span></a>
            <a href="/seller/ui/orders.php" class="nav-item active"><i class="fas fa-shopping-cart"></i><span>Orders</span></a>
            <a href="/seller/ui/employees.php" class="nav-item"><i class="fas fa-users"></i><span>Employees</span></a>
            <a href="/seller/ui/analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
            <a href="#" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar">T</div>
                <div>
                    <h4>Titoy</h4>
                    <p>Seller Account</p>
                </div>
            </div>
            <a href="#" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <header class="main-header">
            <div class="header-left">
                <h1>Orders Management</h1>
                <p>Manage customer orders and fulfillment</p>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search orders...">
                </div>
                <div class="date-display">March 9, 2026</div>
            </div>
        </header>

        <div class="orders-header">
            <h2>Customer Orders (12)</h2>
            <select class="status-select">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>#ORD-00456</strong>
                            <button class="toggle-details"><i class="fas fa-chevron-down"></i> Details</button>
                        </td>
                        <td>Maria Santos</td>
                        <td>Wireless Earbuds Pro</td>
                        <td>2</td>
                        <td>₱2,598.00</td>
                        <td>Mar 8, 2026</td>
                        <td><span class="status-badge status-delivered">Delivered</span></td>
                        <td>
                            <select>
                                <option selected>Delivered</option>
                                <option>Shipped</option>
                                <option>Pending</option>
                                <option>Cancelled</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="order-details">
                        <td colspan="8">
                            <div class="order-details active">
                                <div class="details-grid">
                                    <div class="detail-item">
                                        <div class="detail-label">Order Number</div>
                                        <div class="detail-value">#ORD-00456</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Customer</div>
                                        <div class="detail-value">Maria Santos</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Product</div>
                                        <div class="detail-value">Wireless Earbuds Pro</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Unit Price</div>
                                        <div class="detail-value">₱1,299.00</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Quantity</div>
                                        <div class="detail-value">2 pcs</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Total</div>
                                        <div class="detail-value">₱2,598.00</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Order Date</div>
                                        <div class="detail-value">March 8, 2026 2:45 PM</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Status</div>
                                        <div class="detail-value"><span class="status-badge status-delivered">Delivered</span></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- More demo rows -->
                    <tr>
                        <td><strong>#ORD-00455</strong><button class="toggle-details"><i class="fas fa-chevron-down"></i> Details</button></td>
                        <td>Juan Dela Cruz</td>
                        <td>Smart LED Bulb (4-pack)</td>
                        <td>1</td>
                        <td>₱899.00</td>
                        <td>Mar 7, 2026</td>
                        <td><span class="status-badge status-shipped">Shipped</span></td>
                        <td>
                            <select>
                                <option selected>Shipped</option>
                                <option>Delivered</option>
                                <option>Pending</option>
                                <option>Cancelled</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td><strong>#ORD-00454</strong><button class="toggle-details"><i class="fas fa-chevron-down"></i> Details</button></td>
                        <td>Ana Reyes</td>
                        <td>65W Fast Charger</td>
                        <td>3</td>
                        <td>₱1,797.00</td>
                        <td>Mar 6, 2026</td>
                        <td><span class="status-badge status-pending">Pending</span></td>
                        <td>
                            <select>
                                <option selected>Pending</option>
                                <option>Shipped</option>
                                <option>Delivered</option>
                                <option>Cancelled</option>
                            </select>
                        </td>
                    </tr>

                    <!-- You can add more static rows if needed -->
                </tbody>
            </table>
        </div>

        <!-- No orders placeholder (uncomment if needed) -->
        <!--
        <div class="no-orders">
            <i class="fas fa-shopping-cart"></i>
            <h3>No Orders Yet</h3>
            <p>You haven't received any orders yet. Start selling your products!</p>
        </div>
        -->

        <footer style="margin-top: 3rem; text-align: center; padding: 2rem 0; color: #95a5a6; font-size: 0.9rem; border-top: 1px solid #ebedf0;">
            © 2026 Seller Dashboard. All rights reserved.
        </footer>

    </main>
</div>



</body>
</html>