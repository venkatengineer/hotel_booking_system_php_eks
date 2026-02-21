<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) != 'admin') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: view_rates.php");
    exit();
}

$rate_id      = (int)$_POST['rate_id'];
$effective_to = $_POST['effective_to'];

/* VALIDATION */
$stmt = $conn->prepare("SELECT effective_from FROM tbl_rates WHERE rate_id = ?");
$stmt->bind_param("i", $rate_id);
$stmt->execute();
$stmt->bind_result($effective_from);
$stmt->fetch();
$stmt->close();

if (!$effective_from) {
    die("Rate record not found.");
}

if (strtotime($effective_to) < strtotime($effective_from)) {
    die("End date cannot be before the start date.");
}

/* UPDATE */
$update = $conn->prepare("UPDATE tbl_rates SET effective_to = ? WHERE rate_id = ?");
$update->bind_param("si", $effective_to, $rate_id);
$update->execute();
$update->close();

header("Location: view_rates.php");
exit();
?>
