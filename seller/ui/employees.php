<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        * { margin:0; padding:0; box-sizing:border-box; }

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

        /* ─── Sidebar (identical to orders page) ─── */
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
        }

        .user-profile { display: flex; align-items: center; gap: 10px; }

        .avatar {
            width: 38px; height: 38px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: grid; place-items: center;
            font-weight: bold; font-size: 1.1rem;
        }

        .logout-btn { color: #e74c3c; font-size: 1.3rem; text-decoration: none; }

        /* ─── Main Content ─── */
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

        .search-box {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid #d1d9e0;
            border-radius: 6px;
            padding: 0.5rem 1rem;
        }

        .search-box input {
            border: none;
            outline: none;
            width: 220px;
            font-size: 0.95rem;
        }

        .date-display { color: #7f8c8d; font-size: 0.95rem; white-space: nowrap; }

        /* ─── Employee Specific Styles ─── */
        .employees-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.8rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .add-employee-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: background 0.15s;
        }

        .add-employee-btn:hover {
            background: #2980b9;
        }

        .filter-group {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .filter-select {
            padding: 0.6rem 2rem 0.6rem 1rem;
            border: 1px solid #d1d9e0;
            border-radius: 6px;
            background: white;
            font-size: 0.95rem;
            cursor: pointer;
            min-width: 160px;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.7rem center;
            background-size: 1rem;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1.2rem 1rem;
            text-align: left;
            border-bottom: 1px solid #eef2f6;
        }

        th {
            background: #f8fafc;
            color: #4b5e7a;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        tr:hover { background: #f8fafc; }

        .employee-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .employee-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .employee-avatar.owner {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        }

        .employee-details {
            display: flex;
            flex-direction: column;
        }

        .employee-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .employee-email {
            font-size: 0.85rem;
            color: #7f8c8d;
        }

        .role-badge {
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 500;
            display: inline-block;
        }

        .role-owner { background: #f1c40f20; color: #f39c12; }
        .role-order-tracker { background: #3498db20; color: #3498db; }
        .role-product-adder { background: #2ecc7120; color: #27ae60; }

        .status-badge {
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 500;
        }

        .status-active { background: #e8f5e9; color: #2e7d32; }
        .status-inactive { background: #ffebee; color: #c62828; }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            background: none;
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.15s;
        }

        .edit-btn { color: var(--primary); }
        .edit-btn:hover { background: #e8f4fd; }

        .reset-btn { color: var(--warning); }
        .reset-btn:hover { background: #fff3e0; }

        .delete-btn { color: var(--danger); }
        .delete-btn:hover { background: #ffebee; }

        /* ─── Modal Styles ─── */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #7f8c8d;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2c3e50;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #d1d9e0;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .form-group select {
            width: 100%;
            padding: 0.8rem 2rem 0.8rem 1rem;
            border: 1px solid #d1d9e0;
            border-radius: 6px;
            font-size: 0.95rem;
            background: white;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1rem;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .role-description {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: #f8fafc;
            border-radius: 4px;
            border-left: 3px solid var(--primary);
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-primary, .btn-secondary {
            flex: 1;
            padding: 0.8rem;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-secondary {
            background: #f8fafc;
            color: #2c3e50;
            border: 1px solid #d1d9e0;
        }

        .btn-secondary:hover {
            background: #eef2f6;
        }

        .nav-item:hover, .nav-item.active {
            background: #e8f4fd;
            color: var(--primary);
        }

        @media (max-width: 900px) {
            .dashboard-container { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }

        @media (max-width: 768px) {
            .main-content { padding: 1rem; }
            .action-buttons { flex-direction: column; }
        }
    </style>
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
                <div class="avatar">T</div>
                <div>
                    <h4>Titoy</h4>
                    <p>Owner</p>
                </div>
            </div>
            <a href="#" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
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
                    <option value="order">Order Trackers</option>
                    <option value="product">Product Adders</option>
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

                    <!-- Order Tracker Employees -->
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
                            <span class="role-badge role-order-tracker">Order Tracker</span>
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
                            <span class="role-badge role-order-tracker">Order Tracker</span>
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

                    <!-- Product Adder Employees -->
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
                            <span class="role-badge role-product-adder">Product Adder</span>
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
                            <option value="order_tracker">Order Tracker</option>
                            <option value="product_adder">Product Adder</option>
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

<script>
    // Role descriptions
    const roleDescriptions = {
        order_tracker: 'Can view all orders, update order status (pending → shipped → delivered), and view customer information. Cannot add or edit products.',
        product_adder: 'Can add new products, edit product details (name, price, stock), and upload product images. Cannot view orders.'
    };

    // Update role description when selection changes
    function updateRoleDescription() {
        const role = document.getElementById('role').value;
        const descDiv = document.getElementById('roleDescription');
        
        if (role && roleDescriptions[role]) {
            let icon = role === 'order_tracker' ? 'fa-eye' : 'fa-plus-circle';
            descDiv.innerHTML = `<i class="fas ${icon}"></i> ${roleDescriptions[role]}`;
        } else {
            descDiv.innerHTML = '<i class="fas fa-info-circle"></i> Select a role to see permissions';
        }
    }

    // Modal functions
    function openAddModal() {
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Add New Employee';
        document.getElementById('passwordGroup').style.display = 'block';
        document.getElementById('modalSubmit').textContent = 'Add Employee';
        document.getElementById('employeeForm').reset();
        document.getElementById('roleDescription').innerHTML = '<i class="fas fa-info-circle"></i> Select a role to see permissions';
        document.getElementById('employeeModal').classList.add('active');
    }

    function openEditModal(employeeId) {
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Edit Employee';
        document.getElementById('passwordGroup').style.display = 'none';
        document.getElementById('modalSubmit').textContent = 'Save Changes';
        
        // Simulate loading employee data
        if (employeeId === 2) {
            document.getElementById('fullName').value = 'Juan Dela Cruz';
            document.getElementById('email').value = 'juan@mybusiness.com';
            document.getElementById('role').value = 'order_tracker';
            document.getElementById('status').value = 'active';
        } else if (employeeId === 3) {
            document.getElementById('fullName').value = 'Maria Santos';
            document.getElementById('email').value = 'maria@mybusiness.com';
            document.getElementById('role').value = 'product_adder';
            document.getElementById('status').value = 'active';
        } else if (employeeId === 4) {
            document.getElementById('fullName').value = 'Pedro Reyes';
            document.getElementById('email').value = 'pedro@mybusiness.com';
            document.getElementById('role').value = 'order_tracker';
            document.getElementById('status').value = 'inactive';
        }
        
        updateRoleDescription();
        document.getElementById('employeeModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('employeeModal').classList.remove('active');
    }

    // Owner functions
    function showOwnerMessage() {
        alert('This is your owner account. To reset your password, go to Profile Settings.');
    }

    // Reset password functions
    function resetPassword(employeeId) {
        const names = {
            2: 'Juan Dela Cruz',
            3: 'Maria Santos',
            4: 'Pedro Reyes'
        };
        document.getElementById('resetEmployeeName').textContent = names[employeeId] || 'Employee';
        document.getElementById('resetModal').classList.add('active');
    }

    function closeResetModal() {
        document.getElementById('resetModal').classList.remove('active');
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
    }

    function confirmReset() {
        const newPass = document.getElementById('newPassword').value;
        const confirmPass = document.getElementById('confirmPassword').value;
        
        if (!newPass || !confirmPass) {
            alert('Please fill in both password fields');
            return;
        }
        
        if (newPass !== confirmPass) {
            alert('Passwords do not match');
            return;
        }
        
        if (newPass.length < 6) {
            alert('Password must be at least 6 characters long');
            return;
        }
        
        alert('Password has been updated successfully!');
        closeResetModal();
    }

    // Delete functions
    function deleteEmployee(employeeId) {
        const names = {
            2: 'Juan Dela Cruz',
            3: 'Maria Santos',
            4: 'Pedro Reyes'
        };
        document.getElementById('deleteEmployeeName').textContent = names[employeeId] || 'Employee';
        document.getElementById('deleteModal').classList.add('active');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
    }

    function confirmDelete() {
        alert('Employee has been removed from your team.');
        closeDeleteModal();
    }

    // Form submission
    document.getElementById('employeeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const role = document.getElementById('role').value;
        if (!role) {
            alert('Please select a role for the employee');
            return;
        }
        
        const modalTitle = document.getElementById('modalTitle').innerText;
        if (modalTitle.includes('Add')) {
            alert('Employee added successfully! They can now log in with the provided password.');
        } else {
            alert('Employee information updated successfully!');
        }
        
        closeModal();
    });

    // Filter functions
    function filterRole(role) {
        console.log('Filtering by role:', role);
    }

    function filterStatus(status) {
        console.log('Filtering by status:', status);
    }

    // Search functionality
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            console.log('Searching for:', e.target.value);
        });
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const modals = ['employeeModal', 'resetModal', 'deleteModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (event.target === modal) {
                modal.classList.remove('active');
            }
        });
    }
</script>

</body>
</html>