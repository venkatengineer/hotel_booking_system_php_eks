<?php
session_start();

$error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Eternal Kalyan Login</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg,#d8f3dc, #ffd6e8);
            font-family: 'Segoe UI', sans-serif;
            background-attachment: fixed;
        }

        .login-wrapper {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(15px);
            padding: 50px;
            border-radius: 30px;
            width: 400px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from {opacity:0; transform:translateY(20px);}
            to {opacity:1; transform:translateY(0);}
        }

        .login-card h1 {
            color: #2d6a4f;
            margin-bottom: 10px;
        }

        .subtitle {
            margin-bottom: 30px;
            color: #455a64;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 20px;
            border-radius: 50px;
            border: 1px solid #cfd8dc;
            outline: none;
            background: #ffffff;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            transition: 0.3s;
            font-size: 14px;
        }

        .input-group input:focus {
            border-color: #b9fbc0;
            box-shadow: 0 0 0 3px rgba(185, 251, 192, 0.2);
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            border-radius: 50px;
            border: none;
            font-weight: 600;
            background: linear-gradient(90deg, #b9fbc0, #ffc6d9);
            color: #2d6a4f;
            cursor: pointer;
            transition: 0.3s;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
            background: linear-gradient(90deg, #ffc6d9, #b9fbc0);
        }

        .error-box {
            background: #ffe5ec;
            color: #8e3b63;
            padding: 10px;
            border-radius: 20px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <h1>Eternal Kalyan 🏨</h1>
        <p class="subtitle">Secure Login Portal</p>

        <?php if ($error): ?>
            <div class="error-box">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="authenticate.php">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>

            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>
    </div>
</div>

</body>
</html>
