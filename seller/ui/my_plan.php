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
        /* Downgrade button style */
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
                    <div class="pricing-card bronze" data-plan="bronze" data-billing="lifetime" data-price="0">
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

                    <!-- Silver Monthly -->
                    <div class="pricing-card popular silver" data-plan="silver" data-billing="monthly" data-price="300">
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
                            <button class="pricing-btn btn-upgrade">Upgrade to Silver</button>
                        </div>
                    </div>

                    <!-- Gold Monthly -->
                    <div class="pricing-card gold" data-plan="gold" data-billing="monthly" data-price="800">
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
                            <button class="pricing-btn btn-upgrade btn-gold">Upgrade to Gold</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yearly Plans -->
            <div id="yearlyPlans" class="billing-period">
                <div class="pricing-grid">
                    <!-- Bronze Yearly -->
                    <div class="pricing-card bronze" data-plan="bronze" data-billing="lifetime" data-price="0">
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
                    <div class="pricing-card popular silver" data-plan="silver" data-billing="yearly" data-price="3000">
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
                            <button class="pricing-btn btn-upgrade">Upgrade to Silver</button>
                        </div>
                    </div>

                    <!-- Gold Yearly -->
                    <div class="pricing-card gold" data-plan="gold" data-billing="yearly" data-price="8000">
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
                            <button class="pricing-btn btn-upgrade btn-gold">Upgrade to Gold</button>
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

<!-- ==================== PLAN CONFIRMATION MODAL ==================== -->
<div id="planConfirmModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Confirm Plan Change</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        
        <div class="modal-body">
            <div class="plan-change-info">
                <div class="from-plan">
                    <strong>Current:</strong><br>
                    <span id="currentPlanModal">Bronze Plan</span>
                </div>
                <div class="arrow">→</div>
                <div class="to-plan">
                    <strong>New:</strong><br>
                    <span id="newPlanModal">Silver Plan (Monthly)</span>
                </div>
            </div>

            <div class="price-info">
                <p>You will be charged <strong id="modalAmount">₱300.00</strong> 
                <span id="modalBillingPeriod">per month</span></p>
            </div>

            <div class="warning-note" id="downgradeWarning" style="display: none;">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Downgrading will take effect at the end of your current billing period. Some features may no longer be available.</p>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <button class="btn-confirm" id="confirmBtn" onclick="proceedWithPlanChange()">
                Confirm Upgrade
            </button>
        </div>
    </div>
</div>

<!-- ── LOGOUT CONFIRMATION MODAL ── -->
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
// Global variables
let currentPlanData = null;
let pendingPlanChange = {};

// Set current date
document.getElementById('dateDisplay').textContent = new Date().toLocaleDateString('en-US', { 
    year: 'numeric', month: 'long', day: 'numeric' 
});

// Toggle between monthly and yearly
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

monthlyRadio.addEventListener('change', () => {
    monthlyPlans.classList.add('active');
    yearlyPlans.classList.remove('active');
    updatePriceDisplay();
});

yearlyRadio.addEventListener('change', () => {
    yearlyPlans.classList.add('active');
    monthlyPlans.classList.remove('active');
    updatePriceDisplay();
});

// Check if it's a downgrade (using OFFICIAL plan)
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

// Check if it's just a billing change (using OFFICIAL plan)
function checkIfBillingChange(newPlan, newBilling) {
    if (!currentPlanData) return false;
    return (newPlan === currentPlanData.official_plan && newBilling !== currentPlanData.official_billing);
}

