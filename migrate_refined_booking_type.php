<?php
include 'config.php';
$sql = "ALTER TABLE tbl_invoices MODIFY COLUMN booking_type ENUM('Week Day', 'Week End', 'Concessional', 'Long Stay') DEFAULT 'Week Day'";
if ($conn->query($sql)) {
    echo "Success: Column booking_type updated.";
} else {
    echo "Error: " . $conn->error;
}
?>
