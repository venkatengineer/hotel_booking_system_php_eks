<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    if (function_exists('random_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $_SESSION['csrf_token'] = md5(uniqid(rand(), true));
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>New Booking</title>
<link rel="stylesheet" href="css/style.css">

<style>
body {
    background: linear-gradient(135deg,#d8f3dc, #ffd6e8);
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    background-attachment: fixed;
}

.wrapper {
    display: flex;
    justify-content: center;
    padding: 20px;
}

.card {
    width: 1000px;
    background: rgba(255,255,255,0.95);
    padding: 30px;
    border-radius: 25px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.1);
}

.section {
    margin-top: 20px;
    padding: 20px;
    background: white;
    border-radius: 20px;
    border: 1px solid #eee;
}

h3 {
    margin-top: 0;
    color: #2d6a4f;
    border-bottom: 2px solid #b9fbc0;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

label {
    font-weight: 600;
    margin-bottom: 8px;
    color: #444;
}

input, select {
    width: 100%;
    padding: 12px 15px;
    border-radius: 15px;
    border: 1px solid #ddd;
    background: #fdfdfd;
    box-sizing: border-box;
}

input:focus, select:focus {
    outline: none;
    border-color: #b9fbc0;
    box-shadow: 0 0 0 3px rgba(185, 251, 192, 0.2);
}

.btn-container {
    margin-top: 30px;
    text-align: center;
}

.btn {
    padding: 15px 50px;
    border-radius: 30px;
    border: none;
    background: linear-gradient(90deg, #8e3b63, #2d6a4f);
    color: white;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.full-width {
    grid-column: span 2;
}
</style>
</head>

<body>
<?php include 'header_nav.php'; ?>

<div class="wrapper">
<div class="card">

<h1>New Booking Registration</h1>

<form method="POST" action="save_booking.php">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<div class="section">
    <h3>Room & Date Information</h3>
    <div class="form-grid">
        <div class="form-group">
            <label>Select Room</label>
            <select name="asset_id" required>
                <option value="">-- Choose Room --</option>
                <?php
                $roomResult = $conn->query("SELECT asset_id, asset_name FROM tbl_assets WHERE status='Active' ORDER BY asset_name");
                while ($room = $roomResult->fetch_assoc()) {
                    echo "<option value='{$room['asset_id']}'>" . htmlspecialchars($room['asset_name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Check-in Date</label>
            <input type="date" name="booking_from" required>
        </div>
        <div class="form-group">
            <label>Check-out Date</label>
            <input type="date" name="booking_to" required>
        </div>
        <div class="form-group">
            <label>No. of Persons</label>
            <input type="number" name="no_of_persons" min="1" value="1" required>
        </div>
    </div>
</div>

<div class="section">
    <h3>Customer Information</h3>
    <div class="form-grid">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="Guest Full Name" required>
        </div>
        <div class="form-group">
            <label>Nationality</label>
            <select name="nationality" id="nationality" onchange="toggleOrigin()" required>
                <option value="Indian">Indian</option>
                <option value="Foreigner">Foreigner</option>
            </select>
        </div>
        <div class="form-group" id="origin_group" style="display:none;">
            <label>Country of Origin</label>
            <input type="text" name="country_of_origin" placeholder="Country" id="country_of_origin">
        </div>
        <div class="form-group">
            <label>Passport / Aadhaar Number</label>
            <input type="text" name="identity_no" placeholder="Passport or Aadhaar No" required>
        </div>
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" placeholder="Contact No">
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Email (Optional)">
        </div>
        <div class="form-group full-width">
            <label>Address</label>
            <input type="text" name="address" placeholder="Full Address">
        </div>
    </div>
</div>

<div class="section">
    <h3>Invoice Information</h3>
    <div class="form-grid">
        <div class="form-group">
            <label>Invoice No.</label>
            <input type="text" name="invoice_no" placeholder="Manual Invoice No." required>
        </div>
        <div class="form-group">
            <label>Invoice Date</label>
            <input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
            <label>Invoice Amount (Inclusive of Tax)</label>
            <input type="number" step="0.01" name="invoice_amount" placeholder="Total Amount" required>
        </div>
    </div>
</div>

<div class="btn-container">
    <button type="submit" class="btn">Register & Generate Invoice</button>
</div>

</form>

</div>
</div>

<script>
function toggleOrigin() {
    var nat = document.getElementById("nationality").value;
    var group = document.getElementById("origin_group");
    var input = document.getElementById("country_of_origin");
    
    if (nat === "Foreigner") {
        group.style.display = "flex";
        input.required = true;
    } else {
        group.style.display = "none";
        input.required = false;
        input.value = "";
    }
}
</script>

</body>
</html>
