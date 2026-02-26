<?php
session_start();
include 'config.php';

$allowed_roles = ['admin', 'host', 'admn1', 'admn2'];
$current_role  = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';

if (!isset($_SESSION['user_id']) || !in_array($current_role, $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

$sql = "
SELECT
    b.booking_id,
    a.asset_name,
    c.full_name,
    c.nationality,
    b.booking_from,
    b.booking_to,
    DATEDIFF(b.booking_to,b.booking_from) AS no_of_days,
    IFNULL(i.invoice_no,'NOT GENERATED') AS invoice_no,
    i.booking_type,
    IFNULL(i.total_amount,0) AS invoice_amount,
    IFNULL(pay.paid_amount,0) AS paid_amount,
    IFNULL(i.total_amount,0) - IFNULL(pay.paid_amount,0) AS amount_due,
    IFNULL(i.total_amount,0) AS net_amount,
    pay.reference_no
FROM tbl_bookings b
JOIN tbl_assets a ON a.asset_id = b.asset_id
JOIN tbl_customers c ON c.customer_id = b.customer_id
LEFT JOIN tbl_invoices i ON i.booking_id = b.booking_id
LEFT JOIN (
    SELECT invoice_id,
           SUM(amount) AS paid_amount,
           MAX(reference_no) AS reference_no
    FROM tbl_payments
    GROUP BY invoice_id
) pay ON pay.invoice_id = i.invoice_id
ORDER BY b.booking_id DESC
";

$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Billing Report</title>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

<style>
body {
    background: linear-gradient(135deg,#d8f3dc, #ffd6e8);
    background-attachment: fixed;
    background-size: cover;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #1a3a3a;
    margin: 0;
    padding: 0;
}

.wrapper{
    width:95%;
    margin:30px auto;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(10px);
    padding: 30px;
    border-radius: 30px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.06);
}

h2{ color: #2d6a4f; margin-bottom:25px; font-weight: 700; letter-spacing: -0.5px; }

/* ===== TABLE ===== */
#billingTable {
    width: 100% !important;
    min-width: 1800px;
    border-collapse: separate !important;
    border-spacing: 0 !important;
}

#billingTable.dataTable thead th {
    background: #d8f3dc !important;
    color: #2d6a4f !important;
    padding: 18px 12px !important;
    border-bottom: 2px solid #b9fbc0 !important;
    text-align: center !important;
    font-weight: 700 !important;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1px;
    vertical-align: middle;
}

#billingTable.dataTable tbody td {
    padding: 15px 12px !important;
    border-bottom: 1px solid #eceff1 !important;
    text-align: center !important;
    white-space: nowrap !important;
    color: #37474f;
    font-size: 14px;
    vertical-align: middle;
}

#billingTable.dataTable tbody tr:nth-child(even) { background: #fafafa !important; }
#billingTable.dataTable tbody tr:hover { background: #f1fafa !important; }

/* ===== SEARCH INPUTS ===== */
thead input {
    width: 100%;
    padding: 8px;
    border-radius: 10px;
    border: 1px solid #b9fbc0;
    background: #f1fafa;
    font-size: 11px;
    font-weight: 500;
    box-sizing: border-box;
    text-align: center;
    transition: all 0.3s;
}
thead input:focus {
    outline: none;
    border-color: #b9fbc0;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(185, 251, 192, 0.2);
}

</style>
</head>

<body>
<?php include 'header_nav.php'; ?>

<div class="wrapper">
<h2>Billing Master Report</h2>

<table id="billingTable" class="display nowrap" style="width:100%">

<thead>
<tr>
<th>S.No</th>
<th>Invoice No</th>
<th>Type</th>
<th>Invoice Amount</th>
<th>Category</th>
<th>Name</th>
<th>Nationality</th>
<th>No. Days</th>
<th>Check-In</th>
<th>Check-Out</th>
<th>Amount Due</th>
<th>Net Amount</th>
<th>Reference No</th>
</tr>

<tr>
<th><input type="text" placeholder="SNo"></th>
<th><input type="text" placeholder="Inv#"></th>
<th><input type="text" placeholder="Type"></th>
<th><input type="text" placeholder="Amt"></th>
<th><input type="text" placeholder="Room"></th>
<th><input type="text" placeholder="Name"></th>
<th><input type="text" placeholder="Nat"></th>
<th><input type="text" placeholder="Days"></th>
<th><input type="text" placeholder="In"></th>
<th><input type="text" placeholder="Out"></th>
<th><input type="text" placeholder="Due"></th>
<th><input type="text" placeholder="Net"></th>
<th><input type="text" placeholder="Ref"></th>
</tr>
</thead>

<tbody>
<?php $sn=1; while($row=mysqli_fetch_assoc($result)){ ?>
<tr>
<tr>
<td><?php echo $sn++; ?></td>
<td><?php echo $row['invoice_no']; ?></td>
<td><span class="small" style="text-transform:uppercase; font-weight:700; color:#8e3b63;"><?php echo $row['booking_type'] ?: 'Std/Wknd'; ?></span></td>
<td><?php echo number_format($row['invoice_amount'],2); ?></td>
<td><?php echo $row['asset_name']; ?></td>
<td><?php echo htmlspecialchars($row['full_name']); ?></td>
<td><?php echo htmlspecialchars($row['nationality']); ?></td>
<td><?php echo $row['no_of_days']; ?></td>
<td><?php echo date('d-m-Y',strtotime($row['booking_from'])); ?></td>
<td><?php echo date('d-m-Y',strtotime($row['booking_to'])); ?></td>
<td><?php echo number_format($row['amount_due'],2); ?></td>
<td><?php echo number_format($row['net_amount'],2); ?></td>
<td><?php echo htmlspecialchars($row['reference_no']); ?></td>
</tr>
</tr>
<?php } ?>
</tbody>

</table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>
$(document).ready(function(){

var table = $('#billingTable').DataTable({
    pageLength: 25,
    scrollX: true,
    dom: 'Bfrtip',
    buttons: ['excel', 'csv', 'print'],
    orderCellsTop: true,
    initComplete: function() {
        var api = this.api();

        // 1. Get the real header row that holds the inputs (in scrollHead)
        // because "scrollX": true separates the header from the body.
        var $thead = $('.dataTables_scrollHead thead');

        // 2. Determine which row index contains the inputs (it's the second row, index 1)
        var $inputRow = $thead.find('tr').eq(1);

        // 3. For each column...
        api.columns().every(function(index) {
            var column = this;
            
            // 4. Find the matching input in that row
            var $input = $inputRow.find('th').eq(index).find('input');

            // 5. Bind events
            $input.on('keyup change', function() {
                if (column.search() !== this.value) {
                    column.search(this.value).draw();
                }
            });
        });
    }
});

});
</script>

</body>
</html>
