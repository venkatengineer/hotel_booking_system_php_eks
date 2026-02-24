<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

/* LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ROLE CHECK */
$role = '';
if (isset($_SESSION['role'])) {
    $role = strtolower(trim($_SESSION['role']));
}

if ($role != 'admin') {
    die("Access Denied");
}

/* REQUEST CHECK */
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: assets.php");
    exit();
}

/* GET ID SAFELY */
$asset_id = isset($_POST['asset_id']) ? intval($_POST['asset_id']) : 0;

if ($asset_id <= 0) {
    header("Location: assets.php?error=invalid");
    exit();
}

/* DELETE */
$stmt = $conn->prepare("DELETE FROM tbl_assets WHERE asset_id = ?");
$stmt->bind_param("i", $asset_id);

if (!$stmt->execute()) {
    die("Delete Failed: " . $stmt->error);
}

$stmt->close();
$conn->close();

/* SUCCESS */
header("Location: assets.php?success=deleted");
exit();
?>