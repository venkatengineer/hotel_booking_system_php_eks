<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], 'admin') !== 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$invoice_id       = (int)$_POST['invoice_id'];
$tarrif_per_day  = (float)$_POST['tarrif_per_day'];
$gross_tariff    = (float)$_POST['gross_tariff'];
$cleaning_charges = (float)$_POST['cleaning_charges'];
$total_tariff     = (float)$_POST['total_tariff'];
$deduction_1      = (float)$_POST['deduction_1'];
$deduction_2      = (float)$_POST['deduction_2'];
$net_amount       = (float)$_POST['net_amount'];

if ($invoice_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Invoice ID']);
    exit();
}

$stmt = $conn->prepare("
    UPDATE tbl_invoices 
    SET tarrif_per_day = ?, 
        gross_tariff = ?, 
        cleaning_charges = ?, 
        total_tariff = ?, 
        deduction_1 = ?, 
        deduction_2 = ?, 
        net_amount = ?,
        total_amount = ?
    WHERE invoice_id = ?
");

// net_amount is what the user sees as Due, so we update total_amount as well
$stmt->bind_param("ddddddddi", 
    $tarrif_per_day, 
    $gross_tariff, 
    $cleaning_charges, 
    $total_tariff, 
    $deduction_1, 
    $deduction_2, 
    $net_amount,
    $net_amount,
    $invoice_id
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
exit();
?>
