<?php
// /seller/ui/employees.php
require_once __DIR__ . '/../backend/session/auth.php';

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

    <!-- Sidebar ── identical to orders page -->
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
            <!-- Changed to button that opens modal -->
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
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search employees...">
                </div>
                <div class="date-display">March 13, 2026</div>
            </div>
        </header>

        <div class="employees-header">
            <h2>Team Members (4)</h2>
            <div class="filter-group">
                <select class="filter-select" onchange="filterRole(this.value)">
                    <option value="">All Roles</option>
                    <option value="order">Order Manager</option>
                    <option value="product">Product Manager</option>
                </select>
                <select class="filter-select" onchange="filterStatus(this.value)">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button class="add-employee-btn" onclick="openAddModal()">
                    <i class="fas fa-user-plus"></i>
                    Add Employee
                </button>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <!-- Order Manager Employees -->
                    <tr>
                        <td>
                            <div class="employee-info">
                                <div class="employee-avatar">J</div>
                                <div class="employee-details">
                                    <span class="employee-name">Juan Dela Cruz</span>
                                    <span class="employee-email">juan@mybusiness.com</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge role-order-tracker">Order Manager</span>
                            <div style="font-size: 0.75rem; color: #7f8c8d; margin-top: 4px;">
                                <i class="fas fa-eye"></i> Can view & update orders
                            </div>
                        </td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn edit-btn" onclick="openEditModal(2)" title="Edit"><i class="fas fa-edit"></i></button>
                                <button class="action-btn reset-btn" onclick="resetPassword(2)" title="Reset Password"><i class="fas fa-key"></i></button>
                                <button class="action-btn delete-btn" onclick="deleteEmployee(2)" title="Remove"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div class="employee-info">
                                <div class="employee-avatar">P</div>
                                <div class="employee-details">
                                    <span class="employee-name">Pedro Reyes</span>
                                    <span class="employee-email">pedro@mybusiness.com</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge role-order-tracker">Order Manager</span>
                            <div style="font-size: 0.75rem; color: #7f8c8d; margin-top: 4px;">
                                <i class="fas fa-eye"></i> Can view & update orders
                            </div>
                        </td>
                        <td><span class="status-badge status-inactive">Inactive</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn edit-btn" onclick="openEditModal(4)"><i class="fas fa-edit"></i></button>
                                <button class="action-btn reset-btn" onclick="resetPassword(4)"><i class="fas fa-key"></i></button>
                                <button class="action-btn delete-btn" onclick="deleteEmployee(4)"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>

                    <!-- Product Manager Employees -->
                    <tr>
                        <td>
                            <div class="employee-info">
                                <div class="employee-avatar">M</div>
                                <div class="employee-details">
                                    <span class="employee-name">Maria Santos</span>
                                    <span class="employee-email">maria@mybusiness.com</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge role-product-adder">Product Manager</span>
                            <div style="font-size: 0.75rem; color: #7f8c8d; margin-top: 4px;">
                                <i class="fas fa-plus-circle"></i> Can add & edit products
                            </div>
                        </td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn edit-btn" onclick="openEditModal(3)"><i class="fas fa-edit"></i></button>
                                <button class="action-btn reset-btn" onclick="resetPassword(3)"><i class="fas fa-key"></i></button>
                                <button class="action-btn delete-btn" onclick="deleteEmployee(3)"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Add/Edit Employee Modal -->
        <div class="modal" id="employeeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>
                        <span id="modalTitle">Add New Employee</span>
                    </h2>
                    <button class="close-modal" onclick="closeModal()">&times;</button>
                </div>

                <form id="employeeForm">
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" placeholder="e.g., Juan Dela Cruz" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" placeholder="employee@business.com" required>
                    </div>

                    <div class="form-group" id="passwordGroup">
                        <label for="password">Password</label>
                        <input type="password" id="password" placeholder="Enter password" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Employee Role</label>
                        <select id="role" required onchange="updateRoleDescription()">
                            <option value="">Select a role...</option>
                            <option value="order_tracker">Order Manager</option>
                            <option value="product_adder">Product Manager</option>
                        </select>
                        <div class="role-description" id="roleDescription">
                            <i class="fas fa-info-circle"></i>
                            Select a role to see permissions
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
// ────────────────────────────────────────────────
// Constants & Data
// ────────────────────────────────────────────────
const roleDescriptions = {
    order_manager: {
        icon: 'fa-eye',
        text: 'Can view all orders, update order status (pending → shipped → delivered), and view customer information. Cannot add or edit products.'
    },
    product_manager: {
        icon: 'fa-plus-circle',
        text: 'Can add new products, edit product details (name, price, stock), and upload product images. Cannot view orders.'
    }
};

