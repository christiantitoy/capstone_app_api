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
    <link rel="stylesheet" href="../css/error.css?v=<?= time() ?>">
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
                    <div class="pricing-card bronze" data-plan="bronze" data-billing="lifetime">
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
                    <div class="pricing-card bronze" data-plan="bronze" data-billing="lifetime">
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

<script>
// Global variable to store current plan data
let currentPlanData = null;

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

// Modal variables
let pendingPlanChange = {};

// Check if it's a downgrade
function checkIfDowngrade(newPlan, newBilling) {
    if (!currentPlanData) return false;
    
    const currentPlan = currentPlanData.plan;
    const currentBilling = currentPlanData.billing;
    
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

// Check if it's just a billing change
function checkIfBillingChange(newPlan, newBilling) {
    if (!currentPlanData) return false;
    return (newPlan === currentPlanData.plan && newBilling !== currentPlanData.billing);
}

// Show confirmation modal
function showPlanModal(plan, billing, amount) {
    const currentPlanName = currentPlanData ? 
        `${currentPlanData.plan.charAt(0).toUpperCase() + currentPlanData.plan.slice(1)} Plan (${currentPlanData.billing})` : 
        'Bronze Plan';

    pendingPlanChange = { plan, billing, amount };

    document.getElementById('currentPlanModal').textContent = currentPlanName;
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
        downgradeWarning.style.display = 'block';
        modalTitle.textContent = "Confirm Downgrade";
        confirmBtn.textContent = "Confirm Downgrade";
        confirmBtn.style.backgroundColor = 'var(--danger)';
    } else if (isBillingChange) {
        downgradeWarning.style.display = 'none';
        modalTitle.textContent = "Change Billing Period";
        confirmBtn.textContent = "Confirm & Pay";
        confirmBtn.style.backgroundColor = 'var(--primary)';
    } else {
        downgradeWarning.style.display = 'none';
        modalTitle.textContent = "Confirm Upgrade";
        confirmBtn.textContent = "Confirm & Pay";
        confirmBtn.style.backgroundColor = 'var(--primary)';
    }

    document.getElementById('planConfirmModal').style.display = 'block';
}

// Close modal
function closeModal() {
    document.getElementById('planConfirmModal').style.display = 'none';
}

// Proceed with plan change
function proceedWithPlanChange() {
    if (!pendingPlanChange.plan) return;
    const { plan, billing, amount } = pendingPlanChange;
    closeModal();
    window.location.href = `/seller/ui/payment.php?amount=${amount}&plan=${plan}&billing=${billing}`;
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('planConfirmModal');
    if (event.target === modal) closeModal();
};

// Close with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === "Escape") {
        const modal = document.getElementById('planConfirmModal');
        if (modal.style.display === 'block') closeModal();
    }
});

