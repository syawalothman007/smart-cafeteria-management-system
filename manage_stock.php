<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    header("Location:index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['menu_id'] as $index => $menu_id) {//str
        $menu_id = intval($menu_id);
        $quantity = max(0, intval($_POST['stock_quantity'][$index])); // avoid negative values

        $check = $conn->query("SELECT * FROM stock WHERE menu_id = $menu_id");
        if ($check->num_rows > 0) {
            $conn->query("UPDATE stock SET stock_quantity = $quantity WHERE menu_id = $menu_id");
        } else {
            $conn->query("INSERT INTO stock (menu_id, stock_quantity) VALUES ($menu_id, $quantity)");
        }

        // Update availability
        $availability = $quantity > 0 ? 'available' : 'unavailable';
        $conn->query("UPDATE menu SET availability = '$availability' WHERE id = $menu_id");//en
    }

    echo "<script>alert('All stock updated!'); window.location='manage_stock.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Stock</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(to right, #fbeaff, #e4f9f5);
            padding: 4rem 2rem 2rem;
            margin: 0;
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
        h2 {
            color: #6c5b7b;
            text-align: center;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 1rem;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #fdfbff;
            color: #6c5b7b;
        }
        td {
            color: #555;
        }
        input[type="number"] {
            width: 60px;
            padding: 0.4rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            text-align: center;
        }
        .qty-btn {
            background-color: #ddd;
            border: none;
            padding: 0.2rem 0.6rem;
            cursor: pointer;
            font-size: 1rem;
            border-radius: 6px;
            margin: 0 4px;
        }
        .qty-btn:hover {
            background-color: #bbb;
        }
        .update-all-btn {
            display: block;
            margin: 30px auto 0;
            background-color: #a8e6cf;
            border: none;
            padding: 0.6rem 1.5rem;
            font-weight: bold;
            font-size: 1rem;
            border-radius: 8px;
            cursor: pointer;
        }
        .update-all-btn:hover {
            background-color: #dcedc1;
        }
    </style>
</head>
<body>

<!-- Home Button -->
<a href="staff_dashboard.php" class="home-btn">
    <img src="images/logo1.png" alt="Home">
</a>

<h2>Inventory - Menu Stock</h2>

<form method="POST">
    <table>
        <tr>
            <th>Item</th>
            <th>Current Stock</th>
            <th>Update Stock</th>
        </tr>
        <?php
        $result = $conn->query("
            SELECT m.id, m.name, COALESCE(s.stock_quantity, 0) AS quantity
            FROM menu m
            LEFT JOIN stock s ON m.id = s.menu_id
            ORDER BY m.name ASC
        ");

        while ($row = $result->fetch_assoc()) {
            $menuId = intval($row['id']);
            $menuName = htmlspecialchars($row['name']);
            $stockQty = intval($row['quantity']);

            echo "<tr>
                <td>$menuName</td>
                <td>$stockQty</td>
                <td>
                    <input type='hidden' name='menu_id[]' value='$menuId'>
                    <button type='button' class='qty-btn' onclick='changeQty(this, -1)'>-</button>
                    <input type='number' name='stock_quantity[]' value='$stockQty' min='0' required>
                    <button type='button' class='qty-btn' onclick='changeQty(this, 1)'>+</button>
                </td>
            </tr>";
        }
        ?>
    </table>
    <button type="submit" class="update-all-btn">Update All</button>
</form>

<script>
    function changeQty(button, delta) {
        const input = button.parentElement.querySelector('input[type="number"]');
        let val = parseInt(input.value) || 0;
        val = Math.max(0, val + delta); // Prevent negative
        input.value = val;
    }
</script>

</body>
</html>
