<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['customer_id'])) {
    header("Location: register_booking.php");
    exit();
}

$customer_id = intval($_GET['customer_id']);

$stmt = $conn->prepare("SELECT full_name FROM tbl_customers WHERE customer_id=?");
$stmt->bind_param("i",$customer_id);
$stmt->execute();
$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
<title>Booking Details</title>

<style>
body{background:linear-gradient(135deg,#d8f3dc,#ffd6e8);font-family:'Segoe UI';}
.wrapper{display:flex;justify-content:center;padding:40px;}
.card{width:600px;background:white;padding:40px;border-radius:25px;box-shadow:0 20px 40px rgba(0,0,0,.1);}
input,select{width:100%;padding:12px;margin-bottom:10px;border-radius:25px;border:1px solid #ddd;}
.btn{padding:12px 25px;border:none;border-radius:25px;background:linear-gradient(90deg,#b9fbc0,#ffc6d9);cursor:pointer;}
.small{font-size:12px;color:#777;margin-bottom:15px;}
</style>
</head>

<body>
<?php include 'header_nav.php'; ?>

<div class="wrapper">
<div class="card">

<h2>Booking for <?php echo htmlspecialchars($full_name); ?></h2>

<form method="POST" action="save_booking.php" onsubmit="return prepareDates();">

<input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">

<label>Select Room</label>
<select name="asset_id" required>
<option value="">Select Room</option>
<?php
$res = $conn->query("SELECT asset_id, asset_name FROM tbl_assets WHERE is_active=1 ORDER BY asset_name");
while($row=$res->fetch_assoc()){
    echo "<option value='".(int)$row['asset_id']."'>".htmlspecialchars(trim($row['asset_name']))."</option>";
}
?>
</select>

<!-- BOOKING FROM -->
<label>From Date</label>
<input type="date" id="from_picker">
<div class="small">OR type manually (DD-MM-YYYY)</div>
<input type="text" id="from_manual" placeholder="e.g. 16-02-2026">

<!-- BOOKING TO -->
<label>To Date</label>
<input type="date" id="to_picker">
<div class="small">OR type manually (DD-MM-YYYY)</div>
<input type="text" id="to_manual" placeholder="e.g. 18-02-2026">

<!-- Hidden DB Values -->
<input type="hidden" name="booking_from" id="from_db">
<input type="hidden" name="booking_to" id="to_db">

<label>No. of Guests</label>
<input type="number" name="no_of_persons" min="1" required>

<button class="btn">Confirm Booking</button>

</form>
</div>
</div>

<script>
function convertToDB(dateStr){
    // Convert DD-MM-YYYY → YYYY-MM-DD
    var p = dateStr.split("-");
    if(p.length !== 3) return null;
    return p[2]+"-"+p[1]+"-"+p[0];
}

function prepareDates(){

    var from = document.getElementById("from_picker").value;
    var to   = document.getElementById("to_picker").value;

    var fromManual = document.getElementById("from_manual").value.trim();
    var toManual   = document.getElementById("to_manual").value.trim();

    // If manual entered → use manual
    if(fromManual !== "") from = convertToDB(fromManual);
    if(toManual !== "")   to   = convertToDB(toManual);

    if(!from || !to){
        alert("Enter dates using calendar or manually.");
        return false;
    }

    if(from > to){
        alert("Invalid date range.");
        return false;
    }

    document.getElementById("from_db").value = from;
    document.getElementById("to_db").value   = to;

    return true;
}
</script>

</body>
</html>
