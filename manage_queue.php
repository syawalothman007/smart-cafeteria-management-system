<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    header("Location: index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $queue_id = $_POST['queue_id'];
    $status = $_POST['pickup_status'];
    $time = $_POST['estimated_time'];

    $conn->query("UPDATE queue SET pickup_status = '$status', estimated_time = '$time' WHERE id = $queue_id");
    echo "<script>alert('Queue updated!'); window.location='manage_queue.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Queue</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
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
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        th, td {
            padding: 0.9rem;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #fdfbff;
            color: #6c5b7b;
        }

        td {
            color: #555;
        }

        .status-pending {
            color: #e67e22;
            font-weight: bold;
        }

        .status-ready {
            color: #2980b9;
            font-weight: bold;
        }

        .status-picked {
            color: #27ae60;
            font-weight: bold;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            align-items: center;
        }

        select,
        input[type="datetime-local"] {
            padding: 0.4rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            background-color: #fdfbff;
        }

        button {
            background-color: #a8e6cf;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #dcedc1;
        }
    </style>
</head>
<body>

<!-- Home Button -->
<a href="staff_dashboard.php" class="home-btn">
    <img src="images/logo1.png" alt="Home">
</a>

<h2>Order Pickup Queue</h2>

<table>
    <tr>
        <th>Queue #</th>
        <th>Order ID</th>
        <th>Status</th>
        <th>Estimated Time</th>
        <th>Action</th>
    </tr>
    <?php
    $res = $conn->query("SELECT q.*, o.status AS order_status
                         FROM queue q
                         JOIN orders o ON q.order_id = o.id
                         ORDER BY q.queue_number");

    while ($row = $res->fetch_assoc()) {
        // Determine status class for coloring
        $statusClass = '';
        if ($row['pickup_status'] === 'pending') {
            $statusClass = 'status-pending';
        } elseif ($row['pickup_status'] === 'ready') {
            $statusClass = 'status-ready';
        } elseif ($row['pickup_status'] === 'picked up') {
            $statusClass = 'status-picked';
        }

        echo "<tr>
            <td>{$row['queue_number']}</td>
            <td>{$row['order_id']}</td>
            <td class='$statusClass'>" . ucfirst($row['pickup_status']) . "</td>
            <td>" . ($row['estimated_time'] ? date('Y-m-d H:i', strtotime($row['estimated_time'])) : '-') . "</td>
            <td>
                <form method='POST'>
                    <input type='hidden' name='queue_id' value='{$row['id']}'>
                    <select name='pickup_status'>
                        <option value='pending'" . ($row['pickup_status'] == 'pending' ? ' selected' : '') . ">Pending</option>
                        <option value='ready'" . ($row['pickup_status'] == 'ready' ? ' selected' : '') . ">Ready</option>
                        <option value='picked up'" . ($row['pickup_status'] == 'picked up' ? ' selected' : '') . ">Picked Up</option>
                    </select>
                    
                    <button type='submit'>Update</button>
                </form>
            </td>
        </tr>";
    }
    ?>
</table>

</body>
</html>
