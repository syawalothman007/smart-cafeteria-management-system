<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: index.html");
    exit();
}

if (isset($_GET['remove'])) {
    $index = $_GET['remove'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header("Location: view_cart.php");
    exit();
}

if (isset($_POST['clear_cart'])) {
    unset($_SESSION['cart']);
    header("Location: view_cart.php");
    exit();
}

if (isset($_POST['checkout'])) {//s1
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo "<script>alert('Your cart is empty.'); window.location='view_cart.php';</script>";
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $conn->begin_transaction();

    try {
        // Validate stock before proceeding
        foreach ($_SESSION['cart'] as $item) {//str
            $menu_id = $item['menu_id'];
            $qty = $item['quantity'];

            $checkStock = $conn->query("SELECT stock_quantity FROM stock WHERE menu_id = $menu_id FOR UPDATE");
            $stock = $checkStock->fetch_assoc();

            if (!$stock || $stock['stock_quantity'] < $qty) {
                throw new Exception("Insufficient stock for '{$item['name']}'. Checkout cancelled.");
            }
        }

        // Calculate total
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Insert order
        $conn->query("INSERT INTO orders (user_id, total_price) VALUES ($user_id, $total)");
        $order_id = $conn->insert_id;

        // Order items + stock update
        foreach ($_SESSION['cart'] as $item) {
            $menu_id = $item['menu_id'];
            $qty = $item['quantity'];
            $price = $item['price'];

            $conn->query("INSERT INTO order_items (order_id, menu_id, quantity, price)
                          VALUES ($order_id, $menu_id, $qty, $price)");

            $conn->query("UPDATE stock SET stock_quantity = stock_quantity - $qty WHERE menu_id = $menu_id");//en
        }

        // Auto update availability
        $conn->query("
            UPDATE menu m
            JOIN stock s ON m.id = s.menu_id
            SET m.availability = CASE 
                WHEN s.stock_quantity <= 0 THEN 'unavailable'
                ELSE 'available'
            END
        ");

        // Queue
        $qRes = $conn->query("SELECT MAX(queue_number) AS last FROM queue");
        $qRow = $qRes->fetch_assoc();
        $nextQueue = $qRow['last'] ? $qRow['last'] + 1 : 1;

        $conn->query("INSERT INTO queue (order_id, queue_number) VALUES ($order_id, $nextQueue)");

        $conn->commit();
        unset($_SESSION['cart']);

        echo "<script>alert('Order placed successfully!'); window.location='my_orders.php';</script>";
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
        echo "<script>alert('$error'); window.location='view_cart.php';</script>";
        exit();
    }
}
?>

<!-- HTML same as your current file, no change needed -->


<!DOCTYPE html>
<html>
<head>
    <title>View Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f7f7f7;
            margin: 0;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #fff;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #eaeaea;
        }

        .btn-group {
            text-align: center;
            margin-top: 10px;
        }

        .btn-group button {
            padding: 10px 20px;
            margin-right: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-group button:last-child {
            background: #dc3545;
        }

        .btn-group button:hover {
            opacity: 0.9;
        }

        .remove-btn {
            background: #ffc107;
            color: #000;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
        }

        .remove-btn:hover {
            background: #e0a800;
        }

        .back-link {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .back-link:hover {
            background: #0056b3;
        }

        .total-row td {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        /* Home Button */
        .home-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 999;
        }

        .home-btn img {
            height: 60px;
            width: auto;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .home-btn img:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<!-- Home Button -->
<a href="customer_dashboard.php" class="home-btn">
    <img src="images/logo1.png" alt="Home">
</a>

<h2>My Cart</h2>

<?php if (!empty($_SESSION['cart'])): ?>
    <table>
        <tr><th>Name</th><th>Qty</th><th>Price</th><th>Action</th></tr>
        <?php 
        $total = 0;
        foreach ($_SESSION['cart'] as $index => $item): 
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;
        ?>
        <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>RM<?= number_format($subtotal, 2) ?></td>
            <td><a href="?remove=<?= $index ?>" class="remove-btn" onclick="return confirm('Remove this item?')">Remove</a></td>
        </tr>
        <?php endforeach; ?>
        <tr class="total-row">
            <td colspan="2">Total</td>
            <td colspan="2">RM<?= number_format($total, 2) ?></td>
        </tr>
    </table>

    <form method="POST" class="btn-group">
        <button type="submit" name="checkout" onclick="return confirm('Proceed to checkout?')">Checkout</button>
        <button type="submit" name="clear_cart" onclick="return confirm('Clear all items in cart?')">Clear Cart</button>
    </form>
<?php else: ?>
    <p style="text-align:center;">Your cart is empty.</p>
<?php endif; ?>

<div style="text-align:center;">
    <a href="browse_menu.php" class="back-link">← Back to Menu</a>
</div>

</body>
</html>
