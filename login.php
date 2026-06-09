<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $res = $conn->query("SELECT * FROM users WHERE username = '$username' AND password = '$password'");
    if ($res->num_rows == 1) {
        $user = $res->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
		$_SESSION['full_name'] = $row['full_name'];

        if ($user['role'] == 'customer') {
            header("Location: customer_dashboard.php");
        } elseif ($user['role'] == 'staff') {
            header("Location: staff_dashboard.php");
        } else {
            header("Location: admin_dashboard.php");
        }
    } else {
        echo "<script>alert('Invalid login!'); window.location='index.html';</script>";
    }
}
?>
