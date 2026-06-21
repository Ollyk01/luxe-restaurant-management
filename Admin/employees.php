<?php
// Admin/employees.php
require_once '../includes/auth.php';
checkRole(['Administrator']);

$admin_name = getCurrentUserName();
$admin_id = $_SESSION['employee_number'];

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
                $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
                $role = mysqli_real_escape_string($conn, $_POST['role']);
                $email = mysqli_real_escape_string($conn, $_POST['email']);
                $phone = mysqli_real_escape_string($conn, $_POST['phone']);
                $position = mysqli_real_escape_string($conn, $_POST['position']);
                
                // Generate username based on role
                $username = generateUsername($role);
                $temp_password = bin2hex(random_bytes(4));
                $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                
               $sql = "INSERT INTO users (first_name, last_name, employee_number, username, password, role, status, email, phone, position) 
                VALUES ('$first_name', '$last_name', '$username', '$username', '$hashed_password', '$role', 'active', '$email', '$phone', '$position')";
                
                if ($conn->query($sql)) {
                    $success = "Employee created successfully!<br>
                               <strong>Username:</strong> $username<br>
                               <strong>Password:</strong> $temp_password<br>
                               <small class='text-muted'>Please provide these credentials to the employee.</small>";
                } else {
                    $error = "Error: " . $conn->error;
                }
                break;
                
            case 'toggle_status':
                $user_id = intval($_POST['user_id']);
                $new_status = $_POST['status'] == 'active' ? 'inactive' : 'active';
                $sql = "UPDATE users SET status = '$new_status' WHERE user_id = $user_id AND role != 'Administrator'";
                if ($conn->query($sql)) {
                    header('Location: employees.php');
                    exit();
                }
                break;
                
            case 'delete':
                $user_id = intval($_POST['user_id']);
                $sql = "DELETE FROM users WHERE user_id = $user_id AND role != 'Administrator'";
                if ($conn->query($sql)) {
                    header('Location: employees.php');
                    exit();
                }
                break;
        }
    }
}

// Get all employees
$employees = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

