<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Orders</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #fdfbfb, #ebedee);
            padding: 2rem;
            margin: 0;
            color: #333;
        }

        h2 {
            color: #6c5b7b;
        }

        .order {
            background: #fff;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            border-left: 5px solid #c06c84;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        h4 {
            margin: 0 0 0.5rem;
            color: #355c7d;
        }

        a {
            text-decoration: none;
            color: #0077b6;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        hr {
            margin: 1rem 0;
        }

        .back-link {
            margin-top: 2rem;
            display: inline-block;
            color: #2980b9;
        }

        /* Home Button Spec */
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
    </style>
</head>
<body>

<!-- Home Button -->
<a href="customer_dashboard.php" class="home-btn">
    <img src="images/logo1.png" alt="Home">
</a>

<h2>Your Orders</h2>

<?php
while ($row = $result->fetch_assoc()) {
    echo "<div class='order'>";
    echo "<h4>Order #{$row['id']}</h4>";
    echo "Total: RM" . number_format($row['total_price'], 2) . "<br>";
    echo "Status: {$row['status']}<br>";
    echo "Date: {$row['created_at']}<br>";
    echo "<a href='feedback.php?order_id={$row['id']}'>Give Feedback</a>";
    echo "</div>";
}
?>

<a class="back-link" href='customer_dashboard.php'>← Back</a>

</body>
</html>
