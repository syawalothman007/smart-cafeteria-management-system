<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['picked_up']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    // Update pickup_status to 'picked up' in the queue table
    $conn->query("UPDATE queue SET pickup_status = 'picked up' WHERE order_id = $order_id");

    echo "<script>alert('Thanks! Order marked as picked up.'); window.location='my_orders.php';</script>";
}
?>
