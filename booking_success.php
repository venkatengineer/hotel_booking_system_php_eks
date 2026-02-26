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
<title>Booking Successful</title>

<style>
body{
    background:linear-gradient(135deg,#d8f3dc,#ffd6e8);
    font-family:'Segoe UI',sans-serif;
}

.wrapper{
    display:flex;
    justify-content:center;
    align-items:center;
    height:80vh;
}

.card{
    background:#ffffff;
    padding:45px 60px;
    border-radius:25px;
    text-align:center;
    box-shadow:0 20px 40px rgba(0,0,0,0.12);
    min-width:420px;
}

h2{
    color:#2d6a4f;
    margin-bottom:15px;
}

p{
    color:#666;
    margin-bottom:30px;
}   

/* Buttons container */
.btn-group{
    display:flex;
    justify-content:center;
    gap:20px;
}

/* Common button style */
.btn{
    padding:12px 28px;
    border:none;
    border-radius:25px;
    font-size:15px;
    cursor:pointer;
    transition:0.25s;
}

/* Dashboard button */
.dashboard{
    background:linear-gradient(90deg,#b9fbc0,#95d5b2);
    color:#2d6a4f;
}

/* Add booking button */
.add{
    background:linear-gradient(90deg,#ffc6d9,#ffd6a5);
    color:#2d6a4f;
}

.btn:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 18px rgba(0,0,0,0.15);
}
</style>
</head>

<body>
<?php include 'header_nav.php'; ?>

<div class="wrapper">
<div class="card">

<h2>✅ Booking Saved Successfully!</h2>
<p>The invoice has been generated and the booking is recorded.</p>

<div class="btn-group">

<!-- Go to Dashboard -->
<form action="dashboard.php" method="get">
    <button class="btn dashboard">Go to Dashboard</button>
</form>

<!-- Add Another Booking -->
<!-- R-->
<form action="register_booking.php" method="get">
    <button class="btn add">Add Another Booking</button>
</form>

</div>

</div>
</div>

</body>
</html>
