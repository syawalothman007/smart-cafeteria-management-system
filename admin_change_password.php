<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];

    $check = $conn->query("SELECT * FROM users WHERE id = $user_id AND password = '$old'");

    if ($check->num_rows == 1) {
        if ($old === $new) {
            echo "<script>alert('New password cannot be the same as old password.');</script>";
        } else {
            $conn->query("UPDATE users SET password = '$new' WHERE id = $user_id");
            echo "<script>alert('Password updated successfully.');</script>";
        }
    } else {
        echo "<script>alert('Old password is incorrect.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password (Admin)</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #e8f0f7, #dceef2);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
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

        form {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
        }

        h2 {
            margin-bottom: 1.5rem;
            color: #2c3e50;
        }

        input[type="password"] {
            width: 90%;
            padding: 0.6rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 1rem;
            background-color: #f9f9fb;
        }

        button {
            background-color: #a8dadc;
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
            background-color: #81c3c9;
        }
    </style>
</head>
<body>

<!-- Home Logo Button -->
<a href="admin_dashboard.php" class="home-btn">
    <img src="images/logo1.png" alt="Home">
</a>

<form method="POST">
    <h2>Change Password (Admin)</h2>
    <input type="password" name="old_password" placeholder="Old Password" required><br>
    <input type="password" name="new_password" placeholder="New Password" required><br>
    <button type="submit">Change</button>
</form>

</body>
</html>