// ────────────────────────────────────────────────
// Helper Functions
// ────────────────────────────────────────────────
function updateRoleDescription() {
    const role = document.getElementById('role')?.value;
    const descDiv = document.getElementById('roleDescription');

    if (!descDiv) return;

    if (role && roleDescriptions[role]) {
        const { icon, text } = roleDescriptions[role];
        descDiv.innerHTML = `<i class="fas ${icon}"></i> ${text}`;
    } else {
        descDiv.innerHTML = '<i class="fas fa-info-circle"></i> Select a role to see permissions';
    }
}

function closeAllModals() {
    const modals = [
        document.getElementById('employeeModal'),
        document.getElementById('resetModal'),
        document.getElementById('deleteModal')
    ];

    modals.forEach(modal => {
        if (modal) modal.classList.remove('active');
    });

    // Clear sensitive fields
    const passwordFields = document.querySelectorAll('#password, #newPassword, #confirmPassword');
    passwordFields.forEach(field => field.value = '');
}

// ────────────────────────────────────────────────
// Add / Edit Modal Logic
// ────────────────────────────────────────────────
function openAddModal() {
    const modal = document.getElementById('employeeModal');
    if (!modal) return;

    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Add New Employee';
    document.getElementById('passwordGroup').style.display = 'block';
    document.getElementById('modalSubmit').textContent = 'Add Employee';
    document.getElementById('employeeForm').reset();
    document.getElementById('editEmployeeId').value = '';           // important for add vs edit
    document.getElementById('employeeForm').action = '/seller/backend/employees/add.php';

    updateRoleDescription();
    modal.classList.add('active');
}

function openEditModal(id) {
    // For now: placeholder (you can later fetch via AJAX)
    alert(`Edit employee #${id} → this feature is coming soon.\n\nIn next step we can load real data into the form.`);

    // Example of future real implementation (commented):
    /*
    fetch(`/seller/backend/employees/get.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Edit Employee';
            document.getElementById('fullName').value = data.full_name;
            document.getElementById('email').value = data.email;
            document.getElementById('role').value = data.role;
            document.getElementById('passwordGroup').style.display = 'none';
            document.getElementById('modalSubmit').textContent = 'Save Changes';
            document.getElementById('editEmployeeId').value = id;
            document.getElementById('employeeForm').action = '/seller/backend/employees/update.php';
            updateRoleDescription();
            document.getElementById('employeeModal').classList.add('active');
        });
    */
}

// ────────────────────────────────────────────────
// Reset Password
// ────────────────────────────────────────────────
function resetPassword(id) {
    alert(`Reset password for employee #${id} → coming soon.\n\nWe'll add a secure reset flow next.`);
    // Future: open reset modal + send POST to backend
}

// ────────────────────────────────────────────────
// Delete Employee
// ────────────────────────────────────────────────
function deleteEmployee(id) {
    if (!confirm(`Are you sure you want to remove employee #${id}?\nThis action cannot be undone.`)) {
        return;
    }

    alert(`Employee #${id} would be deleted now (demo mode).\n\nNext step: real DELETE request to backend.`);
    // Future:
    // fetch(`/seller/backend/employees/delete.php?id=${id}`, { method: 'POST' })
    //   .then(() => location.reload());
}

// ────────────────────────────────────────────────
// Client-side Search (real-time filter)
// ────────────────────────────────────────────────
const searchInput = document.getElementById('searchInput');
if (searchInput && document.getElementById('employeesTable')) {
    searchInput.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#employeesTable tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
}

// ────────────────────────────────────────────────
// Form Submit (only for demo/prevention – real submit goes to backend)
// ────────────────────────────────────────────────
const employeeForm = document.getElementById('employeeForm');
if (employeeForm) {
    employeeForm.addEventListener('submit', function(e) {
        // Remove this block when backend is fully working
        // e.preventDefault();   ← comment out when using real POST

        const role = document.getElementById('role')?.value;
        if (!role) {
            e.preventDefault();
            alert('Please select a role.');
            return;
        }

        // Optional: extra client-side validation
        const password = document.getElementById('password')?.value;
        if (document.getElementById('passwordGroup').style.display !== 'none' && password?.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long.');
            return;
        }

        // If you keep e.preventDefault() → show success message
        // alert('Form would be submitted now (demo mode).');
    });
}

// ────────────────────────────────────────────────
// Global Event Listeners
// ────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Close modals when clicking outside
    document.addEventListener('click', function(event) {
        const modals = document.querySelectorAll('.modal.active');
        modals.forEach(modal => {
            if (event.target === modal) {
                closeAllModals();
            }
        });
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });

    // Role change listener
    document.getElementById('role')?.addEventListener('change', updateRoleDescription);
});
</script>

</body>
</html>