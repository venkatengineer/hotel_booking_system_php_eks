<?php
session_start();
include 'config.php';

/* Check login */
$allowed_roles = ['admin', 'host', 'admn1', 'admn2'];
$current_role  = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';

if (!isset($_SESSION['user_id']) || !in_array($current_role, $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}
/* FETCH USERS FOR TABLE */
$users_res = $conn->query("SELECT user_id, username, email, role, is_active, created_at, last_login FROM tbl_users ORDER BY username ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            background: linear-gradient(135deg,#d8f3dc, #ffd6e8);
            background-attachment: fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1a3a3a;
            margin: 0;
            padding: 40px;
        }

        .wrapper {
            width: 95%;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            padding: 35px;
            border-radius: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.06);
        }

        h1 { color: #2d6a4f; margin-bottom:30px; font-weight: 700; letter-spacing: -0.5px; text-align: center; }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        table.dataTable thead th {
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

        table.dataTable tbody td {
            padding: 15px 12px !important;
            border-bottom: 1px solid #eceff1 !important;
            color: #444;
            vertical-align: middle;
            text-align: center !important;
        }

        .back-btn, .add-btn, .btn-save {
            display: inline-block;
            text-decoration: none;
            color: #2d6a4f;
            font-weight: 600;
            background: linear-gradient(90deg, #b9fbc0, #ffc6d9);
            padding: 10px 20px;
            border-radius: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .back-btn:hover, .add-btn:hover, .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
            background: linear-gradient(90deg, #ffc6d9, #b9fbc0);
        }

        .new-user-form {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 25px;
            margin-bottom: 30px;
            display: none;
            border: 1px solid #e0e0e0;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: flex-end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        input, select {
            width: 100%;
            padding: 10px 15px;
            border-radius: 12px;
            border: 1px solid #cfd8dc;
            background: #fff;
            transition: all 0.3s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #b9fbc0;
            box-shadow: 0 0 0 3px rgba(185, 251, 192, 0.2);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 700;
        }
        .status-active { background: #e8f5e9; color: #2e7d32; }
        .status-inactive { background: #ffebee; color: #c62828; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="header-actions">
        <a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
        <button class="add-btn" onclick="toggleForm()">➕ Add New User</button>
    </div>

    <h1>User Management 👤</h1>

    <!-- NEW USER FORM -->
    <div id="addUserForm" class="new-user-form">
        <h3 style="color: #2d6a4f; margin-top: 0;">Create New User Account</h3>
        <form action="save_user.php" method="POST" class="form-grid">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter username" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="Admin">Admin</option>
                    <option value="Manager">Manager</option>
                    <option value="Staff">Staff</option>
                </select>
            </div>
            <button type="submit" class="btn-save">Create User</button>
        </form>
    </div>

    <!-- USERS TABLE -->
    <table id="usersTable" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Last Login</th>
            </tr>
        </thead>
        <tbody>
            <?php while($user = $users_res->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <span class="status-badge <?= $user['is_active'] ? 'status-active' : 'status-inactive' ?>">
                            <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td><?= $user['created_at'] ? date('d-m-Y H:i', strtotime($user['created_at'])) : '-' ?></td>
                    <td><?= $user['last_login'] ? date('d-m-Y H:i', strtotime($user['last_login'])) : 'Never' ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        pageLength: 10,
        scrollX: true,
        order: [[0, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search users..."
        }
    });
});

function toggleForm() {
    var form = document.getElementById('addUserForm');
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}
</script>

</body>
</html>
