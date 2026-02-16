<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

/* ================= SECURITY CHECK ================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: invoice_payments.php");
    exit();
}

/* ================= GET FORM VALUES ================= */

$invoice_id   = isset($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : 0;
$amount       = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
$payment_mode = isset($_POST['payment_mode']) ? trim($_POST['payment_mode']) : 'Cash';
$reference_no = isset($_POST['reference_no']) ? trim($_POST['reference_no']) : '';

if ($invoice_id <= 0) {
    die("Invoice ID Missing — Form Error");
}

if ($amount <= 0) {
    die("Enter valid amount");
}


/* ================= BANK / UPI HANDLING ================= */

$bank_id = NULL;

if ($payment_mode == 'Bank' || $payment_mode == 'UPI') {

    $bank_name  = isset($_POST['bank_name']) ? trim($_POST['bank_name']) : '';
    $account_no = isset($_POST['account_no']) ? trim($_POST['account_no']) : '';
    $upi_id     = isset($_POST['upi_id']) ? trim($_POST['upi_id']) : '';

    if ($payment_mode == 'Bank' && ($bank_name == '' || $account_no == '')) {
        die("Bank name and Account No required");
    }

    if ($payment_mode == 'UPI' && $upi_id == '') {
        die("UPI ID required");
    }

    /* Insert bank details */
    $stmt = $conn->prepare("
        INSERT INTO tbl_bank_details (bank_name, account_no, upi_id)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("sss", $bank_name, $account_no, $upi_id);

    if (!$stmt->execute()) {
        die("Bank Insert Failed: " . $stmt->error);
    }

    $bank_id = $stmt->insert_id;
    $stmt->close();
}

/* ================= INSERT PAYMENT ================= */
/* IMPORTANT: Always INSERT — never UPDATE
   because we need payment history + SUM() works */

$stmt = $conn->prepare("
    INSERT INTO tbl_payments
    (invoice_id, amount, payment_mode, reference_no, bank_id, payment_date)
    VALUES (?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param(
    "idssi",
    $invoice_id,
    $amount,
    $payment_mode,
    $reference_no,
    $bank_id
);

if (!$stmt->execute()) {
    die("Payment Save Failed: " . $stmt->error);
}

$stmt->close();
$conn->close();

/* ================= SUCCESS ================= */

header("Location: invoice_payments.php?success=1");
exit();
?>
