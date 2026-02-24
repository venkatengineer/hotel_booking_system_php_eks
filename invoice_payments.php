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
    i.tarrif_per_day,
    i.gross_tariff,
    i.cleaning_charges,
    i.total_tariff,
    i.deduction_1,
    i.deduction_2,
    i.net_amount,
    r.rate_per_day AS db_rate_std,
    r.rate_weekend AS db_rate_wknd,
    r.rate_weekday AS db_rate_wkdy,
    r.rate_consession AS db_rate_cons,
    r.rate_long_stay AS db_rate_long,
    IFNULL(pay.paid_amount, 0) AS paid_amount,
    pay.bank_name
FROM tbl_bookings b
JOIN tbl_customers c ON c.customer_id = b.customer_id
JOIN tbl_assets a ON a.asset_id = b.asset_id
LEFT JOIN tbl_invoices i ON i.booking_id = b.booking_id
LEFT JOIN tbl_rates r ON r.asset_id = b.asset_id 
    AND b.booking_from BETWEEN r.effective_from AND r.effective_to
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
    min-width:2400px;
}

</style>


<script>
function recalcInvoice(input) {
    let $tr = $(input).closest('tr');
    let days = parseFloat($tr.find('[name="days"]').val()) || 1;
    let base = parseFloat($tr.find('[name="base_tarrif_selector"]').val()) || 0;
    
    // Update the hidden actual tariff field
    $tr.find('[name="base_tarrif"]').val(base);

    // Gross calculation
    let gross = base * days;
    $tr.find('.gross-display').text('₹' + gross.toFixed(2));
    $tr.find('[name="gross_tariff"]').val(gross);

    // If the input was the dropdown, also update total_tariff as a default
    if ($(input).attr('name') === 'base_tarrif_selector') {
        $tr.find('[name="total_tariff"]').val(gross.toFixed(2));
    }

    let cleaning = parseFloat($tr.find('[name="cleaning_charges"]').val()) || 0;
    let totalTariff = parseFloat($tr.find('[name="total_tariff"]').val()) || 0;
    let ded1 = parseFloat($tr.find('[name="deduction_1"]').val()) || 0;
    let ded2 = parseFloat($tr.find('[name="deduction_2"]').val()) || 0;
    let paid = parseFloat($tr.find('[data-paid]').data('paid')) || 0;
    let pendingPay = parseFloat($tr.find('[name="amount"]').val()) || 0;

    // Net Amount Calculation
    let preDedTotal = totalTariff + cleaning;
    let netAmt = preDedTotal * (1 - (ded1 + ded2) / 100);
    
    $tr.find('.net-display').text('₹' + netAmt.toFixed(2));
    $tr.find('[name="net_amount"]').val(netAmt.toFixed(2));
    $tr.find('.due-display').text('₹' + netAmt.toFixed(2));
    $tr.find('[name="due_amount"]').val(netAmt.toFixed(2));

    // Outstanding Calculation
    let totalPaid = paid + pendingPay;
    let out = netAmt - totalPaid;
    $tr.find('.outstanding-display').text('₹' + out.toFixed(2));
}

