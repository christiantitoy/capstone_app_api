<?php
// /seller/ui/seller_profile.php
require_once __DIR__ . '/../backend/session/auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Store - Seller Dashboard</title>
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

        /* Profile Grid */
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .profile-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .profile-card.full-width {
            grid-column: span 2;
        }

        .card-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #eef2f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
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

        .card-body {
            padding: 1.5rem;
        }

        /* Info Rows */
        .info-row {
            display: flex;
            padding: 0.8rem 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            width: 140px;
            color: #7f8c8d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-value {
            flex: 1;
            font-weight: 500;
            color: var(--dark);
        }

        /* Badges */
        .plan-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
        }

        .plan-bronze {
            background: #b45309;
        }

        .plan-silver {
            background: #6b7280;
        }

        .plan-gold {
            background: #fbbf24;
            color: #333;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-warning {
            background: #fff3e0;
            color: #e65100;
        }

        .status-danger {
            background: #ffebee;
            color: #c62828;
        }

        /* Facebook-style Banner */
        .store-banner-container {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            cursor: pointer;
            overflow: hidden;
        }

        .store-banner-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .store-banner-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            gap: 8px;
        }

        .store-banner-placeholder i {
            font-size: 3rem;
            opacity: 0.7;
        }

        .store-banner-placeholder span {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Facebook-style Logo */
        .store-logo-container {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            border: 4px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            position: relative;
            margin-top: -60px;
            cursor: pointer;
            overflow: hidden;
        }

        .store-logo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .store-logo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: white;
            border-radius: 50%;
        }

        .store-logo-placeholder i {
            font-size: 3rem;
        }

        /* Image Overlay */
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            color: white;
            font-size: 1.5rem;
            gap: 8px;
        }

        .store-banner-container:hover .image-overlay,
        .store-logo-container:hover .image-overlay {
            opacity: 1;
        }

        .store-banner-container .image-overlay {
            border-radius: 0;
        }

        .store-logo-container .image-overlay {
            border-radius: 50%;
        }

        /* Full Image Modal */
        .full-image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .full-image-modal.active {
            display: flex;
        }

        .full-image-content {
            max-width: 90%;
            max-height: 90%;
            position: relative;
        }

        .full-image-content img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
        }

        .modal-close-btn {
            position: absolute;
            top: -40px;
            right: 0;
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            padding: 8px;
            transition: opacity 0.2s;
        }

        .modal-close-btn:hover {
            opacity: 0.7;
        }

        .modal-caption {
            position: absolute;
            bottom: -40px;
            left: 0;
            right: 0;
            text-align: center;
            color: white;
            font-size: 1rem;
        }

        /* Edit Button */
        .edit-btn {
            background: none;
            border: 1px solid #d1d9e0;
            padding: 6px 12px;
            border-radius: 6px;
            color: var(--primary);
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .edit-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* No Store Message */
        .no-store-message {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
        }

        .no-store-message i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .no-store-message h3 {
            margin-bottom: 0.5rem;
        }

        .setup-store-btn {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.8rem 1.5rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.15s;
        }

        .setup-store-btn:hover {
            background: #2980b9;
        }

        /* Loading State */
        .loading-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .loading-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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
        @media (max-width: 900px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-card.full-width {
                grid-column: span 1;
            }
        }

        @media (max-width: 600px) {
            .main-content {
                padding: 1rem;
            }
            
            .info-row {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .info-label {
                width: 100%;
            }
            
            .store-banner-container {
                height: 150px;
            }
            
            .store-logo-container {
                width: 100px;
                height: 100px;
                margin-top: -50px;
            }
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
            <a href="/seller/ui/my_plan.php" class="nav-item"><i class="fas fa-crown"></i><span>My Plan</span></a>
            <a href="/seller/ui/seller_profile.php" class="nav-item active"><i class="fas fa-store"></i><span>My Store</span></a>
            <a href="#" class="nav-item"><i class="fas fa-chart-line"></i><span>Sales</span></a>
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
                    <i class="fas fa-store" style="color: var(--primary);"></i>
                    My Store Profile
                </h1>
                <p>View and manage your store information</p>
            </div>
            <div class="header-right">
                <div class="date-display"><?= date('F j, Y') ?></div>
            </div>
        </header>

        <div id="profileContent">
            <div class="loading-state">
                <i class="fas fa-spinner"></i>
                <p>Loading profile information...</p>
            </div>
        </div>

        <footer class="main-footer">
            <p>© <?= date('Y') ?> Seller Dashboard. All rights reserved.</p>
        </footer>
    </main>
</div>

<!-- Full Image Modal -->
<div id="fullImageModal" class="full-image-modal" onclick="closeFullImage()">
    <div class="full-image-content" onclick="event.stopPropagation()">
        <button class="modal-close-btn" onclick="closeFullImage()">×</button>
        <img id="fullImage" src="" alt="Full view">
        <div class="modal-caption" id="imageCaption"></div>
    </div>
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
});

// Full image view functions
function viewFullImage(type, imageUrl) {
    if (!imageUrl) {
        showToast('warning', 'No image available');
        return;
    }
    
    const modal = document.getElementById('fullImageModal');
    const fullImage = document.getElementById('fullImage');
    const caption = document.getElementById('imageCaption');
    
    fullImage.src = imageUrl;
    caption.textContent = type === 'banner' ? 'Store Banner' : 'Store Logo';
    modal.classList.add('active');
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closeFullImage() {
    const modal = document.getElementById('fullImageModal');
    modal.classList.remove('active');
    
    // Restore body scroll
    document.body.style.overflow = '';
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFullImage();
    }
});

