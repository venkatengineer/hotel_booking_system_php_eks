<?php
session_start();
include 'config.php';

/* Check login */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* Check role safely (PHP 5 compatible) */
/* L */
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) != 'admin') {
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
            background: linear-gradient(135deg, #d8f3dc, #ffd6e8);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 40px;
        }

        .wrapper {
            width: 95%;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 25px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        h1 {
            color: #2d6a4f;
            text-align: center;
            margin-bottom: 30px;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        table.dataTable thead th {
            background: #b9fbc0 !important;
            color: #2d6a4f !important;
            padding: 15px !important;
            border-bottom: none !important;
        }

        table.dataTable tbody td {
            padding: 12px 15px !important;
            border-bottom: 1px solid #eee !important;
            color: #444;
        }

        .back-btn, .add-btn {
            display: inline-block;
            text-decoration: none;
            color: #2d6a4f;
            font-weight: bold;
            background: #b9fbc0;
            padding: 10px 20px;
            border-radius: 20px;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }

        .add-btn {
            background: #ffd6a5;
        }

        .back-btn:hover, .add-btn:hover {
            background: #ffc6d9;
            transform: translateY(-2px);
        }

        .new-user-form {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 25px;
            margin-bottom: 30px;
            display: none;
            border: 1px solid #eee;
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
            padding: 10px;
            border-radius: 15px;
            border: 1px solid #ddd;
            background: #fdfdfd;
        }

        .btn-save {
            padding: 12px 30px;
            border-radius: 20px;
            border: none;
            font-weight: 600;
            background: linear-gradient(90deg, #b9fbc0, #ffd6a5);
            cursor: pointer;
            color: #2d6a4f;
            transition: 0.3s;
        }

        .btn-save:hover {
            background: #ffc6d9;
            transform: translateY(-2px);
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .status-active { background: #b9fbc0; color: #2d6a4f; }
        .status-inactive { background: #ffd6a5; color: #8e3b63; }
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
