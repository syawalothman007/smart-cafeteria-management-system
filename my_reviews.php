<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Mark all replies as seen when the user visits this page
$conn->query("UPDATE feedback SET reply_seen = 1 WHERE user_id = $user_id AND reply IS NOT NULL");

// ✅ Fetch the feedback from this customer
$result = $conn->query("
    SELECT f.*, o.id AS order_id, o.created_at 
    FROM feedback f 
    JOIN orders o ON f.order_id = o.id 
    WHERE f.user_id = $user_id 
    ORDER BY o.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Reviews</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: #f5fafd;
            padding: 4rem 2rem 2rem;
            margin: 0;
            position: relative;
        }

        /* ✅ Home Button (Consistent Design) */
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
            color: #00796b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-top: 1rem;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 1rem;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #e0f2f1;
            color: #00796b;
        }

        td {
            color: #444;
        }

        .reply-box {
            background-color: #f0f9ff;
            border-left: 4px solid #00acc1;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
        }

        .no-reviews {
            text-align: center;
            color: #999;
            margin-top: 2rem;
        }
    </style>
</head>
<body>

<!-- ✅ Home Button -->
<a href="customer_dashboard.php" class="home-btn">
    <img src="images/logo1.png" alt="Home">
</a>

<h2>My Reviews & Replies</h2>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Rating</th>
            <th>Your Feedback</th>
            <th>Staff Reply</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td>#<?= $row['order_id'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td><?= $row['rating'] ?> ⭐</td>
                <td><?= nl2br(htmlspecialchars($row['feedback'])) ?></td>
                <td>
                    <?php if (!empty($row['reply'])): ?>
                        <div class="reply-box"><?= nl2br(htmlspecialchars($row['reply'])) ?></div>
                    <?php else: ?>
                        <em>No reply yet.</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p class="no-reviews">You haven't submitted any feedback yet.</p>
<?php endif; ?>

</body>
</html>
