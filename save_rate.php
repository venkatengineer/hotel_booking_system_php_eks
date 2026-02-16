<?php
session_start();
include 'config.php';

$asset_id = (int)$_POST['asset_id'];
$new_rate = (float)$_POST['rate_per_day'];
$new_from = $_POST['effective_from'];

/* ================= GET MAX EFFECTIVE_FROM FOR THIS ROOM ================= */
$res = $conn->query("
SELECT MAX(effective_from) AS max_from
FROM tbl_rates
WHERE asset_id = $asset_id
");

$row = $res->fetch_assoc();
$max_from = $row['max_from'];

/* ================= VALIDATION ================= */
/* allow first record OR strictly greater date */
if ($max_from && strtotime($new_from) <= strtotime($max_from)) {
    die("Effective From must be GREATER than last Effective From ($max_from)");
}

/* ================= CLOSE CURRENT ACTIVE RECORD ================= */
$conn->query("
UPDATE tbl_rates
SET effective_to = DATE_SUB('$new_from', INTERVAL 1 DAY)
WHERE asset_id = $asset_id
AND effective_to = '2036-12-31'
");

/* ================= INSERT NEW CURRENT RECORD ================= */
$conn->query("
INSERT INTO tbl_rates (asset_id, rate_per_day, effective_from, effective_to)
VALUES ($asset_id, $new_rate, '$new_from', '2036-12-31')
");

/* ================= REDIRECT ================= */
header("Location: rates.php");
exit();
?>
