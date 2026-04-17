<?php
// /seller/ui/my_plan.php
require_once __DIR__ . '/../backend/session/auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Plan - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #e67e22;
            --secondary-dark: #d35400;
            --success: #2ecc71;
            --danger: #e74c3c;
            --dark: #2c3e50;
            --gray: #95a5a6;
            --light: #ecf0f1;
            --bg-light: #f9fafb;
            --free: #27ae60;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f5f7fa;
            color: var(--dark);
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

        .avatar {
            width: 38px; height: 38px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: grid; place-items: center;
            font-weight: bold; font-size: 1.1rem;
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

        .header-left h1 { font-size: 1.8rem; font-weight: 600; }
        .header-left p { color: #7f8c8d; margin-top: 0.25rem; }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            flex-wrap: wrap;
        }

        .date-display { color: #7f8c8d; font-size: 0.95rem; white-space: nowrap; }

        /* Pricing Section */
        .pricing-section {
            background: var(--bg-light);
            border-radius: 16px;
            padding: 2rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .section-title h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .section-title p {
            color: var(--gray);
            font-size: 1rem;
        }

        /* Segmented Radio Toggle */
        .pricing-toggle {
            display: flex;
            justify-content: center;
            margin: 2rem 0 3rem;
        }

        .toggle-group {
            display: inline-flex;
            background: #f1f5f9;
            border-radius: 999px;
            padding: 0.5rem;
            box-shadow: inset 0 2px 6px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }

        .toggle-label {
            position: relative;
            cursor: pointer;
        }

        .toggle-label input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-option {
            padding: 0.75rem 1.8rem;
            font-weight: 600;
            font-size: 1rem;
            color: #64748b;
            border-radius: 999px;
            transition: all 0.25s ease;
            user-select: none;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .toggle-option:hover {
            color: #1e293b;
        }

        input[type="radio"]:checked + .toggle-option {
            background: var(--secondary);
            color: white;
            box-shadow: 0 3px 10px rgba(230, 126, 34, 0.3);
        }

        .save-pill {
            background: var(--success);
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.7rem;
            border-radius: 999px;
            box-shadow: 0 2px 6px rgba(46, 204, 113, 0.3);
        }

        /* Pricing Cards */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
        }

        .pricing-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.35s;
            position: relative;
        }

        .pricing-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        }

        .pricing-card.popular {
            border: 2px solid var(--secondary);
            transform: scale(1.02);
            z-index: 2;
        }

        .popular-badge {
            position: absolute;
            top: 18px;
            right: -45px;
            background: var(--secondary);
            color: white;
            padding: 0.4rem 3.2rem;
            font-size: 0.85rem;
            font-weight: 600;
            transform: rotate(45deg);
            z-index: 1;
        }

        .free-badge {
            position: absolute;
            top: 18px;
            left: -45px;
            background: var(--free);
            color: white;
            padding: 0.4rem 3rem;
            font-size: 0.85rem;
            font-weight: 600;
            transform: rotate(-45deg);
            z-index: 1;
        }

        .pricing-header {
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .pricing-header::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255,255,255,0.18) 0%,
                rgba(255,255,255,0) 50%
            );
            transform: rotate(30deg);
            pointer-events: none;
        }

        /* Bronze */
        .bronze .pricing-header {
            background: linear-gradient(135deg, #b45309, #92400e, #a66c2d);
        }
        .bronze .pricing-header h3 { color: #fef3c7; }

        /* Silver */
        .silver .pricing-header {
            background: linear-gradient(135deg, #e5e7eb, #9ca3af, #4b5563);
        }
        .silver .pricing-header h3 { color: #f3f4f6; }

        /* Gold */
        .gold .pricing-header {
            background: linear-gradient(135deg, #fcd34d, #fbbf24, #d97706);
        }
        .gold .pricing-header h3 { color: #fefce8; }

        .pricing-header h3 {
            font-size: 1.6rem;
            margin-bottom: 0.5rem;
        }

        .price {
            font-size: 3rem;
            font-weight: 700;
        }

        .price-period {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 0.3rem;
        }

        .price-free {
            font-size: 2.5rem;
            font-weight: 700;
            color: #27ae60;
        }

        .pricing-body {
            padding: 1.8rem;
        }

        .employee-count {
            background: var(--bg-light);
            padding: 0.9rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
            font-size: 1rem;
        }

        .feature-list {
            list-style: none;
            margin-bottom: 1.8rem;
            text-align: left;
        }

        .feature-list li {
            margin-bottom: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 0.95rem;
        }

        .feature-list i {
            font-size: 1.1rem;
            width: 20px;
        }

        .feature-list i.fa-check {
            color: var(--success);
        }

        .feature-list i.fa-times {
            color: #bdc3c7;
        }

        .feature-list .disabled i {
            color: #bdc3c7;
        }

        .feature-list .disabled {
            color: var(--gray);
        }

        .pricing-btn {
            display: block;
            width: 100%;
            padding: 0.9rem;
            color: white;
            text-align: center;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s;
        }

        .bronze .pricing-btn { background: var(--free); }
        .bronze .pricing-btn:hover { background: #219653; transform: translateY(-2px); }
        .silver .pricing-btn { background: var(--primary); }
        .silver .pricing-btn:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .gold .pricing-btn { background: var(--secondary); }
        .gold .pricing-btn:hover { background: var(--secondary-dark); transform: translateY(-2px); }

        .billing-period {
            display: none;
        }

        .billing-period.active {
            display: block;
        }

        /* Current Plan Badge */
        .current-plan-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--success);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 2;
        }

        footer {
            margin-top: 3rem;
            text-align: center;
            padding: 2rem 0;
            color: #95a5a6;
            font-size: 0.9rem;
            border-top: 1px solid #ebedf0;
        }

        @media (max-width: 1024px) {
            .pricing-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .dashboard-container { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .main-content { padding: 1.5rem; }
            .pricing-grid { grid-template-columns: 1fr; }
            .pricing-card.popular { transform: none; }
            .popular-badge, .free-badge { font-size: 0.75rem; padding: 0.3rem 2.5rem; }
            .toggle-option { padding: 0.6rem 1.2rem; font-size: 0.9rem; }
            .section-title h2 { font-size: 1.6rem; }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Seller<span>Dashboard</span></h2>
        </div>
        <nav class="sidebar-nav">
            <a href="/seller/ui/dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="/seller/ui/products.php" class="nav-item"><i class="fas fa-box"></i><span>Products</span></a>
            <a href="/seller/ui/orders.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Orders</span></a>
            <a href="/seller/ui/employees.php" class="nav-item"><i class="fas fa-users"></i><span>Employees</span></a>
            <a href="/seller/ui/my_plan.php" class="nav-item active"><i class="fas fa-crown"></i><span>My Plan</span></a>
            <a href="#" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
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
                <h1>My Plan</h1>
                <p>Choose the perfect plan for your business</p>
            </div>
            <div class="header-right">
                <div class="date-display" id="dateDisplay"></div>
            </div>
        </header>

        <!-- Pricing Section -->
        <section class="pricing-section">
            <div class="section-title">
                <h2>Choose Your Perfect Plan</h2>
                <p>Start free — upgrade when you're ready to grow</p>
            </div>

            <!-- Segmented Radio Toggle -->
            <div class="pricing-toggle">
                <div class="toggle-group">
                    <label class="toggle-label">
                        <input type="radio" name="billing" value="monthly" id="monthlyRadio" checked />
                        <span class="toggle-option">Monthly</span>
                    </label>

                    <label class="toggle-label">
                        <input type="radio" name="billing" value="yearly" id="yearlyRadio" />
                        <span class="toggle-option">
                            Yearly <span class="save-pill">Save 16%</span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Monthly Plans -->
            <div id="monthlyPlans" class="billing-period active">
                <div class="pricing-grid">
                    <!-- Bronze -->
                    <div class="pricing-card bronze">
                        <div class="free-badge">FREE</div>
                        <div class="pricing-header">
                            <h3>Bronze</h3>
                            <div class="price-free">₱0</div>
                            <div class="price-period">forever</div>
                        </div>
                        <div class="pricing-body">
                            <div class="employee-count">
                                <i class="fas fa-users"></i> 3 employees
                            </div>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i> Up to 50 products</li>
                                <li><i class="fas fa-check"></i> Standard product visibility</li>
                                <li class="disabled"><i class="fas fa-times"></i> Featured products</li>
                                <li class="disabled"><i class="fas fa-times"></i> Priority in search</li>
                            </ul>
                            <a href="#" class="pricing-btn">Current Plan</a>
                        </div>
                    </div>

                    <!-- Silver -->
                    <div class="pricing-card popular silver">
                        <div class="popular-badge">MOST POPULAR</div>
                        <div class="pricing-header">
                            <h3>Silver</h3>
                            <div class="price">₱300</div>
                            <div class="price-period">per month</div>
                        </div>
                        <div class="pricing-body">
                            <div class="employee-count">
                                <i class="fas fa-users"></i> Up to 10 employees
                            </div>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i> Up to 500 products</li>
                                <li><i class="fas fa-check"></i> Products can be featured</li>
                                <li><i class="fas fa-check"></i> Top 10 best-selling products can be featured</li>
                                <li><i class="fas fa-check"></i> Standard + improved visibility</li>
                            </ul>
                            <a href="#" class="pricing-btn">Upgrade to Silver</a>
                        </div>
                    </div>

                    <!-- Gold -->
                    <div class="pricing-card gold">
                        <div class="pricing-header">
                            <h3>Gold</h3>
                            <div class="price">₱800</div>
                            <div class="price-period">per month</div>
                        </div>
                        <div class="pricing-body">
                            <div class="employee-count">
                                <i class="fas fa-users"></i> Unlimited employees
                            </div>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i> Unlimited products</li>
                                <li><i class="fas fa-check"></i> Product priority in search</li>
                                <li><i class="fas fa-check"></i> Top 100 best-selling products can be featured</li>
                                <li><i class="fas fa-check"></i> Maximum visibility & exposure</li>
                            </ul>
                            <a href="#" class="pricing-btn">Upgrade to Gold</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yearly Plans -->
            <div id="yearlyPlans" class="billing-period">
                <div class="pricing-grid">
                    <div class="pricing-card bronze">
                        <div class="free-badge">FREE</div>
                        <div class="pricing-header">
                            <h3>Bronze</h3>
                            <div class="price-free">₱0</div>
                            <div class="price-period">forever</div>
                        </div>
                        <div class="pricing-body">
                            <div class="employee-count">
                                <i class="fas fa-users"></i> 3 employees
                            </div>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i> Up to 50 products</li>
                                <li><i class="fas fa-check"></i> Standard visibility</li>
                                <li class="disabled"><i class="fas fa-times"></i> Featured products</li>
                                <li class="disabled"><i class="fas fa-times"></i> Priority in search</li>
                            </ul>
                            <a href="#" class="pricing-btn">Current Plan</a>
                        </div>
                    </div>

                    <div class="pricing-card popular silver">
                        <div class="popular-badge">BEST VALUE</div>
                        <div class="pricing-header">
                            <h3>Silver</h3>
                            <div class="price">₱3,000</div>
                            <div class="price-period">per year <small>(₱250 / mo)</small></div>
                        </div>
                        <div class="pricing-body">
                            <div class="employee-count">
                                <i class="fas fa-users"></i> Up to 10 employees
                            </div>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i> Up to 500 products</li>
                                <li><i class="fas fa-check"></i> Products can be featured</li>
                                <li><i class="fas fa-check"></i> Top 10 best-selling products can be featured</li>
                                <li><i class="fas fa-check"></i> Standard + improved visibility</li>
                            </ul>
                            <a href="#" class="pricing-btn">Upgrade to Silver</a>
                        </div>
                    </div>

                    <div class="pricing-card gold">
                        <div class="pricing-header">
                            <h3>Gold</h3>
                            <div class="price">₱8,000</div>
                            <div class="price-period">per year <small>(₱667 / mo)</small></div>
                        </div>
                        <div class="pricing-body">
                            <div class="employee-count">
                                <i class="fas fa-users"></i> Unlimited employees
                            </div>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i> Unlimited products</li>
                                <li><i class="fas fa-check"></i> Product priority in search</li>
                                <li><i class="fas fa-check"></i> Top 100 best-selling products can be featured</li>
                                <li><i class="fas fa-check"></i> Maximum visibility & exposure</li>
                            </ul>
                            <a href="#" class="pricing-btn">Upgrade to Gold</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer>
            © 2026 Seller Dashboard. All rights reserved.
        </footer>
    </main>
</div>

<script>
// Set current date
document.getElementById('dateDisplay').textContent = new Date().toLocaleDateString('en-US', { 
    year: 'numeric', month: 'long', day: 'numeric' 
});

// Toggle between monthly and yearly plans
const monthlyRadio = document.getElementById('monthlyRadio');
const yearlyRadio = document.getElementById('yearlyRadio');
const monthlyPlans = document.getElementById('monthlyPlans');
const yearlyPlans = document.getElementById('yearlyPlans');

monthlyRadio.addEventListener('change', function() {
    if (this.checked) {
        monthlyPlans.classList.add('active');
        yearlyPlans.classList.remove('active');
    }
});

yearlyRadio.addEventListener('change', function() {
    if (this.checked) {
        yearlyPlans.classList.add('active');
        monthlyPlans.classList.remove('active');
    }
});
</script>

</body>
</html>