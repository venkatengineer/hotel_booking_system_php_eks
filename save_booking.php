<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: dashboard.php");
    exit();
}

/* CSRF CHECK */
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Security violation: CSRF token mismatch.");
}

/* ===== GET VALUES SAFELY ===== */
// Room & Date Info
$asset_id       = isset($_POST['asset_id']) ? intval($_POST['asset_id']) : 0;
$booking_from   = isset($_POST['booking_from']) ? $_POST['booking_from'] : '';
$booking_to     = isset($_POST['booking_to']) ? $_POST['booking_to'] : '';
$persons        = isset($_POST['no_of_persons']) ? intval($_POST['no_of_persons']) : 1;

// Customer Info
$full_name         = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$nationality       = isset($_POST['nationality']) ? trim($_POST['nationality']) : '';
$country_of_origin = isset($_POST['country_of_origin']) ? trim($_POST['country_of_origin']) : '';
$identity_no       = isset($_POST['identity_no']) ? trim($_POST['identity_no']) : '';
$phone             = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email             = isset($_POST['email']) ? trim($_POST['email']) : '';
$address           = isset($_POST['address']) ? trim($_POST['address']) : '';

// Invoice Info
$invoice_no     = isset($_POST['invoice_no']) ? trim($_POST['invoice_no']) : '';
$invoice_date   = isset($_POST['invoice_date']) ? $_POST['invoice_date'] : '';
$invoice_amount = isset($_POST['invoice_amount']) ? floatval($_POST['invoice_amount']) : 0.00;

// Basic Validation
if (!$asset_id || !$booking_from || !$booking_to || !$full_name || !$identity_no || !$invoice_no || !$invoice_amount) {
    die("Missing mandatory booking/customer/invoice data.");
}

/* ===== 1. CREATE CUSTOMER RECORD ===== */
// As per requirement: "No Stored Customer concept. Customer info to be obtained in Booking Screen itself."
// We create a new customer entry for every booking to maintain data integrity in reports.
$aadhaar_no = null;
$passport_no = null;

if ($nationality === "Indian") {
    $aadhaar_no = $identity_no;
} else {
    $passport_no = $identity_no;
}

$stmt_cust = $conn->prepare("
    INSERT INTO tbl_customers 
    (full_name, nationality, country_of_origin, aadhaar_no, passport_no, phone, email, address, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
// Note: tbl_customers has a UNIQUE constraint on aadhaar_no in some schemas. 
// If it fails due to existing ID, we catch it or handle it.
$stmt_cust->bind_param("ssssssss", $full_name, $nationality, $country_of_origin, $aadhaar_no, $passport_no, $phone, $email, $address);

if (!$stmt_cust->execute()) {
    // If execution fails, it might be due to unique constraint. 
    // In "No Stored Customer" mode, we might want to just find the existing one if ID matches.
    if ($conn->errno == 1062) { // Duplicate entry
        $stmt_find = $conn->prepare("SELECT customer_id FROM tbl_customers WHERE aadhaar_no=? OR passport_no=?");
        $stmt_find->bind_param("ss", $identity_no, $identity_no);
        $stmt_find->execute();
        $stmt_find->bind_result($customer_id);
        $stmt_find->fetch();
        $stmt_find->close();
    } else {
        die("Error creating customer: " . $conn->error);
    }
} else {
    $customer_id = $conn->insert_id;
}
$stmt_cust->close();


/* ===== 2. CHECK DATE CONFLICT ===== */
$conflict = $conn->prepare("
    SELECT booking_id FROM tbl_bookings
    WHERE asset_id=? AND (booking_from < ? AND booking_to > ?)
");
$conflict->bind_param("iss", $asset_id, $booking_to, $booking_from);
$conflict->execute();
$conflict->store_result();

if ($conflict->num_rows > 0) {
    die("Room already booked for these dates.");
}
$conflict->close();


/* ===== 3. INSERT BOOKING ===== */
$stmt_book = $conn->prepare("
    INSERT INTO tbl_bookings
    (asset_id, customer_id, booking_from, booking_to, no_of_persons, status, created_at)
    VALUES (?, ?, ?, ?, ?, 'Booked', NOW())
");
$stmt_book->bind_param("iissi", $asset_id, $customer_id, $booking_from, $booking_to, $persons);

if (!$stmt_book->execute()) {
    die("Error saving booking: " . $conn->error);
}

$booking_id = $conn->insert_id;
$stmt_book->close();


/* ===== 4. GENERATE INVOICE ===== */
// Manual invoice details provided by user
$subtotal = $invoice_amount;
$tax = 0; // Assuming amount is inclusive as per typical manual entry
$total = $invoice_amount;

$stmt_inv = $conn->prepare("
    INSERT INTO tbl_invoices
    (booking_id, invoice_no, invoice_date, subtotal, tax_amount, total_amount, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt_inv->bind_param("issddd", $booking_id, $invoice_no, $invoice_date, $subtotal, $tax, $total);

if (!$stmt_inv->execute()) {
    die("Error saving invoice: " . $conn->error);
}
$stmt_inv->close();


$conn->close();

header("Location: booking_success.php");
exit();
?>
