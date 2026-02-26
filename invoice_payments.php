<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$allowed_roles = ['admin', 'host', 'admn1', 'admn2'];
$current_role  = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';

if (!isset($_SESSION['user_id']) || !in_array($current_role, $allowed_roles)) {
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
    i.booking_type,
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
    background: linear-gradient(135deg,#d8f3dc, #ffd6e8);
    background-attachment: fixed;
    background-size: cover;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #1a3a3a;
    margin: 0;
    padding: 0;
}

.btn{
    display:inline-block;
    padding:10px 20px;
    margin-right:8px;
    border-radius:25px;
    background: linear-gradient(90deg, #b9fbc0, #ffc6d9);
    color: #2d6a4f;
    text-decoration:none;
    border:none;
    cursor:pointer;
    font-weight:600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}
.btn:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 15px rgba(0,0,0,0.1);
    background: linear-gradient(90deg, #ffc6d9, #b9fbc0);
}

.wrapper{
    width:100%;
    margin:20px auto;
    overflow-x:auto;
    padding:0 20px;
}

.card{
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(10px);
    padding: 30px;
    border-radius: 30px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.06);
    display: table;
    min-width: 100%;
    box-sizing: border-box;
}

h2{ color: #2d6a4f; margin-bottom:25px; font-weight: 700; letter-spacing: -0.5px; }

/* ===== DATATABLES CHROME ===== */
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_length {
    font-family:'Segoe UI',sans-serif;
    color: #455a64;
    margin-bottom:15px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius:12px !important;
    color: #455a64 !important;
    background: #f5f5f5 !important;
    border: none !important;
    margin: 3px;
    transition: 0.3s;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #b9fbc0 !important;
    color: #2d6a4f !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #2d6a4f !important;
    color: white !important;
}

/* ===== TABLE ===== */
#payTable {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    margin: 15px 0 !important;
}

#payTable.dataTable thead th {
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

#payTable.dataTable tbody td {
    padding: 15px 12px !important;
    border-bottom: 1px solid #eceff1 !important;
    text-align: center !important;
    white-space: nowrap !important;
    color: #37474f;
    font-size: 14px;
    vertical-align: middle; /* CRITICAL FOR ALIGNMENT */
}

#payTable.dataTable tbody tr:nth-child(even) { background: #fafafa !important; }
#payTable.dataTable tbody tr:hover { background: #f1fafa !important; }

/* ===== INPUTS & SELECTS ===== */
input, select {
    padding: 8px 12px;
    border-radius: 12px;
    border: 1px solid #cfd8dc;
    background: #fff;
    font-size: 13px;
    text-align: center;
    box-sizing: border-box;
    transition: border-color 0.3s, box-shadow 0.3s;
}
input:focus, select:focus {
    outline: none;
    border-color: #b9fbc0;
    box-shadow: 0 0 0 3px rgba(185, 251, 192, 0.2);
}

thead input.col-search {
    width: 100%;
    padding: 8px;
    border-radius: 10px;
    border: 1px solid #b9fbc0;
    background: #f1fafa;
    font-size: 11px;
    font-weight: 500;
}

/* ===== BANK BOX UI ===== */
.bankBox {
    display: none;
    margin-top: 10px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 15px;
    border: 1px solid #e0e0e0;
    text-align: left;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
}
.bankBox input {
    margin-bottom: 6px;
    width: 100%;
    text-align: left;
    background: #fff;
    padding: 6px 10px;
    border-radius: 8px;
}

