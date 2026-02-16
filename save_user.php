<?php
session_start();
include 'config.php';

/* ================= LOGIN + ROLE CHECK ================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) != 'admin') {
    echo "<h2 style='text-align:center;margin-top:50px;color:red;'>Access Denied 🚫</h2>";
    exit();
}

/* ================= CHECK POST ================= */
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: dashboard.php");
    exit();
}

/* ================= GET DATA SAFELY ================= */
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email    = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$role     = isset($_POST['role']) ? trim($_POST['role']) : '';

if ($username == '' || $email == '' || $password == '' || $role == '') {
    die("All fields are required.");
}

/* ================= CHECK DUPLICATE USERNAME ================= */
$check = $conn->prepare("SELECT user_id FROM tbl_users WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    die("Username already exists.");
}
$check->close();

/* ================= HASH PASSWORD (PHP 5.6 SAFE) ================= */
$password_hash = password_hash($password, PASSWORD_BCRYPT);

/* ================= INSERT USER ================= */
$stmt = $conn->prepare("
INSERT INTO tbl_users (username, email, password_hash, role, is_active)
VALUES (?, ?, ?, ?, 1)
");

$stmt->bind_param("ssss", $username, $email, $password_hash, $role);

if (!$stmt->execute()) {
    die("Database Error: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Created</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #d8f3dc, #ffd6e8);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .success-card {
            background: white;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            color: #2d6a4f;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            border-radius: 30px;
            background: linear-gradient(90deg,#b9fbc0,#ffc6d9);
            text-decoration: none;
            color: #2d6a4f;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="success-card">
    <h2>User Created Successfully 🎉</h2>
    <a href="dashboard.php">Back to Dashboard</a>
</div>

</body>
</html>
