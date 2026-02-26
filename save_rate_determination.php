<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: rate_determination.php");
    exit();
}

$asset_id       = (int)$_POST['asset_id'];
$rate_per_day  = (float)$_POST['rate_per_day'];
$rate_weekend  = isset($_POST['rate_weekend']) ? (float)$_POST['rate_weekend'] : 0;
$rate_weekday  = isset($_POST['rate_weekday']) ? (float)$_POST['rate_weekday'] : 0;
$rate_consession = isset($_POST['rate_consession']) ? (float)$_POST['rate_consession'] : 0;
$rate_long_stay  = isset($_POST['rate_long_stay']) ? (float)$_POST['rate_long_stay'] : 0;
$effective_from = $_POST['effective_from'];
$effective_to   = $_POST['effective_to'];

/* DATE VALIDATION */
if (strtotime($effective_from) > strtotime($effective_to)) {
    header("Location: rate_determination.php?error=date_mismatch");
    exit();
}

/* DELIMITING LOGIC: Close any previous record that overlaps with the new start date */
$yesterday = date('Y-m-d', strtotime($effective_from . ' -1 day'));
$conn->query("
    UPDATE tbl_rates 
    SET effective_to = '$yesterday' 
    WHERE asset_id = $asset_id 
      AND effective_from < '$effective_from' 
      AND effective_to >= '$effective_from'
");

/* INSERT */
$stmt = $conn->prepare("
    INSERT INTO tbl_rates (asset_id, rate_per_day, rate_weekend, rate_weekday, rate_consession, rate_long_stay, effective_from, effective_to) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("idddddss", $asset_id, $rate_per_day, $rate_weekend, $rate_weekday, $rate_consession, $rate_long_stay, $effective_from, $effective_to);

if ($stmt->execute()) {
    header("Location: rate_determination.php?success=1");
} else {
    echo "Error inserting record: " . $conn->error;
}

$stmt->close();
$conn->close();
exit();
?>
