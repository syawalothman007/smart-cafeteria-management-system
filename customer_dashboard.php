<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Order ready for pickup
$readyQuery = $conn->query("SELECT o.id 
    FROM orders o
    JOIN queue q ON o.id = q.order_id
    WHERE o.user_id = $user_id 
      AND o.status = 'completed'
      AND q.pickup_status = 'ready'
    LIMIT 1");
$hasReadyPickup = $readyQuery->num_rows > 0;

// Feedback reply notification
$replyNotif = $conn->query("SELECT COUNT(*) AS unseen_count 
    FROM feedback 
    WHERE user_id = $user_id AND reply IS NOT NULL AND reply_seen = 0");
$hasUnseenReply = $replyNotif->fetch_assoc()['unseen_count'] > 0;

// Estimated time for "in process" order (soonest)
$estimatedTimeMsg = '';

date_default_timezone_set('Asia/Kuala_Lumpur');

$estimateRes = $conn->query("
    SELECT q.estimated_time 
    FROM orders o
    JOIN queue q ON o.id = q.order_id
    WHERE o.user_id = $user_id 
      AND o.status = 'in process'
      AND q.estimated_time IS NOT NULL
    ORDER BY q.estimated_time ASC
    LIMIT 1
");

if ($estimateRes->num_rows > 0) {
    $row = $estimateRes->fetch_assoc();
    $eta = strtotime($row['estimated_time']);
    $formatted = date("h:i A", $eta);
    $now = time();
    $remaining = $eta - $now;

    if ($remaining > 0) {
        $minutes = ceil($remaining / 60);
        $estimatedTimeMsg = "⏱ Your order is being prepared. Estimated pickup time: <strong>$formatted</strong> (in ~$minutes min)";
    } else {
        $estimatedTimeMsg = "⏱ Your order is being prepared. Estimated pickup time: <strong>$formatted</strong> (ready soon)";
    }
}

// Preview items
$preview = $conn->query("SELECT * FROM menu WHERE availability = 'available' ORDER BY RAND() LIMIT 4");
$previewItems = [];
while ($row = $preview->fetch_assoc()) {
    $previewItems[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(to right, #e8f0f7, #dceef2);
            min-height: 100vh;
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
            height: 100vh;
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .sidebar h3 {
            margin-top: 80px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            color: #00796b;
            text-decoration: none;
        }

        .content {
            flex-grow: 1;
            padding: 2rem;
            background-color: #f5fafd;
            transition: margin-left 0.3s ease;
            width: 100%;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #ff6b6b;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
        }

        .alert {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-left: 6px solid #28a745;
            border-radius: 5px;
            margin-top: 20px;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 6px solid #ffc107;
        }

        .alert-info {
            background-color: #cce5ff;
            color: #004085;
            border-left: 6px solid #17a2b8;
        }

        .view-btn {
            margin-top: 10px;
            display: inline-block;
            padding: 8px 16px;
            background-color: #00796b;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .food-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .preview-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            text-align: center;
        }

        .preview-card:hover {
            transform: scale(1.05);
        }

        .preview-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
        }

        .preview-card h3 {
            margin: 10px 0 5px;
        }

        .preview-card p {
            font-size: 0.95rem;
            color: #555;
            margin: 0 0 10px;
        }

        .interested-btn {
            background-color: #ffb347;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            margin-top: 8px;
        }

        .interested-btn:hover {
            background-color: #f79f1f;
        }
    </style>
</head>
<body>

<div class="top-left-controls">
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
    <a href="customer_dashboard.php" class="home-btn">
        <img src="images/logo1.png" alt="Home" class="logo">
    </a>
</div>

<div class="sidebar" id="sidebar">
    <h3>Menu</h3>
    <ul>
        <li><a href="browse_menu.php">Browse Menu</a></li>
        <li><a href="view_cart.php">View Cart</a></li>
        <li><a href="my_orders.php">My Orders</a></li>
        <li><a href="my_reviews.php">My Reviews</a></li>
        <li><a href="change_password.php">Change Password</a></li>
    </ul>
</div>

<div class="content" id="mainContent">
    <a href="logout.php" class="logout-btn">Logout</a>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
    <p>This is your customer dashboard.</p>

    <?php if ($hasReadyPickup): ?>
        <div class="alert">
            ✅ Your order is <strong>completed</strong> and <strong>ready to pick up</strong>!
            <br><a class="view-btn" href="my_orders.php">View Order Details</a>
        </div>
    <?php endif; ?>

    <?php if ($hasUnseenReply): ?>
        <div class="alert alert-warning">
            📨 You have new reply from staff on your review!
            <br><a class="view-btn" href="my_reviews.php">View Feedback</a>
        </div>
    <?php endif; ?>

    <?php if ($estimatedTimeMsg): ?>
        <div class="alert alert-info">
            <?= $estimatedTimeMsg ?>
            <br><a class="view-btn" href="my_orders.php">View Order</a>
        </div>
    <?php endif; ?>

    <p>Check out our popular menu items:</p>
    <div class="food-grid">
        <?php foreach ($previewItems as $item): ?>
            <div class="preview-card">
                <img src="images/<?= htmlspecialchars($item['image_filename']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p><?= htmlspecialchars($item['description']) ?></p>
                <p><strong>RM<?= number_format($item['price'], 2) ?></strong></p>
                <a class="interested-btn" href="browse_menu.php">Interested?</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('hidden');
}
</script>

</body>
</html>
