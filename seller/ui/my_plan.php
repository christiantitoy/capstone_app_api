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
    <link rel="stylesheet" href="../css/my_plan.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/logout.css?v=<?= time() ?>">
    <style>
        .pricing-btn.btn-downgrade {
            background: var(--danger, #dc3545);
            color: white;
            border: none;
        }
        .pricing-btn.btn-downgrade:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
        }
        .pricing-btn.btn-switch {
            background: var(--warning, #ffc107);
            color: #333;
            border: none;
        }
        .pricing-btn.btn-switch:hover {
            background: #e0a800;
            transform: translateY(-2px);
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-pending i {
            color: #856404;
        }
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
            max-width: 350px;
        }
        .toast-notification.success {
            border-left: 4px solid #27ae60;
        }
        .toast-notification.error {
            border-left: 4px solid #e74c3c;
        }
        .toast-notification i {
            font-size: 20px;
        }
        .toast-notification.success i {
            color: #27ae60;
        }
        .toast-notification.error i {
            color: #e74c3c;
        }
        .toast-notification span {
            flex: 1;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
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
            <a href="/seller/ui/sales.php" class="nav-item"><i class="fas fa-chart-line"></i><span>Sales</span></a>
            <a href="/seller/ui/payouts.php" class="nav-item"><i class="fas fa-money-bill-wave"></i><span>Payouts</span></a>
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
                    <p id="currentPlanBilling" style="font-size: 0.75rem; color: var(--gray); margin-top: 0.25rem;">
                        <i class="fas fa-calendar-alt"></i> <span id="billingText">Lifetime</span>
                    </p>
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

            <div class="pricing-toggle">
                <div class="toggle-group">
                    <label class="toggle-label">
                        <input type="radio" name="billing" value="monthly" id="monthlyRadio" checked />
                        <span class="toggle-option">Monthly</span>
                    </label>
                    <label class="toggle-label">
                        <input type="radio" name="billing" value="yearly" id="yearlyRadio" />
                        <span class="toggle-option">Yearly <span class="save-pill">Save 16%</span></span>
                    </label>
                </div>
            </div>

            <!-- Monthly Plans -->
            <div id="monthlyPlans" class="billing-period active">
                <div class="pricing-grid">
                    <div class="pricing-card bronze" data-plan="bronze" data-billing="lifetime" data-price="0">
                        <div class="free-badge">CURRENT PLAN</div>
                        <div class="card-left">
                            <div class="plan-color bronze-color"><i class="fas fa-medal"></i></div>
                            <div class="plan-info"><h3>Bronze</h3><div class="price"><span class="price-free-text">₱0</span> / forever</div></div>
                        </div>
                        <div class="card-middle">
                            <div class="feature-badges">
                                <span class="feature-badge"><i class="fas fa-check"></i> 3 employees</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> 50 products</span>
                                <span class="feature-badge"><i class="fas fa-times"></i> Featured products</span>
                            </div>
                        </div>
                        <div class="card-right"><span class="pricing-btn btn-current">Current Plan</span></div>
                    </div>
                    <div class="pricing-card popular silver" data-plan="silver" data-billing="monthly" data-price="300">
                        <div class="popular-badge">MOST POPULAR</div>
                        <div class="card-left">
                            <div class="plan-color silver-color"><i class="fas fa-gem"></i></div>
                            <div class="plan-info"><h3>Silver</h3><div class="price monthly-price">₱300 <span class="price-period">/ month</span></div><div class="price yearly-price" style="display: none;">₱3,000 <span class="price-period">/ year</span></div></div>
                        </div>
                        <div class="card-middle">
                            <div class="feature-badges">
                                <span class="feature-badge"><i class="fas fa-check"></i> 10 employees</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> 100 products</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Featured products</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Improved visibility</span>
                            </div>
                        </div>
                        <div class="card-right"><button class="pricing-btn btn-upgrade">Upgrade to Silver</button></div>
                    </div>
                    <div class="pricing-card gold" data-plan="gold" data-billing="monthly" data-price="800">
                        <div class="card-left">
                            <div class="plan-color gold-color"><i class="fas fa-crown"></i></div>
                            <div class="plan-info"><h3>Gold</h3><div class="price monthly-price">₱800 <span class="price-period">/ month</span></div><div class="price yearly-price" style="display: none;">₱8,000 <span class="price-period">/ year</span></div></div>
                        </div>
                        <div class="card-middle">
                            <div class="feature-badges">
                                <span class="feature-badge"><i class="fas fa-check"></i> Unlimited employees</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Unlimited products</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Priority in search</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Maximum visibility</span>
                            </div>
                        </div>
                        <div class="card-right"><button class="pricing-btn btn-upgrade btn-gold">Upgrade to Gold</button></div>
                    </div>
                </div>
            </div>

            <!-- Yearly Plans -->
            <div id="yearlyPlans" class="billing-period">
                <div class="pricing-grid">
                    <div class="pricing-card bronze" data-plan="bronze" data-billing="lifetime" data-price="0">
                        <div class="free-badge">CURRENT PLAN</div>
                        <div class="card-left">
                            <div class="plan-color bronze-color"><i class="fas fa-medal"></i></div>
                            <div class="plan-info"><h3>Bronze</h3><div class="price"><span class="price-free-text">₱0</span> / forever</div></div>
                        </div>
                        <div class="card-middle">
                            <div class="feature-badges">
                                <span class="feature-badge"><i class="fas fa-check"></i> 3 employees</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> 50 products</span>
                                <span class="feature-badge"><i class="fas fa-times"></i> Featured products</span>
                            </div>
                        </div>
                        <div class="card-right"><span class="pricing-btn btn-current">Current Plan</span></div>
                    </div>
                    <div class="pricing-card popular silver" data-plan="silver" data-billing="yearly" data-price="3000">
                        <div class="popular-badge">BEST VALUE</div>
                        <div class="card-left">
                            <div class="plan-color silver-color"><i class="fas fa-gem"></i></div>
                            <div class="plan-info"><h3>Silver</h3><div class="price">₱3,000 <span class="price-period">/ year</span></div><div class="price" style="font-size: 0.7rem; color: var(--success);">Save ₱600</div></div>
                        </div>
                        <div class="card-middle">
                            <div class="feature-badges">
                                <span class="feature-badge"><i class="fas fa-check"></i> 10 employees</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> 100 products</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Featured products</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Improved visibility</span>
                            </div>
                        </div>
                        <div class="card-right"><button class="pricing-btn btn-upgrade">Upgrade to Silver</button></div>
                    </div>
                    <div class="pricing-card gold" data-plan="gold" data-billing="yearly" data-price="8000">
                        <div class="card-left">
                            <div class="plan-color gold-color"><i class="fas fa-crown"></i></div>
                            <div class="plan-info"><h3>Gold</h3><div class="price">₱8,000 <span class="price-period">/ year</span></div><div class="price" style="font-size: 0.7rem; color: var(--success);">Save ₱1,600</div></div>
                        </div>
                        <div class="card-middle">
                            <div class="feature-badges">
                                <span class="feature-badge"><i class="fas fa-check"></i> Unlimited employees</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Unlimited products</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Priority in search</span>
                                <span class="feature-badge"><i class="fas fa-check"></i> Maximum visibility</span>
                            </div>
                        </div>
                        <div class="card-right"><button class="pricing-btn btn-upgrade btn-gold">Upgrade to Gold</button></div>
                    </div>
                </div>
            </div>
        </section>

        <footer>© 2026 Seller Dashboard. All rights reserved.</footer>
    </main>
</div>

<!-- Plan Confirmation Modal -->
<div id="planConfirmModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3 id="modalTitle">Confirm Plan Change</h3><button class="modal-close" onclick="closeModal()">×</button></div>
        <div class="modal-body">
            <div class="plan-change-info">
                <div class="from-plan"><strong>Current:</strong><br><span id="currentPlanModal">Bronze Plan</span></div>
                <div class="arrow">→</div>
                <div class="to-plan"><strong>New:</strong><br><span id="newPlanModal">Silver Plan (Monthly)</span></div>
            </div>
            <div class="price-info"><p>You will be charged <strong id="modalAmount">₱300.00</strong> <span id="modalBillingPeriod">per month</span></p></div>
            <div class="warning-note" id="downgradeWarning" style="display: none;"><i class="fas fa-exclamation-triangle"></i><p>Downgrading will take effect at the end of your current billing period.</p></div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <button class="btn-confirm" id="confirmBtn" onclick="proceedWithPlanChange()">Confirm Upgrade</button>
        </div>
    </div>
</div>

<!-- Logout Modal -->
<div class="logout-modal-overlay" id="logoutModal">
    <div class="logout-modal-content">
        <div class="logout-modal-header"><h3>Sign Out</h3><button class="logout-modal-close" id="closeModal">×</button></div>
        <div class="logout-modal-body"><p>Are you sure you want to sign out?</p><p class="logout-text-secondary">You will need to log in again to access your dashboard.</p></div>
        <div class="logout-modal-footer">
            <button class="logout-btn2 logout-btn2-secondary" id="cancelLogout">Cancel</button>
            <a href="/seller/backend/auth/logout.php" class="logout-btn2 logout-btn2-danger">Sign Out</a>
        </div>
    </div>
</div>

<script src="/seller/js/logout.js"></script>

<script>
let currentPlanData = null;
let pendingPlanChange = {};

document.getElementById('dateDisplay').textContent = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

const monthlyRadio = document.getElementById('monthlyRadio');
const yearlyRadio = document.getElementById('yearlyRadio');
const monthlyPlans = document.getElementById('monthlyPlans');
const yearlyPlans = document.getElementById('yearlyPlans');

function updatePriceDisplay() {
    const isMonthly = monthlyRadio.checked;
    document.querySelectorAll('.pricing-card').forEach(card => {
        const monthlyPriceEl = card.querySelector('.monthly-price');
        const yearlyPriceEl = card.querySelector('.yearly-price');
        if (monthlyPriceEl && yearlyPriceEl) {
            monthlyPriceEl.style.display = isMonthly ? 'block' : 'none';
            yearlyPriceEl.style.display = isMonthly ? 'none' : 'block';
        }
    });
}

monthlyRadio.addEventListener('change', () => { monthlyPlans.classList.add('active'); yearlyPlans.classList.remove('active'); updatePriceDisplay(); });
yearlyRadio.addEventListener('change', () => { yearlyPlans.classList.add('active'); monthlyPlans.classList.remove('active'); updatePriceDisplay(); });

function checkIfDowngrade(newPlan, newBilling) {
    if (!currentPlanData) return false;
    const currentPlan = currentPlanData.official_plan;
    const currentBilling = currentPlanData.official_billing;
    const planRank = { 'bronze': 1, 'silver': 2, 'gold': 3 };
    const billingMultiplier = { 'monthly': 1, 'yearly': 12, 'lifetime': 999 };
    const currentRank = planRank[currentPlan] || 1;
    const newRank = planRank[newPlan] || 1;
    if (newRank < currentRank) return true;
    if (newRank === currentRank) {
        const currentValue = billingMultiplier[currentBilling] || 1;
        const newValue = billingMultiplier[newBilling] || 1;
        return newValue < currentValue;
    }
    return false;
}

function checkIfBillingChange(newPlan, newBilling) {
    if (!currentPlanData) return false;
    return (newPlan === currentPlanData.official_plan && newBilling !== currentPlanData.official_billing);
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i><span>${message}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

function showPlanModal(plan, billing, amount) {
    const currentPlanDisplay = currentPlanData ? `${currentPlanData.official_plan.charAt(0).toUpperCase() + currentPlanData.official_plan.slice(1)} Plan (${currentPlanData.official_billing})` : 'Bronze Plan (lifetime)';
    const isBronzeDowngrade = (plan === 'bronze' && currentPlanData && currentPlanData.official_plan !== 'bronze');
    
    pendingPlanChange = { plan, billing, amount, isBronze: isBronzeDowngrade };
    
    document.getElementById('currentPlanModal').textContent = currentPlanDisplay;
    document.getElementById('newPlanModal').textContent = isBronzeDowngrade ? 'Bronze Plan (Free)' : `${plan.charAt(0).toUpperCase() + plan.slice(1)} Plan (${billing})`;
    document.getElementById('modalAmount').textContent = isBronzeDowngrade ? '₱0.00' : `₱${amount.toLocaleString('en-US')}`;
    document.getElementById('modalBillingPeriod').textContent = isBronzeDowngrade ? 'free forever' : (billing === 'monthly' ? 'per month' : 'per year');
    
    const isDowngrade = checkIfDowngrade(plan, billing);
    const isBillingChange = checkIfBillingChange(plan, billing);
    const downgradeWarning = document.getElementById('downgradeWarning');
    const confirmBtn = document.getElementById('confirmBtn');
    const modalTitle = document.getElementById('modalTitle');
    
    if (isBronzeDowngrade) {
        downgradeWarning.style.display = 'block';
        downgradeWarning.innerHTML = '<i class="fas fa-exclamation-triangle"></i><p>Downgrading to Bronze will take effect immediately. You will lose access to premium features.</p>';
        modalTitle.textContent = "Confirm Downgrade to Bronze";
        confirmBtn.textContent = "Confirm Downgrade";
        confirmBtn.style.backgroundColor = '#e74c3c';
    } else if (isDowngrade) {
        downgradeWarning.style.display = 'block';
        downgradeWarning.innerHTML = '<i class="fas fa-exclamation-triangle"></i><p>Downgrading will take effect at the end of your current billing period.</p>';
        modalTitle.textContent = "Confirm Downgrade";
        confirmBtn.textContent = "Confirm Downgrade";
        confirmBtn.style.backgroundColor = '#e74c3c';
    } else if (isBillingChange) {
        downgradeWarning.style.display = 'none';
        modalTitle.textContent = "Change Billing Period";
        confirmBtn.textContent = "Confirm & Pay";
        confirmBtn.style.backgroundColor = '#3498db';
    } else {
        downgradeWarning.style.display = 'none';
        modalTitle.textContent = "Confirm Upgrade";
        confirmBtn.textContent = "Confirm & Pay";
        confirmBtn.style.backgroundColor = '#3498db';
    }
    
    document.getElementById('planConfirmModal').style.display = 'block';
}

function closeModal() { document.getElementById('planConfirmModal').style.display = 'none'; }

async function submitBronzeDowngrade() {
    const confirmBtn = document.getElementById('confirmBtn');
    const originalText = confirmBtn.textContent;
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';
    
    try {
        const response = await fetch('/seller/backend/plan/update_to_bronze_plan.php', { method: 'POST', headers: { 'Content-Type': 'application/json' } });
        const result = await response.json();
        if (result.success) {
            closeModal();
            showToast(result.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast('Error: ' + result.message, 'error');
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
        confirmBtn.disabled = false;
        confirmBtn.textContent = originalText;
    }
}

function proceedWithPlanChange() {
    if (!pendingPlanChange.plan) return;
    const { plan, billing, amount, isBronze } = pendingPlanChange;
    closeModal();
    if (isBronze) {
        submitBronzeDowngrade();
    } else {
        window.location.href = `/seller/ui/payment.php?amount=${amount}&plan=${plan}&billing=${billing}`;
    }
}

window.onclick = function(event) { if (event.target === document.getElementById('planConfirmModal')) closeModal(); };
document.addEventListener('keydown', function(e) { if (e.key === "Escape" && document.getElementById('planConfirmModal').style.display === 'block') closeModal(); });

function updatePricingButtons(planData) {
    const officialPlan = planData.official_plan.toLowerCase();
    const officialBilling = planData.official_billing;
    const planRank = { 'bronze': 1, 'silver': 2, 'gold': 3 };
    const officialRank = planRank[officialPlan] || 1;
    
    document.querySelectorAll('.pricing-card[data-plan]').forEach(card => {
        const cardPlan = card.getAttribute('data-plan');
        const cardBilling = card.getAttribute('data-billing');
        const cardPrice = parseInt(card.getAttribute('data-price')) || 0;
        const cardRight = card.querySelector('.card-right');
        const existingBtn = cardRight.querySelector('.pricing-btn');
        const cardRank = planRank[cardPlan] || 1;
        let newButton;
        
        if (cardPlan === 'bronze') {
            if (officialPlan === 'bronze') {
                newButton = document.createElement('span');
                newButton.className = 'pricing-btn btn-current';
                newButton.textContent = 'Current Plan';
                const freeBadge = card.querySelector('.free-badge');
                if (freeBadge) { freeBadge.textContent = 'CURRENT PLAN'; freeBadge.style.background = '#27ae60'; }
            } else {
                newButton = document.createElement('button');
                newButton.type = 'button';
                newButton.className = 'pricing-btn btn-downgrade';
                newButton.textContent = 'Downgrade to Bronze (Free)';
                newButton.onclick = (e) => { e.preventDefault(); showPlanModal('bronze', 'lifetime', 0); };
                const freeBadge = card.querySelector('.free-badge');
                if (freeBadge) { freeBadge.textContent = 'FREE FOREVER'; freeBadge.style.background = '#27ae60'; }
            }
            if (existingBtn) existingBtn.replaceWith(newButton);
            return;
        }
        
        if (cardPlan === officialPlan && cardBilling === officialBilling) {
            newButton = document.createElement('span');
            newButton.className = 'pricing-btn btn-current';
            newButton.textContent = 'Current Plan';
        } else if (cardPlan === officialPlan && cardBilling !== officialBilling) {
            newButton = document.createElement('button');
            newButton.type = 'button';
            if ((officialBilling === 'yearly' && cardBilling === 'monthly') || (officialBilling === 'lifetime' && cardBilling !== 'lifetime')) {
                newButton.className = 'pricing-btn btn-downgrade';
                newButton.textContent = 'Downgrade';
            } else {
                newButton.className = 'pricing-btn btn-switch';
                newButton.textContent = cardBilling === 'yearly' ? 'Switch to Yearly' : 'Switch to Monthly';
            }
            newButton.onclick = (e) => { e.preventDefault(); showPlanModal(cardPlan, cardBilling, cardPrice); };
        } else {
            newButton = document.createElement('button');
            newButton.type = 'button';
            if (cardRank < officialRank) {
                newButton.className = 'pricing-btn btn-downgrade';
                newButton.textContent = `Downgrade to ${cardPlan.charAt(0).toUpperCase() + cardPlan.slice(1)}`;
            } else {
                newButton.className = `pricing-btn btn-upgrade ${cardPlan === 'gold' ? 'btn-gold' : ''}`;
                newButton.textContent = `Upgrade to ${cardPlan.charAt(0).toUpperCase() + cardPlan.slice(1)}`;
            }
            newButton.onclick = (e) => { e.preventDefault(); showPlanModal(cardPlan, cardBilling, cardPrice); };
        }
        if (existingBtn) existingBtn.replaceWith(newButton);
    });
    
    document.querySelectorAll('.popular-badge').forEach(badge => {
        const card = badge.closest('.pricing-card');
        if (card) {
            const cardPlan = card.getAttribute('data-plan');
            const cardBilling = card.getAttribute('data-billing');
            badge.style.display = (cardPlan === officialPlan && cardBilling === officialBilling) ? 'none' : 'block';
        }
    });
}

async function fetchCurrentPlan() {
    try {
        const response = await fetch('/seller/backend/plan/get_current_plan.php');
        const result = await response.json();
        if (result.success && result.data) updatePlanDisplay(result.data);
    } catch (error) { console.error('Error fetching plan:', error); }
}

function updatePlanDisplay(planData) {
    currentPlanData = planData;
    const subscribedPlanName = planData.subscribed_plan.charAt(0).toUpperCase() + planData.subscribed_plan.slice(1);
    document.getElementById('currentPlanName').textContent = `${subscribedPlanName} Plan`;
    document.getElementById('currentPlanDesc').textContent = planData.description;
    
    const billingText = document.getElementById('billingText');
    if (planData.subscribed_billing === 'monthly') billingText.textContent = 'Monthly Billing';
    else if (planData.subscribed_billing === 'yearly') billingText.textContent = 'Yearly Billing';
    else billingText.textContent = 'Lifetime Access';
    
    const statusBadge = document.getElementById('currentPlanStatus');
    const isActive = planData.subscribed_status === 'active';
    statusBadge.className = `status-badge ${isActive ? 'status-active' : 'status-pending'}`;
    statusBadge.innerHTML = `<i class="fas fa-${isActive ? 'check-circle' : 'clock'}"></i> ${isActive ? 'Active' : 'Pending'}`;
    
    const expiryEl = document.getElementById('planExpiry');
    if (planData.end_date_formatted) {
        if (planData.subscribed_billing === 'lifetime') expiryEl.textContent = 'Never expires';
        else if (planData.subscribed_status === 'pending') expiryEl.textContent = 'Activation pending payment';
        else expiryEl.textContent = `Renews on ${planData.end_date_formatted}`;
    } else {
        if (planData.subscribed_billing === 'lifetime') expiryEl.textContent = 'Never expires';
        else if (planData.subscribed_status === 'pending') expiryEl.textContent = 'Activation pending payment';
        else expiryEl.textContent = 'Active subscription';
    }
    
    if (planData.official_billing === 'yearly') {
        yearlyRadio.checked = true;
        monthlyPlans.classList.remove('active');
        yearlyPlans.classList.add('active');
    } else {
        monthlyRadio.checked = true;
        monthlyPlans.classList.add('active');
        yearlyPlans.classList.remove('active');
    }
    updatePriceDisplay();
    updatePricingButtons(planData);
}

document.addEventListener('DOMContentLoaded', fetchCurrentPlan);
</script>
</body>
</html>