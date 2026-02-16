<?php
include 'config.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q == '') {
    exit();
}

$q = mysqli_real_escape_string($conn,$q);

$sql = "
SELECT 
    b.booking_id,
    c.full_name,
    a.asset_name,
    IFNULL(r.rate_per_day,0) AS rate_per_day,
    i.invoice_id,
    IFNULL(SUM(p.amount),0) AS paid_amount

FROM tbl_bookings b
JOIN tbl_customers c ON c.customer_id=b.customer_id
JOIN tbl_assets a ON a.asset_id=b.asset_id

LEFT JOIN tbl_rates r 
ON r.asset_id=b.asset_id
AND CURDATE() BETWEEN r.effective_from AND r.effective_to

LEFT JOIN tbl_invoices i ON i.booking_id=b.booking_id
LEFT JOIN tbl_payments p ON p.invoice_id=i.invoice_id

WHERE 
c.full_name LIKE '%$q%' OR
a.asset_name LIKE '%$q%' OR
b.booking_id LIKE '%$q%' OR
i.invoice_id LIKE '%$q%'

GROUP BY b.booking_id
ORDER BY c.full_name
";

$result=mysqli_query($conn,$sql);

while($row=mysqli_fetch_assoc($result)){

$base  = (float)$row['rate_per_day'];
$final = $base-($base*0.03);
$paid  = (float)$row['paid_amount'];
$due   = $final-$paid;

echo "<tr>";
echo "<td>".$row['full_name']."</td>";
echo "<td>".$row['asset_name']."</td>";
echo "<td>".number_format($base,2)."</td>";
echo "<td>".number_format($final,2)."</td>";
echo "<td>".number_format($paid,2)."</td>";
echo "<td>".number_format($due,2)."</td>";
echo "</tr>";
}
?>