// Update pricing buttons based on current plan
function updatePricingButtons(planData) {
    const currentPlan = planData.plan.toLowerCase();
    const currentBilling = planData.billing;
    const planRank = { 'bronze': 1, 'silver': 2, 'gold': 3 };
    const currentRank = planRank[currentPlan] || 1;
    
    document.querySelectorAll('.pricing-card[data-plan]').forEach(card => {
        const cardPlan = card.getAttribute('data-plan');
        const cardBilling = card.getAttribute('data-billing');
        const cardPrice = parseInt(card.getAttribute('data-price')) || 0;
        const cardRight = card.querySelector('.card-right');
        const existingBtn = cardRight.querySelector('.pricing-btn');
        const cardRank = planRank[cardPlan] || 1;
        
        let newButton;
        
        if (cardPlan === currentPlan && cardBilling === currentBilling) {
            // Current plan and billing
            newButton = document.createElement('span');
            newButton.className = 'pricing-btn btn-current';
            newButton.textContent = 'Current Plan';
        } else if (cardPlan === currentPlan && cardBilling !== currentBilling) {
            // Same plan, different billing
            newButton = document.createElement('button');
            if ((currentBilling === 'yearly' && cardBilling === 'monthly') ||
                (currentBilling === 'lifetime' && cardBilling !== 'lifetime')) {
                newButton.className = 'pricing-btn btn-downgrade';
                newButton.textContent = 'Downgrade';
            } else {
                newButton.className = 'pricing-btn btn-switch';
                newButton.textContent = 'Switch to Yearly';
            }
            newButton.onclick = () => showPlanModal(cardPlan, cardBilling, cardPrice);
        } else {
            // Different plan
            newButton = document.createElement('button');
            if (cardRank < currentRank) {
                newButton.className = 'pricing-btn btn-downgrade';
                newButton.textContent = 'Downgrade';
            } else {
                newButton.className = `pricing-btn btn-upgrade ${cardPlan === 'gold' ? 'btn-gold' : ''}`;
                newButton.textContent = `Upgrade to ${cardPlan.charAt(0).toUpperCase() + cardPlan.slice(1)}`;
            }
            newButton.onclick = () => showPlanModal(cardPlan, cardBilling, cardPrice);
        }
        
        if (existingBtn) existingBtn.replaceWith(newButton);
    });
    
    // Hide popular badge for current plan
    document.querySelectorAll('.popular-badge').forEach(badge => {
        const card = badge.closest('.pricing-card');
        if (card) {
            const cardPlan = card.getAttribute('data-plan');
            const cardBilling = card.getAttribute('data-billing');
            badge.style.display = (cardPlan === currentPlan && cardBilling === currentBilling) ? 'none' : 'block';
        }
    });
}

// Fetch and display current plan
document.addEventListener('DOMContentLoaded', function() {
    fetchCurrentPlan();
});

async function fetchCurrentPlan() {
    try {
        const response = await fetch('/seller/backend/plan/get_current_plan.php');
        const result = await response.json();
        
        if (result.success && result.data) {
            updatePlanDisplay(result.data);
        }
    } catch (error) {
        console.error('Error fetching plan:', error);
    }
}

function updatePlanDisplay(planData) {
    currentPlanData = planData;
    
    const planName = planData.plan.charAt(0).toUpperCase() + planData.plan.slice(1);
    document.getElementById('currentPlanName').textContent = `${planName} Plan`;
    document.getElementById('currentPlanDesc').textContent = planData.description;
    
    const billingText = document.getElementById('billingText');
    if (planData.billing === 'monthly') {
        billingText.textContent = 'Monthly Billing';
    } else if (planData.billing === 'yearly') {
        billingText.textContent = 'Yearly Billing';
    } else {
        billingText.textContent = 'Lifetime Access';
    }
    
    const statusBadge = document.getElementById('currentPlanStatus');
    const isActive = planData.status === 'active';
    
    statusBadge.className = `status-badge ${isActive ? 'status-active' : 'status-pending'}`;
    statusBadge.innerHTML = `
        <i class="fas fa-${isActive ? 'check-circle' : 'clock'}"></i> 
        ${isActive ? 'Active' : 'Pending'}
    `;
    
    const expiryEl = document.getElementById('planExpiry');
    if (planData.end_date_formatted) {
        if (planData.billing === 'lifetime') {
            expiryEl.textContent = 'Never expires';
        } else if (planData.status === 'pending') {
            expiryEl.textContent = 'Activation pending payment';
        } else {
            expiryEl.textContent = `Renews on ${planData.end_date_formatted}`;
        }
    } else {
        if (planData.billing === 'lifetime') {
            expiryEl.textContent = 'Never expires';
        } else if (planData.status === 'pending') {
            expiryEl.textContent = 'Activation pending payment';
        } else {
            expiryEl.textContent = 'Active subscription';
        }
    }
    
    // Set billing toggle
    if (planData.billing === 'yearly') {
        yearlyRadio.checked = true;
        monthlyPlans.classList.remove('active');
        yearlyPlans.classList.add('active');
    } else {
        monthlyRadio.checked = true;
        monthlyPlans.classList.add('active');
        yearlyPlans.classList.remove('active');
    }
    updatePriceDisplay();
    
    // Update pricing buttons
    updatePricingButtons(planData);
}

</script>

</body>
</html>