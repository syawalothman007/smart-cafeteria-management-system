<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}

// Handle add
if (isset($_POST['add'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $full_name = $_POST['full_name'];

    $conn->query("INSERT INTO users (username, password, role, email, full_name)
                  VALUES ('$username', '$password', '$role', '$email', '$full_name')");
    echo "<script>alert('User added successfully!'); window.location='admin_dashboard.php';</script>";
    exit();
}

// Handle delete
if (isset($_POST['delete_btn'])) {
    $id = $_POST['id'];
    $conn->query("DELETE FROM users WHERE id = $id");
    echo "<script>alert('User deleted successfully!'); window.location='admin_dashboard.php';</script>";
    exit();
}

// Handle update
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
    echo "<script>alert('User updated successfully!'); window.location='admin_dashboard.php';</script>";
    exit();
}

// Search & Filter logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter_role']) ? $_GET['filter_role'] : '';

$where = [];

if (!empty($search)) {
    $where[] = "LOWER(full_name) = LOWER('$search')";
}
if (!empty($filter)) {
    $where[] = "role = '$filter'";
}

$where_clause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
$res = $conn->query("SELECT * FROM users $where_clause ORDER BY id ASC");

// Summary counts
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$admins = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'")->fetch_assoc()['total'];
$staff = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'staff'")->fetch_assoc()['total'];
$customers = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'customer'")->fetch_assoc()['total'];

if (isset($_GET['search']) && $res->num_rows === 0) {
    echo "<script>alert('No users found for that name. Please try again.'); window.location='admin_dashboard.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #fbeaff, #e4f9f5);
            padding: 4rem 2rem 2rem;
            margin: 0;
        }

        .logout-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #ff6b6b;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.95rem;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #e63946;
        }

        h2, h3 {
            color: #6c5b7b;
        }

        .summary {
            width: 180px;
            height: 180px;
            background: linear-gradient(135deg, #f3c6f1, #c1f3e8);
            padding: 1rem;
            border-radius: 50%;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            font-weight: bold;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #6c5b7b;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
            border: 4px dashed #ffb6c1;
        }

        .summary:hover {
            transform: scale(1.05);
        }

        .summary span.count {
            font-size: 2.8rem;
            font-weight: bold;
            color: #ff6b6b;
            margin-top: 0.5rem;
        }

        .summary-row {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .summary-card {
            flex: 1;
            min-width: 150px;
            background: linear-gradient(135deg, #fceabb, #f8b500);
            color: #333;
            border-radius: 20px;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            font-weight: bold;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .summary-card .count {
            font-size: 2rem;
            margin-top: 0.5rem;
            color: #fff;
        }

        .filter-form {
            margin: 1rem 0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .filter-form input {
            width: 150px;
        }

        .filter-form select {
            width: 120px;
        }

        .filter-form button {
            background-color: #a8e6cf;
            font-weight: bold;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            min-width: 80px;
        }

        .filter-form button:hover {
            background-color: #dcedc1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
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
        }

        .action-btn {
            padding: 0.4rem 0.8rem;
            background-color: #a8e6cf;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            min-width: 80px;
            text-decoration: none;
        }

        .action-btn:hover {
            background-color: #dcedc1;
        }

        .delete-btn {
            background-color: #ff8b94;
            color: white;
        }

        .delete-btn:hover {
            background-color: #ff5c6b;
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
            justify-content: center;
            align-items: center;
        }
    </style>
    <script>
        function confirmDelete(event) {
            if (event.submitter.name === 'delete_btn') {
                return confirm("Delete this user?");
            }
            return true;
        }
    </script>
</head>
<body>

<a href="logout.php" class="logout-btn">Logout</a>

<h2>Admin Dashboard - User Management</h2>

<!-- Total Users Summary -->
<div class="summary">
    👥 Total Users
    <span class="count"><?= $total_users ?></span>
</div>

<!-- Other Role Summary Cards -->
<div class="summary-row">
    <div class="summary-card" style="background: linear-gradient(135deg, #ffecd2, #fcb69f);">
        Admins
        <div class="count"><?= $admins ?></div>
    </div>
    <div class="summary-card" style="background: linear-gradient(135deg, #a1c4fd, #c2e9fb);">
        Staff
        <div class="count"><?= $staff ?></div>
    </div>
    <div class="summary-card" style="background: linear-gradient(135deg, #ffdde1, #ee9ca7);">
        Customers
        <div class="count"><?= $customers ?></div>
    </div>
</div>

<!-- Filter & Search -->
<form method="GET" class="filter-form">
    <input type="text" name="search" placeholder="Search name..." value="<?= htmlspecialchars($search) ?>">
    <select name="filter_role">
        <option value="">All Roles</option>
        <option value="admin" <?= $filter == 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="staff" <?= $filter == 'staff' ? 'selected' : '' ?>>Staff</option>
        <option value="customer" <?= $filter == 'customer' ? 'selected' : '' ?>>Customer</option>
    </select>
    <button type="submit">Search</button>
</form>

<!-- Add New User -->
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
    <button class="action-btn" type="submit" name="add">Add User</button>
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
    <?php while ($row = $res->fetch_assoc()): ?>
    <tr>
        <form method="POST" class="inline-form" onsubmit="return confirmDelete(event)">
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
                <button class="action-btn" type="submit" name="update">Update</button>
                <button class="action-btn delete-btn" type="submit" name="delete_btn">Delete</button>
            </td>
        </form>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
