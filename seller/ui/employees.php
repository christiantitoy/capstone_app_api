<?php
// /seller/ui/employees.php
require_once __DIR__ . '/../backend/session/auth.php';
require_once __DIR__ . '/../../connection/db_connection.php';

// Fetch real employees for this seller
$stmt = $conn->prepare("
    SELECT id, full_name, email, role, status
    FROM employees
    WHERE seller_id = ?
    ORDER BY full_name ASC
");
$stmt->execute([$seller_id]);
$employees = $stmt->fetchAll();

// Display mapping for roles
$role_display = [
    'order_manager'    => 'Order Manager',
    'product_manager'  => 'Product Manager'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/employees.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/error.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/logout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <a href="/seller/ui/employees.php" class="nav-item active"><i class="fas fa-users"></i><span>Employees</span></a>
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
                    <i class="fas fa-users" style="color: var(--primary);"></i>
                    Employee Management
                </h1>
                <p>Add and manage your team members</p>
            </div>
            <!-- Add this button – only visible on mobile -->
            <button class="mobile-menu-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search employees..." id="searchInput">
                </div>
                <div class="date-display"><?= date('F j, Y') ?></div>
            </div>
        </header>

        <div class="employees-header">
            <h2>Team Members (<?= count($employees) ?>)</h2>
            <div class="filter-group">
                <select class="filter-select" id="filterRole">
                    <option value="">All Roles</option>
                    <option value="order_manager">Order Manager</option>
                    <option value="product_manager">Product Manager</option>
                </select>
                <select class="filter-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button class="add-employee-btn" onclick="openAddModal()">
                    <i class="fas fa-user-plus"></i> Add Employee
                </button>
            </div>
        </div>

        <div class="table-container">
            <?php if (empty($employees)): ?>
                <div style="
                    text-align: center;
                    padding: 4rem 1rem;
                    color: #7f8c8d;
                    background: #f8f9fa;
                    border-radius: 12px;
                    margin: 2rem 0;
                ">
                    <i class="fas fa-users-slash" style="font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.6;"></i>
                    <h3 style="margin: 0.5rem 0; font-size: 1.4rem;">You have no employees yet</h3>
                    <p style="margin: 0;">Click "Add Employee" to invite your first team member.</p>
                </div>
            <?php else: ?>
                <table id="employeesTable">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employeesBody">
                        <?php foreach ($employees as $emp): ?>
                            <tr 
                                data-role="<?= htmlspecialchars($emp['role'] ?? '') ?>" 
                                data-status="<?= htmlspecialchars($emp['status'] ?? '') ?>"
                            >
                                <td>
                                    <div class="employee-info">
                                        <div class="employee-avatar">
                                            <?= strtoupper(substr($emp['full_name'] ?? 'E', 0, 1)) ?>
                                        </div>
                                        <div class="employee-details">
                                            <span class="employee-name"><?= htmlspecialchars($emp['full_name'] ?? 'Unknown') ?></span>
                                            <span class="employee-email"><?= htmlspecialchars($emp['email'] ?? '—') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge role-<?= htmlspecialchars($emp['role'] ?? '') ?>">
                                        <?= htmlspecialchars($role_display[$emp['role']] ?? ucfirst($emp['role'] ?? 'Unknown')) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= htmlspecialchars($emp['status'] ?? 'unknown') ?>">
                                        <?= ucfirst($emp['status'] ?? 'Unknown') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn edit-btn" onclick="openEditModal(<?= (int)$emp['id'] ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn reset-btn" onclick="resetPassword(<?= (int)$emp['id'] ?>)" title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button class="action-btn delete-btn" onclick="deleteEmployee(<?= (int)$emp['id'] ?>)" title="Remove">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Add/Edit Employee Modal -->
        <div class="modal" id="employeeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><span id="modalTitle">Add New Employee</span></h2>
                    <button class="close-modal" onclick="closeModal()">×</button>
                </div>

                <form id="employeeForm" method="POST" action="/seller/backend/employees/add.php">
                    <input type="hidden" name="employee_id" id="editEmployeeId" value="">

                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="full_name" placeholder="e.g., Juan Dela Cruz" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="employee@business.com" required>
                    </div>

                    <div class="form-group" id="passwordGroup">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Employee Role</label>
                        <select id="role" name="role" required onchange="updateRoleDescription()">
                            <option value="">Select a role...</option>
                            <option value="order_manager">Order Manager</option>
                            <option value="product_manager">Product Manager</option>
                        </select>
                        <div class="role-description" id="roleDescription">
                            <i class="fas fa-info-circle"></i> Select a role to see permissions
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn-primary" id="modalSubmit">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reset Password Modal -->
        <div class="modal" id="resetModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>
                        <i class="fas fa-key" style="color: var(--warning);"></i>
                        Reset Password
                    </h2>
                    <button class="close-modal" onclick="closeResetModal()">&times;</button>
                </div>

                <div style="padding: 1rem 0;">
                    <p style="margin-bottom: 1.5rem;">Set a new password for <strong id="resetEmployeeName">Juan Dela Cruz</strong></p>
                    
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" placeholder="Enter new password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" placeholder="Confirm new password" required>
                    </div>
                </div>

                <div class="modal-actions">
                    <button class="btn-secondary" onclick="closeResetModal()">Cancel</button>
                    <button class="btn-primary" onclick="confirmReset()" style="background: var(--warning);">Update Password</button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal" id="deleteModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>
                        <i class="fas fa-exclamation-triangle" style="color: var(--danger);"></i>
                        Remove Employee
                    </h2>
                    <button class="close-modal" onclick="closeDeleteModal()">&times;</button>
                </div>

                <div style="text-align: center; padding: 1.5rem 0;">
                    <i class="fas fa-user-minus" style="font-size: 3rem; color: var(--danger); margin-bottom: 1rem;"></i>
                    <p>Are you sure you want to remove <strong id="deleteEmployeeName">Juan Dela Cruz</strong>?</p>
                    <p style="color: #7f8c8d; font-size: 0.9rem; margin-top: 0.5rem;">They will no longer be able to access the dashboard.</p>
                </div>

                <div class="modal-actions">
                    <button class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                    <button class="btn-primary" onclick="confirmDelete()" style="background: var(--danger);">Remove</button>
                </div>
            </div>
        </div>

        <footer style="margin-top: 3rem; text-align: center; padding: 2rem 0; color: #95a5a6; font-size: 0.9rem; border-top: 1px solid #ebedf0;">
            © 2026 Seller Dashboard. All rights reserved.
        </footer>

    </main>
</div>

<!-- Logout Confirmation Modal -->
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
// Role descriptions
const roleDescriptions = {
    order_manager: 'Can view all orders, update order status (pending → shipped → delivered), and view customer information. Cannot add or edit products.',
    product_manager: 'Can add new products, edit product details (name, price, stock), and upload product images. Cannot view orders.'
};

function updateRoleDescription() {
    const role = document.getElementById('role')?.value;
    const desc = document.getElementById('roleDescription');
    if (!desc) return;

    if (role && roleDescriptions[role]) {
        const icon = role === 'order_manager' ? 'fa-eye' : 'fa-plus-circle';
        desc.innerHTML = `<i class="fas ${icon}"></i> ${roleDescriptions[role]}`;
    } else {
        desc.innerHTML = '<i class="fas fa-info-circle"></i> Select a role to see permissions';
    }
}

function openAddModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Add New Employee';
    document.getElementById('passwordGroup').style.display = 'block';
    document.getElementById('modalSubmit').textContent = 'Add Employee';
    document.getElementById('employeeForm').reset();
    document.getElementById('editEmployeeId').value = '';
    document.getElementById('employeeModal').classList.add('active');
    updateRoleDescription();
}

function closeModal() {
    document.getElementById('employeeModal').classList.remove('active');
}

// Placeholder for other actions
function openEditModal(id)    { alert('Edit employee #' + id + ' → coming soon'); }
function resetPassword(id)    { alert('Reset password for #' + id + ' → coming soon'); }
function deleteEmployee(id)   { alert('Delete employee #' + id + ' → coming soon'); }

// Close modal on outside click / Esc
document.addEventListener('click', e => {
    if (e.target.classList.contains('modal') || e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.active, .modal-overlay.active').forEach(m => m.classList.remove('active'));
    }
});

// Replace your existing toggleSidebar() with this improved version
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;
    fmain-content
    sidebar.classList.toggle('active');
    
    // Optional: close sidebar when clicking a nav link on mobile
    const navLinks = sidebar.querySelectorAll('.nav-item');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 900) {
                sidebar.classList.remove('active');
            }
        }, { once: true }); // only once per open
    });
}

