<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

/* LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ALLOW ONLY POST */
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: add_customer.php");
    exit();
}

/* GET VALUES SAFELY */
$nationality = isset($_POST['nationality']) ? trim($_POST['nationality']) : '';
$aadhaar_no  = isset($_POST['aadhaar_no']) ? trim($_POST['aadhaar_no']) : '';
$passport_no = isset($_POST['passport_no']) ? trim($_POST['passport_no']) : '';
$full_name   = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$phone       = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email       = isset($_POST['email']) ? trim($_POST['email']) : '';
$address           = isset($_POST['address']) ? trim($_POST['address']) : '';
$country_of_origin = isset($_POST['country_of_origin']) ? trim($_POST['country_of_origin']) : '';

/* ================= VALIDATIONS ================= */

/* Name + Address */
if ($full_name == '' || $address == '') {
    die("Name and Address cannot be blank.");
}

/* Nationality Required */
if ($nationality == '') {
    die("Nationality is required.");
}

/* Aadhaar validation for Indian */
if ($nationality == 'Indian') {

    if (!preg_match('/^[0-9]{12}$/', $aadhaar_no)) {
        die("Aadhaar must be exactly 12 digits.");
    }

    /* Phone must be 10 digits */
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        die("Phone must be exactly 10 digits for Indian customers.");
    }

    $passport_no = NULL;
}

/* Passport required for Foreign */
if ($nationality == 'Foreign') {

    if ($passport_no == '') {
        die("Passport number is required for Foreign customers.");
    }

    $aadhaar_no = NULL;
}

/* Email Validation (if provided) */
if ($email != '') {

    if (!preg_match('/^[A-Za-z0-9._+\-@]+$/', $email)) {
        die("Invalid characters in email.");
    }

    if (substr_count($email, '@') != 1) {
        die("Email must contain only one '@'.");
    }

    if (preg_match('/^[.@]|[.@]$/', $email)) {
        die("Email cannot start or end with '.' or '@'.");
    }

    if (strpos($email, '..') !== false) {
        die("Email cannot contain consecutive periods.");
    }
}

/* ================= DUPLICATE CHECK ================= */

if ($aadhaar_no != NULL) {
    $check = $conn->prepare("SELECT customer_id FROM tbl_customers WHERE aadhaar_no=?");
    $check->bind_param("s", $aadhaar_no);
}
else {
    $check = $conn->prepare("SELECT customer_id FROM tbl_customers WHERE passport_no=?");
    $check->bind_param("s", $passport_no);
}

$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    die("Customer already exists.");
}
$check->close();

/* ================= INSERT CUSTOMER ================= */

$stmt = $conn->prepare("
INSERT INTO tbl_customers
(nationality, aadhaar_no, passport_no, country_of_origin, full_name, phone, email, address, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param(
    "ssssssss",
    $nationality,
    $aadhaar_no,
    $passport_no,
    $country_of_origin,
    $full_name,
    $phone,
    $email,
    $address
);

if (!$stmt->execute()) {
    die("Insert Failed: " . $stmt->error);
}

$stmt->close();
$conn->close();

/* SUCCESS → Redirect to success page */
header("Location: customer_success.php");
exit();

?>
