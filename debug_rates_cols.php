<?php
include 'config.php';
$res = $conn->query("DESCRIBE tbl_rates");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
