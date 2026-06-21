<?php
// Kitchen/dashboard.php
require_once '../includes/auth.php';
checkRole(['Kitchen Staff']);

$chef_name = getCurrentUserName();
$chef_id = $_SESSION['employee_number'];

// Get active orders (Being Prepared, Delayed)
$sql = "SELECT o.*, u.first_name, u.last_name, rt.table_number 
        FROM orders o
        LEFT JOIN users u ON o.waiter_id = u.user_id
        LEFT JOIN restaurant_tables rt ON o.table_id = rt.table_id
        WHERE o.order_status IN ('Being Prepared', 'Delayed')
        ORDER BY o.created_at ASC";
$active_orders = $conn->query($sql);


$count_being_prepared = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'Being Prepared'")->fetch_assoc()['count'];
$count_delayed = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'Delayed'")->fetch_assoc()['count'];
$count_ready = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'Ready'")->fetch_assoc()['count'];
$count_cancelled = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'Cancelled'")->fetch_assoc()['count'];


if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_sql = "UPDATE orders SET order_status = '$new_status' WHERE order_id = $order_id";
    if ($conn->query($update_sql)) {
        if ($new_status == 'Ready') {
        
            $waiter_sql = "SELECT waiter_id, order_number FROM orders WHERE order_id = $order_id";
            $waiter_result = $conn->query($waiter_sql);
            if ($waiter_result && $waiter_result->num_rows > 0) {
                $order_data = $waiter_result->fetch_assoc();
                
                $notify_sql = "INSERT INTO notifications (order_id, waiter_id, order_number, message, is_read) 
                               VALUES ($order_id, {$order_data['waiter_id']}, '{$order_data['order_number']}', 
                                       'Order {$order_data['order_number']} is ready for collection', 0)";
                $conn->query($notify_sql);
            }
        }
        
        if ($new_status == 'Ready' || $new_status == 'Cancelled') {
            $conn->query("UPDATE restaurant_tables SET status = 'available' 
                          WHERE table_id = (SELECT table_id FROM orders WHERE order_id = $order_id)");
        }
        header('Location: dashboard.php?success=Order status updated');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>LUXE · Kitchen Terminal</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #0a0a0a; color: #e0e0e0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; min-height: 100vh; }

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
        .logo-sidebar { font-size: 22px; font-weight: 500; letter-spacing: 3px; color: #d4af37; margin-bottom: 10px; text-transform: uppercase; }
        .logo-subtitle-sidebar { font-size: 14px; color: #fff; letter-spacing: 1px; margin-bottom: 40px; text-transform: uppercase; }
        .nav-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 30px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #a89436; text-decoration: none; font-size: 18px; border-radius: 4px; transition: all 0.3s ease; cursor: pointer; }
        .nav-item:hover { background-color: rgba(212, 175, 55, 0.1); color: #d4af37; }
        .nav-item.active { background-color: rgba(212, 175, 55, 0.2); color: #fff; border-left: 3px solid #d4af37; padding-left: 12px; }
        .nav-icon { font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer { margin-top: auto; border-top: 1px solid rgba(212, 175, 55, 0.1); padding-top: 20px; }
        .user-info { color: #e0e0e0; font-size: 12px; margin-bottom: 15px; }
        .user-name { font-weight: 600; color: #fff; }
        .user-role { color: #ffff; font-size: 10px; text-transform: uppercase; margin-top: 3px; }
        .logout { display: flex; align-items: center; gap: 8px; padding: 10px 12px; background: transparent; border: 1px solid rgba(212, 175, 55, 0.2); color: #ffff; text-decoration: none; font-size: 12px; cursor: pointer; transition: all 0.3s ease; border-radius: 3px; width: 100%; justify-content: center; }
        .logout:hover { background-color: rgba(212, 175, 55, 0.1); border-color: rgba(212, 175, 55, 0.4); color: #d4af37; }

        .main-content { margin-left: 200px; flex: 1; display: flex; flex-direction: column; }
        .top-nav { background-color: #0f0f0f; border-bottom: 1px solid rgba(212, 175, 55, 0.1); padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
        .breadcrumb { font-size: 18px; color: #888888; }
        .breadcrumb-home { color: #a89436; }
        .user-profile { display: flex; align-items: center; gap: 15px; }
        .notification-icon { width: 32px; height: 32px; border-radius: 50%; background-color: #d4af37; color: #0a0a0a; display: flex; justify-content: center; align-items: center; font-size: 16px; cursor: pointer; }
        .profile-avatar { width: 32px; height: 32px; border-radius: 50%; background-color: rgba(212, 175, 55, 0.2); display: flex; justify-content: center; align-items: center; color: #d4af37; font-size: 14px; font-weight: 600; }
        .profile-info { text-align: right; }
        .profile-name { color: #e0e0e0; font-size: 14px; font-weight: 600; }
        .profile-role { color: #a89436; font-size: 10px; text-transform: uppercase; }

        .content { padding: 30px; overflow-y: auto; flex: 1; }
        .page-header { margin-bottom: 30px; }
        .page-title { font-size: 28px; font-weight: 300; letter-spacing: 1px; color: #d4af37; margin-bottom: 8px; }

        .alert { padding: 15px 20px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; }
        .alert-success { background-color: #1a3a1a; border: 1px solid #4caf50; color: #a0e0a0; }
        .alert-info { background-color: #1a2a4a; border: 1px solid #2196f3; color: #a0c0ff; }

        .filter-tabs { display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 1px solid rgba(212, 175, 55, 0.1); padding-bottom: 15px; flex-wrap: wrap; }
        .filter-tab { padding: 8px 16px; background: transparent; border: 1px solid rgba(212, 175, 55, 0.2); color: #a89436; font-size: 12px; cursor: pointer; transition: all 0.3s ease; border-radius: 3px; text-transform: uppercase; font-weight: 500; }
        .filter-tab:hover { border-color: rgba(212, 175, 55, 0.4); color: #d4af37; }
        .filter-tab.active { background-color: #d4af37; border-color: #d4af37; color: #0a0a0a; }

        .order-card { background: linear-gradient(135deg, rgba(212, 175, 55, 0.05) 0%, rgba(0, 0, 0, 0.3) 100%); border: 1px solid rgba(212, 175, 55, 0.2); padding: 20px; margin-bottom: 20px; border-radius: 4px; }
        .order-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; flex-wrap: wrap; }
        .order-number { font-size: 14px; color: #d4af37; font-weight: 600; letter-spacing: 1px; }
        .order-meta { display: flex; gap: 15px; font-size: 12px; color: #888888; }
        .order-time { font-size: 12px; color: #a89436; }
        .order-items { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid rgba(212, 175, 55, 0.1); }
        .order-items-label { font-size: 10px; color: #a89436; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; font-weight: 600; }
        .order-item { font-size: 13px; color: #e0e0e0; margin-bottom: 6px; padding-left: 10px; }
        .item-quantity { color: #a89436; font-weight: 600; }

        .order-actions { display: flex; gap: 12px; margin-top: 20px; flex-wrap: wrap; }
        .status-btn { background: transparent; border: 2px solid; padding: 10px 16px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s ease; }
        .ready-btn { color: #4caf50; border-color: #4caf50; }
        .ready-btn:hover { background: rgba(76, 175, 80, 0.15); }
        .preparing-btn { color: #ff9800; border-color: #ff9800; }
        .preparing-btn:hover { background: rgba(255, 152, 0, 0.15); }
        .delayed-btn { color: #2196f3; border-color: #2196f3; }
        .delayed-btn:hover { background: rgba(33, 150, 243, 0.15); }
        .cancelled-btn { color: #f44336; border-color: #f44336; }
        .cancelled-btn:hover { background: rgba(244, 67, 54, 0.15); }

        @media (max-width: 1024px) { .sidebar { width: 180px; padding: 20px 15px; } .main-content { margin-left: 180px; } .content { padding: 20px; } .page-title { font-size: 24px; } }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-sidebar">LUXE</div>
        <div class="logo-subtitle-sidebar">MANAGEMENT</div>
 
        <div class="nav-group">
            <div class="nav-item active">
                <span class="nav-icon"><i class="fas fa-receipt"></i></span>
                <span>Orders</span>
            </div>    
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
                <span class="breadcrumb-home">LUXE</span> > Kitchen Dashboard
            </div>
            <div class="user-profile">
                <div class="notification-icon"><i class="fa-solid fa-bell"></i></div>
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
                <div class="page-title">Kitchen Dashboard</div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
 
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <div class="filter-tab active" data-filter="incoming">Incoming <span class="filter-count"><?php echo $count_being_prepared; ?></span></div>
                <div class="filter-tab" data-filter="ready">Ready <span class="filter-count"><?php echo $count_ready; ?></span></div>
                <div class="filter-tab" data-filter="delayed">Delayed <span class="filter-count"><?php echo $count_delayed; ?></span></div>
                <div class="filter-tab" data-filter="cancelled">Cancelled <span class="filter-count"><?php echo $count_cancelled; ?></span></div>
                <div class="filter-tab" data-filter="past">Past Orders</div>
                <div class="filter-tab" data-filter="complaints">Complaints</div>
            </div>
            
            <!-- Order Cards -->
            <?php if ($active_orders && $active_orders->num_rows > 0): ?>
                <?php while ($order = $active_orders->fetch_assoc()): 
                    $items_sql = "SELECT oi.*, mi.item_name 
                                  FROM order_items oi 
                                  LEFT JOIN menu_items mi ON oi.menu_item_id = mi.item_id 
                                  WHERE oi.order_id = " . $order['order_id'];
                    $items_result = $conn->query($items_sql);
                    
                    $items = array();
                    if ($items_result && $items_result->num_rows > 0) {
                        while ($row = $items_result->fetch_assoc()) {
                            $items[] = $row;
                        }
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
                         <div class="order-time"><?php echo date('H:i', strtotime($order['created_at'])); ?> • <?php echo $order['order_status']; ?></div>
                    </div>

                    <div class="order-items">
                        <div class="order-items-label">Order Items</div>
                        <?php if (count($items) > 0): ?>
                            <?php foreach ($items as $item): ?>
                                <div class="order-item">
                                    <span class="item-quantity"><?php echo $item['quantity']; ?>x</span> 
                                    <?php echo htmlspecialchars($item['item_name']); ?>
                                </div>
                                <?php if ($item['special_notes']): ?>
                                    <div class="order-item" style="font-size: 11px; color: #888888; margin-left: 20px;">
                                        <?php echo htmlspecialchars($item['special_notes']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($item['cooking_preference']): ?>
                                    <div class="order-item" style="font-size: 11px; color: #d4af37; margin-left: 20px;">
                                        <strong><?php echo htmlspecialchars($item['cooking_preference']); ?></strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ($item['allergy_notes']): ?>
                                    <div class="order-item" style="font-size: 11px; color: #dc3545; margin-left: 20px;">
                                        <strong><?php echo htmlspecialchars($item['allergy_notes']); ?></strong>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="order-item" style="color: #666;">No items found for this order</div>
                        <?php endif; ?>
                    </div>

                    <div class="order-actions">
                       <form method="POST" style="display: inline;">
                        <input type="hidden" name="update_status" value="1">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <input type="hidden" name="status" value="Ready">
                        <input type="hidden" name="notify_waiter" value="1">
                        <button type="submit" class="status-btn ready-btn">
                            Ready
                        </button>
                    </form>

                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <input type="hidden" name="status" value="Delayed">
                            <button type="submit" class="status-btn delayed-btn">
                                Delayed
                            </button>
                        </form>

                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <input type="hidden" name="status" value="Cancelled">
                            <button type="submit" class="status-btn cancelled-btn">
                                Cancelled
                            </button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No active orders in the kitchen right now.
                </div>
            <?php endif; ?>
        </div>
    </div>

    
        // Filter tab functionality
        <script>
    document.addEventListener('DOMContentLoaded', function() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    const orderCards = document.querySelectorAll('.order-card');
    
    // If no order cards, exit
    if (orderCards.length === 0) {
        console.log('No order cards found');
        return;
    }
    
    // Add click event to each filter tab
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const filterText = this.textContent.trim().toLowerCase();
            console.log('Filter clicked:', filterText);
            
            orderCards.forEach(card => {
                const statusElement = card.querySelector('.order-time');
                if (statusElement) {
                    const status = statusElement.textContent.toLowerCase();
                    
                    if (filterText.includes('incoming')) {
                        if (status.includes('being prepared')) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    } else if (filterText.includes('ready')) {
                        if (status.includes('ready')) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    } else if (filterText.includes('delayed')) {
                        if (status.includes('delayed')) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    } else if (filterText.includes('cancelled')) {
                        if (status.includes('cancelled')) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    } else if (filterText.includes('past orders')) {
                        if (status.includes('ready') || status.includes('cancelled')) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    } else if (filterText.includes('complaints')) {
                        const allergyElement = card.querySelector('.allergy-alert');
                        if (allergyElement) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    } else {
                        card.style.display = '';
                    }
                } else {
                    card.style.display = '';
                }
            });
        });
    });
    

    console.log('Filter tabs script loaded');
    console.log('Found ' + filterTabs.length + ' filter tabs');
    console.log('Found ' + orderCards.length + ' order cards');
});
</script>
        
</body>
</html>