function updateInvoice(btn, invoice_id) {
    let $tr = $(btn).closest('tr');
    let data = {
        invoice_id: invoice_id,
        tarrif_per_day: $tr.find('[name="base_tarrif"]').val(),
        gross_tariff: $tr.find('[name="gross_tariff"]').val(),
        cleaning_charges: $tr.find('[name="cleaning_charges"]').val(),
        total_tariff: $tr.find('[name="total_tariff"]').val(),
        deduction_1: $tr.find('[name="deduction_1"]').val(),
        deduction_2: $tr.find('[name="deduction_2"]').val(),
        net_amount: $tr.find('[name="net_amount"]').val()
    };

    $.post('update_invoice_breakdown.php', data, function(response) {
        if (response.success) {
            alert('Invoice updated successfully! ✨');
            // Flash row green
            $tr.css('background-color', '#d1fae5');
            setTimeout(() => $tr.css('background-color', ''), 1000);
        } else {
            alert('Error updating invoice: ' + (response.message || 'Unknown error'));
        }
    }, 'json');
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
    <button onclick="window.print()" class="btn">🖨 Print</button>
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
<th>Gross</th>
<th>Cleaning</th>
<th>Total Tariff</th>
<th>Ded 1 (%)</th>
<th>Ded 2 (%)</th>
<th>Net Amt</th>
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
$base  = (float)$row['tarrif_per_day'];
if ($base <= 0) {
    $base = $days > 0 ? ($sub / $days) : $sub;
}
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

<input type="hidden" name="days" value="<?php echo $days; ?>">
<input type="hidden" name="base_tarrif" value="<?php echo $base; ?>">
<input type="hidden" name="gross_tariff" value="<?php echo $row['gross_tariff']; ?>">
<input type="hidden" name="net_amount" value="<?php echo $row['net_amount']; ?>">
<input type="hidden" name="due_amount" value="<?php echo $due; ?>">

<td>
    <select name="base_tarrif_selector" onchange="recalcInvoice(this)" style="width: 110px; font-size: 11px;">
        <option value="<?php echo (float)$row['db_rate_std']; ?>" <?php if((float)$base == (float)$row['db_rate_std']) echo 'selected'; ?>>Standard: ₹<?php echo number_format((float)$row['db_rate_std'],0); ?></option>
        <?php if((float)$row['db_rate_wknd'] > 0): ?>
            <option value="<?php echo (float)$row['db_rate_wknd']; ?>" <?php if((float)$base == (float)$row['db_rate_wknd']) echo 'selected'; ?>>Weekend: ₹<?php echo number_format((float)$row['db_rate_wknd'],0); ?></option>
        <?php endif; ?>
        <?php if((float)$row['db_rate_wkdy'] > 0): ?>
            <option value="<?php echo (float)$row['db_rate_wkdy']; ?>" <?php if((float)$base == (float)$row['db_rate_wkdy']) echo 'selected'; ?>>Weekday: ₹<?php echo number_format((float)$row['db_rate_wkdy'],0); ?></option>
        <?php endif; ?>
        <?php if((float)$row['db_rate_cons'] > 0): ?>
            <option value="<?php echo (float)$row['db_rate_cons']; ?>" <?php if((float)$base == (float)$row['db_rate_cons']) echo 'selected'; ?>>Concession: ₹<?php echo number_format((float)$row['db_rate_cons'],0); ?></option>
        <?php endif; ?>
        <?php if((float)$row['db_rate_long'] > 0): ?>
            <option value="<?php echo (float)$row['db_rate_long']; ?>" <?php if((float)$base == (float)$row['db_rate_long']) echo 'selected'; ?>>Long Stay: ₹<?php echo number_format((float)$row['db_rate_long'],0); ?></option>
        <?php endif; ?>
    </select>
</td>

<td class="amount gross-display">₹<?php echo number_format((float)$row['gross_tariff'],2); ?></td>

<td>
    <input type="number" step="0.01" name="cleaning_charges" value="<?php echo (float)$row['cleaning_charges']; ?>" oninput="recalcInvoice(this)" style="width: 70px;">
</td>

<td>
    <input type="number" step="0.01" name="total_tariff" value="<?php echo (float)$row['total_tariff']; ?>" oninput="recalcInvoice(this)" style="width: 70px;">
</td>

<td>
    <input type="number" step="0.01" name="deduction_1" value="<?php echo (float)$row['deduction_1']; ?>" oninput="recalcInvoice(this)" placeholder="%" style="width: 60px;">
</td>

<td>
    <input type="number" step="0.01" name="deduction_2" value="<?php echo (float)$row['deduction_2']; ?>" oninput="recalcInvoice(this)" placeholder="%" style="width: 60px;">
</td>

<td class="amount net-display">₹<?php echo number_format((float)$row['net_amount'],2); ?></td>

<td class="amount due-display">₹<?php echo number_format($due,2); ?></td>

<td>
<input type="number" step="0.01" name="amount" required oninput="recalcInvoice(this)">
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
<span data-paid="<?php echo $paid; ?>" style="display:none;"></span>
</td>

<td class="outstanding outstanding-display">₹<?php echo number_format($out,2); ?></td>

<td>
<button type="button" onclick="updateInvoice(this, <?php echo (int)$row['invoice_id']; ?>)" style="margin-bottom: 5px; font-size: 11px; padding: 4px 8px;">Save Inv</button><br>
<button type="button" onclick="saveRow(this, <?php echo (int)$row['invoice_id']; ?>)" style="background: #2d6a4f; color: #fff; font-size: 11px; padding: 4px 8px;">Save Pay</button>
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
            { width: '220px', targets: 1 },  // Customer
            { width: '150px', targets: 2 },  // Room
            { width: '60px', targets: 3 },   // Days
            { width: '100px', targets: 4 },  // Check-In
            { width: '100px', targets: 5 },  // Check-Out
            { width: '80px', targets: 6 },   // Base
            { width: '80px', targets: 7 },   // Gross
            { width: '80px', targets: 8 },   // Cleaning
            { width: '100px', targets: 9 },  // Total Tariff
            { width: '80px', targets: 10 },  // Ded 1
            { width: '80px', targets: 11 },  // Ded 2
            { width: '100px', targets: 12 }, // Net Amt
            { width: '100px', targets: 13 }, // Due
            { width: '140px', targets: 14 }, // Enter Payment
            { width: '160px', targets: 15 }, // Mode
            { width: '100px', targets: 16 }, // Paid
            { width: '120px', targets: 17 }, // Outstanding
            { width: '80px', targets: 18 },  // Save
            { orderable: false, searchable: false, targets: [14, 15, 18] }
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
