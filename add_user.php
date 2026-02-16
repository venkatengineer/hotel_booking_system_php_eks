<?php
session_start();
include 'config.php';

/* Check login */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* Check role safely (PHP 5 compatible) */
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) != 'admin') {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #d8f3dc, #ffd6e8);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

        .form-wrapper {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-card {
            background: rgba(255,255,255,0.9);
            padding: 40px;
            border-radius: 25px;
            width: 450px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #2d6a4f;
            margin-bottom: 20px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 30px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .btn {
            width: 100%;
            padding: 12px;
            border-radius: 30px;
            border: none;
            font-weight: 600;
            background: linear-gradient(90deg,#b9fbc0,#ffc6d9);
            cursor: pointer;
        }

        .back-btn {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            color: #8e3b63;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'header_nav.php'; ?>


<div class="form-wrapper">
<div class="form-card">

<h1>Add New User 👤</h1>

<form method="POST" action="save_user.php">

    <input type="text" name="username" placeholder="Username" required>

    <input type="email" name="email" placeholder="Email" required>

    <input type="password" name="password" placeholder="Password" required>

    <select name="role" required>
        <option value="">Select Role</option>
        <option value="Admin">Admin</option>
        <option value="Admin">Manager</option>
        <option value="Staff">Staff</option>
    </select>

    <button type="submit" class="btn">Create User</button>

</form>

<a href="dashboard.php" class="back-btn">Back to Dashboard</a>

</div>
</div>

</body>
</html>
