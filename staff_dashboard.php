<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    header("Location:index.html");
    exit();
}

if (!isset($_SESSION['full_name'])) {
    $staff_id = $_SESSION['user_id'];
    $res = $conn->query("SELECT full_name FROM users WHERE id = $staff_id");
    if ($res->num_rows > 0) {
        $_SESSION['full_name'] = $res->fetch_assoc()['full_name'];
    }
}

$today = date('Y-m-d');
$ordersToday = $conn->query("
    SELECT o.id, u.full_name, o.status, o.total_price, o.created_at 
    FROM orders o
    JOIN users u ON o.user_id = u.id
");

$totalSales = 0;
$totalOrders = 0;
$statusCount = [
    "pending" => 0,
    "in process" => 0,
    "completed" => 0
];

$todayOrders = [];
while ($row = $ordersToday->fetch_assoc()) {
    $orderDate = date('Y-m-d', strtotime($row['created_at']));
    if ($orderDate === $today) {
        $todayOrders[] = $row;
        $totalSales += $row['total_price'];
        $totalOrders++;
        $statusKey = strtolower($row['status']);
        if (isset($statusCount[$statusKey])) {
            $statusCount[$statusKey]++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f1f8fc;
            display: flex;
        }

        .top-left-controls {
            position: fixed;
            top: 20px;
            left: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1001;
        }

        .toggle-btn {
            width: 50px;
            height: 50px;
            background-color: #00796b;
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .home-btn img.logo {
            height: 60px;
            cursor: pointer;
        }

        .sidebar {
            width: 220px;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.08);
            height: 100vh;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .sidebar h2 {
            margin-top: 80px;
            color: #2c3e50;
        }

        .sidebar a {
            display: block;
            margin: 12px 0;
            text-decoration: none;
            color: #00796b;
            font-weight: 500;
        }

        .sidebar a:hover {
            color: #004d40;
        }

        .content {
            flex-grow: 1;
            padding: 2rem;
            width: 100%;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #ff6b6b;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .cards {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .card {
            flex: 1;
            min-width: 220px;
            background: white;
            padding: 20px;
            border-left: 6px solid #00796b;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .card h3 {
            margin: 0 0 10px;
            color: #333;
        }

        .card p {
            font-size: 1.5rem;
            color: #00796b;
        }

        #statusChart {
            max-width: 600px;
            margin: 0 auto 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #fff;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #e0f7fa;
        }

        .btn-view {
            background-color: #00796b;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
        }

        .filter-box {
            margin-bottom: 20px;
        }

        .status.pending { color: #ff9800; font-weight: bold; }
        .status.in-process { color: #2196f3; font-weight: bold; }
        .status.completed { color: #4caf50; font-weight: bold; }
    </style>
</head>
<body>

<div class="top-left-controls">
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
    <a href="staff_dashboard.php" class="home-btn">
        <img src="images/logo1.png" alt="Home" class="logo">
    </a>
</div>

<div class="sidebar" id="sidebar">
    <h2>Staff Panel</h2>
    <a href="manage_menu.php">Manage Menu</a>
    <a href="manage_orders.php">Manage Orders</a>
    <a href="manage_stock.php">Manage Stock</a>
    <a href="manage_queue.php">Manage Queue</a>
    <a href="manage_reviews.php">View Reviews</a>
    <a href="change_password.php">Change Password</a>
</div>

<div class="content">
    <a href="logout.php" class="logout-btn">Logout</a>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>
    <p>This is your staff dashboard.</p>

    <!-- Summary Cards -->
    <div class="cards">
        <div class="card">
            <h3>Total Sales (Today)</h3>
            <p>RM <?= number_format($totalSales, 2) ?></p>
        </div>
        <div class="card">
            <h3>Total Orders</h3>
            <p><?= $totalOrders ?></p>
        </div>
        <div class="card">
            <h3>Pending</h3>
            <p><?= $statusCount['pending'] ?></p>
        </div>
        <div class="card">
            <h3>In Process</h3>
            <p><?= $statusCount['in process'] ?></p>
        </div>
        <div class="card">
            <h3>Completed</h3>
            <p><?= $statusCount['completed'] ?></p>
        </div>
    </div>

    <!-- Chart -->
    <canvas id="statusChart"></canvas>

    <!-- Filter + Orders Table -->
    <h2>Today's Orders</h2>
    <div class="filter-box">
        <label for="statusFilter"><strong>Filter by Status:</strong></label>
        <select id="statusFilter" onchange="filterTable()">
            <option value="">All</option>
            <option value="pending">Pending</option>
            <option value="in process">In Process</option>
            <option value="completed">Completed</option>
        </select>
    </div>

    <?php if (count($todayOrders) > 0): ?>
    <table id="ordersTable">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Total (RM)</th>
                <th>Time</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($todayOrders as $row): 
                $statusClass = strtolower(str_replace(' ', '-', $row['status']));
            ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td class="status <?= $statusClass ?>"><?= ucfirst($row['status']) ?></td>
                <td><?= number_format($row['total_price'], 2) ?></td>
                <td><?= date("h:i A", strtotime($row['created_at'])) ?></td>
                <td><a class="btn-view" href="manage_orders.php">View Details</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No orders received today.</p>
    <?php endif; ?>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('hidden');
    }

    function filterTable() {
        const status = document.getElementById("statusFilter").value.toLowerCase();
        const rows = document.querySelectorAll("#ordersTable tbody tr");

        rows.forEach(row => {
            const statusCell = row.cells[2].textContent.toLowerCase();
            row.style.display = (status === "" || statusCell === status) ? "" : "none";
        });
    }

    // Chart.js: Bar Chart
    const ctx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pending', 'In Process', 'Completed'],
            datasets: [{
                label: 'Order Status Count',
                data: [<?= $statusCount['pending'] ?>, <?= $statusCount['in process'] ?>, <?= $statusCount['completed'] ?>],
                backgroundColor: ['#ff9800', '#2196f3', '#4caf50']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: true, text: "Today's Order Status Breakdown" }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    precision: 0
                }
            }
        }
    });
</script>

</body>
</html>
