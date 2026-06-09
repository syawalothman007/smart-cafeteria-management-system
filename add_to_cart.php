<?php
session_start();
include("config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $menu_id = intval($_POST['menu_id']);
    $qty = max(1, intval($_POST['quantity']));

    // Validate item
    $res = $conn->query("SELECT * FROM menu WHERE id = $menu_id AND availability = 'available'");
    $item = $res->fetch_assoc();
    if (!$item) {
        echo "<script>alert('Item not available.'); window.location='browse_menu.php';</script>";
        exit();
    }

    // Get current stock
    $stockRes = $conn->query("SELECT stock_quantity FROM stock WHERE menu_id = $menu_id");
    $stock = $stockRes->fetch_assoc();
    $stock_qty = $stock ? intval($stock['stock_quantity']) : 0;

    // Existing quantity in cart
    $existing_qty = 0;
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    foreach ($_SESSION['cart'] as $cart_item) {
        if ($cart_item['menu_id'] == $menu_id) {
            $existing_qty = $cart_item['quantity'];
            break;
        }
    }

    $total_requested = $existing_qty + $qty;

    if ($total_requested > $stock_qty) {
        $remaining = max(0, $stock_qty - $existing_qty);
        echo "<script>alert('Only $remaining unit(s) left in stock. Cannot add more.'); window.location='browse_menu.php';</script>";
        exit();
    }

    // Add or update cart
    $found = false;
    foreach ($_SESSION['cart'] as &$cart) {
        if ($cart['menu_id'] == $menu_id) {
            $cart['quantity'] += $qty;
            $found = true;
            break;
        }
    }
    unset($cart);

    if (!$found) {
        $_SESSION['cart'][] = [
            'menu_id' => $menu_id,
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $qty
        ];
    }

    echo "<script>alert('Item added to cart.'); window.location='view_cart.php';</script>";
    exit();
}
?>
