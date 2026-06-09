<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    header("Location:index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'], $_POST['feedback_id'])) {
    $reply = $conn->real_escape_string($_POST['reply']);
    $feedback_id = intval($_POST['feedback_id']);
    $conn->query("UPDATE feedback SET reply = '$reply' WHERE id = $feedback_id");
    echo "<script>alert('Reply submitted successfully.'); window.location='manage_reviews.php';</script>";
}

$filter_rating = isset($_GET['rating']) && is_numeric($_GET['rating']) ? intval($_GET['rating']) : 0;

$filter_query = "SELECT f.*, u.full_name, o.id AS order_id, o.created_at 
    FROM feedback f 
    JOIN users u ON f.user_id = u.id 
    JOIN orders o ON f.order_id = o.id";

if ($filter_rating > 0) {
    $filter_query .= " WHERE f.rating = $filter_rating";
}

$filter_query .= " ORDER BY f.created_at DESC";
$reviews = $conn->query($filter_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Reviews</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(to right, #fbeaff, #e4f9f5);
            padding: 4rem 2rem 2rem;
            margin: 0;
            position: relative;
        }

        /* Home Button */
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

        .filter-bar {
            margin-bottom: 1rem;
        }

        label {
            font-weight: bold;
            color: #444;
        }

        select {
            padding: 0.4rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            background-color: #fff6fb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 1rem;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #fdfbff;
            color: #6c5b7b;
        }

        td {
            color: #555;
        }

        textarea {
            width: 100%;
            padding: 0.6rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            background-color: #fefefe;
            resize: vertical;
        }

        button {
            margin-top: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: #a8e6cf;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
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

<h2>Customer Feedback</h2>

<div class="filter-bar">
    <form method="GET">
        <label for="rating">Filter by Rating:</label>
        <select name="rating" onchange="this.form.submit()">
            <option value="0">-- All Ratings --</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>" <?= $filter_rating == $i ? 'selected' : '' ?>><?= $i ?> Star</option>
            <?php endfor; ?>
        </select>
    </form>
</div>

<?php if ($reviews->num_rows > 0): ?>
    <table>
        <tr>
            <th>Customer</th>
            <th>Order ID</th>
            <th>Date</th>
            <th>Rating</th>
            <th>Feedback</th>
            <th>Reply</th>
        </tr>
        <?php while ($row = $reviews->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td>#<?= $row['order_id'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td><?= $row['rating'] ?> ⭐</td>
                <td><?= nl2br(htmlspecialchars($row['feedback'])) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="feedback_id" value="<?= $row['id'] ?>">
                        <textarea name="reply" rows="3" placeholder="Reply to this review..."><?= htmlspecialchars($row['reply'] ?? '') ?></textarea>
                        <button type="submit">Submit</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No feedback found for the selected filter.</p>
<?php endif; ?>

</body>
</html>
