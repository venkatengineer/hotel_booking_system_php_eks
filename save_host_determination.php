<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

/* ================= SECURITY CHECK ================= */
$allowed_roles = ['admin', 'host', 'admn1', 'admn2'];
$current_role  = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';

if (!isset($_SESSION['user_id']) || !in_array($current_role, $allowed_roles)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized Access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
    exit();
}

/* ================= GET VALUES ================= */
$invoice_id       = (int)$_POST['invoice_id'];
$booking_type     = mysqli_real_escape_string($conn, $_POST['booking_type']);
$tarrif_per_day   = (float)$_POST['tarrif_per_day'];
$gross_tariff     = (float)$_POST['gross_tariff'];
$cleaning_charges = (float)$_POST['cleaning_charges'];
$total_tariff     = (float)$_POST['total_tariff'];
$deduction_1      = (float)$_POST['deduction_1'];
$deduction_2      = (float)$_POST['deduction_2'];
$net_amount       = (float)$_POST['net_amount'];

// Payment data
$payment_amount   = (float)$_POST['payment_amount'];
$payment_mode     = mysqli_real_escape_string($conn, $_POST['payment_mode']);
$bank_name        = mysqli_real_escape_string($conn, $_POST['bank_name']);
$account_no       = mysqli_real_escape_string($conn, $_POST['account_no']);
$upi_id           = mysqli_real_escape_string($conn, $_POST['upi_id']);
$reference_no     = mysqli_real_escape_string($conn, $_POST['reference_no']);

/* ================= UPDATE INVOICE ================= */
$sql_update = "
    UPDATE tbl_invoices 
    SET 
        booking_type = ?,
        tarrif_per_day = ?, 
        gross_tariff = ?, 
        cleaning_charges = ?, 
        total_tariff = ?, 
        deduction_1 = ?, 
        deduction_2 = ?, 
        net_amount = ? 
    WHERE invoice_id = ?
";

$stmt = $conn->prepare($sql_update);
$stmt->bind_param("sdddddddi", 
    $booking_type,
    $tarrif_per_day, 
    $gross_tariff, 
    $cleaning_charges, 
    $total_tariff, 
    $deduction_1, 
    $deduction_2, 
    $net_amount, 
    $invoice_id
);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Invoice Update Failed: ' . $stmt->error]);
    exit();
}
$stmt->close();

/* ================= HANDLE PAYMENT ================= */
if ($payment_amount > 0) {
    $bank_id = NULL;

    if ($payment_mode == 'Bank' || $payment_mode == 'UPI') {
        /* Insert bank details */
        $stmt_bank = $conn->prepare("
            INSERT INTO tbl_bank_details (bank_name, account_no, upi_id)
            VALUES (?, ?, ?)
        ");
        $stmt_bank->bind_param("sss", $bank_name, $account_no, $upi_id);
        if ($stmt_bank->execute()) {
            $bank_id = $stmt_bank->insert_id;
        }
        $stmt_bank->close();
    }

    $stmt_pay = $conn->prepare("
        INSERT INTO tbl_payments
        (invoice_id, amount, payment_mode, reference_no, bank_id, payment_date)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt_pay->bind_param("idssi", $invoice_id, $payment_amount, $payment_mode, $reference_no, $bank_id);
    
    if (!$stmt_pay->execute()) {
        echo json_encode(['success' => false, 'message' => 'Payment Save Failed: ' . $stmt_pay->error]);
        exit();
    }
    $stmt_pay->close();
}

echo json_encode(['success' => true]);
$conn->close();
?>
