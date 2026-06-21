<?php
// Waiter/new-order.php
require_once '../includes/auth.php';
checkRole(['Waiter']);

$waiter_name = getCurrentUserName();
$waiter_id = $_SESSION['employee_number'];

// Get available tables
$tables = $conn->query("SELECT * FROM restaurant_tables WHERE status = 'available'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LUXE · New Order</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #080808;
            color: #d7c08a;
            height: 100vh;
            overflow: hidden;
        }

        .dashboard {
            display: flex;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 200px;
            background: #090909;
            border-right: 1px solid #282318;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .logo {
            padding: 25px;
            letter-spacing: 8px;
            font-size: 18px;
        }

        .logo span {
            display: block;
            font-size: 9px;
            letter-spacing: 3px;
            color: #777;
            margin-top: 10px;
        }

        nav a {
            display: block;
            padding: 18px 25px;
            color: #777;
            font-size: 13px;
            border-left: 2px solid transparent;
            text-decoration: none;
        }

        nav a i {
            margin-right: 12px;
        }

        nav a:hover {
            color: #d7b54a;
        }

        nav .active {
            background: #242012;
            color: #d7b54a;
            border-left-color: #c6a43b;
        }

        .sidebar-bottom {
            border-top: 1px solid #252015;
            padding: 20px;
            font-size: 12px;
        }

        .sidebar-bottom small {
            display: block;
            color: #666;
            margin-top: 5px;
        }

        .logout {
            margin-top: 25px;
            color: #777;
            text-decoration: none;
            display: block;
        }

        .logout:hover {
            color: #d7b54a;
        }

        /* Main content area */
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        header {
            height: 70px;
            border-bottom: 1px solid #252015;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            font-size: 14px;
        }

        .crumb {
            color: #777;
            margin-right: 10px;
        }

        .profile {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .circle {
            border: 1px solid #594d29;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
        }

        .profile small {
            display: block;
            font-size: 10px;
            color: #777;
        }

        /* Main content grid layout */
        .content {
            padding: 30px;
            overflow-y: auto;
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
        }

        /* Menu section */
        .menu-section {
            display: flex;
            flex-direction: column;
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 1px;
            color: #d4af37;
            margin-bottom: 8px;
        }

        .table-selector {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .table-label {
            font-size: 12px;
            color: #a89436;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .table-select {
            padding: 10px 15px;
            background-color: #1a1a1a;
            border: 1px solid rgba(212, 175, 55, 0.2);
            color: #e0e0e0;
            font-size: 14px;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .table-select:hover,
        .table-select:focus {
            border-color: rgba(212, 175, 55, 0.5);
            outline: none;
            background-color: #252525;
        }

        /* Category tabs */
        .menu-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
            padding-bottom: 15px;
        }

        .menu-tab {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid rgba(212, 175, 55, 0.2);
            color: #a89436;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 3px;
            text-transform: uppercase;
            font-weight: 500;
            letter-spacing: 1px;
        }

        .menu-tab:hover {
            border-color: rgba(212, 175, 55, 0.4);
            color: #d4af37;
        }

        .menu-tab.active {
            background-color: #d4af37;
            border-color: #d4af37;
            color: #0a0a0a;
        }

        /* Individual menu items */
        .menu-items {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .menu-item {
            background: transparent;
            border: 1px solid rgba(212, 175, 55, 0.15);
            border-bottom: none;
            padding: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .menu-item:last-child {
            border-bottom: 1px solid rgba(212, 175, 55, 0.15);
        }

        .menu-item:hover {
            background-color: rgba(212, 175, 55, 0.08);
            border-color: rgba(212, 175, 55, 0.3);
        }

        .menu-item-info {
            flex: 1;
        }

        .menu-item-name {
            font-size: 14px;
            color: #e0e0e0;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .menu-item-desc {
            font-size: 11px;
            color: #888888;
            font-style: italic;
        }

        .menu-item-tags {
            display: flex;
            gap: 6px;
            margin-top: 6px;
            flex-wrap: wrap;
        }

        .menu-tag {
            background-color: rgba(244, 67, 54, 0.2);
            color: #ff6b6b;
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 2px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .menu-item-right {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: 15px;
        }

        .menu-item-price {
            font-size: 16px;
            color: #d4af37;
            font-weight: 600;
            min-width: 50px;
            text-align: right;
        }

        .menu-item-add {
            background-color: transparent;
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: #d4af37;
            width: 36px;
            height: 36px;
            border-radius: 3px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .menu-item-add:hover {
            background-color: rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.5);
        }

        /* Order summary panel */
        .order-panel {
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.05) 0%, rgba(0, 0, 0, 0.3) 100%);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 4px;
            padding: 20px;
            height: fit-content;
            position: sticky;
            top: 30px;
        }

        .order-panel-title {
            font-size: 12px;
            color: #a89436;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .order-table-info {
            font-size: 14px;
            color: #d4af37;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }

        .order-items-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 15px;
            max-height: 250px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .order-item {
            background-color: rgba(212, 175, 55, 0.1);
            padding: 10px;
            border-radius: 3px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 12px;
        }

        .order-item-info {
            flex: 1;
        }

        .order-item-name {
            color: #e0e0e0;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .order-item-pref {
            color: #d4af37;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .order-item-price {
            color: #d4af37;
            font-weight: 600;
        }

        .order-item-remove {
            background: none;
            border: none;
            color: #ff6b6b;
            cursor: pointer;
            font-size: 14px;
            padding: 0 5px;
            transition: all 0.3s ease;
        }

        .order-item-remove:hover {
            color: #ff8888;
        }

        .order-divider {
            height: 1px;
            background-color: rgba(212, 175, 55, 0.2);
            margin-bottom: 15px;
        }

        .special-instructions-label {
            font-size: 10px;
            color: #a89436;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .special-instructions {
            width: 100%;
            padding: 10px;
            background-color: #1a1a1a;
            border: 1px solid rgba(212, 175, 55, 0.2);
            color: #e0e0e0;
            font-size: 11px;
            border-radius: 3px;
            resize: none;
            height: 50px;
            margin-bottom: 10px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .special-instructions:focus {
            outline: none;
            border-color: rgba(212, 175, 55, 0.5);
            background-color: #252525;
        }

        .special-instructions::placeholder {
            color: #666666;
        }

        .allergies-label {
            font-size: 10px;
            color: #a89436;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .allergies {
            width: 100%;
            padding: 10px;
            background-color: #1a1a1a;
            border: 1px solid rgba(212, 175, 55, 0.2);
            color: #e0e0e0;
            font-size: 11px;
            border-radius: 3px;
            resize: none;
            height: 50px;
            margin-bottom: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .allergies:focus {
            outline: none;
            border-color: rgba(212, 175, 55, 0.5);
            background-color: #252525;
        }

        .allergies::placeholder {
            color: #666666;
        }

        .order-total-section {
            padding-top: 12px;
            border-top: 1px solid rgba(212, 175, 55, 0.2);
            margin-bottom: 15px;
        }

        .order-total-label {
            font-size: 11px;
            color: #a89436;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .order-total {
            font-size: 28px;
            color: #d4af37;
            font-weight: 300;
            letter-spacing: 1px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background-color: #d4af37;
            color: #0a0a0a;
            border: none;
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 3px;
            margin-top: 15px;
        }

        .submit-btn:hover {
            background-color: #e5c158;
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.3);
        }

        .submit-btn:disabled {
            background-color: #888888;
            cursor: not-allowed;
            box-shadow: none;
        }

        /* Modal for cooking preferences */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background-color: #1a1a1a;
            border: 1px solid rgba(212, 175, 55, 0.3);
            padding: 30px;
            border-radius: 4px;
            max-width: 400px;
            width: 90%;
        }

        .modal-title {
            font-size: 16px;
            color: #d4af37;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .cooking-preference-label {
            font-size: 11px;
            color: #a89436;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 600;
            display: block;
        }

        .preference-option {
            padding: 12px 15px;
            background-color: transparent;
            border: 1px solid rgba(212, 175, 55, 0.2);
            color: #e0e0e0;
            cursor: pointer;
            margin-bottom: 8px;
            border-radius: 3px;
            transition: all 0.3s ease;
            font-size: 13px;
            text-align: left;
        }

        .preference-option:hover {
            border-color: rgba(212, 175, 55, 0.5);
            background-color: rgba(212, 175, 55, 0.1);
        }

        .preference-option.selected {
            background-color: #d4af37;
            border-color: #d4af37;
            color: #0a0a0a;
            font-weight: 600;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .modal-btn {
            flex: 1;
            padding: 10px;
            border: 1px solid rgba(212, 175, 55, 0.2);
            background: transparent;
            color: #a89436;
            cursor: pointer;
            border-radius: 3px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 11px;
        }

        .modal-btn:hover {
            border-color: rgba(212, 175, 55, 0.5);
            color: #d4af37;
        }

        .modal-btn.confirm {
            background-color: #d4af37;
            color: #0a0a0a;
            border-color: #d4af37;
        }

        .modal-btn.confirm:hover {
            background-color: #e5c158;
        }

        /* Notification */
        .notification {
            display: none;
            position: fixed;
            top: 30px;
            right: 30px;
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.2) 0%, rgba(0, 0, 0, 0.4) 100%);
            border: 1px solid rgba(76, 175, 80, 0.4);
            padding: 20px 25px;
            border-radius: 4px;
            color: #4caf50;
            font-size: 13px;
            z-index: 2000;
            box-shadow: 0 8px 24px rgba(76, 175, 80, 0.15);
            animation: slideIn 0.3s ease;
        }

        .notification.active {
            display: block;
        }

        .notification-title {
            font-weight: 700;
            margin-bottom: 5px;
            color: #66bb6a;
            font-size: 14px;
        }

        .notification-content {
            color: #81c784;
            font-size: 12px;
            line-height: 1.5;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .content {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .order-panel {
                position: static;
            }
        }
    </style>
</head>

<body>

<div class="dashboard">

    <!-- Sidebar -->
    <aside class="sidebar">

        <div class="logo">
            LUXE
            <span>MANAGEMENT</span>
        </div>

        <nav>
            <a class="active" href="new-order.php"><i class="fas fa-receipt"></i> New Orders</a>
            <a href="active-orders.php"><i class="fas fa-chart-line"></i> Active Orders</a>
        </nav>

        <div class="sidebar-bottom">
            <div>
                <strong><?php echo $waiter_name; ?></strong>
                <small>WAITER</small>
                <small><?php echo $waiter_id; ?></small>
            </div>

            <a href="../logout.php" class="logout">
                <i class="fas fa-right-from-bracket"></i> Logout
            </a>
        </div>

    </aside>

    <!-- Main content -->
    <main>
        <header>
            <div>
                <span class="crumb">LUXE /</span>
                New Order
            </div>

            <div class="profile">
                <div class="circle"><?php echo substr($waiter_name, 0, 1); ?></div>
                <div>
                    <strong><?php echo $waiter_name; ?></strong>
                    <small><?php echo $waiter_id; ?> · Waiter</small>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="content">

            <!-- Left Side - Menu -->
            <div class="menu-section">
                <div class="page-header">
                    <div class="page-title">New Order</div>
                    <div class="table-selector">
                        <label class="table-label">Table</label>
                        <select id="tableSelect" class="table-select">
                            <option value="">Select a table...</option>
                            <?php while ($table = $tables->fetch_assoc()): ?>
                            <option value="<?php echo $table['table_id']; ?>">Table <?php echo $table['table_number']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <!-- Menu Tabs -->
                <div class="menu-tabs">
                    <button class="menu-tab active" data-category="starters">Starters</button>
                    <button class="menu-tab" data-category="mains">Mains</button>
                    <button class="menu-tab" data-category="desserts">Desserts</button>
                    <button class="menu-tab" data-category="beverages">Beverages</button>
                </div>

                <!-- Menu Items -->
                <div class="menu-items" id="menuItems"></div>
            </div>

            <!-- Right Side - Order Summary -->
            <div class="order-panel">
                <div class="order-panel-title">Order Summary</div>
                <div class="order-table-info" id="orderTableInfo" style="display: none;"></div>

                <div class="order-items-list" id="orderItemsList">
                    <div style="text-align: center; color: #666666; font-size: 12px; padding: 20px 0;">
                        No items added yet
                    </div>
                </div>

                <div class="order-divider"></div>

                <label class="special-instructions-label">Special Instructions</label>
                <textarea id="specialInstructions" class="special-instructions" placeholder="Special instructions"></textarea>

                <label class="allergies-label">Allergies & Dietary Requirements</label>
                <textarea id="allergies" class="allergies" placeholder="Allergies and dietary requirements"></textarea>

                <div class="order-total-section">
                    <div class="order-total-label">Order Total</div>
                    <div class="order-total" id="orderTotal">R0.00</div>
                </div>

                <button class="submit-btn" id="submitBtn" disabled>Submit Order</button>
            </div>

        </div>
    </main>

</div>

<!-- Modal - Cooking Preference -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <div class="modal-title">How would you like your <span id="meatType"></span>?</div>
        
        <label class="cooking-preference-label">Cooking Preference</label>
        <div id="preferenceOptions"></div>

        <div class="modal-buttons">
            <button class="modal-btn" id="cancelBtn">Cancel</button>
            <button class="modal-btn confirm" id="confirmBtn">Confirm</button>
        </div>
    </div>
</div>

<!-- Notification -->
<div class="notification" id="notification">
    <div class="notification-title">Order Submitted</div>
    <div class="notification-content">
        Order <span id="notificationOrderNum"></span> for <span id="notificationTable"></span> has been sent to the kitchen
    </div>
</div>

<script>
    // Menu data from database
    const menuData = {
        starters: [
            <?php
            $starters = $conn->query("SELECT * FROM menu_items WHERE category = 'Starters' AND availability_status = 'available'");
            $items = [];
            while ($item = $starters->fetch_assoc()) {
                $items[] = "{ name: '" . addslashes($item['item_name']) . "', price: " . $item['price'] . " }";
            }
            echo implode(",\n", $items);
            ?>
        ],
        mains: [
            <?php
            $mains = $conn->query("SELECT * FROM menu_items WHERE category = 'Mains' AND availability_status = 'available'");
            $items = [];
            while ($item = $mains->fetch_assoc()) {
                $items[] = "{ name: '" . addslashes($item['item_name']) . "', price: " . $item['price'] . " }";
            }
            echo implode(",\n", $items);
            ?>
        ],
        desserts: [
            <?php
            $desserts = $conn->query("SELECT * FROM menu_items WHERE category = 'Desserts' AND availability_status = 'available'");
            $items = [];
            while ($item = $desserts->fetch_assoc()) {
                $items[] = "{ name: '" . addslashes($item['item_name']) . "', price: " . $item['price'] . " }";
            }
            echo implode(",\n", $items);
            ?>
        ],
        beverages: [
            <?php
            $beverages = $conn->query("SELECT * FROM menu_items WHERE category = 'Beverages' AND availability_status = 'available'");
            $items = [];
            while ($item = $beverages->fetch_assoc()) {
                $items[] = "{ name: '" . addslashes($item['item_name']) . "', price: " . $item['price'] . " }";
            }
            echo implode(",\n", $items);
            ?>
        ]
    };

    // Application state
    let currentCategory = 'starters';
    let orderItems = [];
    let selectedItemForPreference = null;
    let currentTable = '';

    // Meat items that require cooking preference
    const meatItems = {
        'Wagyu Beef Tenderloin': 'beef',
        'Rack of Lamb': 'lamb',
        'Braised Pork Belly': 'pork',
        'Prime Ribeye Steak': 'beef',
        'Châteaubriand': 'beef',
        'Grilled Lamb Chops': 'lamb',
        'Veal Osso Buco': 'veal'
    };

    // Cooking preferences by meat type
    const cookingPreferences = {
        beef: ['Rare', 'Medium Rare', 'Medium', 'Medium Well', 'Well Done'],
        lamb: ['Rare', 'Medium Rare', 'Medium', 'Medium Well', 'Well Done'],
        veal: ['Rare', 'Medium Rare', 'Medium', 'Medium Well', 'Well Done'],
        pork: ['Medium', 'Well Done']
    };

    // Initialize the page
    function init() {
        renderMenu('starters');
        setupEventListeners();
    }


    function setupEventListeners() {
        document.querySelectorAll('.menu-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const category = tab.dataset.category;
                document.querySelectorAll('.menu-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                renderMenu(category);
            });
        });

        document.getElementById('tableSelect').addEventListener('change', (e) => {
            currentTable = e.target.value;
            updateOrderPanel();
        });

        document.getElementById('submitBtn').addEventListener('click', submitOrder);
        document.getElementById('cancelBtn').addEventListener('click', () => {
            document.getElementById('modalOverlay').classList.remove('active');
        });
        document.getElementByI('confirmBtn').addEventListener('click', confirmPreference);
    }

    // Render menu items for a category
    function renderMenu(category) {
        currentCategory = category;
        const menuItems = document.getElementById('menuItems');
        menuItems.innerHTML = '';

        menuData[category].forEach(item => {
            const itemEl = document.createElement('div');
            itemEl.className = 'menu-item';

            itemEl.innerHTML = `
                <div class="menu-item-info">
                    <div class="menu-item-name">${item.name}</div>
                    <div class="menu-item-price" style="font-size:12px;color:#888;">R${item.price}</div>
                </div>
                <button class="menu-item-add" data-item="${item.name}" data-price="${item.price}">+</button>
            `;

            itemEl.querySelector('.menu-item-add').addEventListener('click', () => {
                if (!currentTable) {
                    alert('Please select a table first');
                    return;
                }
                
                if (meatItems[item.name]) {
                    selectedItemForPreference = {
                        name: item.name,
                        price: item.price,
                        meatType: meatItems[item.name]
                    };
                    showCookingPreferenceModal(item.name, meatItems[item.name]);
                } else {
                    addItemToOrder(item.name, item.price, null);
                }
            });

            menuItems.appendChild(itemEl);
        });
    }

    function showCookingPreferenceModal(itemName, meatType) {
        document.getElementById('meatType').textContent = meatType;
        const modal = document.getElementById('modalOverlay');
        const optionsContainer = document.getElementById('preferenceOptions');
        optionsContainer.innerHTML = '';

        const prefs = cookingPreferences[meatType];
        prefs.forEach(pref => {
            const btn = document.createElement('button');
            btn.className = 'preference-option';
            btn.textContent = pref;
            btn.addEventListener('click', () => {
                document.querySelectorAll('.preference-option').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
            });
            optionsContainer.appendChild(btn);
        });

        modal.classList.add('active');
    }

    
    function confirmPreference() {
        const selected = document.querySelector('.preference-option.selected');
        if (!selected) return;

        const preference = selected.textContent;
        addItemToOrder(selectedItemForPreference.name, selectedItemForPreference.price, preference);
        document.getElementById('modalOverlay').classList.remove('active');
    }


    function addItemToOrder(name, price, preference) {
        orderItems.push({
            id: Date.now(),
            name,
            price,
            preference
        });
        updateOrderPanel();
    }

    function removeItemFromOrder(id) {
        orderItems = orderItems.filter(item => item.id !== id);
        updateOrderPanel();
    }

    function updateOrderPanel() {
        const tableSelect = document.getElementById('tableSelect').value;

        const itemsList = document.getElementById('orderItemsList');
        if (orderItems.length === 0) {
            itemsList.innerHTML = '<div style="text-align: center; color: #666666; font-size: 12px; padding: 20px 0;">No items added yet</div>';
        } else {
            itemsList.innerHTML = orderItems.map(item => `
                <div class="order-item">
                    <div class="order-item-info">
                        <div class="order-item-name">${item.name}</div>
                        ${item.preference ? `<div class="order-item-pref">${item.preference}</div>` : ''}
                        <div class="order-item-price">R${item.price}</div>
                    </div>
                    <button class="order-item-remove" data-id="${item.id}">×</button>
                </div>
            `).join('');

            document.querySelectorAll('.order-item-remove').forEach(btn => {
                btn.addEventListener('click', () => {
                    removeItemFromOrder(parseInt(btn.dataset.id));
                });
            });
        }

        // Update total
        const total = orderItems.reduce((sum, item) => sum + item.price, 0);
        document.getElementById('orderTotal').textContent = `R${total}.00`;

        document.getElementById('submitBtn').disabled = orderItems.length === 0 || !tableSelect;
    }

    // Submit the order
    function submitOrder() {
    const tableSelect = document.getElementById('tableSelect');
    const table_id = tableSelect.value;
    
    if (!table_id) {
        alert('Please select a table');
        return;
    }
    
    if (orderItems.length === 0) {
        alert('Please add items to the order');
        return;
    }
    
    const orderData = {
        table_id: table_id,
        items: orderItems.map(item => ({
            name: item.name,
            price: item.price,
            preference: item.preference || null,
            quantity: 1
        })),
        special_instructions: document.getElementById('specialInstructions').value,
        allergies: document.getElementById('allergies').value
    };
    
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    // Send the order to the server
    fetch('submit-order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            
            document.getElementById('notificationOrderNum').textContent = data.order_number;
            document.getElementById('notificationTable').textContent = `Table ${tableSelect.options[tableSelect.selectedIndex].text}`;
            const notification = document.getElementById('notification');
            notification.classList.add('active');
            
            // Reset the form after a delay
            setTimeout(() => {
                orderItems = [];
                tableSelect.value = '';
                document.getElementById('specialInstructions').value = '';
                document.getElementById('allergies').value = '';
                updateOrderPanel();
                
            
                notification.classList.remove('active');
            }, 4000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error submitting order: ' + error);
    })
    .finally(() => {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submit Order';
    });
}


    init();
</script>

</body>
</html>