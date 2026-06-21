<?php
// Kitchen/update-order-status.php
require_once '../includes/auth.php';
checkRole(['Kitchen Staff']);

$chef_name = getCurrentUserName();
$chef_id = $_SESSION['employee_number'];

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_sql = "UPDATE orders SET order_status = '$new_status' WHERE order_id = $order_id";
    if ($conn->query($update_sql)) {
        if ($new_status == 'Ready' || $new_status == 'Cancelled') {
            $conn->query("UPDATE restaurant_tables SET status = 'available' 
                          WHERE table_id = (SELECT table_id FROM orders WHERE order_id = $order_id)");
        }
        header('Location: dashboard.php?success=Order status updated');
        exit();
    }
}

// Get all active orders
$sql = "SELECT o.*, u.first_name, u.last_name, rt.table_number 
        FROM orders o
        LEFT JOIN users u ON o.waiter_id = u.user_id
        LEFT JOIN restaurant_tables rt ON o.table_id = rt.table_id
        WHERE o.order_status IN ('Being Prepared', 'Delayed', 'Ready')
        ORDER BY o.created_at ASC";
$active_orders = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>LUXE · Update Orders</title>

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
            color: #d4af37;
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
            margin-bottom: 30px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #a89436;
            text-decoration: none;
            font-size: 18px;
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
            color: #fff;
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
            font-size: 12px;
            margin-bottom: 15px;
        }

        .user-name {
            font-weight: 600;
            color: #fff;
        }

        .user-role {
            color: #ffff;
            font-size: 10px;
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
            color: #ffff;
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
            font-size: 14px;
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
            color: #d4af37;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: #888;
            font-size: 14px;
        }

        /* Alert Messages */
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

        .alert-info {
            background-color: #1a2a4a;
            border: 1px solid #2196f3;
            color: #a0c0ff;
        }

        /* Order Card */
        .order-card {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.05) 0%, rgba(0, 0, 0, 0.3) 100%);
            border: 1px solid rgba(212, 175, 55, 0.2);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .order-number {
            font-size: 14px;
            color: #d4af37;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .order-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #888888;
        }

        .order-time {
            font-size: 12px;
            color: #a89436;
        }

        .order-status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-being-prepared {
            background-color: rgba(255, 152, 0, 0.2);
            color: #ff9800;
            border: 1px solid rgba(255, 152, 0, 0.3);
        }

        .status-ready {
            background-color: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .status-delayed {
            background-color: rgba(33, 150, 243, 0.2);
            color: #2196f3;
            border: 1px solid rgba(33, 150, 243, 0.3);
        }

        .status-cancelled {
            background-color: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        .order-items {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        }

        .order-items-label {
            font-size: 10px;
            color: #a89436;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .order-item {
            font-size: 13px;
            color: #e0e0e0;
            margin-bottom: 6px;
            padding-left: 10px;
        }

        .item-quantity {
            color: #a89436;
            font-weight: 600;
        }

        /* Order Actions */
        .order-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .status-btn {
            background: transparent;
            border: 2px solid;
            padding: 10px 16px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        /* Ready - Green */
        .ready-btn {
            color: #4caf50;
            border-color: #4caf50;
        }

        .ready-btn:hover {
            background: rgba(76, 175, 80, 0.15);
        }

        /* Being Prepared - Amber */
        .preparing-btn {
            color: #ff9800;
            border-color: #ff9800;
        }

        .preparing-btn:hover {
            background: rgba(255, 152, 0, 0.15);
        }

        /* Delayed - Blue */
        .delayed-btn {
            color: #2196f3;
            border-color: #2196f3;
        }

        .delayed-btn:hover {
            background: rgba(33, 150, 243, 0.15);
        }

        /* Cancelled - Red */
        .cancelled-btn {
            color: #f44336;
            border-color: #f44336;
        }

        .cancelled-btn:hover {
            background: rgba(244, 67, 54, 0.15);
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
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-sidebar">LUXE</div>
        <div class="logo-subtitle-sidebar">MANAGEMENT</div>

        <div class="nav-group">
            <a href="dashboard.php" class="nav-item">
                <span class="nav-icon"><i class="fas fa-receipt"></i></span>
                <span>Dashboard</span>
            </a>
            <a href="update-order-status.php" class="nav-item active">
                <span class="nav-icon"><i class="fas fa-edit"></i></span>
                <span>Update Orders</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-name"><?php echo $chef_name; ?></div>
                <div class="user-role">Kitchen Staff</div>
                <div class="user-role"><?php echo $chef_id; ?></div>
            </div>
            <a href="../logout.php" class="logout">
                <i class="fas fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="breadcrumb">
                <span class="breadcrumb-home">LUXE</span> > Update Orders
            </div>
            <div class="user-profile">
                <div class="notification-icon">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div class="profile-avatar"><?php echo substr($chef_name, 0, 1); ?></div>
                <div class="profile-info">
                    <div class="profile-name"><?php echo $chef_name; ?></div>
                    <div class="profile-role">Kitchen Staff</div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="page-header">
                <div class="page-title">Update Order Status</div>
                <div class="page-subtitle">View and update the status of all active orders</div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>

            <!-- Order Cards -->
            <?php if ($active_orders->num_rows > 0): ?>
                <?php while ($order = $active_orders->fetch_assoc()): 
                    $items_sql = "SELECT oi.*, mi.item_name 
                                  FROM order_items oi 
                                  LEFT JOIN menu_items mi ON oi.menu_item_id = mi.item_id 
                                  WHERE oi.order_id = " . $order['order_id'];
                    $items = $conn->query($items_sql);

                    // Determine status badge class
                    $status_class = '';
                    switch ($order['order_status']) {
                        case 'Being Prepared':
                            $status_class = 'status-being-prepared';
                            break;
                        case 'Ready':
                            $status_class = 'status-ready';
                            break;
                        case 'Delayed':
                            $status_class = 'status-delayed';
                            break;
                        case 'Cancelled':
                            $status_class = 'status-cancelled';
                            break;
                        default:
                            $status_class = 'status-being-prepared';
                    }
                ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number">ORDER <?php echo $order['order_number']; ?></div>
                            <div class="order-meta">
                                <span>Table <?php echo $order['table_number']; ?></span>
                                <span><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></span>
                            </div>
                        </div>
                        <div>
                            <span class="order-status-badge <?php echo $status_class; ?>">
                                <?php echo $order['order_status']; ?>
                            </span>
                            <div class="order-time"><?php echo date('H:i', strtotime($order['created_at'])); ?></div>
                        </div>
                    </div>

                    <div class="order-items">
                        <div class="order-items-label">Order Items</div>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <div class="order-item">
                                <span class="item-quantity"><?php echo $item['quantity']; ?>x</span> 
                                <?php echo htmlspecialchars($item['item_name']); ?>
                            </div>
                            <?php if ($item['cooking_preference']): ?>
                                <div class="order-item" style="font-size: 11px; color: #d4af37; margin-left: 20px;">
                                    <strong><?php echo htmlspecialchars($item['cooking_preference']); ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if ($item['allergy_notes']): ?>
                                <div class="order-item" style="font-size: 11px; color: #dc3545; margin-left: 20px;">
                                    <strong>⚠️ <?php echo htmlspecialchars($item['allergy_notes']); ?></strong>
                                </div>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </div>

                    <!-- Order Actions - Update Status Buttons -->
                    <div class="order-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <input type="hidden" name="status" value="Being Prepared">
                            <button type="submit" class="status-btn preparing-btn">
                                <i class="fas fa-clock"></i> In Progress
                            </button>
                        </form>

                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <input type="hidden" name="status" value="Ready">
                            <button type="submit" class="status-btn ready-btn">
                                <i class="fas fa-check"></i> Ready
                            </button>
                        </form>

                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <input type="hidden" name="status" value="Delayed">
                            <button type="submit" class="status-btn delayed-btn">
                                <i class="fas fa-pause"></i> Delayed
                            </button>
                        </form>

                        <form method="POST" style="display: inline;" onsubmit="return confirm('Cancel this order? This cannot be undone!')">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <input type="hidden" name="status" value="Cancelled">
                            <button type="submit" class="status-btn cancelled-btn">
                                <i class="fas fa-times"></i> Cancelled
                            </button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No active orders to update.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>