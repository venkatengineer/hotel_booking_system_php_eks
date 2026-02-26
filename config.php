<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "db_eks";
/* N */

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Auto-fix missing column that causes invoice_payments.php to fail
@$conn->query("ALTER TABLE tbl_invoices ADD COLUMN booking_type VARCHAR(50) DEFAULT 'Weekend/Weekday'");
?>
