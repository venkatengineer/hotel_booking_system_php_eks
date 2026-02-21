<?php
session_start();
include 'config.php';

$id=$_POST['rate_id'];

$conn->query("
UPDATE tbl_rates
SET effective_to = CURDATE()
WHERE rate_id=$id
");

header("Location: view_rates.php");