// Show confirmation modal
function showPlanModal(plan, billing, amount) {
    console.log('showPlanModal called with:', { plan, billing, amount });
    console.log('currentPlanData:', currentPlanData);
    
    if (!currentPlanData) {
        console.error('Current plan data not loaded yet');
        alert('Please wait, loading plan information...');
        return;
    }

    const currentPlanDisplay = 
        `${currentPlanData.official_plan.charAt(0).toUpperCase() + currentPlanData.official_plan.slice(1)} Plan (${currentPlanData.official_billing})`;

    pendingPlanChange = { plan, billing, amount };

    document.getElementById('currentPlanModal').textContent = currentPlanDisplay;
    document.getElementById('newPlanModal').textContent = 
        `${plan.charAt(0).toUpperCase() + plan.slice(1)} Plan (${billing})`;
    
    document.getElementById('modalAmount').textContent = `₱${amount.toLocaleString('en-US')}`;
    document.getElementById('modalBillingPeriod').textContent = 
        billing === 'monthly' ? 'per month' : 'per year';

    const isDowngrade = checkIfDowngrade(plan, billing);
    const isBillingChange = checkIfBillingChange(plan, billing);
    
    const downgradeWarning = document.getElementById('downgradeWarning');
    const confirmBtn = document.getElementById('confirmBtn');
    const modalTitle = document.getElementById('modalTitle');

    if (isDowngrade) {
        downgradeWarning.style.display = 'flex';
        downgradeWarning.style.alignItems = 'flex-start';
        downgradeWarning.style.gap = '0.75rem';
        modalTitle.textContent = "Confirm Downgrade";
        confirmBtn.textContent = "Confirm Downgrade";
        confirmBtn.style.backgroundColor = '#dc3545';
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

    const modal = document.getElementById('planConfirmModal');
    modal.classList.add('show');
    console.log('Modal display set to block');
}

// Close modal
function closeModal() {
    const modal = document.getElementById('planConfirmModal');
    modal.classList.remove('show');
}

// Proceed with plan change
function proceedWithPlanChange() {
    if (!pendingPlanChange.plan) {
        console.error('No pending plan change');
        return;
    }
    const { plan, billing, amount } = pendingPlanChange;
    closeModal();
    window.location.href = `/seller/ui/payment.php?amount=${amount}&plan=${plan}&billing=${billing}`;
}

// Update pricing buttons based on OFFICIAL plan and billing
function updatePricingButtons(planData) {
    console.log('Updating pricing buttons with data:', planData);
    
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
        
        // Check against OFFICIAL plan (source of truth)
        if (cardPlan === officialPlan && cardBilling === officialBilling) {
            // This is the seller's official current plan
            newButton = document.createElement('span');
            newButton.className = 'pricing-btn btn-current';
            newButton.textContent = 'Current Plan';
        } else if (cardPlan === officialPlan && cardBilling !== officialBilling) {
            // Same plan, different billing period
            newButton = document.createElement('button');
            if ((officialBilling === 'yearly' && cardBilling === 'monthly') ||
                (officialBilling === 'lifetime' && cardBilling !== 'lifetime')) {
                newButton.className = 'pricing-btn btn-downgrade';
                newButton.textContent = 'Downgrade';
            } else {
                newButton.className = 'pricing-btn btn-switch';
                newButton.textContent = 'Switch to Yearly';
            }
            // Use addEventListener instead of onclick for better reliability
            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                showPlanModal(cardPlan, cardBilling, cardPrice);
            });
        } else {
            // Different plan
            newButton = document.createElement('button');
            if (cardRank < officialRank) {
                newButton.className = 'pricing-btn btn-downgrade';
                newButton.textContent = 'Downgrade';
            } else {
                newButton.className = `pricing-btn btn-upgrade ${cardPlan === 'gold' ? 'btn-gold' : ''}`;
                newButton.textContent = `Upgrade to ${cardPlan.charAt(0).toUpperCase() + cardPlan.slice(1)}`;
            }
            // Use addEventListener instead of onclick
            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                showPlanModal(cardPlan, cardBilling, cardPrice);
            });
        }
        
        if (existingBtn) {
            existingBtn.replaceWith(newButton);
        }
    });
    
    // Hide popular badge for official current plan
    document.querySelectorAll('.popular-badge').forEach(badge => {
        const card = badge.closest('.pricing-card');
        if (card) {
            const cardPlan = card.getAttribute('data-plan');
            const cardBilling = card.getAttribute('data-billing');
            badge.style.display = (cardPlan === officialPlan && cardBilling === officialBilling) ? 'none' : 'block';
        }
    });
}

// Fetch and display current plan
async function fetchCurrentPlan() {
    try {
        console.log('Fetching current plan...');
        const response = await fetch('/seller/backend/plan/get_current_plan.php');
        const result = await response.json();
        
        console.log('Plan API response:', result);
        
        if (result.success && result.data) {
            updatePlanDisplay(result.data);
        } else {
            console.error('Failed to fetch plan:', result);
        }
    } catch (error) {
        console.error('Error fetching plan:', error);
    }
}

