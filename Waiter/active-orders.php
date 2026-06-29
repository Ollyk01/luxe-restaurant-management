<?php
// Waiter/active-orders.php
require_once '../includes/auth.php';
checkRole(['Waiter']);

$waiter_name = getCurrentUserName();
$waiter_id = $_SESSION['employee_number'];

// Get active orders for waiter
$sql = "SELECT o.*, rt.table_number 
        FROM orders o
        LEFT JOIN restaurant_tables rt ON o.table_id = rt.table_id
        WHERE o.waiter_id = " . $_SESSION['user_id'] . " 
        AND o.order_status != 'Ready' 
        AND o.order_status != 'Cancelled'
        ORDER BY o.created_at DESC";

$orders = $conn->query($sql);

// Check if query was successful
if (!$orders) {
    $orders = [];
    $error = "Error fetching orders: " . $conn->error;
} else {
    $orders = $orders->fetch_all(MYSQLI_ASSOC);
}

// Get success/error message
$success_message = '';
$error_message = '';

if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Active Orders · LUXE</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
        body { background: #080808; color: #d7c08a; height: 100vh; overflow: hidden; }
        .dashboard { display: flex; height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: 200px;
            background: #090909;
            border-right: 1px solid #282318;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .logo { padding: 25px; letter-spacing: 8px; font-size: 18px; }
        .logo span { display: block; font-size: 9px; letter-spacing: 3px; color: #777; margin-top: 10px; }
        nav a {
            display: block;
            padding: 18px 25px;
            color: #777;
            font-size: 13px;
            border-left: 2px solid transparent;
            text-decoration: none;
        }
        nav a i { margin-right: 12px; }
        nav a:hover { color: #d7b54a; }
        nav .active { background: #242012; color: #d7b54a; border-left-color: #c6a43b; }
        .sidebar-bottom {
            border-top: 1px solid #252015;
            padding: 20px;
            font-size: 12px;
        }
        .sidebar-bottom small { display: block; color: #666; margin-top: 5px; }
        .logout {
            margin-top: 25px;
            color: #777;
            text-decoration: none;
            display: block;
        }
        .logout:hover { color: #d7b54a; }

        /* Main content */
        main { flex: 1; display: flex; flex-direction: column; }
        header {
            height: 70px;
            border-bottom: 1px solid #252015;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            font-size: 14px;
        }
        .crumb { color: #777; margin-right: 10px; }
        .profile { display: flex; gap: 12px; align-items: center; }
        .circle {
            border: 1px solid #594d29;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
        }
        .profile small { display: block; font-size: 10px; color: #777; }

        /* Content */
        .content {
            padding: 30px;
            overflow-y: auto;
            flex: 1;
        }

        .page { width: 100%; }

        .orders-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            width: 100%;
        }

        .order-card {
            background: #151515;
            border: 1px solid #2a2418;
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            width: 100%;
        }

        .order-title { font-size: 22px; font-weight: bold; color: #d8bd55; }
        .order-badges { display: flex; gap: 8px; }
        .badge {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid #444;
        }
        .badge.prep { background: #123a1f; color: #4cff8b; border-color: #4cff8b; }
        .badge.delayed { background: #3a2a0f; color: #ffb84c; border-color: #ffb84c; }
        .badge.ready { background: #0f223a; color: #4ab3ff; border-color: #4ab3ff; }
        .badge.cancelled { background: #3a0f0f; color: #ff4c4c; border-color: #ff4c4c; }
        .badge.unpaid { background: #1a1a1a; color: #aaa; }

        .order-meta {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #777;
        }
        .order-total { font-size: 24px; font-weight: bold; color: #fff; }
        .order-items {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 14px;
            color: #ddd;
        }
        .order-items small { font-size: 12px; color: #777; }

        .order-notes { display: flex; flex-direction: column; gap: 8px; }
        .note { font-size: 13px; color: #aaa; font-style: italic; }
        .alert { font-size: 13px; color: #ff6b6b; font-weight: 500; }

        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .order-actions button {
            flex: 1;
            padding: 10px;
            border: none;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        .order-actions button.danger {
            background: #3a1212;
            color: #ff6b6b;
            border: 1px solid #ff6b6b;
        }
        .order-actions button.danger:hover { background: #4a1a1a; }
        .order-actions button.secondary {
            background: #1a1a1a;
            color: #d8bd55;
            border: 1px solid #d8bd55;
        }
        .order-actions button.secondary:hover { background: #2a2a2a; }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 40px;
            color: #666;
        }
        .empty-state i { font-size: 48px; color: #28a745; }
        .empty-state h3 { margin-top: 15px; color: #d7c08a; }
        .empty-state p { margin-top: 8px; color: #666; }

        /* ========================================
           RESPONSIVE - TABLET (768px)
           ======================================== */

        @media (max-width: 768px) {
            /* Sidebar - collapsed */
            .sidebar {
                width: 60px;
                padding: 15px 10px;
                align-items: center;
            }

            .logo {
                font-size: 14px;
                letter-spacing: 4px;
                padding: 15px 10px;
                text-align: center;
            }

            .logo span {
                display: none;
            }

            nav a {
                padding: 15px 10px;
                font-size: 12px;
                justify-content: center;
                text-align: center;
            }

            nav a span {
                display: none;
            }

            nav a i {
                margin-right: 0;
                font-size: 20px;
            }

            nav .active {
                border-left: 3px solid #c6a43b;
            }

            .sidebar-bottom {
                text-align: center;
                padding: 15px 10px;
            }

            .sidebar-bottom strong {
                font-size: 10px;
                display: block;
            }

            .sidebar-bottom small {
                font-size: 8px;
            }

            .logout {
                font-size: 10px;
                margin-top: 15px;
            }

            .logout i {
                font-size: 16px;
            }

            /* Main content */
            main {
                flex: 1;
            }

            header {
                padding: 12px 15px;
                height: 60px;
                flex-wrap: wrap;
                gap: 8px;
            }

            .crumb {
                font-size: 12px;
            }

            .profile {
                gap: 8px;
            }

            .circle {
                width: 24px;
                height: 24px;
                font-size: 12px;
            }

            .profile small {
                font-size: 9px;
            }

            .profile strong {
                font-size: 12px;
            }

            /* Content */
            .content {
                padding: 15px;
            }

            .orders-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .order-card {
                padding: 16px;
                gap: 10px;
            }

            .order-title {
                font-size: 18px;
            }

            .badge {
                font-size: 10px;
                padding: 3px 8px;
            }

            .order-meta {
                font-size: 11px;
                flex-wrap: wrap;
                gap: 8px;
            }

            .order-total {
                font-size: 20px;
            }

            .order-items {
                font-size: 13px;
                gap: 4px;
            }

            .order-items small {
                font-size: 11px;
            }

            .order-actions {
                flex-wrap: wrap;
                gap: 8px;
            }

            .order-actions button {
                padding: 8px 12px;
                font-size: 10px;
                flex: 1 1 auto;
                min-width: 80px;
            }

            .empty-state {
                padding: 40px 20px;
            }

            .empty-state i {
                font-size: 36px;
            }

            .empty-state h3 {
                font-size: 18px;
            }

            .empty-state p {
                font-size: 13px;
            }
        }

        /* ========================================
           RESPONSIVE - MOBILE (480px)
           ======================================== */

        @media (max-width: 480px) {
            /* Sidebar - minimal */
            .sidebar {
                width: 50px;
                padding: 10px 5px;
            }

            .logo {
                font-size: 11px;
                letter-spacing: 2px;
                padding: 10px 5px;
            }

            nav a {
                padding: 12px 5px;
                font-size: 10px;
            }

            nav a i {
                font-size: 16px;
            }

            .sidebar-bottom {
                padding: 10px 5px;
            }

            .sidebar-bottom strong {
                font-size: 8px;
            }

            .sidebar-bottom small {
                font-size: 7px;
            }

            .logout {
                font-size: 8px;
            }

            .logout i {
                font-size: 14px;
            }

            header {
                padding: 10px 12px;
                height: 55px;
                gap: 5px;
            }

            .crumb {
                font-size: 10px;
            }

            .profile {
                gap: 6px;
            }

            .circle {
                width: 20px;
                height: 20px;
                font-size: 10px;
            }

            .profile small {
                font-size: 8px;
            }

            .profile strong {
                font-size: 10px;
            }

            .content {
                padding: 10px;
            }

            .orders-grid {
                gap: 12px;
            }

            .order-card {
                padding: 12px;
                gap: 8px;
            }

            .order-title {
                font-size: 15px;
            }

            .badge {
                font-size: 8px;
                padding: 2px 6px;
            }

            .order-meta {
                font-size: 10px;
                gap: 5px;
            }

            .order-total {
                font-size: 17px;
            }

            .order-items {
                font-size: 11px;
                gap: 3px;
            }

            .order-items small {
                font-size: 10px;
            }

            .order-actions {
                gap: 5px;
            }

            .order-actions button {
                padding: 6px 8px;
                font-size: 8px;
                min-width: 60px;
            }

            .order-actions button i {
                font-size: 10px;
            }

            .empty-state {
                padding: 30px 15px;
            }

            .empty-state i {
                font-size: 28px;
            }

            .empty-state h3 {
                font-size: 15px;
            }

            .empty-state p {
                font-size: 11px;
            }
        }
    </style>
</head>

<body>

<div class="dashboard">

    <aside class="sidebar">
        <div class="logo">
            LUXE
            <span>MANAGEMENT</span>
        </div>

        <nav>
            <a href="dashboard.php"><i class="fas fa-receipt"></i> <span>New Order</span></a>
            <a class="active" href="active-orders.php"><i class="fas fa-chart-line"></i> <span>Active Orders</span></a>
            <a href="feedback.html"><i class="fas fa-comment"></i> <span>Feedback</span></a>
        </nav>

        <div class="sidebar-bottom">
            <strong><?php echo $waiter_name; ?></strong>
            <small>WAITER</small>
            <small><?php echo $waiter_id; ?></small>
            <a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <main>

        <header>
            <div><span class="crumb">LUXE /</span> Active Orders</div>
            <div class="profile">
                <div class="circle"><?php echo substr($waiter_name, 0, 1); ?></div>
                <div>
                    <strong><?php echo $waiter_name; ?></strong>
                    <small><?php echo $waiter_id; ?> · Waiter</small>
                </div>
            </div>
        </header>

        <div class="content">

            <section class="page">

                <div class="orders-grid">
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): 
                            // Get order items
                            $items_sql = "SELECT oi.*, mi.item_name 
                                          FROM order_items oi 
                                          LEFT JOIN menu_items mi ON oi.menu_item_id = mi.item_id 
                                          WHERE oi.order_id = " . $order['order_id'];
                            $items_result = $conn->query($items_sql);
                            $items = [];
                            if ($items_result) {
                                while ($row = $items_result->fetch_assoc()) {
                                    $items[] = $row;
                                }
                            }

                            $badge_class = 'prep';
                            switch ($order['order_status']) {
                                case 'Being Prepared': $badge_class = 'prep'; break;
                                case 'Delayed': $badge_class = 'delayed'; break;
                                case 'Ready': $badge_class = 'ready'; break;
                                case 'Cancelled': $badge_class = 'cancelled'; break;
                                default: $badge_class = 'prep';
                            }
                        ?>
                        <div class="order-card">

                            <div class="order-title">ORDER <?php echo $order['order_number']; ?></div>

                            <div class="order-badges">
                                <span class="badge <?php echo $badge_class; ?>"><?php echo $order['order_status']; ?></span>
                            </div>

                            <div class="order-meta">
                                <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($order['created_at'])); ?></span>
                                <span><i class="fas fa-table"></i> Table <?php echo $order['table_number']; ?></span>
                            </div>

                            <div class="order-total">R<?php echo number_format($order['total_amount'], 2); ?></div>

                            <div class="order-items">
                                <?php foreach ($items as $item): ?>
                                    <div><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['item_name']); ?>
                                        <?php if ($item['cooking_preference']): ?>
                                            <small>(<?php echo $item['cooking_preference']; ?>)</small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="order-actions">
                                <form method="POST" action="cancel-order.php" onsubmit="return confirm('Cancel this order?')">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <button type="submit" class="danger"><i class="fas fa-times"></i> CANCEL</button>
                                </form>
                                <button class="secondary" onclick="alert('Feedback feature coming soon!')"><i class="fas fa-comment"></i> FEEDBACK</button>
                            </div>

                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <h3>No Active Orders</h3>
                            <p>All your orders have been completed!</p>
                        </div>
                    <?php endif; ?>
                </div>

            </section>

        </div>

    </main>

</div>

</body>
</html>