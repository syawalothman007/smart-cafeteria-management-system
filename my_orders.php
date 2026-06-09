<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #fdfbfb, #ebedee);
            margin: 0;
            padding: 4rem 20px 20px;
            color: #333;
        }

        .home-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
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

        h2 {
            color: #6c5b7b;
        }

        .order-section {
            background: #ffffff;
            border: 1px solid #ddd;
            border-left: 6px solid #c06c84;
            margin-bottom: 25px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.04);
        }

        .order-section h3 {
            margin-top: 0;
            color: #355c7d;
        }

        .status-label {
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 12px;
            color: white;
        }

        .status-pending {
            background-color: #e67e22;
        }

        .status-inprocess {
            background-color: #3498db;
        }

        .status-completed {
            background-color: #27ae60;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
            background: #fdfdfd;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f7f1ff;
            color: #6c5b7b;
        }

        .review-button, .pickup-button {
            background-color: #88d8b0;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            margin-right: 10px;
            display: inline-block;
        }

        .review-button:hover, .pickup-button:hover {
            background-color: #6cd4a3;
        }

        .pickup-info {
            margin-top: 10px;
            font-weight: 500;
            color: #444;
        }

        .completed {
            color: #27ae60;
        }

        .estimate-time {
            color: #2980b9;
            font-weight: bold;
            margin-top: 5px;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: #2980b9;
            font-weight: bold;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<a href="customer_dashboard.php" class="home-btn">
    <img src="images/logo1.png" alt="Home">
</a>

<h2>My Orders</h2>

<?php if ($orders->num_rows > 0): ?>
    <?php while ($order = $orders->fetch_assoc()): ?>
        <div class="order-section">
            <?php
            $statusClass = '';
            switch ($order['status']) {
                case 'pending':
                    $statusClass = 'status-pending';
                    break;
                case 'in process':
                    $statusClass = 'status-inprocess';
                    break;
                case 'completed':
                    $statusClass = 'status-completed';
                    break;
            }
            ?>
            <h3>
                Order #<?= $order['id'] ?> -
                <span class="status-label <?= $statusClass ?>">
                    <?= ucfirst($order['status']) ?>
                </span>
            </h3>
            <p>Total: <strong>RM<?= number_format($order['total_price'], 2) ?></strong> | Placed at: <?= $order['created_at'] ?></p>

            <?php
            $queueRes = $conn->query("SELECT * FROM queue WHERE order_id = {$order['id']}");
            $queue = null;
            if ($queueRes->num_rows > 0):
                $queue = $queueRes->fetch_assoc();
                echo "<p class='pickup-info'>Queue No: <strong>{$queue['queue_number']}</strong> | Pickup: <strong>" . ucfirst($queue['pickup_status']) . "</strong></p>";

                if ($order['status'] === 'in process' && $queue['estimated_time']) {
                    $formatted = date("h:i A", strtotime($queue['estimated_time']));
                    echo "<p class='estimate-time'>⏱ Estimated Pickup Time: $formatted</p>";
                }

                if ($queue['pickup_status'] === 'ready') {
                    echo "
                        <form method='POST' action='pickup_confirm.php'>
                            <input type='hidden' name='order_id' value='{$order['id']}'>
                            <strong>Done picking up?</strong> Click here
                            <button class='pickup-button' type='submit' name='picked_up'>I've Picked Up</button>
                        </form>
                    ";
                } elseif ($queue['pickup_status'] === 'picked up') {
                    echo "<p class='completed'>✓ Order Picked Up</p>";
                }
            endif;
            ?>

            <table>
                <tr><th>Item</th><th>Quantity</th><th>Price (each)</th></tr>
                <?php
                $items = $conn->query("SELECT oi.*, m.name FROM order_items oi 
                                       JOIN menu m ON oi.menu_id = m.id 
                                       WHERE oi.order_id = {$order['id']}");
                while ($item = $items->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>RM<?= number_format($item['price'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <?php
            if ($order['status'] == 'completed' && $queue && $queue['pickup_status'] == 'picked up') {
                $reviewCheck = $conn->query("SELECT * FROM feedback WHERE order_id = {$order['id']} AND user_id = $user_id");
                if ($reviewCheck->num_rows == 0) {
                    echo "<a class='review-button' href='give_review.php?order_id={$order['id']}'>Give Review</a>";
                } else {
                    echo "<p class='completed'>✓ You have reviewed this order.</p>";
                }
            }
            ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>You have no orders yet.</p>
<?php endif; ?>

</body>
</html>