// Toast notification
function showToast(type, message) {
    const existingToast = document.querySelector('.toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 3000;
        display: flex;
        align-items: center;
        gap: 10px;
        border-left: 4px solid ${type === 'success' ? '#2ecc71' : type === 'warning' ? '#f39c12' : '#e74c3c'};
    `;
    
    const icon = type === 'success' ? 'fa-check-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle';
    const color = type === 'success' ? '#2ecc71' : type === 'warning' ? '#f39c12' : '#e74c3c';
    
    toast.innerHTML = `
        <i class="fas ${icon}" style="color: ${color}; font-size: 1.2rem;"></i>
        <span>${escapeHtml(message)}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Load profile data
async function loadProfile() {
    try {
        const response = await fetch('/seller/backend/profile/get_seller_profile.php');
        const result = await response.json();
        
        if (result.success && result.data) {
            displayProfile(result.data);
        } else {
            showError(result.message || 'Failed to load profile');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Error loading profile data');
    }
}

function displayProfile(data) {
    const seller = data.seller;
    const store = data.store;
    
    // Plan class mapping
    const planClass = {
        'Bronze': 'plan-bronze',
        'Silver': 'plan-silver',
        'Gold': 'plan-gold'
    };
    
    let html = `
        <div class="profile-grid">
            <!-- Account Information Card -->
            <div class="profile-card">
                <div class="card-header">
                    <h2><i class="fas fa-user-circle"></i> Account Information</h2>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-user"></i> Full Name</span>
                        <span class="info-value">${escapeHtml(seller.full_name)}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                        <span class="info-value">${escapeHtml(seller.email)}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-calendar"></i> Member Since</span>
                        <span class="info-value">${escapeHtml(seller.member_since)}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-check-circle"></i> Email Status</span>
                        <span class="info-value">
                            <span class="status-badge ${seller.is_confirmed ? 'status-success' : 'status-warning'}">
                                <i class="fas fa-${seller.is_confirmed ? 'check' : 'clock'}"></i>
                                ${seller.is_confirmed ? 'Confirmed' : 'Pending'}
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Subscription Card -->
            <div class="profile-card">
                <div class="card-header">
                    <h2><i class="fas fa-crown"></i> Subscription</h2>
                    <a href="/seller/ui/my_plan.php" class="edit-btn">
                        <i class="fas fa-arrow-right"></i> Manage
                    </a>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-medal"></i> Current Plan</span>
                        <span class="info-value">
                            <span class="plan-badge ${planClass[seller.seller_plan] || 'plan-bronze'}">
                                <i class="fas fa-${seller.seller_plan === 'Gold' ? 'crown' : (seller.seller_plan === 'Silver' ? 'gem' : 'medal')}"></i>
                                ${escapeHtml(seller.plan_display)}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-clock"></i> Billing</span>
                        <span class="info-value">${escapeHtml(seller.billing_display)}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-clipboard-check"></i> Approval Status</span>
                        <span class="info-value">
                            <span class="status-badge status-${seller.approval_status_display.class}">
                                <i class="fas fa-${seller.approval_status === 'approved' ? 'check-circle' : 'clock'}"></i>
                                ${escapeHtml(seller.approval_status_display.text)}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
    `;
    
    // Store Information Card
    if (store) {
        const categoryDisplay = store.category ? store.category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Not specified';
        
        html += `
            <div class="profile-card full-width">
                <div class="card-header">
                    <h2><i class="fas fa-store-alt"></i> Store Information</h2>
                    <button class="edit-btn" onclick="editStore()">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <div class="card-body" style="padding: 0;">
                    <!-- Facebook-style Banner -->
                    <div class="store-banner-container" onclick="viewFullImage('banner', '${escapeHtml(store.banner_url || '')}')">
                        ${store.banner_url ? 
                            `<img src="${escapeHtml(store.banner_url)}" alt="Store Banner" class="store-banner-img">` : 
                            `<div class="store-banner-placeholder">
                                <i class="fas fa-image"></i>
                                <span>Add Cover Photo</span>
                            </div>`
                        }
                        ${store.banner_url ? `
                            <div class="image-overlay">
                                <i class="fas fa-search-plus"></i> Click to view
                            </div>
                        ` : ''}
                    </div>
                    
                    <!-- Facebook-style Logo (positioned over banner) -->
                    <div style="padding: 0 1.5rem 1.5rem 1.5rem; position: relative;">
                        <div class="store-logo-container" onclick="viewFullImage('logo', '${escapeHtml(store.logo_url || '')}')">
                            ${store.logo_url ? 
                                `<img src="${escapeHtml(store.logo_url)}" alt="Store Logo" class="store-logo-img">` : 
                                `<div class="store-logo-placeholder">
                                    <i class="fas fa-store"></i>
                                </div>`
                            }
                            ${store.logo_url ? `
                                <div class="image-overlay">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            ` : ''}
                        </div>
                        
                        <div style="margin-top: 1rem;">
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-tag"></i> Store Name</span>
                                <span class="info-value">${escapeHtml(store.store_name)}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-folder"></i> Category</span>
                                <span class="info-value">${escapeHtml(categoryDisplay)}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-align-left"></i> Description</span>
                                <span class="info-value">${escapeHtml(store.description)}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-phone"></i> Contact</span>
                                <span class="info-value">${escapeHtml(store.contact_number)}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-clock"></i> Operating Hours</span>
                                <span class="info-value">
                                    ${store.open_time_formatted && store.close_time_formatted ? 
                                        `${store.open_time_formatted} - ${store.close_time_formatted}` : 
                                        'Not set'
                                    }
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-map-marker-alt"></i> Location</span>
                                <span class="info-value">
                                    ${store.latitude && store.longitude ? 
                                        `${store.latitude}, ${store.longitude}` : 
                                        'Not set'
                                    }
                                    ${store.plus_code ? `<br><small style="color: var(--gray);">Plus Code: ${escapeHtml(store.plus_code)}</small>` : ''}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-user-shield"></i> Owner Name</span>
                                <span class="info-value">${escapeHtml(store.owner_full_name)}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-id-card"></i> ID Type</span>
                                <span class="info-value">${escapeHtml(store.id_type)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        html += `
            <div class="profile-card full-width">
                <div class="card-header">
                    <h2><i class="fas fa-store-alt"></i> Store Information</h2>
                </div>
                <div class="card-body">
                    <div class="no-store-message">
                        <i class="fas fa-store-slash"></i>
                        <h3>No Store Setup Yet</h3>
                        <p>You haven't set up your store information yet.</p>
                        <a href="/seller/ui/setup_shop.php" class="setup-store-btn">
                            <i class="fas fa-plus"></i> Set Up Your Store
                        </a>
                    </div>
                </div>
            </div>
        `;
    }
    
    html += `</div>`;
    
    document.getElementById('profileContent').innerHTML = html;
}

function showError(message) {
    document.getElementById('profileContent').innerHTML = `
        <div class="profile-card">
            <div class="card-body">
                <div class="no-store-message">
                    <i class="fas fa-exclamation-triangle" style="color: var(--danger);"></i>
                    <h3>Error Loading Profile</h3>
                    <p>${escapeHtml(message)}</p>
                    <button class="setup-store-btn" onclick="location.reload()">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                </div>
            </div>
        </div>
    `;
}

function editStore() {
    window.location.href = '/seller/ui/edit_store.php';
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Load profile on page load
document.addEventListener('DOMContentLoaded', loadProfile);
</script>

</body>
</html>