/* ===== AMOUNTS ===== */
.amount { font-weight:700; color: #2d6a4f; }
.outstanding { color: #d81b60; font-weight:700; }
.small { font-size:11px; color: #78909c; display: block; margin-top: 4px; }

#payTable {
    width:100% !important;
    min-width:2600px;
}

/* ROW ACTIONS */
.action-cell {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.save-btn-all {
    background: linear-gradient(135deg, #43a047, #2e7d32);
    color: white;
    font-size: 11px;
    padding: 8px 12px;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    font-weight: 700;
    transition: 0.3s;
}
.save-btn-all:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(46, 125, 50, 0.3);
}

.bankBox {
    position: absolute;
    z-index: 1000;
    background: #fff;
    padding: 15px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border: 1px solid #eee;
    width: 200px;
    display: none;
    text-align: left;
}

</style>


<script>
function recalcInvoice(input) {
    let $tr = $(input).closest('tr');
    let days = parseFloat($tr.find('[name="days"]').val()) || 1;
    let type = $tr.find('[name="booking_type"]').val();
    let $selector = $tr.find('[name="base_tarrif_selector"]');
    let $manualInput = $tr.find('[name="manual_base_rate"]');
    
    let base = 0;
    if (type === 'Week Day' || type === 'Week End') {
        $selector.show();
        $manualInput.hide();
        
        // Nudge logic: if Type is Week End, try to find "Wknd" in selector
        if (type === 'Week End') {
             let wkndVal = $selector.find('option:contains("Wknd")').val();
             if (wkndVal) $selector.val(wkndVal);
        } else if (type === 'Week Day') {
             let wkdyVal = $selector.find('option:contains("Wkdy")').val();
             if (wkdyVal) $selector.val(wkdyVal);
        }

        base = parseFloat($selector.val()) || 0;
    } else {
        $selector.hide();
        $manualInput.show();
        base = parseFloat($manualInput.val()) || 0;
    }
    
    // Update the hidden actual tariff field
    $tr.find('[name="base_tarrif"]').val(base);

    // Gross calculation
    let gross = base * days;
    $tr.find('.gross-display').text('₹' + gross.toFixed(2));
    $tr.find('[name="gross_tariff"]').val(gross);

    let cleaning = parseFloat($tr.find('[name="cleaning_charges"]').val()) || 0;
    let totalTariff = parseFloat($tr.find('[name="total_tariff"]').val()) || 0;
    
    // Auto-update total tariff if it matches gross (default behavior)
    if (totalTariff === 0 || totalTariff === (base * days)) {
        totalTariff = gross;
        $tr.find('[name="total_tariff"]').val(totalTariff.toFixed(2));
    }

    let ded1 = parseFloat($tr.find('[name="deduction_1"]').val()) || 0;
    let ded2 = parseFloat($tr.find('[name="deduction_2"]').val()) || 0.1; // Default tax 0.1%
    $tr.find('[name="deduction_2"]').val(ded2.toFixed(2));

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

function saveRecord(btn, invoice_id) {
    let $tr = $(btn).closest('tr');
    
    // Final check on recalc to ensure consistency
    recalcInvoice(btn);

    let data = {
        invoice_id: invoice_id,
        booking_type: $tr.find('[name="booking_type"]').val(),
        tarrif_per_day: $tr.find('[name="base_tarrif"]').val(),
        gross_tariff: $tr.find('[name="gross_tariff"]').val(),
        cleaning_charges: $tr.find('[name="cleaning_charges"]').val(),
        total_tariff: $tr.find('[name="total_tariff"]').val(),
        deduction_1: $tr.find('[name="deduction_1"]').val(),
        deduction_2: $tr.find('[name="deduction_2"]').val(),
        net_amount: $tr.find('[name="net_amount"]').val(),
        
        // Payment data
        payment_amount: $tr.find('input[name="amount"]').val(),
        payment_mode: $tr.find('select[name="payment_mode"]').val(),
        bank_name: $tr.find('input[name="bank_name"]').val(),
        account_no: $tr.find('input[name="account_no"]').val(),
        upi_id: $tr.find('input[name="upi_id"]').val(),
        reference_no: $tr.find('input[name="reference_no"]').val()
    };

    $(btn).text('Saving...').prop('disabled', true);

    $.post('save_host_determination.php', data, function(response) {
        if (response.success) {
            alert('Record saved successfully! ✨');
            $tr.css('background-color', '#d1fae5');
            setTimeout(() => {
                $tr.css('background-color', '');
                $(btn).text('Apply & Save').prop('disabled', false);
                if (data.payment_amount > 0) {
                     location.reload(); // Reload to update paid totals if payment was made
                }
            }, 1000);
        } else {
            alert('Error saving: ' + (response.message || 'Unknown error'));
            $(btn).text('Apply & Save').prop('disabled', false);
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
<th>Type</th>
<th>Days</th>
<th>Check-In</th>
<th>Check-Out</th>
<th>Rate</th>
<th>Gross</th>
<th>Cleaning</th>
<th>Total Tariff</th>
<th>Ded 1 (%)</th>
<th>Ded 2 (%)</th>
<th>Net Amt</th>
<th>Amt Realized thru Bank</th>
<th>Mode</th>
<th>Paid</th>
<th>Outstanding</th>
<th>Action</th>
</tr>
<tr class="header-row-2">
<th><input type="text" class="col-search" placeholder="ID"></th>
<th><input type="text" class="col-search" placeholder="Name"></th>
<th><input type="text" class="col-search" placeholder="Room"></th>
<th><input type="text" class="col-search" placeholder="Type"></th>
<th><input type="text" class="col-search" placeholder="Days"></th>
<th><input type="text" class="col-search" placeholder="In"></th>
<th><input type="text" class="col-search" placeholder="Out"></th>
<th><input type="text" class="col-search" placeholder="Rate"></th>
<th><input type="text" class="col-search" placeholder="Gross"></th>
<th><input type="text" class="col-search" placeholder="Clean"></th>
<th><input type="text" class="col-search" placeholder="Total"></th>
<th><input type="text" class="col-search" placeholder="D1"></th>
<th><input type="text" class="col-search" placeholder="D2"></th>
<th><input type="text" class="col-search" placeholder="Net"></th>
<th class="no-print"></th>
<th class="no-print"></th>
<th><input type="text" class="col-search" placeholder="Paid"></th>
<th><input type="text" class="col-search" placeholder="Bal"></th>
<th class="no-print"></th>
</tr>
</thead>
<tbody>
<?php while($row=mysqli_fetch_assoc($result)){
    $days  = (int)$row['no_of_days'];
    $due   = (float)$row['due_amount'];
    $sub   = (float)$row['subtotal'];
    
    // Logic for Default Values
    $booking_type = $row['booking_type'] ?: 'Weekend/Weekday';
    $base = (float)$row['tarrif_per_day'];
    if ($base <= 0) {
        $base = (float)$row['db_rate_std'];
    }
    
    $cleaning = $row['invoice_id'] ? (float)$row['cleaning_charges'] : 500.00;
    $ded1 = $row['invoice_id'] ? (float)$row['deduction_1'] : 3.00;
    $ded2 = $row['invoice_id'] ? (float)$row['deduction_2'] : 0.10;
    
    $paid  = (float)$row['paid_amount'];
    $out   = $row['net_amount'] > 0 ? ($row['net_amount'] - $paid) : ($due - $paid);
?>

<tr>
    <td><?php echo $row['booking_id']; ?></td>
    <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
    <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
    
    <td>
        <select name="booking_type" onchange="recalcInvoice(this)" style="width: 100px; font-size: 11px;">
            <option value="Week Day" <?php if($booking_type == 'Week Day') echo 'selected'; ?>>Week Day</option>
            <option value="Week End" <?php if($booking_type == 'Week End') echo 'selected'; ?>>Week End</option>
            <option value="Concessional" <?php if($booking_type == 'Concessional') echo 'selected'; ?>>Concessional</option>
            <option value="Long Stay" <?php if($booking_type == 'Long Stay') echo 'selected'; ?>>Long Stay</option>
        </select>
    </td>

    <td><?php echo $days; ?></td>
    <td><?php echo date('d-m-Y',strtotime($row['booking_from'])); ?></td>
    <td><?php echo date('d-m-Y',strtotime($row['booking_to'])); ?></td>

    <input type="hidden" name="days" value="<?php echo $days; ?>">
    <input type="hidden" name="base_tarrif" value="<?php echo $base; ?>">
    <input type="hidden" name="gross_tariff" value="<?php echo $row['gross_tariff']; ?>">
    <input type="hidden" name="net_amount" value="<?php echo $row['net_amount']; ?>">
    <input type="hidden" name="due_amount" value="<?php echo $due; ?>">

    <td>
        <!-- Dropdown for standard rates -->
        <select name="base_tarrif_selector" onchange="recalcInvoice(this)" style="width: 110px; font-size: 11px; <?php if($booking_type != 'Week Day' && $booking_type != 'Week End') echo 'display:none;'; ?>">
            <option value="<?php echo (float)$row['db_rate_std']; ?>" <?php if((float)$base == (float)$row['db_rate_std']) echo 'selected'; ?>>Main: ₹<?php echo number_format((float)$row['db_rate_std'],0); ?></option>
            <?php if((float)$row['db_rate_wknd'] > 0): ?>
                <option value="<?php echo (float)$row['db_rate_wknd']; ?>" <?php if((float)$base == (float)$row['db_rate_wknd']) echo 'selected'; ?>>Wknd: ₹<?php echo number_format((float)$row['db_rate_wknd'],0); ?></option>
            <?php endif; ?>
            <?php if((float)$row['db_rate_wkdy'] > 0): ?>
                <option value="<?php echo (float)$row['db_rate_wkdy']; ?>" <?php if((float)$base == (float)$row['db_rate_wkdy']) echo 'selected'; ?>>Wkdy: ₹<?php echo number_format((float)$row['db_rate_wkdy'],0); ?></option>
            <?php endif; ?>
        </select>
        <!-- Manual input for other types -->
        <input type="number" step="0.01" name="manual_base_rate" value="<?php echo $base; ?>" oninput="recalcInvoice(this)" style="width: 80px; <?php if($booking_type == 'Week Day' || $booking_type == 'Week End') echo 'display:none;'; ?>">
    </td>

    <td class="amount gross-display">₹<?php echo number_format((float)$row['gross_tariff'],2); ?></td>

    <td>
        <input type="number" step="0.01" name="cleaning_charges" value="<?php echo $cleaning; ?>" oninput="recalcInvoice(this)" style="width: 60px;">
    </td>

    <td>
        <input type="number" step="0.01" name="total_tariff" value="<?php echo (float)$row['total_tariff'] ?: (float)$row['gross_tariff']; ?>" oninput="recalcInvoice(this)" style="width: 70px;">
    </td>

    <td>
        <input type="number" step="0.01" name="deduction_1" value="<?php echo $ded1; ?>" oninput="recalcInvoice(this)" placeholder="%" style="width: 50px;">
    </td>

    <td>
        <input type="number" step="0.01" name="deduction_2" value="<?php echo $ded2; ?>" oninput="recalcInvoice(this)" placeholder="%" style="width: 50px;">
    </td>

    <td class="amount net-display">₹<?php echo number_format((float)$row['net_amount'],2); ?></td>

    <td>
        <input type="number" step="0.01" name="amount" placeholder="Pmt" oninput="recalcInvoice(this)" style="width: 80px;">
    </td>

    <td style="position: relative;">
        <select name="payment_mode" onchange="toggleBank(this)" style="width: 70px; font-size: 11px;">
            <option value="Cash">Cash</option>
            <option value="Bank">Bank</option>
            <option value="UPI">UPI</option>
        </select>

        <div class="bankBox">
            <input name="bank_name" placeholder="Bank Name">
            <input name="account_no" placeholder="Acc No">
            <input name="upi_id" placeholder="UPI ID">
            <input name="reference_no" placeholder="Ref No">
            <button type="button" class="btn" onclick="$(this).parent().fadeOut()" style="padding:4px 8px; font-size:10px; margin-top:5px;">Close</button>
        </div>
    </td>

    <td>
        ₹<?php echo number_format($paid,2); ?><br>
        <span class="small"><?php echo htmlspecialchars($row['bank_name']); ?></span>
        <span data-paid="<?php echo $paid; ?>" style="display:none;"></span>
    </td>

    <td class="outstanding outstanding-display">₹<?php echo number_format($out,2); ?></td>

    <td>
        <button type="button" onclick="saveRecord(this, <?php echo (int)$row['invoice_id']; ?>)" class="save-btn-all">Apply & Save</button>
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
            { width: '60px', targets: 0 },   // Booking
            { width: '180px', targets: 1 },  // Customer
            { width: '130px', targets: 2 },  // Room
            { width: '100px', targets: 3 },  // Type
            { width: '50px', targets: 4 },   // Days
            { width: '90px', targets: 5 },   // Check-In
            { width: '90px', targets: 6 },   // Check-Out
            { width: '110px', targets: 7 },  // Rate
            { width: '80px', targets: 8 },   // Gross
            { width: '70px', targets: 9 },   // Cleaning
            { width: '80px', targets: 10 },  // Total Tariff
            { width: '60px', targets: 11 },  // Ded 1
            { width: '60px', targets: 12 },  // Ded 2
            { width: '90px', targets: 13 },  // Net Amt
            { width: '90px', targets: 14 },  // Enter Pmt
            { width: '100px', targets: 15 }, // Mode
            { width: '90px', targets: 16 },  // Paid
            { width: '100px', targets: 17 }, // Outstanding
            { width: '100px', targets: 18 }, // Action
            { orderable: false, searchable: false, targets: [14, 15, 18] }
        ],
        initComplete: function() {
            var api = this.api();
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
            }, 500);
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
