<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Delete item
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ? AND user_id = ?");
    $stmt->execute([$delete_id, $user_id]);
    header("Location: inventory.php"); // Redirect after deletion to avoid form resubmission
    exit;
}

if (isset($_POST['increase_quantity'])) {
    $item_id = (int)$_POST['item_id'];
    $quantity = (int)$_POST['quantity'] + 1;
    $stmt = $pdo->prepare("UPDATE inventory SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$quantity, $item_id, $user_id]);
}

if (isset($_POST['decrease_quantity'])) {
    $item_id = (int)$_POST['item_id'];
    $quantity = (int)$_POST['quantity'] - 1;
    if ($quantity < 0) {
        $quantity = 0; 
    }
    $stmt = $pdo->prepare("UPDATE inventory SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$quantity, $item_id, $user_id]);
}

// Add new inventory item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['increase_quantity']) && !isset($_POST['decrease_quantity'])) {
    $item_name = $_POST['item_name'];
    $category = $_POST['category'];
    $quantity = (int)$_POST['quantity'];
    $restocked_date = date('Y-m-d'); // Automatically set to the current date

    $stmt = $pdo->prepare("INSERT INTO inventory (item_name, category, quantity, restocked_date, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$item_name, $category, $quantity, $restocked_date, $user_id]);
}

// Fetch inventory items sorted by category and expiry date
$stmt = $pdo->prepare("SELECT * FROM inventory WHERE user_id = ? ORDER BY category ASC, restocked_date ASC");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

// Group items by category
$grouped_items = [];
foreach ($items as $item) {
    $grouped_items[$item['category']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            background-color: #f4f4f9;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            height: 100vh;
            padding: 20px;
            color: #ecf0f1;
            position: fixed;
            transition: width 0.3s ease;
        }

        .sidebar h2 {
            margin-bottom: 30px;
        }

        .sidebar form input,
        .sidebar form button {
            width: 85%;
            padding: 12px;
            margin-bottom: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
        }
        
        .sidebar form button {
            background-color: #e74c3c;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .sidebar form button:hover {
            background-color: #c0392b;
        }

        .content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
            background-color: #fff;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        .content h1 {
            font-size: 2.5em;
            color: #34495e;
            margin-bottom: 20px;
        }

        /* Search Bar */
        .search-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-container input {
            width: 50%;
            padding: 10px;
            font-size: 16px;
            border-radius: 10px;
            border: 1px solid #ccc;
        }

        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            text-align: center;
            background-color: #f9f9f9;
        }

        table th, table td {
            padding: 15px;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #34495e;
            color: white;
            text-transform: uppercase;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #e8e8e8;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .action-buttons form button {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .delete-button {
            background-color: #e74c3c;
            color: white;
        }

        .delete-button:hover {
            background-color: #c0392b;
        }

        .quantity-button {
            background-color: #3498db;
            color: white;
        }

        .quantity-button:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .content {
                margin-left: 0;
                padding: 10px;
                width: 100%;
            }

            table {
                font-size: 14px;
            }

            .sidebar form input,
            .sidebar form button {
                font-size: 14px;
                width: 90%;
            }

            .search-container input {
                width: 75%;
            }
        }

        @media (max-width: 480px) {
            .content h1 {
                font-size: 1.8em;
            }

            table th, table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <img src="1.jpg" alt="Inventory Logo" width="250px">
    <h2>INVENTORY SYSTEM</h2>
    <form method="post">
        <input type="text" name="item_name" placeholder="Item Name" required>
        <input type="text" name="category" placeholder="Category">
        <input type="number" name="quantity" placeholder="Quantity" required>
        <button type="submit">Add Item</button>
    </form>
    
    <form method="post" action="logout.php">
        <button type="submit">Logout</button>
    </form>
</div>

<div class="content">
    <!-- Search Bar -->
    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Search items...">
    </div>

    <!-- Display Inventory -->
    <?php foreach ($grouped_items as $category => $items): ?>
        <h4><?php echo htmlspecialchars($category); ?></h4>
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Restocked Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="inventoryTable">
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($item['restocked_date']); ?></td>
                    <td>
                        <div class="action-buttons">
                            <!-- Decrease Quantity Button -->
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="quantity" value="<?php echo $item['quantity']; ?>">
                                <button type="submit" name="decrease_quantity" class="quantity-button">-</button>
                            </form>

                            <!-- Increase Quantity Button -->
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="quantity" value="<?php echo $item['quantity']; ?>">
                                <button type="submit" name="increase_quantity" class="quantity-button">+</button>
                            </form>

                            <!-- Delete Button -->
                            <form method="get" action="inventory.php" onsubmit="return confirm('Are you sure you want to delete this item?')" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="delete-button">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    let input = document.getElementById('searchInput').value.toLowerCase();
    let rows = document.querySelectorAll('#inventoryTable tr');
    
    rows.forEach(row => {
        let itemName = row.getElementsByTagName('td')[0].textContent.toLowerCase();
        if (itemName.includes(input)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});
</script>

</body>
</html>

