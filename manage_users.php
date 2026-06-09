<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location:index.html");
    exit();
}

// Add user
if (isset($_POST['add'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $full_name = $_POST['full_name'];

    $conn->query("INSERT INTO users (username, password, role, email, full_name)
                  VALUES ('$username', '$password', '$role', '$email', '$full_name')");
    echo "<script>alert('User added successfully!'); window.location='manage_users.php';</script>";
}

// Delete user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $id");
    echo "<script>alert('User deleted successfully!'); window.location='manage_users.php';</script>";
}

// Update user
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $full_name = $_POST['full_name'];

    $conn->query("UPDATE users SET 
        username = '$username', 
        password = '$password', 
        role = '$role', 
        email = '$email', 
        full_name = '$full_name' 
        WHERE id = $id");
    echo "<script>alert('User updated successfully!'); window.location='manage_users.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #fbeaff, #e4f9f5);
            padding: 4rem 2rem 2rem;
            margin: 0;
            position: relative;
        }

        /* Home Button Styling */
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

        h2, h3 {
            color: #6c5b7b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            margin-top: 1rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 0.75rem;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #fdfbff;
            color: #6c5b7b;
        }

        input[type="text"],
        select {
            padding: 0.4rem;
            width: 100%;
            box-sizing: border-box;
            border-radius: 6px;
            border: 1px solid #ccc;
            background-color: #fefefe;
        }

        button {
            padding: 0.4rem 0.8rem;
            background-color: #a8e6cf;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #dcedc1;
        }

        a {
            color: #ff8b94;
            text-decoration: none;
            margin-left: 0.5rem;
        }

        a:hover {
            text-decoration: underline;
        }

        .form-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group > * {
            flex: 1;
            min-width: 150px;
        }

        form.inline-form {
            display: flex;
            gap: 0.5rem;
        }

        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: #0077b6;
        }
    </style>
</head>
<body>

<!-- Home Button -->
<a href="admin_dashboard.php" class="home-btn">
    <img src="images/logo1.png" alt="Home">
</a>

<h2>Manage Registered Users</h2>

<!-- Add New User Form -->
<h3>Add New User</h3>
<form method="POST">
    <div class="form-group">
        <input type="text" name="username" placeholder="Username" required>
        <input type="text" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="">Role</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
            <option value="customer">Customer</option>
        </select>
        <input type="text" name="email" placeholder="Email">
        <input type="text" name="full_name" placeholder="Full Name" required>
    </div>
    <button type="submit" name="add">Add User</button>
</form>

<!-- All Users Table -->
<h3>All Users</h3>
<table>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Password</th>
        <th>Role</th>
        <th>Email</th>
        <th>Full Name</th>
        <th>Actions</th>
    </tr>
    <?php
    $res = $conn->query("SELECT * FROM users");
    while ($row = $res->fetch_assoc()):
    ?>
    <tr>
        <form method="POST" class="inline-form">
            <td><?= $row['id'] ?><input type="hidden" name="id" value="<?= $row['id'] ?>"></td>
            <td><input type="text" name="username" value="<?= $row['username'] ?>" required></td>
            <td><input type="text" name="password" value="<?= $row['password'] ?>" required></td>
            <td>
                <select name="role" required>
                    <option value="admin" <?= $row['role'] == 'admin' ? 'selected' : '' ?>>admin</option>
                    <option value="staff" <?= $row['role'] == 'staff' ? 'selected' : '' ?>>staff</option>
                    <option value="customer" <?= $row['role'] == 'customer' ? 'selected' : '' ?>>customer</option>
                </select>
            </td>
            <td><input type="text" name="email" value="<?= $row['email'] ?>"></td>
            <td><input type="text" name="full_name" value="<?= $row['full_name'] ?>" required></td>
            <td>
                <button type="submit" name="update">Update</button>
                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
            </td>
        </form>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
