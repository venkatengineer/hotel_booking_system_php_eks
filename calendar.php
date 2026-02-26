<?php
session_start();
include 'config.php';

$allowed_roles = ['admin', 'host', 'admn1', 'admn2'];
$current_role  = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';

if (!isset($_SESSION['user_id']) || !in_array($current_role, $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

$events = [];

/* Fetch bookings with room name */
$query = "
SELECT 
    b.booking_id,
    b.booking_from,
    b.booking_to,
    c.full_name,
    c.phone,
    a.asset_name,

    i.total_amount,

    IFNULL(SUM(p.amount),0) AS paid_amount

FROM tbl_bookings b

JOIN tbl_assets a ON b.asset_id = a.asset_id
JOIN tbl_customers c ON b.customer_id = c.customer_id

LEFT JOIN tbl_invoices i ON i.booking_id = b.booking_id
LEFT JOIN tbl_payments p ON p.invoice_id = i.invoice_id

GROUP BY b.booking_id
";



$result = $conn->query($query);

/* Pastel colors for different rooms */
$roomColors = [
    "#ffc6d9",  // pink
    "#b9fbc0",  // green
    "#cdb4db",  // lavender
    "#fbcfe8",  // light rose
    "#ffd6a5",  // peach
];

$colorIndex = 0;
$roomColorMap = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $roomName = $row['asset_name'];

        // Assign unique color per room
        if (!isset($roomColorMap[$roomName])) {
            $roomColorMap[$roomName] = $roomColors[$colorIndex % count($roomColors)];
            $colorIndex++;
        }

        $total  = (float)$row['total_amount'];
$paid   = (float)$row['paid_amount'];

$statusIcon = "🔴"; // default not paid

if ($paid == 0) {
    $statusIcon = "🔴"; // Not Paid
}
elseif ($paid < $total) {
    $statusIcon = "🟡"; // Partial
}
elseif ($paid >= $total && $total > 0) {
    $statusIcon = "🟢"; // Fully Paid
}

$endDate = $row['booking_to'];
        $events[] = [
    "title" => $statusIcon . " " . $roomName,

    "start" => $row['booking_from'],
    "end"   => $endDate,
    "backgroundColor" => $roomColorMap[$roomName],
    "borderColor" => $roomColorMap[$roomName],
    "textColor" => "#2d6a4f",

    /* EXTRA DATA FOR POPUP */
    "extendedProps" => [
        
        "booking_id" => $row['booking_id'],
        "customer"   => $row['full_name'],
        "phone"      => $row['phone'],
        "room"       => $roomName,
        "from" => date('d-m-Y', strtotime($row['booking_from'])),
        "to"   => date('d-m-Y', strtotime($row['booking_to']))

    ]
];

    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Calendar</title>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            background: linear-gradient(135deg,#d8f3dc, #ffd6e8);
            background-attachment: fixed;
        }

        .calendar-wrapper {
            width: 90%;
            margin: 40px auto;
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(15px);
            padding: 30px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
        }
        /* ===== MODAL STYLE ===== */
.modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.25);
}

.modal-content {
    background: linear-gradient(135deg,#d8f3dc, #ffd6e8);
    width: 400px;
    margin: 10% auto;
    padding: 30px;
    border-radius: 30px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.2);
    color: #1a3a3a;
    animation: popIn 0.3s ease;
}

@keyframes popIn {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.close-btn {
    float: right;
    font-size: 22px;
    cursor: pointer;
    font-weight: bold;
}


        .calendar-title {
            text-align: center;
            color: #2d6a4f;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .back-btn {
    background: #b9fbc0;
    border: none;
    padding: 8px 18px;
    border-radius: 20px;
    font-weight: bold;
    cursor: pointer;
    color: #2d6a4f;
    transition: 0.3s;
}

.back-btn:hover {
    background: #ffc6d9;
}


        #calendar {
            margin-top: 20px;
        }

        /* Make calendar cleaner */
        .fc-toolbar-title {
            color: #2d6a4f;
        }

        .fc-button {
            background: linear-gradient(90deg, #b9fbc0, #ffc6d9) !important;
            border: none !important;
            color: #2d6a4f !important;
            border-radius: 20px !important;
            font-weight: 600 !important;
        }

        .fc-button:hover {
            background: linear-gradient(90deg, #ffc6d9, #b9fbc0) !important;
        }
    </style>
</head>
<body>

<div class="calendar-wrapper">

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <button onclick="goBack()" class="back-btn">⬅ Back</button>
        <h1 class="calendar-title" style="flex:1; text-align:center;">Room Booking Calendar 📅</h1>
        <div style="width:80px;"></div>
    </div>

    <div id="calendar"></div>
</div>
<!-- BOOKING POPUP -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">×</span>

        <h2>Booking Details</h2>

        <p><strong>Customer:</strong> <span id="mCustomer"></span></p>
        <p><strong>Phone:</strong> <span id="mPhone"></span></p>
        <p><strong>Room:</strong> <span id="mRoom"></span></p>
        <p><strong>Check-In:</strong> <span id="mFrom"></span></p>
        <p><strong>Check-Out:</strong> <span id="mTo"></span></p>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* GET CALENDAR DIV */
    var calendarEl = document.getElementById('calendar');

    /* INITIALIZE CALENDAR */
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 650,
        events: <?php echo json_encode($events); ?>,

        /* CLICK EVENT */
        eventClick: function(info) {

            var data = info.event.extendedProps;

            document.getElementById("mCustomer").innerHTML = data.customer;
            document.getElementById("mPhone").innerHTML    = data.phone;
            document.getElementById("mRoom").innerHTML     = data.room;
            document.getElementById("mFrom").innerHTML     = data.from;
            document.getElementById("mTo").innerHTML       = data.to;

            document.getElementById("bookingModal").style.display = "block";
        }
    });

    calendar.render();
});


/* CLOSE MODAL */
function closeModal(){
    document.getElementById("bookingModal").style.display = "none";
}

/* CLICK OUTSIDE TO CLOSE */
window.onclick = function(e){
    var modal = document.getElementById("bookingModal");
    if(e.target == modal){
        modal.style.display = "none";
    }
};

/* BACK BUTTON */
/* U */
function goBack() {
    window.history.back();
}
</script>


</body>
</html>
