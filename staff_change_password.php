<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];

    $result = $conn->query("SELECT password FROM users WHERE id = $user_id");
    $row = $result->fetch_assoc();

    if ($row && password_verify($old, $row['password'])) {
        $hashed_new = password_hash($new, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password = '$hashed_new' WHERE id = $user_id");
        echo "<script>alert('Password updated successfully.'); window.location='staff_dashboard.php';</script>";
    } else {
        echo "<script>alert('Old password incorrect.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password (Staff)</title>
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --bg-color: #f0f6fc;
            --text-color: #333;
            --card-bg: #fff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-color);
            padding: 40px;
            margin: 0;
        }

        /* Home button */
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
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
        }

        form {
            background: var(--card-bg);
            padding: 25px 30px;
            max-width: 420px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px;
            margin-top: 10px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: var(--primary-dark);
        }

        a {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            color: var(--primary-color);
            transition: color 0.3s ease;
        }

        a:hover {
            color: var(--primary-dark);
        }
    </style>
</head>
<body>

<!-- Home Button -->
<a href="staff_dashboard.php" class="home-btn">
    <img src="images/logo1.png" alt="Home">
</a>

<h2>Change Password (Staff)</h2>

<form method="POST">
    <input type="password" name="old_password" placeholder="Old Password" required>
    <input type="password" name="new_password" placeholder="New Password" required>
    <button type="submit">Change</button>
</form>

<a href="staff_dashboard.php">← Back to Dashboard</a>

</body>
</html>
