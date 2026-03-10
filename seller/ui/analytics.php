<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/analytics.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="dashboard-container">

    <!-- Sidebar (static) -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Seller<span>Dashboard</span></h2>
        </div>

        <nav class="sidebar-nav">
            <a href="#" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="#" class="nav-item"><i class="fas fa-box"></i><span>Products</span></a>
            <a href="#" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Orders</span></a>
            <a href="#" class="nav-item active"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
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

    <!-- Main content -->
    <main class="main-content">

        <header class="main-header">
            <div>
                <h1>Sales Analytics</h1>
                <p>Detailed insights into your business performance</p>
            </div>
            <div class="header-right">
                <select class="period-selector">
                    <option>Last 7 days</option>
                    <option selected>Last 30 days</option>
                    <option>Last 3 months</option>
                    <option>Last 6 months</option>
                    <option>Last year</option>
                </select>
                <div class="date-display">March 9, 2026</div>
            </div>
        </header>

        <!-- Stat cards -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-info">
                    <h3>$18,420.50</h3>
                    <p>Total Revenue</p>
                </div>
                <div class="stat-trend positive"><i class="fas fa-arrow-up"></i> 14%</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e67e2220;color:#e67e22"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-info">
                    <h3>342</h3>
                    <p>Total Orders</p>
                </div>
                <div class="stat-trend positive"><i class="fas fa-arrow-up"></i> 9%</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#3498db20;color:#3498db"><i class="fas fa-calculator"></i></div>
                <div class="stat-info">
                    <h3>$53.86</h3>
                    <p>Avg Order Value</p>
                </div>
                <div class="stat-trend positive"><i class="fas fa-arrow-up"></i> 6%</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e67e2220;color:#e67e22"><i class="fas fa-chart-line"></i></div>
                <div class="stat-info">
                    <h3>26.8%</h3>
                    <p>Conversion Rate</p>
                </div>
                <div class="stat-trend negative"><i class="fas fa-arrow-down"></i> 1.4%</div>
            </div>
        </section>

        <!-- Charts -->
        <section class="content-section">
            <div class="chart-container">
                <div class="section-header"><h2>Monthly Revenue</h2></div>
                <div class="chart-placeholder"><canvas id="revenueChart"></canvas></div>
            </div>

            <div class="chart-container">
                <div class="section-header"><h2>Order Status Distribution</h2></div>
                <div class="chart-placeholder"><canvas id="statusChart"></canvas></div>
            </div>
        </section>

        <!-- Top products + insights -->
        <section class="content-section">
            <div class="recent-orders">
                <div class="section-header"><h2>Top Selling Products</h2></div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Units Sold</th>
                                <th>Revenue</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Wireless Earbuds Pro</td><td>148</td><td>$5,920.00</td><td><span class="status-badge status-delivered">Excellent</span></td></tr>
                            <tr><td>Smart LED Bulb (4-pack)</td><td>97</td><td>$2,328.00</td><td><span class="status-badge status-shipped">Good</span></td></tr>
                            <tr><td>Phone Fast Charger 65W</td><td>82</td><td>$1,722.00</td><td><span class="status-badge status-shipped">Good</span></td></tr>
                            <tr><td>Reusable Water Bottle 1L</td><td>65</td><td>$1,105.00</td><td><span class="status-badge status-pending">Average</span></td></tr>
                            <tr><td>Portable Power Bank 20000mAh</td><td>51</td><td>$1,428.00</td><td><span class="status-badge status-pending">Average</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <div class="section-header"><h2>Quick Insights</h2></div>
                <div class="insights-list">
                    <div class="insight-item">
                        <div class="stat-icon" style="background:#3498db20;color:#3498db"><i class="fas fa-calendar"></i></div>
                        <div><h3>Best Month</h3><p>February 2026</p></div>
                    </div>
                    <div class="insight-item">
                        <div class="stat-icon" style="background:#e67e2220;color:#e67e22"><i class="fas fa-clock"></i></div>
                        <div><h3>Pending Value</h3><p>$3,840.00</p></div>
                    </div>
                    <div class="insight-item">
                        <div class="stat-icon" style="background:#3498db20;color:#3498db"><i class="fas fa-truck"></i></div>
                        <div><h3>In Transit</h3><p>$2,190.00</p></div>
                    </div>
                    <div class="insight-item">
                        <div class="stat-icon" style="background:#e67e2220;color:#e67e22"><i class="fas fa-chart-pie"></i></div>
                        <div><h3>Top Performers</h3><p>5 products</p></div>
                    </div>
                </div>
            </div>
        </section>

    </main>
</div>

<script>
// Revenue Chart (bar)
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: ['Oct 25', 'Nov 25', 'Dec 25', 'Jan 26', 'Feb 26', 'Mar 26'],
        datasets: [{
            label: 'Revenue ($)',
            data: [12400, 9800, 15600, 14200, 19800, 6200],
            backgroundColor: 'rgba(52, 152, 219, 0.65)',
            borderColor: 'rgba(52, 152, 219, 0.9)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Status Distribution (doughnut)
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Shipped', 'Delivered', 'Cancelled'],
        datasets: [{
            data: [68, 94, 162, 18],
            backgroundColor: [
                'rgba(243, 156, 18, 0.65)',
                'rgba(52, 152, 219, 0.65)',
                'rgba(46, 204, 113, 0.65)',
                'rgba(231, 76, 60, 0.65)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>
</body>
</html>