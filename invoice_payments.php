<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* SEARCH */
$search = "";
$search_sql = "";

if (!empty($_GET['q'])) {
    $search = mysqli_real_escape_string($conn,$_GET['q']);
    $search_sql = " WHERE 
        c.full_name LIKE '%$search%' OR
        a.asset_name LIKE '%$search%' OR
        b.booking_id LIKE '%$search%' ";
}

/* QUERY */

$sql = "
SELECT 
    b.booking_id,
    b.booking_from,
    b.booking_to,

    c.full_name,
    c.nationality,

    a.asset_name,

    r.rate_per_day AS base_rate,

    DATEDIFF(b.booking_to, b.booking_from) AS no_of_days,



    i.invoice_id,

    IFNULL(SUM(p.amount),0) AS paid_amount,

    MAX(bd.bank_name) AS bank_name

FROM tbl_bookings b

JOIN tbl_customers c ON c.customer_id = b.customer_id
JOIN tbl_assets a ON a.asset_id = b.asset_id

LEFT JOIN tbl_rates r 
ON r.asset_id = b.asset_id
AND r.effective_to = '2036-12-31'

LEFT JOIN tbl_invoices i ON i.booking_id = b.booking_id
LEFT JOIN tbl_payments p ON p.invoice_id = i.invoice_id
LEFT JOIN tbl_bank_details bd ON bd.bank_id = p.bank_id

$search_sql

GROUP BY b.booking_id
ORDER BY b.booking_from DESC
";

$result = mysqli_query($conn,$sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Invoice Payments</title>

<style>
body{
    background:linear-gradient(135deg,#d8f3dc,#ffd6e8);
    font-family:'Segoe UI',sans-serif;
    color:#2d6a4f;
}

/* wrapper allows horizontal scroll like excel */
/* Wrapper must allow horizontal scroll */
.wrapper{
    width:100%;
    margin:30px auto;
    overflow-x:auto;
    padding:0 20px;
}

/* Card must grow with content (IMPORTANT) */
.card{
    background:#ffffffee;
    padding:25px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.06);

    display:inline-block;   /* THIS makes it expand with table */
    min-width:100%;         /* at least screen width */
}


h2{
    color:#2d6a4f;
    margin-bottom:20px;
}

/* ===== TABLE (EXCEL STYLE) ===== */

/* ===== TABLE (STRICT GRID — NO SHRINKING) ===== */

table{
    border-collapse:collapse;
    table-layout:fixed;     /* IMPORTANT: prevents column collapsing */
    width:1800px;           /* force wide table */
}

/* HEADERS */
th{
    background:#b9fbc0;
    color:#2d6a4f;
    padding:12px;
    border-bottom:2px solid #e3f7ea;
    text-align:center;
    font-weight:600;
}

/* CELLS */
td{
    padding:10px;
    border-bottom:1px solid #f0f0f0;
    text-align:center;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;     /* NEVER wrap */
}

/* SOFT ROW */
tr:nth-child(even){
    background:#fff6fa;
}


/* ===== INPUTS ===== */

input,select{
    width:140px;            /* fixed width prevents collapsing */
    padding:6px 8px;
    border-radius:10px;
    border:1px solid #e6e6e6;
    background:#fafafa;
    font-size:13px;
    text-align:center;
}

input:focus,select:focus{
    outline:none;
    border:1px solid #b9fbc0;
    background:#fff;
}

/* ===== BUTTON ===== */

button{
    background:linear-gradient(90deg,#b9fbc0,#ffc6d9);
    border:none;
    padding:7px 16px;
    border-radius:20px;
    cursor:pointer;
    font-weight:600;
    color:#2d6a4f;
    transition:.25s;
}

button:hover{
    transform:translateY(-1px);
    box-shadow:0 6px 12px rgba(0,0,0,0.08);
}

.bankBox{
display:none;
margin-top:6px;
padding:8px;
background:#f7fbf8;
border-radius:8px;
}

.bankBox input{
display:block;
width:100%;
margin-bottom:5px;
}


/* ===== AMOUNTS ===== */

.amount{
    font-weight:600;
    color:#2d6a4f;
}

.outstanding{
    color:#c77d9b;   /* soft pastel rose */
    font-weight:600;
}

/* ===== BANK BOX ===== */

.bankBox{
    display:none;
    margin-top:6px;
}

/* small info */
.small{
    font-size:11px;
    color:#777;
}
</style>


<script>
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

<form method="GET">
<input type="text" name="q" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
<button type="submit">Search</button>
<a href="invoice_payments.php">Reset</a>
</form>

<br>

<table>
<tr>
<th>Booking</th>
<th style="width:180px;">Customer</th>
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

<?php while($row=mysqli_fetch_assoc($result)){

$base  = (float)$row['base_rate'];
$days  = (int)$row['no_of_days'];
$due   = $base * $days;
$paid  = (float)$row['paid_amount'];
$out   = $due - $paid;
?>

<form method="POST" action="save_daily_payment.php">

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
<input type="hidden" name="invoice_id"
value="<?php echo (int)$row['invoice_id']; ?>">

<button type="submit">Save</button>
</td>

</tr>

</form>

<?php } ?>


</table>

</div>
</div>
<script>
function toggleBank(sel){
    var bankBox = sel.parentNode.querySelector('.bankBox');

    if(sel.value === "Cash"){
        bankBox.style.display = "none";
    } else {
        bankBox.style.display = "block";
    }
}
</script>

</body>
</html>
