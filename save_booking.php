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

/* ===== GET VALUES SAFELY ===== */
$customer_id  = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;
$asset_id     = isset($_POST['asset_id']) ? intval($_POST['asset_id']) : 0;
$booking_from = isset($_POST['booking_from']) ? $_POST['booking_from'] : '';
$booking_to   = isset($_POST['booking_to']) ? $_POST['booking_to'] : '';
$persons      = isset($_POST['no_of_persons']) ? intval($_POST['no_of_persons']) : 1;

if(!$customer_id || !$asset_id || !$booking_from || !$booking_to){
    die("Missing booking data.");
}

/* ===== CHECK RATE EXISTS (Applicable for Check-in Date) ===== */
$rateCheck = $conn->prepare("
SELECT rate_per_day FROM tbl_rates
WHERE asset_id=? AND ? BETWEEN effective_from AND effective_to
ORDER BY updated_at DESC, rate_id DESC
LIMIT 1
");
$rateCheck->bind_param("is",$asset_id, $booking_from);
$rateCheck->execute();
$rateCheck->bind_result($rate_per_day);
$rateCheck->fetch();
$rateCheck->close();

if(!$rate_per_day){
    die("Tariff not defined for this room.");
}

/* ===== CHECK DATE CONFLICT ===== */
$conflict = $conn->prepare("
SELECT booking_id FROM tbl_bookings
WHERE asset_id=? AND (booking_from < ? AND booking_to > ?)
");
$conflict->bind_param("iss",$asset_id,$booking_to,$booking_from);
$conflict->execute();
$conflict->store_result();

if($conflict->num_rows>0){
    die("Room already booked.");
}
$conflict->close();

/* ===== INSERT BOOKING ===== */
$stmt = $conn->prepare("
INSERT INTO tbl_bookings
(asset_id,customer_id,booking_from,booking_to,no_of_persons,status,created_at)
VALUES (?,?,?,?,?,'Booked',NOW())
");
$stmt->bind_param("iissi",$asset_id,$customer_id,$booking_from,$booking_to,$persons);
$stmt->execute();

$booking_id = $conn->insert_id;
$stmt->close();

/* ===== CALCULATE DAYS (Inclusive) ===== */
$from = new DateTime($booking_from);
$to   = new DateTime($booking_to);
$days = $to->diff($from)->days;

/* Minimum 1 day billing safeguard */
if ($days <= 0) {
    $days = 1;
}


$subtotal = $rate_per_day * $days;
$tax = 0;
$total = $subtotal;

/* ===== GENERATE INVOICE ===== */
$invoice_no = "INV".date("Ymd").str_pad($booking_id,4,"0",STR_PAD_LEFT);

$inv = $conn->prepare("
INSERT INTO tbl_invoices
(booking_id,invoice_no,invoice_date,subtotal,tax_amount,total_amount,created_at)
VALUES (?,?,CURDATE(),?,?,?,NOW())
");
$inv->bind_param("isddd",$booking_id,$invoice_no,$subtotal,$tax,$total);
$inv->execute();
$inv->close();

$conn->close();

header("Location: booking_success.php");
exit();
?>
