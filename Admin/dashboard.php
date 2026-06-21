<?php
// Admin/dashboard.php
require_once '../includes/auth.php';
checkRole(['Administrator']);

$admin_name = getCurrentUserName();
$admin_id = $_SESSION['employee_number'];

// Get success/error messages from URL
$success_message = '';
$error_message = '';

if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}

// Get statistics
$total_employees = $conn->query("SELECT COUNT(*) as count FROM users WHERE role != 'Administrator'")->fetch_assoc()['count'];
$total_reservations = $conn->query("SELECT COUNT(*) as count FROM reservations")->fetch_assoc()['count'];
$pending_reservations = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'Pending'")->fetch_assoc()['count'];
$active_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status != 'Ready' AND order_status != 'Cancelled'")->fetch_assoc()['count'];


$recent_reservations = $conn->query("SELECT * FROM reservations ORDER BY created_at DESC LIMIT 5");
?>

<?php if ($success_message): ?>
    <div style="background: #1a3a1a; border: 1px solid #4caf50; color: #a0e0a0; padding: 12px 15px; border-radius: 4px; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div style="background: #3a1a1a; border: 1px solid #ff6b6b; color: #ffa0a0; padding: 12px 15px; border-radius: 4px; margin-bottom: 20px;">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>LUXE · ADMIN DASHBOARD</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

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
        }

        .logo-sidebar {
            font-size: 22px;
            font-weight: 500;
            letter-spacing: 3px;
            color: #ffffff;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .logo-subtitle-sidebar {
            font-size: 14px;
            color: #fcfcfc;
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
            color: #f8f8f8;
            text-decoration: none;
            font-size: 13px;
            border-radius: 4px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .nav-item:hover {
            background-color: rgba(212, 175, 55, 0.1);
            color: #bd9923b0;
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
            color: #f5f5f5;
        }

        .user-role {
            color: #ffffff;
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
            padding: 30px;
            overflow-y: auto;
            flex: 1;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 1px;
            color: #ffffff;
            margin-bottom: 8px;
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.05) 0%, rgba(0, 0, 0, 0.3) 100%);
            border: 1px solid rgba(212, 175, 55, 0.2);
            padding: 20px;
            border-radius: 4px;
        }

        .stat-label {
            font-size: 11px;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 28px;
            color: #d4af37;
            font-weight: 300;
            letter-spacing: 1px;
        }

        .stat-sublabel {
            font-size: 10px;
            color: #666666;
            margin-top: 8px;
        }

        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
            padding-bottom: 15px;
        }

        .filter-tab {
            padding: 8px 16px;
            background: transparent;
            border: 1px solid rgba(212, 175, 55, 0.2);
            color: #a89436;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 3px;
            text-transform: uppercase;
            font-weight: 500;
        }

        .filter-tab:hover {
            border-color: rgba(212, 175, 55, 0.4);
            color: #d4af37;
        }

        .filter-tab.active {
            background-color: #d4af37;
            border-color: #d4af37;
            color: #0a0a0a;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table thead {
            border-bottom: 2px solid rgba(212, 175, 55, 0.2);
        }

        .data-table th {
            padding: 15px;
            text-align: left;
            font-size: 11px;
            color: #a89436;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            background-color: rgba(212, 175, 55, 0.05);
        }

        .data-table td {
            padding: 14px 15px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
            font-size: 13px;
            color: #e0e0e0;
        }

        .data-table tbody tr:hover {
            background-color: rgba(212, 175, 55, 0.05);
        }

        .guest-info {
            color: #e0e0e0;
            font-weight: 500;
        }

        .guest-contact {
            font-size: 11px;
            color: #888888;
            margin-top: 3px;
        }

        .guest-phone {
            font-size: 10px;
            color: #888888;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .status-confirmed {
            background-color: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.4);
        }

        .status-pending {
            background-color: rgba(33, 150, 243, 0.2);
            color: #2196f3;
            border: 1px solid rgba(33, 150, 243, 0.4);
        }

        .status-declined {
            background-color: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.4);
        }

        .notification-badge {
            background-color: rgba(255, 152, 0, 0.2);
            color: #ff9800;
            font-size: 9px;
            padding: 3px 8px;
            border-radius: 2px;
            display: inline-block;
            margin-right: 5px;
        }

        .action-btn {
            padding: 6px 12px;
            background: transparent;
            border: 1px solid rgba(212, 175, 55, 0.2);
            color: #a89436;
            font-size: 10px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 3px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-right: 5px;
            margin-bottom: 5px;
            text-decoration: none;
            display: inline-block;
        }

        .action-btn:hover {
            border-color: rgba(212, 175, 55, 0.4);
            background-color: rgba(212, 175, 55, 0.1);
            color: #d4af37;
        }

        .action-btn.success {
            border-color: rgba(76, 175, 80, 0.3);
            color: #4caf50;
        }

        .action-btn.success:hover {
            background-color: rgba(76, 175, 80, 0.1);
            border-color: rgba(76, 175, 80, 0.5);
        }

        .action-btn.danger {
            border-color: rgba(244, 67, 54, 0.3);
            color: #ff6b6b;
        }

        .action-btn.danger:hover {
            background-color: rgba(244, 67, 54, 0.1);
            border-color: rgba(244, 67, 54, 0.5);
        }

        .deposit-amount {
            color: #d4af37;
            font-weight: 600;
        }

        .deposit-status {
            font-size: 10px;
            color: #a89436;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .alert-banner {
            background-color: rgba(255, 152, 0, 0.15);
            border: 1px solid rgba(255, 152, 0, 0.3);
            padding: 12px 15px;
            border-radius: 3px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .alert-content {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ff9800;
            font-size: 12px;
        }

        .alert-close {
            background: none;
            border: none;
            color: #ff9800;
            cursor: pointer;
            font-size: 18px;
        }

        .text-muted {
            color: #666;
            font-size: 12px;
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 180px;
                padding: 20px 15px;
            }

            .main-content {
                margin-left: 180px;
            }

            .content {
                padding: 20px;
            }

            .page-title {
                font-size: 24px;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
        }
    </style>
</head>

<body>
<div class="sidebar">
    <div class="logo-sidebar">LUXE</div>
    <div class="logo-subtitle-sidebar">MANAGEMENT</div>

    <div class="nav-group">

    <a href="dashboard.php" class="nav-item active">
        <i class="fas fa-calendar-check nav-icon"></i>
        <span>Reservations</span>
    </a>

    <a href="employees.php" class="nav-item">
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
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="top-nav">
        <div class="breadcrumb">
            <span class="breadcrumb-home">LUXE</span> > Reservations
        </div>

        <div class="user-profile">
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
            <div class="page-title">Reservations</div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Employees</div>
                <div class="stat-value"><?php echo $total_employees; ?></div>
                <div class="stat-sublabel">Active Staff</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Active Orders</div>
                <div class="stat-value"><?php echo $active_orders; ?></div>
                <div class="stat-sublabel">Being Served</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Total Reservations</div>
                <div class="stat-value"><?php echo $total_reservations; ?></div>
                <div class="stat-sublabel">All Time</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Pending Reservations</div>
                <div class="stat-value"><?php echo $pending_reservations; ?></div>
                <div class="stat-sublabel">Awaiting Review</div>
            </div>
        </div>

        <div class="alert-banner" id="alertBanner">
            <div class="alert-content">
            </div>
            <button class="alert-close" id="closeAlert">✕</button>
        </div>

        <div class="filter-tabs">
            <div class="filter-tab active" data-filter="all">All</div>
            <div class="filter-tab" data-filter="pending">Pending</div>
            <div class="filter-tab" data-filter="confirmed">Confirmed</div>
            <div class="filter-tab" data-filter="declined">Declined</div>
        </div>

       <table class="data-table">
    <thead>
        <tr>
            <th>Guest</th>
            <th>Date & Time</th>
            <th>Party</th>
            <th>Special Requests</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody id="reservationTable">
    <?php if ($recent_reservations->num_rows > 0): ?>
        <?php while ($res = $recent_reservations->fetch_assoc()): ?>
        <tr>
            <td>
                <div class="guest-info"><?php echo $res['first_name'] . ' ' . $res['last_name']; ?></div>
                <div class="guest-contact"><?php echo $res['email']; ?></div>
            </td>
            <td>
                <?php echo date('Y-m-d', strtotime($res['reservation_date'])); ?><br>
                <?php echo date('H:i', strtotime($res['reservation_time'])); ?>
            </td>
            <td><?php echo $res['number_of_guests'] ?: $res['guests']; ?></td>
            <td><?php echo $res['occasion'] ?: 'None'; ?></td>
            <td>
                <span class="status-badge status-<?php echo strtolower($res['status']); ?>">
                    <?php echo ucfirst($res['status']); ?>
                </span>
            </td>
            <td>
                <?php if ($res['status'] == 'Pending'): ?>
                    <a href="approve-reservation.php?id=<?php echo $res['reservation_id']; ?>" class="action-btn success" onclick="return confirm('Confirm this reservation?')">Accept</a>
                    <a href="decline-reservation.php?id=<?php echo $res['reservation_id']; ?>" class="action-btn danger" onclick="return confirm('Decline this reservation?')">Decline</a>
                <?php else: ?>
                    <span class="text-muted">No actions</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" style="text-align: center; color: #666;">No reservations found</td>
        </tr>
    <?php endif; ?>
</tbody>
</table>

<script>
    /* ALERT CLOSE */
    document.getElementById("closeAlert").addEventListener("click", function () {
        document.getElementById("alertBanner").style.display = "none";
    });

    /* FILTER */
    const tabs = document.querySelectorAll(".filter-tab");
    const rows = document.querySelectorAll("#reservationTable tr");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {

            tabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");

            const filter = tab.getAttribute("data-filter");

            rows.forEach(row => {
                const statusEl = row.querySelector(".status-badge");
                if (!statusEl) return;

                const status = statusEl.textContent.toLowerCase();

                if (filter === "all") {
                    row.style.display = "";
                } else {
                    row.style.display = status.includes(filter) ? "" : "none";
                }
            });
        });
    });

    /* VIEW */
    document.querySelectorAll(".view-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const row = btn.closest("tr");
            const name = row.querySelector(".guest-info").textContent;
            alert("Viewing reservation for: " + name);
        });
    });
</script>

</body>
</html>