// Close sidebar when clicking overlay (outside)
document.addEventListener('click', function(e) {
    const sidebar = document.querySelector('.sidebar');
    const menuBtn = document.querySelector('.mobile-menu-btn');
    
    if (!sidebar || !sidebar.classList.contains('active')) return;
    
    // Clicked outside sidebar and not on hamburger
    if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
        sidebar.classList.remove('active');
    }
});

// ────────────────────────────────────────────────
// Role & Status Filter - Client-side only
// ────────────────────────────────────────────────
function applyFilters() {
    const roleFilter = document.getElementById('filterRole')?.value || '';
    const statusFilter = document.getElementById('filterStatus')?.value || '';

    const rows = document.querySelectorAll('#employeesTable tbody tr');

    rows.forEach(row => {
        const rowRole   = row.getAttribute('data-role') || '';
        const rowStatus = row.getAttribute('data-status') || '';

        const matchRole   = !roleFilter   || rowRole === roleFilter;
        const matchStatus = !statusFilter || rowStatus === statusFilter;

        row.style.display = (matchRole && matchStatus) ? '' : 'none';
    });
}

// Attach listeners
document.getElementById('filterRole')?.addEventListener('change', applyFilters);
document.getElementById('filterStatus')?.addEventListener('change', applyFilters);

// Run once on load (in case of pre-selected values, though unlikely)
document.addEventListener('DOMContentLoaded', applyFilters);
</script>

</body>
</html>