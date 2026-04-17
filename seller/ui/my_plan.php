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
            --warning: #f39c12;
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

        /* Pending Payment Alert */
        .pending-alert {
            background: linear-gradient(135deg, #fff3e0, #ffe8cc);
            border-left: 4px solid var(--warning);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pending-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .pending-icon {
            width: 48px;
            height: 48px;
            background: var(--warning);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }

        .pending-text h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #e65100;
        }

        .pending-text p {
            font-size: 0.85rem;
            color: #bf360c;
        }

        .pending-amount {
            text-align: right;
        }

        .pending-amount .amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: #e65100;
        }

        .pending-amount .label {
            font-size: 0.7rem;
            color: #bf360c;
        }

        .pay-now-btn {
            background: var(--warning);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pay-now-btn:hover {
            background: #e65100;
            transform: translateY(-2px);
        }

        /* Current Plan Card */
        .current-plan-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .plan-badge {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .plan-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .plan-details h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .plan-details p {
            font-size: 0.8rem;
            color: var(--gray);
        }

        .plan-status {
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-expired {
            background: #ffebee;
            color: #c62828;
        }

        .status-pending {
            background: #fff3e0;
            color: #e65100;
        }

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
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .section-title p {
            color: var(--gray);
            font-size: 0.95rem;
        }

        /* Segmented Radio Toggle */
        .pricing-toggle {
            display: flex;
            justify-content: center;
            margin: 1.5rem 0 2rem;
        }

        .toggle-group {
            display: inline-flex;
            background: #f1f5f9;
            border-radius: 999px;
            padding: 0.4rem;
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
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: #64748b;
            border-radius: 999px;
            transition: all 0.25s ease;
            user-select: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
        }

        /* Horizontal Rectangle Cards */
        .pricing-grid {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .pricing-card {
            background: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.3s;
            position: relative;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pricing-card:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }

        .pricing-card.popular {
            border: 2px solid var(--secondary);
            background: linear-gradient(135deg, white, #fff8f0);
        }

        .popular-badge {
            position: absolute;
            top: -10px;
            left: 20px;
            background: var(--secondary);
            color: white;
            padding: 0.2rem 1rem;
            font-size: 0.7rem;
            font-weight: 600;
            border-radius: 20px;
        }

        .free-badge {
            position: absolute;
            top: -10px;
            left: 20px;
            background: var(--free);
            color: white;
            padding: 0.2rem 1rem;
            font-size: 0.7rem;
            font-weight: 600;
            border-radius: 20px;
        }

        .card-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex: 2;
        }

        .plan-color {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
        }

        .bronze-color { background: linear-gradient(135deg, #cd7f32, #a0522d); }
        .silver-color { background: linear-gradient(135deg, #c0c0c0, #808080); }
        .gold-color { background: linear-gradient(135deg, #ffd700, #daa520); }

        .plan-info h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .plan-info .price {
            font-size: 0.85rem;
            color: var(--gray);
        }

        .price-free-text {
            color: var(--free);
            font-weight: 600;
        }

        .card-middle {
            flex: 3;
        }

        .feature-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .feature-badge {
            background: var(--bg-light);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            color: var(--dark);
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .feature-badge i {
            font-size: 0.65rem;
        }

        .feature-badge .fa-check { color: var(--success); }
        .feature-badge .fa-times { color: var(--danger); }

        .card-right {
            text-align: right;
            min-width: 140px;
        }

        .pricing-btn {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-current {
            background: #e0e0e0;
            color: #666;
            cursor: default;
        }

        .btn-upgrade {
            background: var(--primary);
            color: white;
        }

        .btn-upgrade:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-silver {
            background: var(--primary);
        }

        .btn-gold {
            background: var(--secondary);
        }

        .btn-gold:hover {
            background: var(--secondary-dark);
        }

        .billing-period {
            display: none;
        }

        .billing-period.active {
            display: block;
        }

        footer {
            margin-top: 2rem;
            text-align: center;
            padding: 2rem 0;
            color: #95a5a6;
            font-size: 0.85rem;
            border-top: 1px solid #ebedf0;
        }

        @media (max-width: 900px) {
            .dashboard-container { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .main-content { padding: 1.5rem; }
            .pricing-card { flex-direction: column; text-align: center; }
            .card-left { flex-direction: column; text-align: center; }
            .card-middle { text-align: center; }
            .card-right { text-align: center; }
            .feature-badges { justify-content: center; }
            .pending-alert { flex-direction: column; text-align: center; }
            .pending-info { justify-content: center; }
            .pending-amount { text-align: center; }
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
                <p>Manage your subscription and billing</p>
            </div>
            <div class="header-right">
                <div class="date-display" id="dateDisplay"></div>
            </div>
        </header>

        <!-- Pending Payment Alert -->
        <div class="pending-alert" id="pendingAlert" style="display: none;">
            <div class="pending-info">
                <div class="pending-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="pending-text">
                    <h3>Pending Payment</h3>
                    <p>Your plan upgrade requires payment confirmation</p>
                </div>
            </div>
            <div class="pending-amount">
                <div class="amount" id="pendingAmount">₱300.00</div>
                <div class="label">due for Silver Plan (Monthly)</div>
            </div>
            <a href="/seller/ui/payment.php?amount=300&plan=silver&billing=monthly" class="pay-now-btn">
                <i class="fas fa-credit-card"></i> Pay Now
            </a>
        </div>

        <!-- Current Plan Card -->
        <div class="current-plan-card">
            <div class="plan-badge">
                <div class="plan-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="plan-details">
                    <h3 id="currentPlanName">Bronze Plan</h3>
                    <p id="currentPlanDesc">Free forever · 3 employees · Up to 50 products</p>
                </div>
            </div>
            <div class="plan-status">
                <span class="status-badge status-active" id="currentPlanStatus">
                    <i class="fas fa-check-circle"></i> Active
                </span>
                <p id="planExpiry" style="font-size: 0.7rem; color: var(--gray); margin-top: 0.3rem;"></p>
            </div>
        </div>

        <!-- Pricing Section -->
        <section class="pricing-section">
            <div class="section-title">
                <h2>Upgrade Your Plan</h2>
                <p>Get more features and grow your business</p>
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
                        <div class="free-badge">CURRENT PLAN</div>
                        <div class="card-left">
                            <div class="plan-color bronze-color">
                                <i class="fas fa-medal"></i>
                            </div>
                            <div class="plan-info">
                                <h3>Bronze</h3>
                                <div class="price"><span class="price-free-text">₱0</span> / forever</div>
                            </div>
                        </div>
                        <div class="card-middle">
                            <div class="feature-badges">
                                <span class="feature-badge"><i class="fas fa-check"></i> 3 employees</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> 50 products</span>
                                <span class="feature-badge"><i class="fas fa-times"></i> Featured products</span>
                            </div>
                        </div>
                        <div class="card-right">
                            <span class="pricing-btn btn-current">Current Plan</span>
                        </div>
                    </div>

                    <!-- Silver -->
                    <div class="pricing-card popular silver" data-plan="silver" data-monthly-price="300" data-yearly-price="3000">
                        <div class="popular-badge">MOST POPULAR</div>
                        <div class="card-left">
                            <div class="plan-color silver-color">
                                <i class="fas fa-gem"></i>
                            </div>
                            <div class="plan-info">
                                <h3>Silver</h3>
                                <div class="price monthly-price">₱300 <span class="price-period">/ month</span></div>
                                <div class="price yearly-price" style="display: none;">₱3,000 <span class="price-period">/ year</span></div>
                            </div>
                        </div>
                        <div class="card-middle">
                            <div class="feature-badges">
                                <span class="feature-badge"><i class="fas fa-check"></i> 10 employees</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> 100 products</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Featured products</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Improved visibility</span>
                            </div>
                        </div>
                        <div class="card-right">
                            <button class="pricing-btn btn-upgrade" onclick="upgradePlan('silver', 'monthly', 300)">Upgrade to Silver</button>
                        </div>
                    </div>

                    <!-- Gold -->
                    <div class="pricing-card gold" data-plan="gold" data-monthly-price="800" data-yearly-price="8000">
                        <div class="card-left">
                            <div class="plan-color gold-color">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="plan-info">
                                <h3>Gold</h3>
                                <div class="price monthly-price">₱800 <span class="price-period">/ month</span></div>
                                <div class="price yearly-price" style="display: none;">₱8,000 <span class="price-period">/ year</span></div>
                            </div>
                        </div>
                        <div class="card-middle">
                            <div class="feature-badges">
                                <span class="feature-badge"><i class="fas fa-check"></i> Unlimited employees</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Unlimited products</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Priority in search</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Maximum visibility</span>
                            </div>
                        </div>
                        <div class="card-right">
                            <button class="pricing-btn btn-upgrade btn-gold" onclick="upgradePlan('gold', 'monthly', 800)">Upgrade to Gold</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yearly Plans -->
            <div id="yearlyPlans" class="billing-period">
                <div class="pricing-grid">
                    <!-- Bronze Yearly (same) -->
                    <div class="pricing-card bronze">
                        <div class="free-badge">CURRENT PLAN</div>
                        <div class="card-left">
                            <div class="plan-color bronze-color">
                                <i class="fas fa-medal"></i>
                            </div>
                            <div class="plan-info">
                                <h3>Bronze</h3>
                                <div class="price"><span class="price-free-text">₱0</span> / forever</div>
                            </div>
                        </div>
                        <div class="card-middle">
                            <div class="feature-badges">
                                <span class="feature-badge"><i class="fas fa-check"></i> 3 employees</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> 50 products</span>
                                <span class="feature-badge"><i class="fas fa-times"></i> Featured products</span>
                            </div>
                        </div>
                        <div class="card-right">
                            <span class="pricing-btn btn-current">Current Plan</span>
                        </div>
                    </div>

                    <!-- Silver Yearly -->
                    <div class="pricing-card popular silver">
                        <div class="popular-badge">BEST VALUE</div>
                        <div class="card-left">
                            <div class="plan-color silver-color">
                                <i class="fas fa-gem"></i>
                            </div>
                            <div class="plan-info">
                                <h3>Silver</h3>
                                <div class="price">₱3,000 <span class="price-period">/ year</span></div>
                                <div class="price" style="font-size: 0.7rem; color: var(--success);">Save ₱600</div>
                            </div>
                        </div>
                        <div class="card-middle">
                            <div class="feature-badges">
                                <span class="feature-badge"><i class="fas fa-check"></i> 10 employees</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> 100 products</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Featured products</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Improved visibility</span>
                            </div>
                        </div>
                        <div class="card-right">
                            <button class="pricing-btn btn-upgrade" onclick="upgradePlan('silver', 'yearly', 3000)">Upgrade to Silver</button>
                        </div>
                    </div>

                    <!-- Gold Yearly -->
                    <div class="pricing-card gold">
                        <div class="card-left">
                            <div class="plan-color gold-color">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="plan-info">
                                <h3>Gold</h3>
                                <div class="price">₱8,000 <span class="price-period">/ year</span></div>
                                <div class="price" style="font-size: 0.7rem; color: var(--success);">Save ₱1,600</div>
                            </div>
                        </div>
                        <div class="card-middle">
                            <div class="feature-badges">
                                <span class="feature-badge"><i class="fas fa-check"></i> Unlimited employees</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Unlimited products</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Priority in search</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Maximum visibility</span>
                            </div>
                        </div>
                        <div class="card-right">
                            <button class="pricing-btn btn-upgrade btn-gold" onclick="upgradePlan('gold', 'yearly', 8000)">Upgrade to Gold</button>
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

function updatePriceDisplay() {
    const isMonthly = monthlyRadio.checked;
    
    // Update price displays in cards
    document.querySelectorAll('.pricing-card').forEach(card => {
        const monthlyPrice = card.querySelector('.monthly-price');
        const yearlyPrice = card.querySelector('.yearly-price');
        
        if (monthlyPrice && yearlyPrice) {
            if (isMonthly) {
                monthlyPrice.style.display = 'block';
                yearlyPrice.style.display = 'none';
            } else {
                monthlyPrice.style.display = 'none';
                yearlyPrice.style.display = 'block';
            }
        }
    });
}

monthlyRadio.addEventListener('change', function() {
    if (this.checked) {
        monthlyPlans.classList.add('active');
        yearlyPlans.classList.remove('active');
        updatePriceDisplay();
    }
});

yearlyRadio.addEventListener('change', function() {
    if (this.checked) {
        yearlyPlans.classList.add('active');
        monthlyPlans.classList.remove('active');
        updatePriceDisplay();
    }
});

// Upgrade plan function
function upgradePlan(plan, billing, amount) {
    // You can show a confirmation modal here
    if (confirm(`Upgrade to ${plan.charAt(0).toUpperCase() + plan.slice(1)} Plan (${billing}) for ₱${amount.toFixed(2)}?`)) {
        // Redirect to payment page
        window.location.href = `/seller/ui/payment.php?amount=${amount}&plan=${plan}&billing=${billing}`;
    }
}

// Show pending alert if there's a pending payment (example - can be dynamic from backend)
function showPendingAlert(plan, amount) {
    const alertDiv = document.getElementById('pendingAlert');
    const pendingAmountSpan = document.getElementById('pendingAmount');
    const payNowBtn = document.getElementById('payNowBtn');
    
    pendingAmountSpan.textContent = `₱${amount.toFixed(2)}`;
    alertDiv.style.display = 'flex';
}

// Example: Uncomment to test pending alert
// showPendingAlert('silver', 300);
</script>

</body>
</html>