function updatePlanDisplay(planData) {
    console.log('Updating plan display with:', planData);
    currentPlanData = planData;
    
    // Update CURRENT PLAN CARD using SUBSCRIBED data
    const subscribedPlanName = planData.subscribed_plan.charAt(0).toUpperCase() + planData.subscribed_plan.slice(1);
    document.getElementById('currentPlanName').textContent = `${subscribedPlanName} Plan`;
    document.getElementById('currentPlanDesc').textContent = planData.description;
    
    // Update billing display
    const billingText = document.getElementById('billingText');
    if (planData.subscribed_billing === 'monthly') {
        billingText.textContent = 'Monthly Billing';
    } else if (planData.subscribed_billing === 'yearly') {
        billingText.textContent = 'Yearly Billing';
    } else {
        billingText.textContent = 'Lifetime Access';
    }
    
    // Update status badge
    const statusBadge = document.getElementById('currentPlanStatus');
    const isActive = planData.subscribed_status === 'active';
    
    statusBadge.className = `status-badge ${isActive ? 'status-active' : 'status-pending'}`;
    statusBadge.innerHTML = `
        <i class="fas fa-${isActive ? 'check-circle' : 'clock'}"></i> 
        ${isActive ? 'Active' : 'Pending'}
    `;
    
    // Update expiry/renewal text
    const expiryEl = document.getElementById('planExpiry');
    if (planData.end_date_formatted) {
        if (planData.subscribed_billing === 'lifetime') {
            expiryEl.textContent = 'Never expires';
        } else if (planData.subscribed_status === 'pending') {
            expiryEl.textContent = 'Activation pending payment';
        } else {
            expiryEl.textContent = `Renews on ${planData.end_date_formatted}`;
        }
    } else {
        if (planData.subscribed_billing === 'lifetime') {
            expiryEl.textContent = 'Never expires';
        } else if (planData.subscribed_status === 'pending') {
            expiryEl.textContent = 'Activation pending payment';
        } else {
            expiryEl.textContent = 'Active subscription';
        }
    }
    
    // Set billing toggle based on OFFICIAL billing
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
    
    // Update pricing buttons using OFFICIAL data
    updatePricingButtons(planData);
    
    // Check for pending payment
    if (planData.subscribed_status === 'pending') {
        const pendingAlert = document.getElementById('pendingAlert');
        const pendingAmount = document.getElementById('pendingAmount');
        if (pendingAlert) {
            pendingAlert.style.display = 'flex';
            // Update amount based on pending plan
            const amount = planData.subscribed_billing === 'yearly' ? '3000' : '300';
            pendingAmount.textContent = `₱${parseInt(amount).toLocaleString('en-US')}`;
        }
    }
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    
    // Make functions globally available FIRST
    window.showPlanModal = showPlanModal;
    window.closeModal = closeModal;
    window.proceedWithPlanChange = proceedWithPlanChange;
    
    // Remove inline onclick handlers and set up proper event listeners
    const modal = document.getElementById('planConfirmModal');
    const modalCloseBtn = document.querySelector('.modal-close');
    const cancelBtn = document.querySelector('.btn-cancel');
    const confirmBtn = document.getElementById('confirmBtn');
    
    // Remove inline onclick attributes
    if (modalCloseBtn) {
        modalCloseBtn.removeAttribute('onclick');
        modalCloseBtn.addEventListener('click', closeModal);
    }
    
    if (cancelBtn) {
        cancelBtn.removeAttribute('onclick');
        cancelBtn.addEventListener('click', closeModal);
    }
    
    if (confirmBtn) {
        confirmBtn.removeAttribute('onclick');
        confirmBtn.addEventListener('click', proceedWithPlanChange);
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    
    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === "Escape" && modal && modal.style.display === 'block') {
            closeModal();
        }
    });
    
    // Fetch current plan data
    fetchCurrentPlan();
    
    // Add click handlers to initial buttons (before they're replaced)
    // Event delegation for pricing buttons
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.pricing-btn');
        
        if (!btn) return;
        
        // Ignore current plan
        if (btn.classList.contains('btn-current')) return;

        const card = btn.closest('.pricing-card');
        if (!card) return;

        const plan = card.getAttribute('data-plan');
        const billing = card.getAttribute('data-billing');
        const price = parseInt(card.getAttribute('data-price')) || 0;

        showPlanModal(plan, billing, price);
    });
});

</script>

</body>
</html>