<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], 'admin') !== 0) {
    header("Location: dashboard.php");
    exit();
}

/* FETCH ASSETS FOR DROPDOWN */
$assets_res = $conn->query("SELECT asset_id, asset_name FROM tbl_assets WHERE is_active = 1 ORDER BY asset_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Determination | EKM</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #d8f3dc, #ffd6e8);
            background-attachment: fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1a3a3a;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 35px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 800px;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .host-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .host-header h1 {
            color: #2d6a4f;
            font-size: 2.2rem;
            margin: 0;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .host-header p {
            color: #546e7a;
            margin-top: 8px;
            font-size: 1rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .full-width {
            grid-column: span 2;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 700;
            color: #455a64;
            font-size: 0.9rem;
            margin-left: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, select {
            padding: 12px 18px;
            border-radius: 15px;
            border: 2px solid #e1e8ed;
            background: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
            color: #2c3e50;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #b9fbc0;
            box-shadow: 0 0 0 4px rgba(185, 251, 192, 0.15);
            background: #fdfdfd;
        }

        .rate-inputs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 20px;
            border: 1px solid #edf2f7;
        }

        .rate-field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .rate-field span {
            font-size: 0.75rem;
            font-weight: 700;
            color: #78909c;
            text-align: center;
        }

        .btn-submit {
            margin-top: 30px;
            width: 100%;
            padding: 15px;
            border-radius: 20px;
            border: none;
            background: linear-gradient(90deg, #8e3b63, #2d6a4f);
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 20px rgba(45, 106, 79, 0.2);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(45, 106, 79, 0.3);
            filter: brightness(1.1);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .success-msg {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 15px;
            text-align: center;
            font-weight: 700;
            margin-bottom: 25px;
            border: 1px solid #c8e6c9;
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
        }
    </style>
</head>
<body>
    <?php include 'header_nav.php'; ?>

    <div class="main-container">
        <div class="glass-card">
            <div class="host-header">
                <h1>Rate Determination 💰</h1>
                <p>Welcome, Host. Set and verify room tariffs for upcoming dates.</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="success-msg">
                    ✨ Rate record successfully updated and synchronized!
                </div>
            <?php endif; ?>

            <form action="save_rate_determination.php" method="POST">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="asset_id">Select Asset / Room Type</label>
                        <select name="asset_id" id="asset_id" required>
                            <option value="">Choose a room...</option>
                            <?php while($a = $assets_res->fetch_assoc()): ?>
                                <option value="<?= $a['asset_id'] ?>"><?= htmlspecialchars($a['asset_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="effective_from">Effective From</label>
                        <input type="date" name="effective_from" id="effective_from" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="effective_to">Effective Until</label>
                        <input type="date" name="effective_to" id="effective_to" value="2036-12-31" required>
                    </div>

                    <div class="form-group full-width">
                        <label>Rate Distribution (Daily Pricing)</label>
                        <div class="rate-inputs">
                            <div class="rate-field">
                                <span>STANDARD</span>
                                <input type="number" step="0.01" name="rate_per_day" placeholder="0.00" required>
                            </div>
                            <div class="rate-field">
                                <span>WEEKEND</span>
                                <input type="number" step="0.01" name="rate_weekend" placeholder="optional">
                            </div>
                            <div class="rate-field">
                                <span>WEEKDAY</span>
                                <input type="number" step="0.01" name="rate_weekday" placeholder="optional">
                            </div>
                            <div class="rate-field">
                                <span>CONCESSION</span>
                                <input type="number" step="0.01" name="rate_consession" placeholder="0.00">
                            </div>
                            <div class="rate-field">
                                <span>LONG STAY</span>
                                <input type="number" step="0.01" name="rate_long_stay" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Confirm and Apply Rates</button>
            </form>
        </div>
    </div>
</body>
</html>
