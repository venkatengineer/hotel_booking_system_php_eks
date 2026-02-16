<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$customer = null;
$visitCount = 0;
$previousBookings = null;

/* LOAD CUSTOMER WHEN SELECTED FROM AUTOCOMPLETE */
if (isset($_GET['customer_id'])) {

    $cid = intval($_GET['customer_id']);

    $stmt = $conn->prepare("SELECT * FROM tbl_customers WHERE customer_id=?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();

        /* Visit Count */
        $stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM tbl_bookings WHERE customer_id=?");
        $stmt2->bind_param("i", $cid);
        $stmt2->execute();
        $visitResult = $stmt2->get_result();
        $visitCount = $visitResult->fetch_assoc()['total'];
        $stmt2->close();

        /* Previous Bookings */
        $stmt3 = $conn->prepare("
            SELECT b.booking_from, b.booking_to, a.asset_name
            FROM tbl_bookings b
            JOIN tbl_assets a ON b.asset_id = a.asset_id
            WHERE b.customer_id=?
            ORDER BY b.booking_from DESC
        ");
        $stmt3->bind_param("i", $cid);
        $stmt3->execute();
        $previousBookings = $stmt3->get_result();
        $stmt3->close();
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>New Booking</title>
<link rel="stylesheet" href="css/style.css">

<style>
body {
    background: linear-gradient(135deg, #d8f3dc, #ffd6e8);
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
}

.wrapper {
    display: flex;
    justify-content: center;
    padding: 40px;
}

.card {
    width: 1000px;
    background: rgba(255,255,255,0.9);
    padding: 40px;
    border-radius: 25px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.1);
}

.section {
    margin-top: 25px;
    padding: 20px;
    background: white;
    border-radius: 20px;
}

input, select {
    width: 100%;
    padding: 14px;
    border-radius: 30px;
    border: none;
    background: #f5f5f5;
    margin-bottom: 15px;
}

.btn {
    padding: 14px 30px;
    border-radius: 30px;
    border: none;
    background: linear-gradient(90deg,#b9fbc0,#ffc6d9);
    cursor: pointer;
}

/* AUTOCOMPLETE */
#suggestions {
    background: white;
    border-radius: 12px;
    max-height: 250px;
    overflow-y: auto;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.suggestion {
    padding: 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.suggestion:hover {
    background: #f1fff3;
}
</style>
</head>

<body>
<?php include 'header_nav.php'; ?>

<div class="wrapper">
<div class="card">

<h1>New Booking</h1>

<!-- SEARCH -->
<div class="section">
<h3>Search Customer</h3>

<input type="text" id="searchBox"
placeholder="Start typing Name / Aadhaar / Passport..."
autocomplete="off">

<div id="suggestions"></div>
</div>

<?php if ($customer): ?>

<!-- CUSTOMER DETAILS -->
<div class="section">
<h3>Customer Details</h3>
<p><strong>Name:</strong> <?= htmlspecialchars($customer['full_name']) ?></p>
<p><strong>Nationality:</strong> <?= htmlspecialchars($customer['nationality']) ?></p>
<p><strong>Total Visits:</strong> <?= $visitCount ?></p>
</div>

<?php if ($visitCount > 0): ?>
<div class="section">
<h3>Previous Bookings</h3>

<table width="100%" border="1" cellpadding="8">
<tr>
<th>Room</th>
<th>From</th>
<th>To</th>
</tr>

<?php while($row = $previousBookings->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['asset_name']) ?></td>
<td><?= date("d-m-Y", strtotime($row['booking_from'])) ?></td>
<td><?= date("d-m-Y", strtotime($row['booking_to'])) ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>
<?php endif; ?>

<!-- BOOKING FORM -->
<div class="section">
<h3>Register Booking</h3>

<form method="POST" action="save_booking.php">
<input type="hidden" name="customer_id" value="<?= $customer['customer_id'] ?>">

<select name="asset_id" required>
<option value="">Select Room</option>

<?php
$roomResult = $conn->query("SELECT asset_id, asset_name FROM tbl_assets ORDER BY asset_name");

while ($room = $roomResult->fetch_assoc()) {
    echo "<option value='{$room['asset_id']}'>"
        . htmlspecialchars($room['asset_name']) .
        "</option>";
}
?>
</select>

<input type="date" name="booking_from" required>
<input type="date" name="booking_to" required>
<input type="number" name="no_of_persons" min="1" placeholder="Guests">

<button class="btn">Register Booking</button>
</form>
</div>

<?php endif; ?>

</div>
</div>

<script>
var searchBox = document.getElementById("searchBox");
var suggestions = document.getElementById("suggestions");

/* Trigger while typing (not Enter) */
searchBox.oninput = function() {

    var query = searchBox.value;

    if (query.length < 2) {
        suggestions.innerHTML = "";
        return;
    }

    /* OLD SCHOOL AJAX (works with legacy browsers) */
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "search_customer_ajax.php?q=" + encodeURIComponent(query), true);

    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            suggestions.innerHTML = xhr.responseText;
        }
    };

    xhr.send();
};


/* When user clicks suggestion */
suggestions.onclick = function(e) {

    var target = e.target;

    /* find parent div with class suggestion */
    while (target && !target.classList.contains("suggestion")) {
        target = target.parentNode;
    }

    if (!target) return;

    var id = target.getAttribute("data-id");

    window.location = "booking_details.php?customer_id=" + id;

};
</script>


</body>
</html>
