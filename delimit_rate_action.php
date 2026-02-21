<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    die("Missing ID");
}

$id = (int)$_GET['id'];

// Setting effective_to to yesterday so it stops being active today
$yesterday = date('Y-m-d', strtotime('yesterday'));

$conn->query("
    UPDATE tbl_rates
    SET effective_to = '$yesterday'
    WHERE rate_id = $id
");

header("Location: view_rates.php");
exit();
?>
