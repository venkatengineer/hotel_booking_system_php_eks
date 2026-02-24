<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = strtolower(trim($_SESSION['role']));
if ($role != 'admin') {
    die("Access Denied");
}

$asset_id = (int)$_POST['asset_id'];
$margin   = (float)$_POST['margin_percent'];
$status   = $_POST['status'];

$stmt = $conn->prepare("
UPDATE tbl_assets
SET margin_percent=?, status=?
WHERE asset_id=?
");

$stmt->bind_param("dsi", $margin, $status, $asset_id);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: assets.php?success=1");
exit();
?>
