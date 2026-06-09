<?php
include("config.php");

$message = "";
$redirect = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username     = $_POST['username'];
    $password     = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    $email        = $_POST['email'];
    $full_name    = $_POST['full_name'];
    $role         = $_POST['role'];

    if ($password !== $confirm_pass) {
        $message = "Passwords do not match.";
    } elseif (!in_array($role, ['customer', 'staff'])) {
        $message = "Invalid role selection.";
    } else {
        $check = $conn->query("SELECT * FROM users WHERE username = '$username'");
        if ($check->num_rows > 0) {
            $message = "Username already taken.";
        } else {
            $sql = "INSERT INTO users (username, password, email, full_name, role)
                    VALUES ('$username', '$password', '$email', '$full_name', '$role')";
            if ($conn->query($sql) === TRUE) {
                $message = "Registration successful! Redirecting to login...";
                $redirect = "index.html";
            } else {
                $message = "Database error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Cafeteria System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #e8f0f7, #dceef2);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        form {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            text-align: center;
            width: 340px;
        }

        h2 {
            margin-bottom: 1.5rem;
            color: #2c3e50;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        select {
            width: 90%;
            padding: 0.6rem;
            margin-bottom: 1rem;
            border: 1px solid #b0bec5;
            border-radius: 10px;
            font-size: 1rem;
            background-color: #f5fafd;
        }

        button {
            background-color: #81d4fa;
            color: #1e2a38;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #4fc3f7;
        }

        p {
            margin-top: 1rem;
            font-size: 0.95rem;
            color: #546e7a;
        }

        a {
            color: #00796b;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>

    <script>
        function validateForm() {
            const username = document.forms["regForm"]["username"].value.trim();
            const password = document.forms["regForm"]["password"].value;
            const confirm  = document.forms["regForm"]["confirm_password"].value;
            const email    = document.forms["regForm"]["email"].value.trim();
            const fullName = document.forms["regForm"]["full_name"].value.trim();
            const role     = document.forms["regForm"]["role"].value;

            if (username === "" || password === "" || confirm === "" || email === "" || fullName === "" || role === "") {
                alert("All fields are required.");
                return false;
            }

            if (password.length < 5) {
                alert("Password must be at least 5 characters long.");
                return false;
            }

            if (password !== confirm) {
                alert("Passwords do not match.");
                return false;
            }

            if (role !== "customer" && role !== "staff") {
                alert("Invalid role selection.");
                return false;
            }

            return true;
        }

        <?php if (!empty($message)): ?>
        window.onload = function() {
            alert("<?= $message ?>");
            <?php if (!empty($redirect)): ?>
            setTimeout(function() {
                window.location.href = "<?= $redirect ?>";
            }, 1000);
            <?php endif; ?>
        };
        <?php endif; ?>
    </script>
</head>
<body>
    <form name="regForm" method="POST" onsubmit="return validateForm();">
        <h2>Register</h2>
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password (min 5 chars)" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="text" name="full_name" placeholder="Full Name" required><br>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="customer">Customer</option>
            <option value="staff">Staff</option>
        </select><br>
        <button type="submit">Register</button>
        <p>Already have an account? <a href="index.html">Login here</a>.</p>
    </form>
</body>
</html>
