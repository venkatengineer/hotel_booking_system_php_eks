<?php
include 'config.php';
echo "Current System Date (PHP): " . date('Y-m-d') . "\n";
$res = $conn->query("SELECT CURDATE() as db_date");
$row = $res->fetch_assoc();
echo "Current DB Date (MySQL): " . $row['db_date'] . "\n\n";

echo "tbl_assets:\n";
$assets = $conn->query("SELECT * FROM tbl_assets");
while($a = $assets->fetch_assoc()) {
    print_r($a);
}

echo "\ntbl_rates:\n";
$rates = $conn->query("SELECT r.*, a.asset_name FROM tbl_rates r JOIN tbl_assets a ON a.asset_id = r.asset_id ORDER BY a.asset_name, r.effective_from");
while($r = $rates->fetch_assoc()) {
    print_r($r);
}
?>
