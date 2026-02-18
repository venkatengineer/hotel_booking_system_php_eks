<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

/* ---------- FIX FOR EXCEL UTF-8 ---------- */
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=invoice_payments.csv');

/* This BOM makes Excel open UTF-8 correctly */
echo "\xEF\xBB\xBF";

/* SEARCH SUPPORT */
$search = "";
$search_sql = "";

if (!empty($_GET['q'])) {
    $search = mysqli_real_escape_string($conn, $_GET['q']);
    $search_sql = " WHERE 
        c.full_name LIKE '%$search%' OR
        a.asset_name LIKE '%$search%' OR
        b.booking_id LIKE '%$search%' ";
}

/* QUERY (NO LIMIT — EXPORT ALL) */
$sql = "
SELECT 
    b.booking_id,
    c.full_name,
    a.asset_name,
    b.booking_from,
    b.booking_to,
    r.rate_per_day,
    DATEDIFF(b.booking_to,b.booking_from) AS days,
    IFNULL(SUM(p.amount),0) AS paid
FROM tbl_bookings b
JOIN tbl_customers c ON c.customer_id=b.customer_id
JOIN tbl_assets a ON a.asset_id=b.asset_id
LEFT JOIN tbl_rates r ON r.asset_id=b.asset_id AND r.effective_to='2036-12-31'
LEFT JOIN tbl_invoices i ON i.booking_id=b.booking_id
LEFT JOIN tbl_payments p ON p.invoice_id=i.invoice_id
$search_sql
GROUP BY b.booking_id
ORDER BY b.booking_from DESC
";

$result = mysqli_query($conn, $sql);

/* OPEN OUTPUT */
$output = fopen('php://output', 'w');

/* COLUMN HEADERS */
fputcsv($output, array(
    'Booking ID',
    'Customer',
    'Room',
    'Check-In',
    'Check-Out',
    'Rate',
    'Days',
    'Total Due',
    'Paid',
    'Outstanding'
));

/* DATA ROWS */
while ($row = mysqli_fetch_assoc($result)) {

    $rate = (float)$row['rate_per_day'];
    $days = (int)$row['days'];
    $paid = (float)$row['paid'];

    $due = $rate * $days;
    $outstanding = $due - $paid;

    /* Excel-safe date format */
    $checkin  = '="'.date('Y-m-d', strtotime($row['booking_from'])).'"';
    $checkout = '="'.date('Y-m-d', strtotime($row['booking_to'])).'"';

    fputcsv($output, array(
        $row['booking_id'],
        $row['full_name'],
        $row['asset_name'],
        $checkin,
        $checkout,
        number_format($rate, 2, '.', ''),
        $days,
        number_format($due, 2, '.', ''),
        number_format($paid, 2, '.', ''),
        number_format($outstanding, 2, '.', '')
    ));
}

fclose($output);
exit;
?>
