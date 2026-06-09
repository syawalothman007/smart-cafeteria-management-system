<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: index.html");
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Prevent duplicate
$check = $conn->query("SELECT * FROM feedback WHERE user_id = $user_id AND order_id = $order_id");
if ($check->num_rows > 0) {
    echo "<script>alert('You have already submitted a review.'); window.location='my_orders.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $feedback = $conn->real_escape_string($_POST['feedback']);

    $conn->query("INSERT INTO feedback (user_id, order_id, rating, feedback) 
                  VALUES ($user_id, $order_id, $rating, '$feedback')");
    echo "<script>alert('Thank you for your review!'); window.location='my_orders.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Give Review</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #fbeaff, #e4f9f5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        form {
            background-color: #fff6fb;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }

        h2 {
            color: #6c5b7b;
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 0.3rem;
            color: #444;
        }

        input[type="number"],
        textarea {
            width: 100%;
            padding: 0.6rem;
            margin-bottom: 1rem;
            border: 1px solid #d3cce3;
            border-radius: 10px;
            font-size: 1rem;
            background-color: #fdfbff;
        }

        textarea {
            resize: vertical;
        }

        button {
            background-color: #a8e6cf;
            color: #333;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #dcedc1;
        }

        a {
            display: inline-block;
            margin-top: 1rem;
            color: #0077b6;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<form method="POST">
    <h2>Review for Order #<?= htmlspecialchars($order_id) ?></h2>

    <label for="rating">Rating (1-5):</label>
    <input type="number" name="rating" id="rating" min="1" max="5" required>

    <label for="feedback">Feedback:</label>
    <textarea name="feedback" id="feedback" rows="4" required></textarea>

    <button type="submit">Submit Review</button>
    <a href="my_orders.php">← Back to Orders</a>
</form>

</body>
</html>
