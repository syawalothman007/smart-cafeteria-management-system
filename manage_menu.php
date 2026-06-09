<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    header("Location: ../index.html");
    exit();
}

// Add
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    $conn->query("INSERT INTO menu (name, description, price, category) 
                  VALUES ('$name', '$desc', $price, '$category')");
    echo "<script>alert('Menu item added!'); window.location='manage_menu.php';</script>";
}

// Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM menu WHERE id = $id");
    echo "<script>alert('Menu item deleted!'); window.location='manage_menu.php';</script>";
}

// Edit
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    $conn->query("UPDATE menu SET name='$name', description='$desc', price=$price, category='$category' WHERE id=$id");
    echo "<script>alert('Menu item updated!'); window.location='manage_menu.php';</script>";
}

// For Edit Mode
$edit_item = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM menu WHERE id = $edit_id");
    if ($edit_result->num_rows > 0) {
        $edit_item = $edit_result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Menu</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #fbeaff, #e4f9f5);
            margin: 0;
            padding: 4rem 2rem 2rem;
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

        h2, h3 {
            color: #6c5b7b;
        }

        form {
            background-color: #fff6fb;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin-bottom: 2rem;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 0.6rem;
            margin-bottom: 1rem;
            border: 1px solid #d3cce3;
            border-radius: 8px;
            font-size: 1rem;
            background-color: #fdfbff;
        }

        button {
            background-color: #a8e6cf;
            color: #333;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #dcedc1;
        }

        a {
            text-decoration: none;
            font-size: 0.95rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 0.8rem;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #fdfbff;
            color: #6c5b7b;
        }

        .btn {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.9rem;
            text-decoration: none;
            margin-right: 0.3rem;
            display: inline-block;
            text-align: center;
            color: #fff;
        }

        .edit-btn {
            background-color: #8ecae6;
        }

        .edit-btn:hover {
            background-color: #219ebc;
        }

    </style>
</head>
<body>

<!-- Home Button -->
<a href="staff_dashboard.php" class="home-btn">
    <img src="images/logo1.png" alt="Home">
</a>

<h2><?= $edit_item ? 'Edit Menu Item' : 'Add Menu Item' ?></h2>

<form method="POST">
    <?php if ($edit_item): ?>
        <input type="hidden" name="id" value="<?= $edit_item['id'] ?>">
    <?php endif; ?>
    <input type="text" name="name" placeholder="Name" required value="<?= $edit_item['name'] ?? '' ?>">
    <textarea name="description" placeholder="Description"><?= $edit_item['description'] ?? '' ?></textarea>
    <input type="number" name="price" step="0.01" placeholder="Price" required value="<?= $edit_item['price'] ?? '' ?>">
    <select name="category" required>
        <option value="food" <?= (isset($edit_item) && $edit_item['category'] == 'food') ? 'selected' : '' ?>>Food</option>
        <option value="drink" <?= (isset($edit_item) && $edit_item['category'] == 'drink') ? 'selected' : '' ?>>Drink</option>
    </select>
    <button type="submit" name="<?= $edit_item ? 'update' : 'add' ?>">
        <?= $edit_item ? 'Update' : 'Add' ?>
    </button>
    <?php if ($edit_item): ?>
        <br><br><a href="manage_menu.php">Cancel Edit</a>
    <?php endif; ?>
</form>

<h3>All Menu Items</h3>
<table>
    <tr><th>ID</th><th>Name</th><th>Price</th><th>Category</th><th>Action</th></tr>
    <?php
    $result = $conn->query("SELECT * FROM menu");
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>" . htmlspecialchars($row['name']) . "</td>
            <td>RM" . number_format($row['price'], 2) . "</td>
            <td>" . ucfirst($row['category']) . "</td>
            <td>
                <a href='?edit={$row['id']}' class='btn edit-btn'>Edit</a>
            </td>
        </tr>";
    }
    ?>
</table>

</body>
</html>
