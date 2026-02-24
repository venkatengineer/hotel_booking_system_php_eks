<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (strcasecmp($_SESSION['role'], 'admin') !== 0) {
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
body{
background:linear-gradient(135deg,#d8f3dc,#ffd6e8);
font-family:'Segoe UI',sans-serif;
}

.wrapper{
width:95%;
margin:30px auto;
background:#fff;
padding:25px;
border-radius:25px;
box-shadow:0 20px 40px rgba(0,0,0,.1);
}

h2{ color:#2d6a4f; }

table.dataTable tbody td{
white-space:nowrap;
}

thead input{
width:100%;
padding:4px;
box-sizing:border-box;
}

#billingTable {
    width: 100% !important;
    min-width: 1800px;
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
<th><input type="text" placeholder="Search"></th>
<th><input type="text" placeholder="Search"></th>
<th><input type="text" placeholder="Search"></th>
<th><input type="text" placeholder="Search"></th>
<th><input type="text" placeholder="Search"></th>
<th><input type="text" placeholder="Search"></th>
<th><input type="text" placeholder="Search"></th>
<th><input type="text" placeholder="Search"></th>
<th><input type="text" placeholder="Search"></th>
<th><input type="text" placeholder="Search"></th>
<th><input type="text" placeholder="Search"></th>
<th><input type="text" placeholder="Search"></th>
</tr>
</thead>

<tbody>
<?php $sn=1; while($row=mysqli_fetch_assoc($result)){ ?>
<tr>
<td><?php echo $sn++; ?></td>
<td><?php echo $row['invoice_no']; ?></td>
<td><?php echo number_format($row['invoice_amount'],2); ?></td>
<td><?php echo $row['asset_name']; ?></td>
<td><?php echo $row['full_name']; ?></td>
<td><?php echo $row['nationality']; ?></td>
<td><?php echo $row['no_of_days']; ?></td>
<td><?php echo date('d-m-Y',strtotime($row['booking_from'])); ?></td>
<td><?php echo date('d-m-Y',strtotime($row['booking_to'])); ?></td>
<td><?php echo number_format($row['amount_due'],2); ?></td>
<td><?php echo number_format($row['net_amount'],2); ?></td>
<td><?php echo $row['reference_no']; ?></td>
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
