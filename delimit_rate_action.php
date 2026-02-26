<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], 'admin') !== 0) {
    header("Location: dashboard.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Missing ID");
}

$id = (int)$_GET['id'];

// Setting effective_to to yesterday so it stops being active today
$yesterday = date('Y-m-d', strtotime('yesterday'));

$stmt = $conn->prepare("
    UPDATE tbl_rates
    SET effective_to = ?
    WHERE rate_id = ?
");
$stmt->bind_param("si", $yesterday, $id);
$stmt->execute();
$stmt->close();

header("Location: view_rates.php");
exit();
?>
