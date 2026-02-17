<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>

        body {
            background: linear-gradient(135deg, #d8f3dc, #ffd6e8);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

        .dashboard-wrapper {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 50px 20px;
        }

        .dashboard-card {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 30px;
            width: 1100px;
            max-width: 95%;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #2d6a4f;
            margin-bottom: 10px;
        }
        /* MAIN TITLE — Make Dashboard Name Prominent */
/* ===== MAIN TITLE STYLE ===== */
.main-title {
    text-align: center;
    font-size: 44px;
    font-weight: 700;
    letter-spacing: 1.5px;
    color: #2d6a4f;
    position: relative;
    display: inline-block;
    left: 50%;
    transform: translateX(-50%);
    cursor: default;

    /* Soft glow */
    text-shadow: 0 0 8px rgba(185,251,192,0.6),
                 0 0 18px rgba(255,198,217,0.5);

    transition: all 0.4s ease;
}



.main-title::before {
    content: "";
    position: absolute;
    top: 0;
    left: -120%;
    width: 60%;
    height: 100%;
    background: linear-gradient(
        120deg,
        transparent,
        rgba(255,255,255,0.6),
        transparent
    );
    transform: skewX(-25deg);
}


.main-title:hover::before {
    animation: shineMove 0.9s ease forwards;
}

@keyframes shineMove {
    0% { left: -120%; }
    100% { left: 130%; }
}

.main-title:hover{
    transform : translateX(-50%) translateY(-3px) scale(1.02);
}

.welcome-text{
    text-align : center;
    color : #888;
    font-size : 15px;
    margin-top : 8px;
    margin-bottom : 40px;
    letter-spacing : 0.5px;
}


@keyframes glowPulse {
    0% {
        text-shadow: 0 0 6px rgba(185,251,192,0.4),
                     0 0 12px rgba(255,198,217,0.3);
    }
    50% {
        text-shadow: 0 0 14px rgba(185,251,192,0.9),
                     0 0 28px rgba(255,198,217,0.7);
    }
    100% {
        text-shadow: 0 0 6px rgba(185,251,192,0.4),
                     0 0 12px rgba(255,198,217,0.3);
    }
}

.main-title {
    animation: glowPulse 3s ease-in-out infinite;
}



.welcome-text {
    text-align: center;
    color: #888;
    font-size: 16px;
    margin-bottom: 40px;
}


        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
        }

        

        .top-Admin {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-bottom: 40px;
        }

        .small-btn {
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            background: #b9fbc0;
            color: #2d6a4f;
            transition: 0.3s;
        }

        .small-btn:hover {
            background: #ffc6d9;
            transform: translateY(-3px);
        }

       

        .widget-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 50px;
        }

        .widget {
            background: white;
            border-radius: 25px;
            padding: 40px;
            text-align: center;
            text-decoration: none;
            color: #2d6a4f;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            transition: 0.3s;
        }

        .widget:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .widget span {
            font-size: 40px;
            display: block;
            margin-bottom: 15px;
        }

        .widget h3 {
            margin: 0;
        }

       

        .bottom-grid {
            display: flex;
            justify-content: center;
            gap: 40px;
        }

        .bottom-btn {
            padding: 15px 35px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            background: #ffc6d9;
            color: #8e3b63;
            transition: 0.3s;
        }

        .bottom-btn:hover {
            background: #b9fbc0;
            color: #2d6a4f;
            transform: translateY(-3px);
        }

        .logout-btn {
            margin-top: 40px;
            display: block;
            text-align: center;
            color: #8e3b63;
            text-decoration: none;
            font-weight: bold;
        }

    </style>
</head>
<body>

<div class="dashboard-wrapper">
    <div class="dashboard-card">


        <h1 class="main-title">Eternal Kalyan Dashboard</h1>
        <p class="welcome-text">Welcome, <?php echo $_SESSION['username']; ?> ✨</p>



        
         <?php
$role = isset($_SESSION['role']) ? trim($_SESSION['role']) : '';
if (strcasecmp($role, 'admin') === 0):
?>


        <div class="top-Admin">
            <a href="assets.php" class="small-btn">🏨 Assets</a>
            <a href="rates.php" class="small-btn">💰 Tariff</a>
            <a href="add_user.php" class="small-btn">➕ Add User</a>
        </div>
        <?php endif; ?>

        <!-- ===== MIDDLE WIDGETS ===== -->
        <div class="widget-grid">

            <a href="add_customer.php" class="widget">
                <span>👤</span>
                <h3>Add Customer</h3>
            </a>

            <a href="register_booking.php" class="widget">
                <span>📝</span>
                <h3>Add Booking</h3>
            </a>

            <a href="calendar.php" class="widget">
                <span>📋</span>
                <h3>View Bookings</h3>
            </a>

        </div>

        <!-- ===== BOTTOM BUTTONS ===== -->
<!-- K -->
        <div class="bottom-grid">
            <a href="invoice_payments.php" class="bottom-btn">💳 Payment and Invoice</a>
            <a href="billing_report.php" class="bottom-btn">📊 Billing Report</a>

            
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>

    </div>
</div>

</body>
</html>
