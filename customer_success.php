<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Customer Saved</title>

<style>
body {
    background: linear-gradient(135deg,#d8f3dc,#ffd6e8);
    font-family: 'Segoe UI', sans-serif;
    margin:0;
}

.wrapper {
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.card {
    background:white;
    padding:50px;
    border-radius:25px;
    text-align:center;
    box-shadow:0 25px 50px rgba(0,0,0,0.1);
}

h2 {
    color:#2d6a4f;
}

.btn {
    display:inline-block;
    margin-top:20px;
    padding:12px 25px;
    border-radius:30px;
    background:linear-gradient(90deg,#b9fbc0,#ffc6d9);
    text-decoration:none;
    color:#2d6a4f;
    font-weight:600;
}
</style>
</head>

<body>

<?php include 'header_nav.php'; ?>

<div class="wrapper">
<div class="card">

<h2>✅ Customer Saved Successfully</h2>

<a href="add_customer.php" class="btn">Add Another Customer</a>
<br><br>
<a href="dashboard.php" class="btn">Go to Dashboard</a>

</div>
</div>

</body>
</html>
