<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: view_rates.php");
    exit();
}

$rate_id       = (int)$_POST['rate_id'];
$rate_per_day  = (float)$_POST['rate_per_day'];
$effective_from = $_POST['effective_from'];
$effective_to   = $_POST['effective_to'];

/* DATE VALIDATION */
if (strtotime($effective_from) > strtotime($effective_to)) {
    header("Location: view_rates.php?error=date_mismatch");
    exit();
}

/* DELIMITING LOGIC: Close any previous record that overlaps with the new start date */
// First, find the asset_id for this rate_id if not already known (it's not in POST)
$res_asset = $conn->query("SELECT asset_id FROM tbl_rates WHERE rate_id = $rate_id");
$row_asset = $res_asset->fetch_assoc();
$asset_id  = $row_asset['asset_id'];

$yesterday = date('Y-m-d', strtotime($effective_from . ' -1 day'));
$conn->query("
    UPDATE tbl_rates 
    SET effective_to = '$yesterday' 
    WHERE asset_id = $asset_id 
      AND rate_id != $rate_id 
      AND effective_from < '$effective_from' 
      AND effective_to >= '$effective_from'
");

/* UPDATE */
$stmt = $conn->prepare("
    UPDATE tbl_rates 
    SET rate_per_day = ?, effective_from = ?, effective_to = ? 
    WHERE rate_id = ?
");
$stmt->bind_param("dssi", $rate_per_day, $effective_from, $effective_to, $rate_id);

if ($stmt->execute()) {
    header("Location: view_rates.php?success=1");
} else {
    echo "Error updating record: " . $conn->error;
}

$stmt->close();
$conn->close();
exit();
?>