// Count statistics
$total_employees = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$active_employees = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>LUXE · Employee Management</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #0a0a0a;
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            min-height: 100vh;
        }

        .add-btn {
            background-color: #d4af37;
            color: #0a0a0a;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .add-btn:hover {
            background-color: #e5c158;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 200px;
            background-color: #0f0f0f;
            border-right: 1px solid rgba(212, 175, 55, 0.1);
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }

        .logo-sidebar {
            font-size: 22px;
            font-weight: 500;
            letter-spacing: 3px;
            color: #fff;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .logo-subtitle-sidebar {
            font-size: 14px;
            color: #fff;
            letter-spacing: 1px;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .nav-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            border-radius: 4px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .nav-item:hover {
            background-color: rgba(212, 175, 55, 0.1);
            color: #d4af37;
        }

        .nav-item.active {
            background-color: rgba(212, 175, 55, 0.2);
            color: #d4af37;
            border-left: 3px solid #d4af37;
            padding-left: 12px;
        }

        .nav-icon {
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            margin-top: auto;
            border-top: 1px solid rgba(212, 175, 55, 0.1);
            padding-top: 20px;
        }

        .user-info {
            color: #e0e0e0;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .user-name {
            font-weight: 600;
            color: #fff;
        }

        .user-role {
            color: #ffff;
            font-size: 12px;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .logout {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: transparent;
            border: 1px solid rgba(212, 175, 55, 0.2);
            color: #a89436;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 3px;
            width: 100%;
            justify-content: center;
        }

        .logout:hover {
            background-color: rgba(212, 175, 55, 0.1);
            border-color: rgba(212, 175, 55, 0.4);
            color: #d4af37;
        }

        /* Main Content */
        .main-content {
            margin-left: 200px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Top Navigation */
        .top-nav {
            background-color: #0f0f0f;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .breadcrumb {
            font-size: 18px;
            color: #888888;
        }

        .breadcrumb-home {
            color: #a89436;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .notification-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #d4af37;
            color: #0a0a0a;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            cursor: pointer;
        }

        .profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: rgba(212, 175, 55, 0.2);
            display: flex;
            justify-content: center;
            align-items: center;
            color: #d4af37;
            font-size: 14px;
            font-weight: 600;
        }

        .profile-info {
            text-align: right;
        }

        .profile-name {
            color: #e0e0e0;
            font-size: 12px;
            font-weight: 600;
        }

        .profile-role {
            color: #a89436;
            font-size: 10px;
            text-transform: uppercase;
        }

        /* Content Area */
        .content {
            padding: 40px;
            overflow-x: auto;
        }

        .shift-info {
            color: #666;
            font-size: 13px;
            margin-bottom: 30px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #1a3a1a;
            border: 1px solid #4caf50;
            color: #a0e0a0;
        }

        .alert-danger {
            background-color: #3a1a1a;
            border: 1px solid #ff6b6b;
            color: #ffa0a0;
        }

        /* Table */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #1a1a1a;
            border-radius: 4px;
            overflow: hidden;
            min-width: 700px;
        }

        thead {
            background-color: #2a2a2a;
        }

        th {
            padding: 20px;
            text-align: left;
            color: #999;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #3a3a3a;
        }

        td {
            padding: 20px;
            border-bottom: 1px solid #2a2a2a;
            color: #e0e0e0;
            font-size: 14px;
        }

        tr:hover {
            background-color: #2a2a2a;
        }

        .employee-name {
            font-weight: 500;
            display: block;
            margin-bottom: 3px;
        }

        .employee-position {
            color: #888;
            font-size: 12px;
        }

        .employee-id {
            color: #d4af37;
            font-weight: 500;
        }

        .role-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .role-Waiter {
            background-color: #3a4a5a;
            color: #a0b0c0;
        }

        .role-Kitchen-Staff {
            background-color: #5a3a2a;
            color: #e0a080;
        }

        .role-Administrator {
            background-color: #5a5a2a;
            color: #d4af37;
        }

        .contact-info {
            font-size: 12px;
            color: #b0b0b0;
        }

        .contact-info div {
            margin: 3px 0;
        }

        .contact-info i {
            width: 16px;
            color: #a89436;
        }

        .toggle-switch {
            width: 50px;
            height: 26px;
            background-color: #3a3a3a;
            border: none;
            border-radius: 13px;
            cursor: pointer;
            position: relative;
            transition: background-color 0.3s ease;
        }

        .toggle-switch.active {
            background-color: #4caf50;
        }

        .toggle-switch::after {
            content: '';
            position: absolute;
            width: 22px;
            height: 22px;
            background-color: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: left 0.3s ease;
        }

        .toggle-switch.active::after {
            left: 26px;
        }

        .delete-btn {
            background: none;
            border: none;
            color: #d4af37;
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .delete-btn:hover {
            color: #ff6b6b;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .action-btn {
            background: none;
            border: none;
            color: #d4af37;
            cursor: pointer;
            font-size: 16px;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background-color: #2a2a2a;
        }

        .action-btn.danger:hover {
            color: #ff6b6b;
        }

        .text-muted {
            color: #666;
            font-size: 12px;
        }

        .no-data {
            text-align: center;
            color: #666;
            padding: 40px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #2a2a2a;
            padding: 40px;
            border-radius: 4px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .modal-header h3 {
            font-size: 24px;
            color: #e0e0e0;
            font-weight: 300;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            color: #d4af37;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: #e5c158;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #d4af37;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            background-color: #3a3a3a;
            border: 1px solid #4a4a4a;
            border-radius: 4px;
            color: #e0e0e0;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input::placeholder {
            color: #888;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #d4af37;
            background-color: #3a3a3a;
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23d4af37' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
            padding-right: 40px;
        }

        .form-hint {
            background-color: #1a1a3a;
            border: 1px solid #d4af37;
            color: #d4af37;
            padding: 12px 15px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .submit-btn {
            width: 100%;
            background-color: #d4af37;
            color: #0a0a0a;
            border: none;
            padding: 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .submit-btn:hover {
            background-color: #e5c158;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Success Message */
        .success-message {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4caf50;
            color: white;
            padding: 15px 20px;
            border-radius: 4px;
            z-index: 2000;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
            }
            to {
                transform: translateX(0);
            }
        }

        .success-message.show {
            display: block;
        }

        /* RESPONSIVE DESIGN */

        /* Tablet */
        @media (max-width: 1024px) {
            .sidebar {
                width: 180px;
                padding: 20px 15px;
            }

            .main-content {
                margin-left: 180px;
            }

            .content {
                padding: 30px;
            }

            table {
                min-width: 650px;
            }
        }

        /* Small Tablet / Large Phone */
        @media (max-width: 768px) {
            .sidebar {
                width: 160px;
                padding: 15px 10px;
            }

            .sidebar .logo-sidebar {
                font-size: 18px;
            }

            .sidebar .logo-subtitle-sidebar {
                font-size: 11px;
                margin-bottom: 25px;
            }

            .nav-item {
                font-size: 11px;
                padding: 10px 12px;
            }

            .main-content {
                margin-left: 160px;
            }

            .top-nav {
                padding: 15px 20px;
                flex-direction: column;
                align-items: flex-start;
            }

            .breadcrumb {
                font-size: 14px;
            }

            .user-profile {
                width: 100%;
                justify-content: flex-start;
            }

            .add-btn {
                padding: 8px 15px;
                font-size: 12px;
            }

            .content {
                padding: 20px;
            }

            .page-title {
                font-size: 22px;
            }

            table {
                min-width: 550px;
            }

            th, td {
                padding: 12px 15px;
                font-size: 12px;
            }

            .modal-content {
                padding: 30px 20px;
                margin: 15px;
            }
        }

        /* Phone */
        @media (max-width: 480px) {
            .sidebar {
                width: 140px;
                padding: 10px 8px;
            }

            .sidebar .logo-sidebar {
                font-size: 15px;
                letter-spacing: 2px;
            }

            .sidebar .logo-subtitle-sidebar {
                font-size: 9px;
                margin-bottom: 20px;
            }

            .nav-item {
                font-size: 10px;
                padding: 8px 10px;
                gap: 8px;
            }

            .nav-icon {
                font-size: 14px;
                width: 16px;
            }

            .main-content {
                margin-left: 140px;
            }

            .top-nav {
                padding: 10px 15px;
            }

            .breadcrumb {
                font-size: 12px;
            }

            .add-btn {
                padding: 6px 12px;
                font-size: 10px;
            }

            .add-btn i {
                font-size: 10px;
            }

            .profile-avatar {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }

            .profile-name {
                font-size: 10px;
            }

            .profile-role {
                font-size: 8px;
            }

            .content {
                padding: 15px;
            }

            .page-title {
                font-size: 18px;
            }

            .shift-info {
                font-size: 11px;
            }

            table {
                min-width: 480px;
            }

            th, td {
                padding: 8px 10px;
                font-size: 10px;
            }

            .employee-name {
                font-size: 11px;
            }

            .employee-position {
                font-size: 9px;
            }

            .role-badge {
                font-size: 9px;
                padding: 4px 8px;
            }

            .contact-info {
                font-size: 9px;
            }

            .toggle-switch {
                width: 40px;
                height: 20px;
            }

            .toggle-switch::after {
                width: 16px;
                height: 16px;
            }

            .toggle-switch.active::after {
                left: 22px;
            }

            .modal-content {
                padding: 20px 15px;
                margin: 10px;
            }

            .modal-header h3 {
                font-size: 18px;
            }

            .form-group label {
                font-size: 10px;
            }

            .form-group input,
            .form-group select {
                font-size: 12px;
                padding: 10px 12px;
            }

            .submit-btn {
                font-size: 12px;
                padding: 12px;
            }

            .alert {
                font-size: 12px;
                padding: 10px 15px;
            }

            .action-btn {
                font-size: 12px;
                padding: 2px 4px;
            }

            .success-message {
                font-size: 12px;
                padding: 10px 15px;
                bottom: 10px;
                right: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo-sidebar">LUXE</div>
        <div class="logo-subtitle-sidebar">MANAGEMENT</div>

        <div class="nav-group">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-calendar-check nav-icon"></i>
                <span>Reservations</span>
            </a>

            <a href="employees.php" class="nav-item active">
                <i class="fas fa-users nav-icon"></i>
                <span>Employees</span>
            </a>

            <a href="inventory.html" class="nav-item">
                <i class="fas fa-boxes-stacked nav-icon"></i>
                <span>Inventory</span>
            </a>

            <a href="financial.html" class="nav-item">
                <i class="fas fa-wallet nav-icon"></i>
                <span>Financial</span>
            </a>

            <a href="reports.html" class="nav-item">
                <i class="fas fa-chart-pie nav-icon"></i>
                <span>Reports</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-name"><?php echo $admin_name; ?></div>
                <div class="user-role">Administrator</div>
                <div class="user-role"><?php echo $admin_id; ?></div>
            </div>

            <a href="../logout.php" class="logout">
                <i class="fas fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-nav">
            <div class="breadcrumb">
                <span class="breadcrumb-home">LUXE</span> > Employee Management
            </div>

            <div class="user-profile">
                <button class="add-btn" id="addEmployeeBtn">
                    <i class="fas fa-user-plus"></i> Add Employee
                </button>
                <div class="notification-icon">
                    <i class="fa-solid fa-bell"></i>
                </div>

                <div class="profile-avatar"><?php echo substr($admin_name, 0, 1); ?></div>

                <div class="profile-info">
                    <div class="profile-name"><?php echo $admin_name; ?></div>
                    <div class="profile-role">Administrator</div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="page-header">
                <div class="page-title">Employee Management</div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="shift-info"><?php echo $active_employees; ?> / <?php echo $total_employees; ?> active</div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name & Position</th>
                            <th>Role</th>
                            <th>Contact</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTable">
                        <?php if ($employees->num_rows > 0): ?>
                            <?php while ($emp = $employees->fetch_assoc()): ?>
                            <tr>
                                <td class="employee-id"><?php echo $emp['employee_number']; ?></td>
                                <td>
                                    <span class="employee-name"><?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?></span>
                                    <span class="employee-position"><?php echo $emp['role']; ?></span>
                                </td>
                                <td>
                                    <span class="role-badge role-<?php echo str_replace(' ', '-', $emp['role']); ?>">
                                        <?php echo $emp['role']; ?>
                                    </span>
                                </td>
                                <td class="contact-info">
                                    <div><i class="fas fa-envelope"></i> <?php echo isset($emp['email']) && $emp['email'] ? $emp['email'] : 'N/A'; ?></div>
                                    <div><i class="fas fa-phone"></i> <?php echo isset($emp['phone']) && $emp['phone'] ? $emp['phone'] : 'N/A'; ?></div>
                                </td>
                                <td><?php echo date('d M Y', strtotime($emp['created_at'])); ?></td>
                                <td>
                                    <?php if ($emp['role'] != 'Administrator'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?php echo $emp['user_id']; ?>">
                                            <input type="hidden" name="status" value="<?php echo $emp['status']; ?>">
                                            <button type="submit" class="toggle-switch <?php echo $emp['status'] == 'active' ? 'active' : ''; ?>"></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">System</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($emp['role'] != 'Administrator'): ?>
                                            <form method="POST" style="display:inline;" 
                                                  onsubmit="return confirm('Delete <?php echo $emp['first_name']; ?>? This cannot be undone!')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo $emp['user_id']; ?>">
                                                <button type="submit" class="action-btn danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-lock"></i></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">
                                    <i class="fas fa-users" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                                    No employees found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="addEmployeeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Employee</h3>
                <button class="close-btn" id="closeModalBtn">&times;</button>
            </div>
            <form method="POST" id="employeeForm">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" placeholder="Thomas" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Beaumont" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="employee@luxe.com">
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" placeholder="+27 82 123 4567">
                </div>

                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="text" id="position" name="position" placeholder="Senior Waiter">
                </div>

                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" required>
                        <option value="">Select a role</option>
                        <option value="Waiter">Waiter</option>
                        <option value="Kitchen Staff">Kitchen Staff</option>
                        <option value="Administrator">Administrator</option>
                    </select>
                </div>

                <div class="form-hint">
                    <i class="fas fa-info-circle"></i> 
                    Username will be auto-generated based on role (AD, WEP, KD) + 3-digit number.
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-user-plus"></i> Add Employee
                </button>
            </form>
        </div>
    </div>

    <!-- Success Message -->
    <div class="success-message" id="successMessage">
        <i class="fas fa-check-circle"></i> Employee added successfully!
    </div>

    <script>
        const modal = document.getElementById('addEmployeeModal');
        const addBtn = document.getElementById('addEmployeeBtn');
        const closeBtn = document.getElementById('closeModalBtn');
        const successMsg = document.getElementById('successMessage');

        // Open modal
        addBtn.addEventListener('click', () => {
            modal.classList.add('show');
        });

        function closeModal() {
            modal.classList.remove('show');
            document.getElementById('employeeForm').reset();
        }

        closeBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        <?php if ($success): ?>
        document.addEventListener('DOMContentLoaded', function() {
            successMsg.classList.add('show');
            setTimeout(() => {
                successMsg.classList.remove('show');
            }, 4000);
        });
        <?php endif; ?>
    </script>
</body>
</html>