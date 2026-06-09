<?php
session_start();
include("config.php");

// Set the timezone to Kuala Lumpur
date_default_timezone_set('Asia/Kuala_Lumpur');

// Initialize error or success message
$estimatedTimeMsg = "";

// Handle the form submission to simulate status change to 'in process'
if (isset($_POST['update'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    // Update order status
    $conn->query("UPDATE orders SET status = '$new_status' WHERE id = $order_id");

    // Insert into order history
    $conn->query("INSERT INTO order_history (order_id, status) VALUES ($order_id, '$new_status')");//str

    // If status is 'in process', set estimated_time = current time + 5 minutes
    if ($new_status === 'in process') {
        // Capture the current time (when the status is changed)
        $current_time = date("Y-m-d H:i:s");  // This gets the current time in Kuala Lumpur timezone
        
        // Calculate the estimated time (current time + 5 minutes)
        $estimated_time = date("Y-m-d H:i:s", strtotime("$current_time +5 minutes"));
        
        // Update the estimated time in the queue table
        $conn->query("UPDATE queue SET estimated_time = '$estimated_time' WHERE order_id = $order_id");//en
    }

    echo "<script>alert('Order updated!'); window.location='manage_orders.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #fbeaff, #e4f9f5);
            margin: 0;
            padding: 4rem 2rem 2rem;
            position: relative;
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
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        th, td {
            padding: 0.8rem;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #fdfbff;
            color: #6c5b7b;
        }

        td {
            color: #555;
        }

        select {
            padding: 0.4rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            background-color: #fdfbff;
        }

        button {
            background-color: #a8e6cf;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-left: 0.5rem;
        }

        button:hover {
            background-color: #dcedc1;
        }

        form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</head>
<body>

<!-- Home Button -->
<a href="staff_dashboard.php" class="home-btn">
    <img src="images/logo1.png" alt="Home">
</a>

<h2>Manage Orders</h2>

<table>
    <tr><th>ID</th><th>User</th><th>Total</th><th>Status</th><th>Update</th></tr>
    <?php
    $orders = $conn->query("
        SELECT o.*, u.full_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at ASC
    ");
    while ($row = $orders->fetch_assoc()):
    ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['full_name']) ?></td>
        <td>RM<?= number_format($row['total_price'], 2) ?></td>
        <td><?= ucfirst($row['status']) ?></td>
        <td>
            <?php if ($row['status'] === 'completed'): ?>
                <span style="color: green; font-weight: bold;">✔ Completed</span>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                    <select name="status">
                        <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="in process" <?= $row['status'] === 'in process' ? 'selected' : '' ?>>In Process</option>
                        <option value="completed">Completed</option>
                    </select>
                    <button type="submit" name="update">Update</button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
