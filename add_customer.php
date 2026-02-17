<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Add Customer</title>
<link rel="stylesheet" href="css/style.css">

<style>
body {
    background: linear-gradient(135deg, #d8f3dc, #ffd6e8);
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
}

.form-wrapper {
    width: 100%;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.form-card {
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(15px);
    padding: 45px;
    width: 600px;
    border-radius: 30px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.1);
}

.form-card h1 {
    text-align: center;
    color: #2d6a4f;
    margin-bottom: 20px;
}

input, select {
    width: 100%;
    padding: 14px;
    margin-bottom: 18px;
    border-radius: 40px;
    border: none;
    outline: none;
    background: white;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
}

.premium-btn {
    width: 100%;
    padding: 14px;
    border-radius: 40px;
    border: none;
    font-weight: 600;
    background: linear-gradient(90deg, #b9fbc0, #ffc6d9);
    color: #2d6a4f;
    cursor: pointer;
}
</style>
</head>

<body>

<?php include 'header_nav.php'; ?>

<div class="form-wrapper">
<div class="form-card">

<h1>Add Customer 👤</h1>

<form method="POST" action="save_customer.php" id="customerForm">

<select name="nationality" id="nationalitySelect" required>
    <option value="">Select Nationality</option>
    <option value="Indian">Indian</option>
    <option value="Foreign">Foreign</option>
</select>

<input type="text" name="aadhaar_no" id="aadhaarField"
       placeholder="Aadhaar Number (12 digits)"
       maxlength="12"
       style="display:none;">

<input type="text" name="passport_no" id="passportField"
       placeholder="Passport Number"
       style="display:none;">

<input type="text" name="full_name" id="full_name"
       placeholder="Full Name" required>

<input type="text" name="phone" id="phone"
       placeholder="Phone Number" required>

<input type="text" name="email" id="email"
       placeholder="Email Address">

<input type="text" name="address" id="address"
       placeholder="Address" required>

<button type="submit" class="premium-btn">Save Customer</button>

</form>

</div>
</div>

<script>
var nationality = document.getElementById("nationalitySelect");
var aadhaar = document.getElementById("aadhaarField");
var passport = document.getElementById("passportField");

/* Function to control visibility */
function toggleIdFields() {

    if(nationality.value === "Indian"){
        aadhaar.style.display = "block";
        aadhaar.required = true;

        passport.style.display = "none";
        passport.required = false;
    }
    else if(nationality.value === "Foreign"){
        passport.style.display = "block";
        passport.required = true;

        aadhaar.style.display = "none";
        aadhaar.required = false;
    }
    else{
        aadhaar.style.display = "none";
        passport.style.display = "none";
    }
}

/* Run when dropdown changes */
nationality.onchange = toggleIdFields;

/* ✅ Run once when page loads (THIS FIXES YOUR ISSUE) */
/* I */
window.onload = function(){
    toggleIdFields();
};
</script>


</body>
</html>
