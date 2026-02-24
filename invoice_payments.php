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




/* QUERY — load all rows; DataTables handles client-side filtering */

$sql = "
SELECT 
    b.booking_id,
    b.booking_from,
    b.booking_to,
    c.full_name,
    c.nationality,
    a.asset_name,
    GREATEST(DATEDIFF(b.booking_to, b.booking_from), 1) AS no_of_days,
    i.invoice_id,
    i.subtotal,
    i.total_amount AS due_amount,
    IFNULL(pay.paid_amount, 0) AS paid_amount,
    pay.bank_name
FROM tbl_bookings b
JOIN tbl_customers c ON c.customer_id = b.customer_id
JOIN tbl_assets a ON a.asset_id = b.asset_id
LEFT JOIN tbl_invoices i ON i.booking_id = b.booking_id
LEFT JOIN (
    SELECT 
        p.invoice_id, 
        SUM(p.amount) AS paid_amount,
        MAX(bd.bank_name) AS bank_name
    FROM tbl_payments p
    LEFT JOIN tbl_bank_details bd ON bd.bank_id = p.bank_id
    GROUP BY p.invoice_id
) pay ON pay.invoice_id = i.invoice_id
GROUP BY b.booking_id
ORDER BY b.booking_id DESC
";

$result = mysqli_query($conn,$sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Invoice Payments</title>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

<style>
body{
    background: linear-gradient(135deg,#d8f3dc,#ffd6e8);
    background-attachment: fixed;
    background-size: cover;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2d6a4f;
    margin: 0;
    padding: 0;
}

.btn{
    display:inline-block;
    padding:8px 16px;
    margin-right:8px;
    border-radius:20px;
    background:linear-gradient(90deg,#b9fbc0,#ffc6d9);
    color:#2d6a4f;
    text-decoration:none;
    border:none;
    cursor:pointer;
    font-weight:600;
}
.btn:hover{
    transform:translateY(-1px);
    box-shadow:0 6px 12px rgba(0,0,0,0.08);
}

.wrapper{
    width:100%;
    margin:30px auto;
    overflow-x:auto;
    padding:0 20px;
}

.card{
    background: rgba(255, 255, 255, 0.95);
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    display: table; /* Grows with wide table */
    min-width: 100%;
    box-sizing: border-box;
}

h2{ color:#2d6a4f; margin-bottom:20px; }

/* ===== DATATABLES CHROME ===== */
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_length {
    font-family:'Segoe UI',sans-serif;
    color:#2d6a4f;
    margin-bottom:10px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius:10px !important;
    color:#2d6a4f !important;
    background:#f1f7f3 !important;
    border:none !important;
    margin:2px;
    transition:0.2s;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background:#b9fbc0 !important;
    color:#2d6a4f !important;
    border:none !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background:#2d6a4f !important;
    color:white !important;
    border:none !important;
    font-weight:600;
}

/* ===== TABLE ===== */
#payTable {
    width: 100% !important;
    border-collapse: collapse !important;
    margin: 10px 0 !important;
}

#payTable.dataTable thead th {
    background: #b9fbc0 !important;
    color: #2d6a4f !important;
    padding: 15px 10px !important;
    border-bottom: 2px solid #e3f7ea !important;
    text-align: center !important;
    font-weight: 700 !important;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#payTable.dataTable tbody td {
    padding: 12px 10px !important;
    border-bottom: 1px solid #f0f0f0 !important;
    text-align: center !important;
    white-space: nowrap !important;
    color: #444;
    font-size: 14px;
}

#payTable.dataTable tbody tr:nth-child(even) { background: #fff6fa !important; }
#payTable.dataTable tbody tr:hover { background: #eefdf2 !important; }


/* ===== INPUTS ===== */
input, select {
    padding: 6px 8px;
    border-radius: 10px;
    border: 1px solid #e6e6e6;
    background: #fafafa;
    font-size: 13px;
    text-align: center;
    box-sizing: border-box;
}
input:focus, select:focus {
    outline: none;
    border: 1px solid #b9fbc0;
    background: #fff;
}
thead input.col-search {
    width: 100%;
    padding: 5px 6px;
    box-sizing: border-box;
    border-radius: 8px;
    border: 1px solid #cce8d4;
    background: #f0fdf4;
    font-size: 11px;
}

/* ===== PRINT STYLES ===== */
@media print {
    body {
        background: white !important;
        margin: 0;
        padding: 0;
    }
    .wrapper {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        overflow: visible !important;
    }
    .card {
        box-shadow: none !important;
        padding: 0 !important;
        display: block !important;
        width: 100% !important;
    }
    .dataTables_scrollHead, .dt-buttons, .dataTables_filter, .dataTables_length, .dataTables_info, .dataTables_paginate, .col-search, .header-row-2, .btn, .actions, .bankBox, select, button {
        display: none !important;
    }
    .dataTables_scrollBody {
        overflow: visible !important;
        width: 100% !important;
    }
    table.dataTable {
        width: 100% !important;
        min-width: 0 !important;
        table-layout: auto !important;
        border: 1px solid #ddd !important;
    }
    th, td {
        white-space: normal !important;
        font-size: 10px !important;
        padding: 4px !important;
        border: 1px solid #eee !important;
    }
    .no-print { display: none !important; }
}

/* Fix ScrollX alignment */
.dataTables_scrollHeadInner, .dataTables_scrollHeadInner table {
    width: 100% !important;
}


/* ===== BUTTONS ===== */
button {
    background:linear-gradient(90deg,#b9fbc0,#ffc6d9);
    border:none;
    padding:7px 16px;
    border-radius:20px;
    cursor:pointer;
    font-weight:600;
    color:#2d6a4f;
    transition:.25s;
}
button:hover { transform:translateY(-1px); box-shadow:0 6px 12px rgba(0,0,0,0.08); }

/* DataTables export buttons */
.dt-buttons .dt-button {messages
    background:linear-gradient(90deg,#b9fbc0,#ffc6d9) !important;
    color:#2d6a4f !important;
    border:none !important;
    border-radius:20px !important;
    padding:6px 16px !important;
    font-weight:600 !important;
    font-family:'Segoe UI',sans-serif !important;
    box-shadow:none !important;
    margin-right:6px;
    transition:.25s;
}   
.dt-buttons .dt-button:hover {
    transform:translateY(-1px);
    box-shadow:0 6px 12px rgba(0,0,0,0.1) !important;
}

/* ===== AMOUNTS ===== */
.amount    { font-weight:600; color:#2d6a4f; }
.outstanding { color:#c77d9b; font-weight:600; }
.small     { font-size:11px; color:#777; }

/* ===== BANK BOX ===== */
.bankBox {
    display:none;
    margin-top:6px;
}

#payTable {
    width:100% !important;
    min-width:1800px;
}

</style>


<script>
function printPage(){
    window.print();
}
function toggleBank(selectObj)
{
    var bankBox = selectObj.parentNode.querySelector('.bankBox');
    if(selectObj.value === "Cash"){
        bankBox.style.display = "none";
    } else {
        bankBox.style.display = "block";
    }
}
</script>


</head>

<body>
<?php include 'header_nav.php'; ?>

<div class="wrapper">
<div class="card">

<h2>Invoice Payments</h2>

<div style="margin-bottom:10px;">
    <a href="invoice_payments.php" class="btn">↺ Reset Filters</a>
    <button onclick="printPage()" class="btn">🖨 Print</button>
</div>


<table id="payTable" class="display nowrap" style="width:100%">
<thead>
<tr>
<th>Booking</th>
<th>Customer</th>
<th>Room</th>
<th>Days</th>
<th>Check-In</th>
<th>Check-Out</th>
<th>Base</th>
<th>Due</th>
<th>Enter Payment</th>
<th>Mode</th>
<th>Paid</th>
<th>Outstanding</th>
<th>Save</th>
</tr>
<tr class="header-row-2">
<th><input type="text" class="col-search" placeholder="Search"></th>
<th><input type="text" class="col-search" placeholder="Search"></th>
<th><input type="text" class="col-search" placeholder="Search"></th>
<th><input type="text" class="col-search" placeholder="Search"></th>
<th><input type="text" class="col-search" placeholder="Search"></th>
<th><input type="text" class="col-search" placeholder="Search"></th>
<th><input type="text" class="col-search" placeholder="Search"></th>
<th><input type="text" class="col-search" placeholder="Search"></th>
<th class="no-print"></th>
<th class="no-print"></th>
<th><input type="text" class="col-search" placeholder="Search"></th>
<th><input type="text" class="col-search" placeholder="Search"></th>
<th class="no-print"></th>
</tr>
</thead>
<tbody>
<?php while($row=mysqli_fetch_assoc($result)){

$days  = (int)$row['no_of_days'];
$due   = (float)$row['due_amount'];
$sub   = (float)$row['subtotal'];
$base  = $days > 0 ? ($sub / $days) : $sub;
$paid  = (float)$row['paid_amount'];
$out   = $due - $paid;
?>

<tr>

<td><?php echo $row['booking_id']; ?></td>

<td><?php echo htmlspecialchars($row['full_name']); ?></td>



<td><?php echo htmlspecialchars($row['asset_name']); ?></td>

<td><?php echo $days; ?></td>

<td><?php echo date('d-m-Y',strtotime($row['booking_from'])); ?></td>

<td><?php echo date('d-m-Y',strtotime($row['booking_to'])); ?></td>

<td class="amount">₹<?php echo number_format($base,2); ?></td>

<td class="amount">₹<?php echo number_format($due,2); ?></td>

<td>
<input type="number" step="0.01" name="amount" required>
</td>

<td>
<select name="payment_mode" onchange="toggleBank(this)">
<option value="Cash">Cash</option>
<option value="Bank">Bank</option>
<option value="UPI">UPI</option>
</select>

<div class="bankBox">
<input name="bank_name" placeholder="type the bank name here">
<input name="account_no" placeholder="Account Number">
<input name="upi_id" placeholder="UPI ID">
<input name="reference_no" placeholder="Reference No">
</div>
</td>

<td>
₹<?php echo number_format($paid,2); ?><br>
<span class="small"><?php echo htmlspecialchars($row['bank_name']); ?></span>
</td>

<td class="outstanding">₹<?php echo number_format($out,2); ?></td>

<td>
<button type="button" onclick="saveRow(this, <?php echo (int)$row['invoice_id']; ?>)">Save</button>
</td>

</tr>

<?php } ?>


</tbody>
</table>

</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>
$(document).ready(function(){

    var table = $('#payTable').DataTable({
        pageLength: 25,
        scrollX: true,
        autoWidth: false,
        dom: 'Bfrtip',
        buttons: ['excel','csv'],
        order: [[0, 'desc']],
        orderCellsTop: true,
        columnDefs: [
            { width: '80px', targets: 0 },   // Booking
            { width: '250px', targets: 1 },  // Customer
            { width: '150px', targets: 2 },  // Room
            { width: '80px', targets: 3 },   // Days
            { width: '120px', targets: 4 },  // Check-In
            { width: '120px', targets: 5 },  // Check-Out
            { width: '100px', targets: 6 },  // Base
            { width: '120px', targets: 7 },  // Due
            { width: '150px', targets: 8 },  // Enter Payment
            { width: '180px', targets: 9 },  // Mode
            { width: '120px', targets: 10 }, // Paid
            { width: '150px', targets: 11 }, // Outstanding
            { width: '100px', targets: 12 }, // Save
            { orderable: false, searchable: false, targets: [8, 9, 12] }
        ],
        initComplete: function() {
            var api = this.api();
            // DataTables scrollX creates .dataTables_scrollHead
            var $thead = $('.dataTables_scrollHead thead');
            var $inputRow = $thead.find('tr').eq(1);

            api.columns().every(function(index) {
                var column = this;
                var $input = $inputRow.find('th').eq(index).find('input.col-search');
                if($input.length) {
                    $input.on('keyup change', function() {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
                }
            });
            setTimeout(function(){
                table.columns.adjust();
            }, 300);
        }
    });

    // Recalculate column widths when window is resized or drawer toggled
    $(window).on('resize', function() {
        table.columns.adjust();
    });


});

function saveRow(btn, invoice_id) {
    var $tr = $(btn).closest('tr');
    var amount = $tr.find('input[name="amount"]').val();
    var mode = $tr.find('select[name="payment_mode"]').val();
    var bank_name = $tr.find('input[name="bank_name"]').val();
    var account_no = $tr.find('input[name="account_no"]').val();
    var upi_id = $tr.find('input[name="upi_id"]').val();
    var reference_no = $tr.find('input[name="reference_no"]').val();

    if(!amount) {
        alert("Please enter amount");
        return;
    }

    var $form = $('<form method="POST" action="save_daily_payment.php"></form>');
    $form.append('<input type="hidden" name="invoice_id" value="' + invoice_id + '">');
    $form.append('<input type="hidden" name="amount" value="' + amount + '">');
    $form.append('<input type="hidden" name="payment_mode" value="' + mode + '">');
    $form.append('<input type="hidden" name="bank_name" value="' + bank_name + '">');
    $form.append('<input type="hidden" name="account_no" value="' + account_no + '">');
    $form.append('<input type="hidden" name="upi_id" value="' + upi_id + '">');
    $form.append('<input type="hidden" name="reference_no" value="' + reference_no + '">');
    
    $('body').append($form);
    $form.submit();
}
</script>

</body>
</html>
