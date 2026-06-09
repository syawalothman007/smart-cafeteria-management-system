<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: index.html");
    exit();
}

// Handle filters
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$availabilityFilter = isset($_GET['availability']) ? $_GET['availability'] : '';

// Build base query
$query = "SELECT m.*, s.stock_quantity 
          FROM menu m 
          LEFT JOIN stock s ON m.id = s.menu_id 
          WHERE 1";

// Apply filters
if ($categoryFilter === 'food' || $categoryFilter === 'drink') {
    $query .= " AND m.category = '" . $conn->real_escape_string($categoryFilter) . "'";
}

if ($availabilityFilter === 'available' || $availabilityFilter === 'unavailable') {
    $query .= " AND m.availability = '" . $conn->real_escape_string($availabilityFilter) . "'";
}

$menu = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Menu</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f8;
            margin: 0;
            padding: 4rem 2rem 2rem;
            color: #333;
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
            transform: scale(1.1);
        }

        h2 {
            color: #2f4f4f;
            text-align: center;
        }

        form.filter-form {
            text-align: center;
            margin-bottom: 30px;
        }

        form select {
            padding: 8px 12px;
            border-radius: 6px;
            margin: 0 10px;
            border: 1px solid #ccc;
        }

        .food-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 2rem;
        }

        .preview-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            text-align: center;
        }

        .preview-card:hover {
            transform: scale(1.05);
        }

        .preview-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
        }

        .preview-card h3 {
            margin: 10px 0 5px;
            color: #00796b;
        }

        .preview-card p {
            font-size: 0.95rem;
            color: #555;
            margin: 0 0 10px;
        }

        .preview-card .price {
            font-weight: bold;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .preview-card form {
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .qty-btn {
            background-color: #8fd3f4;
            color: #1e2a38;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
        }

        .qty-btn:hover {
            background-color: #6ec1e4;
        }

        input[type="number"] {
            width: 50px;
            padding: 0.4rem;
            border-radius: 6px;
            border: 1px solid #b0c4de;
            background-color: #f5faff;
            text-align: center;
        }

        .add-btn {
            background-color: #00796b;
            color: white;
            padding: 0.4rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .add-btn:hover {
            background-color: #004d40;
        }

        em {
            color: #b22222;
            font-weight: bold;
        }
		.cart-btn {
			position: fixed;
			top: 90px; /* directly below the home button */
			left: 20px;
			z-index: 1000;
		}

		.cart-btn img {
			height: 60px;
			width: auto;
			cursor: pointer;
			transition: transform 0.2s ease;
		}

		.cart-btn img:hover {
			transform: scale(1.1);
		}


    </style>
</head>
<body>

<!-- Home Button -->
<a href="customer_dashboard.php" class="home-btn">
    <img src="images/logo1.png" alt="Home">
</a>

<!-- Cart Button below Home -->
<a href="view_cart.php" class="cart-btn">
    <img src="images/cart1.png" alt="Cart">
</a>



<h2>Browse Menu</h2>

<!-- Filter Form -->
<form method="GET" class="filter-form">
    <label for="category">Category:</label>
    <select name="category" id="category">
        <option value="">All</option>
        <option value="food" <?= $categoryFilter === 'food' ? 'selected' : '' ?>>Food</option>
        <option value="drink" <?= $categoryFilter === 'drink' ? 'selected' : '' ?>>Drink</option>
    </select>

    <label for="availability">Availability:</label>
    <select name="availability" id="availability">
        <option value="">All</option>
        <option value="available" <?= $availabilityFilter === 'available' ? 'selected' : '' ?>>available</option>
        <option value="unavailable" <?= $availabilityFilter === 'unavailable' ? 'selected' : '' ?>>unavailable</option>
    </select>

    <button type="submit" class="add-btn">Filter</button>
</form>

<div class="food-grid">
    <?php while ($row = $menu->fetch_assoc()): ?>
        <div class="preview-card">
            <img src="images/<?= htmlspecialchars($row['image_filename']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <p><?= htmlspecialchars($row['description']) ?></p>
            <div class="price">RM<?= number_format($row['price'], 2) ?></div>
            <?php if ($row['availability'] === 'available' && ($row['stock_quantity'] ?? 0) > 0): ?>
                <form method="POST" action="add_to_cart.php">
                    <input type="hidden" name="menu_id" value="<?= $row['id'] ?>">
                    <button type="button" class="qty-btn" onclick="adjustQty(this, -1, <?= $row['stock_quantity'] ?>)">−</button>
                    <input type="number" name="quantity" value="1" min="1" max="<?= $row['stock_quantity'] ?>">
                    <button type="button" class="qty-btn" onclick="adjustQty(this, 1, <?= $row['stock_quantity'] ?>)">+</button>
                    <button type="submit" class="add-btn">Add to Cart</button>
                </form>
            <?php else: ?>
                <em>Out Of Stock</em>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>

<script>
    function adjustQty(button, change, maxQty) {
        const input = button.parentElement.querySelector('input[type="number"]');
        let current = parseInt(input.value) || 1;
        current += change;
        if (current < 1) current = 1;
        if (current > maxQty) current = maxQty;
        input.value = current;
    }
</script>

</body>
</html>
