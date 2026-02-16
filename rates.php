<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (strtolower($_SESSION['role']) != 'admin') {
    die("Access Denied");
}

/* CURRENT ACTIVE RATES */
$current = $conn->query("
SELECT r.*, a.asset_name
FROM tbl_rates r
JOIN tbl_assets a ON a.asset_id = r.asset_id
WHERE r.effective_to = '2036-12-31'
ORDER BY a.asset_name
");

/* OLD DELIMITED RATES */
$history = $conn->query("
SELECT r.*, a.asset_name
FROM tbl_rates r
JOIN tbl_assets a ON a.asset_id = r.asset_id
WHERE r.effective_to <> '2036-12-31'
ORDER BY r.effective_from DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Room Rates Master</title>
<style>
body{background:linear-gradient(135deg,#d8f3dc,#ffd6e8);font-family:'Segoe UI';}
.wrapper{width:92%;margin:30px auto;}
.card{background:#fff;padding:25px;border-radius:25px;box-shadow:0 20px 40px rgba(0,0,0,.1);}
table{width:100%;border-collapse:collapse;}
th{background:#b9fbc0;padding:12px;}
td{padding:12px;text-align:center;}
input{padding:7px;border-radius:15px;border:none;background:#f5f5f5;}
button{padding:7px 15px;border:none;border-radius:20px;background:#ffc6d9;cursor:pointer;}
.history{max-height:250px;overflow-y:auto;margin-top:25px;}
</style>
</head>

<body>
<?php include 'header_nav.php'; ?>

<div class="wrapper">
<div class="card">

<h2>Current Room Rates</h2>

<table>
<tr>
<th>Room</th>
<th>Current Rate</th>
<th>Effective From</th>
<th>New Rate</th>
<th>New Effective From</th>
<th>Update</th>
<th>Delimit</th>
</tr>

<?php while($r=$current->fetch_assoc()){ ?>
<tr>

<!-- DISPLAY CURRENT RATE -->
<td><?php echo $r['asset_name']; ?></td>
<td><strong>₹ <?php echo number_format($r['rate_per_day'],2); ?></strong></td>
<td><?php echo $r['effective_from']; ?></td>

<!-- SAVE NEW RATE -->
<form method="POST" action="save_rate.php">
<td>
    <input type="number" step="0.01" name="rate_per_day" placeholder="Enter New Rate" required>
</td>

<td>
    <input type="date" name="effective_from" required>
</td>

<td>
    <input type="hidden" name="asset_id" value="<?php echo $r['asset_id']; ?>">
    <button>Update</button>
</td>
</form>

<!-- DELIMIT BUTTON -->
<form method="POST" action="delimit_rate.php">
<td>
    <input type="hidden" name="rate_id" value="<?php echo $r['rate_id']; ?>">
    <button style="background:#ffadad;">Delimit</button>
</td>
</form>

</tr>
<?php } ?>
</table>



<h2 style="margin-top:30px;">Old / Delimited Rates</h2>

<div class="history">
<table>
<tr>
<th>Room</th>
<th>Rate</th>
<th>From</th>
<th>To</th>
</tr>

<?php while($h=$history->fetch_assoc()){ ?>
<tr>
<td><?php echo $h['asset_name']; ?></td>
<td><?php echo $h['rate_per_day']; ?></td>
<td><?php echo $h['effective_from']; ?></td>
<td><?php echo $h['effective_to']; ?></td>
</tr>
<?php } ?>
</table>
</div>

</div>
</div>
</body>
</html>
