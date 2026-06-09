<?php
session_start();
include("config.php");

// Set the timezone to Kuala Lumpur
date_default_timezone_set('Asia/Kuala_Lumpur');

// Initialize the error or success message
$estimatedTimeMsg = "";

// Handle the form submission to simulate status change to 'in process'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = 1;  // Hardcoded order_id for testing
    $new_status = 'in process';

    // Capture the current time when status is changed
    $current_time = date("Y-m-d H:i:s");  // Current server time in Kuala Lumpur timezone
    
    // Calculate the estimated time (current time + 5 minutes)
    $estimated_time = date("Y-m-d H:i:s", strtotime("$current_time +5 minutes"));

    // Update the order status and estimated time (simulated database update)
    // For this test, we're using a session to store the estimated time instead of updating the DB
    $_SESSION['estimated_time'] = $estimated_time;

    // Set a success message
    $estimatedTimeMsg = "Order status updated to 'in process'. Estimated time is set to: $estimated_time";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Estimated Time Calculation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 50px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin: 10px 0;
            border-left: 6px solid #28a745;
        }

        .button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .button:hover {
            background-color: #218838;
        }

        .estimated-time {
            font-size: 18px;
            color: #007bff;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h1>Test Estimated Time Calculation</h1>

    <!-- Display success or error message if any -->
    <?php if ($estimatedTimeMsg): ?>
        <div class="message"><?= $estimatedTimeMsg ?></div>
    <?php endif; ?>

    <!-- Simulate status change to 'in process' -->
    <form method="POST">
        <button type="submit" name="update_status" class="button">Update Status to 'In Process'</button>
    </form>

    <div class="estimated-time">
        <!-- Display estimated time -->
        <?php
            if (isset($_SESSION['estimated_time'])) {
                $estimated_time = $_SESSION['estimated_time'];
                echo "Estimated Pickup Time: " . date("h:i A", strtotime($estimated_time));
            } else {
                echo "No estimated time set yet.";
            }
        ?>
    </div>

</body>
</html>
