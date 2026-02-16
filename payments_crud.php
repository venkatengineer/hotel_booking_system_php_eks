<?php
session_start();
include 'config.php';

$action = $_POST['action'];

$invoice_id   = $_POST['invoice_id'];
$mode         = $_POST['payment_mode'];
$amount       = $_POST['amount'];
$reference    = $_POST['reference_no'];
$date         = $_POST['payment_date'];

if($action=="insert"){

mysqli_query($conn,"
INSERT INTO tbl_payments
(invoice_id,payment_mode,amount,reference_no,payment_date)
VALUES
('$invoice_id','$mode','$amount','$reference','$date')
");

}

if($action=="update"){

$payment_id = $_POST['payment_id'];

mysqli_query($conn,"
UPDATE tbl_payments SET
payment_mode='$mode',
amount='$amount',
reference_no='$reference',
payment_date='$date'
WHERE payment_id='$payment_id'
");

}

if($action=="delete"){

$payment_id = $_POST['payment_id'];

mysqli_query($conn,"DELETE FROM tbl_payments WHERE payment_id='$payment_id'");
}

header("Location: invoice_payments.php");
exit();
?>
