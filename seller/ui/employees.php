<?php
// /seller/ui/employees.php
require_once __DIR__ . '/../backend/session/auth.php';
require_once __DIR__ . '/../../connection/db_connection.php';

// Fetch real employees for this seller (only those not removed)
$stmt = $conn->prepare("
    SELECT id, full_name, email, role, status, last_login
    FROM employees
    WHERE seller_id = ? AND (is_removed IS NULL OR is_removed = FALSE)
    ORDER BY full_name ASC
");
$stmt->execute([$seller_id]);
$employees = $stmt->fetchAll();

// Process each employee to check last_login and update status if needed
foreach ($employees as &$employee) {
    $needs_update = false;
    $new_status = $employee['status'];
    
    // Check if last_login is null or 7 days or more old
    if ($employee['last_login'] === null) {
        if ($employee['status'] !== 'inactive') {
            $needs_update = true;
            $new_status = 'inactive';
        }
        $employee['status'] = 'inactive'; // Update for display
    } else {
        $last_login = new DateTime($employee['last_login']);
        $now = new DateTime();
        $interval = $last_login->diff($now);
        
        // If 7 days or more have passed
        if ($interval->days >= 7) {
            if ($employee['status'] !== 'inactive') {
                $needs_update = true;
                $new_status = 'inactive';
            }
            $employee['status'] = 'inactive'; // Update for display
        }
    }
    
    // Update database if status needs to change
    if ($needs_update) {
        $update_stmt = $conn->prepare("
            UPDATE employees 
            SET status = ? 
            WHERE id = ? AND seller_id = ?
        ");
        $update_stmt->execute([$new_status, $employee['id'], $seller_id]);
    }
}

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
                            <th>Action</th>
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
                                        <button class="action-btn delete-btn" onclick="openDeleteModal(<?= (int)$emp['id'] ?>, '<?= htmlspecialchars($emp['full_name'] ?? 'this employee') ?>')" title="Remove">
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
                    <p>Are you sure you want to remove <strong id="deleteEmployeeName">this employee</strong>?</p>
                    <p style="color: #7f8c8d; font-size: 0.9rem; margin-top: 0.5rem;">This action can't be undone.</p>
                    <input type="hidden" id="deleteEmployeeId" value="">
                </div>

                <div class="modal-actions">
                    <button class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                    <button class="btn-primary" onclick="confirmDelete()" style="background: var(--danger);">Remove</button>
                </div>
            </div>
        </div>

        <!-- Delete Success/Error Message Toast (hidden by default) -->
        <div id="deleteToast" class="toast" style="display: none; position: fixed; bottom: 20px; right: 20px; background: white; padding: 1rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 2000; border-left: 4px solid var(--success);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-check-circle" style="color: var(--success); font-size: 1.2rem;"></i>
                <span id="toastMessage">Employee removed successfully</span>
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

// Delete modal functions
function openDeleteModal(id, name) {
    document.getElementById('deleteEmployeeId').value = id;
    document.getElementById('deleteEmployeeName').textContent = name;
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
}

function confirmDelete() {
    const employeeId = document.getElementById('deleteEmployeeId').value;
    
    if (!employeeId) {
        showToast('Error: No employee selected', 'error');
        closeDeleteModal();
        return;
    }
    
    // Show loading state on button
    const deleteBtn = document.querySelector('#deleteModal .btn-primary');
    const originalText = deleteBtn.textContent;
    deleteBtn.textContent = 'Removing...';
    deleteBtn.disabled = true;
    
    // Send AJAX request to delete employee
    fetch('/seller/backend/employees/delete-employee.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'employee_id=' + encodeURIComponent(employeeId)
    })
    .then(response => response.json())
    .then(data => {
        deleteBtn.textContent = originalText;
        deleteBtn.disabled = false;
        
        if (data.success) {
            showToast('Employee removed successfully', 'success');
            closeDeleteModal();
            
            // Remove the employee row from the table
            setTimeout(() => {
                location.reload(); // Simple reload to refresh the list
            }, 1000);
        } else {
            showToast('Error: ' + (data.message || 'Failed to remove employee'), 'error');
        }
    })
    .catch(error => {
        deleteBtn.textContent = originalText;
        deleteBtn.disabled = false;
        showToast('Error: Network error - ' + error.message, 'error');
        console.error('Error:', error);
    });
}

// Toast notification function
function showToast(message, type = 'success') {
    const toast = document.getElementById('deleteToast');
    const toastMessage = document.getElementById('toastMessage');
    const icon = toast.querySelector('i');
    
    toastMessage.textContent = message;
    
    if (type === 'error') {
        toast.style.borderLeftColor = 'var(--danger)';
        icon.style.color = 'var(--danger)';
        icon.className = 'fas fa-exclamation-circle';
    } else {
        toast.style.borderLeftColor = 'var(--success)';
        icon.style.color = 'var(--success)';
        icon.className = 'fas fa-check-circle';
    }
    
    toast.style.display = 'block';
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Toggle sidebar function
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;
    
    sidebar.classList.toggle('active');
    
    // Optional: close sidebar when clicking a nav link on mobile
    const navLinks = sidebar.querySelectorAll('.nav-item');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 900) {
                sidebar.classList.remove('active');
            }
        }, { once: true });
    });
}

// Close sidebar when clicking outside
document.addEventListener('click', function(e) {
    const sidebar = document.querySelector('.sidebar');
    const menuBtn = document.querySelector('.mobile-menu-btn');
    
    if (!sidebar || !sidebar.classList.contains('active')) return;
    
    // Clicked outside sidebar and not on hamburger
    if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
        sidebar.classList.remove('active');
    }
});

// Close modals on outside click / Esc
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

// Role & Status Filter
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

// Search functionality
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#employeesTable tbody tr');
    
    rows.forEach(row => {
        const employeeName = row.querySelector('.employee-name')?.textContent.toLowerCase() || '';
        const employeeEmail = row.querySelector('.employee-email')?.textContent.toLowerCase() || '';
        const role = row.querySelector('.role-badge')?.textContent.toLowerCase() || '';
        
        if (employeeName.includes(searchTerm) || employeeEmail.includes(searchTerm) || role.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Attach filter listeners
document.getElementById('filterRole')?.addEventListener('change', applyFilters);
document.getElementById('filterStatus')?.addEventListener('change', applyFilters);

// Run on load
document.addEventListener('DOMContentLoaded', applyFilters);
</script>

</body>
</html>