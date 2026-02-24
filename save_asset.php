<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ================= ROLE CHECK (SAFE FOR PHP 5) ================= */
$role = '';
if (isset($_SESSION['role'])) {
    $role = strtolower(trim($_SESSION['role']));
}

if ($role != 'admin') {
    die("Access Denied");
}

/* ================= REQUEST CHECK ================= */
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: assets.php");
    exit();
}

/* ================= GET VALUES SAFELY ================= */
$asset_code  = isset($_POST['asset_code'])  ? trim($_POST['asset_code'])  : '';
$asset_name  = isset($_POST['asset_name'])  ? trim($_POST['asset_name'])  : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if ($asset_code == '' || $asset_name == '' || $description == '') {
    header("Location: assets.php?error=empty");
    exit();
}

/* ================= DUPLICATE CHECK ================= */
$check = $conn->prepare("SELECT asset_id FROM tbl_assets WHERE asset_code = ?");
$check->bind_param("s", $asset_code);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    header("Location: assets.php?error=duplicate");
    exit();
}
$check->close();

/* ================= INSERT ================= */
$insert = $conn->prepare("
INSERT INTO tbl_assets
(asset_code, asset_name, description, is_active, created_at, updated_at)
VALUES (?, ?, ?, 1, NOW(), NOW())
");

$insert->bind_param("sss", $asset_code, $asset_name, $description);

if (!$insert->execute()) {
    die("Database Error: " . $insert->error);
}

$insert->close();
$conn->close();

/* ================= SUCCESS REDIRECT ================= */
header("Location: assets.php?success=created");
exit();
?>
