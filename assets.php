<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
session_start();
include 'config.php';

/* ================= LOGIN CHECK ================= */
$allowed_roles = ['admin', 'host', 'admn1', 'admn2'];
$current_role  = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';

if (!isset($_SESSION['user_id']) || !in_array($current_role, $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

/* ================= FETCH ASSETS ================= */
$result = $conn->query("SELECT * FROM tbl_assets ORDER BY asset_name");

if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Assets</title>

<style>
body {
    background: linear-gradient(135deg,#d8f3dc, #ffd6e8);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #1a3a3a;
    margin: 0;
    padding: 0;
}

.wrapper{
    width:90%;
    margin:40px auto;
}

.card{
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(10px);
    padding: 35px;
    border-radius: 30px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.06);
}

h2{ color: #2d6a4f; margin-bottom:25px; font-weight: 700; letter-spacing: -0.5px; }

input, select {
    padding: 10px 15px;
    border-radius: 15px;
    border: 1px solid #cfd8dc;
    background: #fff;
    font-size: 14px;
    transition: all 0.3s;
    box-sizing: border-box;
}
input:focus, select:focus {
    outline: none;
    border-color: #b9fbc0;
    box-shadow: 0 0 0 3px rgba(185, 251, 192, 0.2);
}

.btn-modern {
    padding: 10px 20px;
    border-radius: 25px;
    background: linear-gradient(90deg, #b9fbc0, #ffc6d9);
    color: #2d6a4f;
    border:none;
    padding:10px 22px;
    border-radius:20px;
    cursor:pointer;
    font-weight:600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}
.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    background: linear-gradient(90deg, #ffc6d9, #b9fbc0);
}

table{
    width:100%;
    margin-top:30px;
    border-collapse: separate;
    border-spacing: 0;
}

th{
    background: #d8f3dc !important;
    color: #2d6a4f !important;
    padding: 18px 15px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 13px;
    border-bottom: 2px solid #b9fbc0;
}

td{
    padding: 15px;
    text-align: center;
    border-bottom: 1px solid #eceff1;
    vertical-align: middle;
    color: #37474f;
    font-size: 14px;
}

tr:hover { background: #f1fafa; }

.delete-btn {
    background: #ffebee !important;
    color: #c62828 !important;
}
.delete-btn:hover {
    background: #ffcdd2 !important;
}

</style>
</head>

<body>
    <?php include 'header_nav.php'; ?>

<div class="wrapper">
<div class="card">

<h2>Assets Master</h2>

<?php if(isset($_GET['success'])){ ?>
<p style="color:green;font-weight:bold;">Asset Added Successfully ✔</p>
<?php } ?>

<?php if(isset($_GET['error'])){ ?>
<p style="color:red;font-weight:bold;">Error Adding Asset</p>
<?php } ?>

<!-- ================= ADD ASSET FORM ================= -->
<form method="POST" action="save_asset.php">
<input type="text" name="asset_code" placeholder="Asset Code" required>
<input type="text" name="asset_name" placeholder="Asset Name" required>
<input type="text" name="description" placeholder="Description" required>
<button type="submit">Add Asset</button>
</form>

<!-- ================= ASSET LIST ================= -->
 <!-- Y -->
<table>
<tr>
<th>Code</th>
<th>Name</th>
<th>Description</th>
<th>Margin %</th>
<th>Status</th>
<th>Save</th>
<th>Delete</th>
</tr>

<?php while($row = $result->fetch_assoc()){ ?>
<tr>

<form method="POST" action="update_asset.php">

<td><?php echo htmlspecialchars($row['asset_code']); ?></td>
<td><?php echo htmlspecialchars($row['asset_name']); ?></td>
<td><?php echo htmlspecialchars($row['description']); ?></td>

<td>
<input type="number" step="0.01"
name="margin_percent"
value="<?php echo $row['margin_percent']; ?>"
style="width:90px;">
</td>

<td>
<select name="status">
<option value="Active"
<?php if($row['status']=='Active') echo 'selected'; ?>>
Active</option>

<option value="Inactive"
<?php if($row['status']=='Inactive') echo 'selected'; ?>>
Inactive</option>
</select>
</td>

<td>
<input type="hidden" name="asset_id"
value="<?php echo $row['asset_id']; ?>">
<button type="submit">Save</button>
</td>

</form>

<td>
<form method="POST" action="delete_asset.php"
onsubmit="return confirm('Delete this asset?');">
<input type="hidden" name="asset_id"
value="<?php echo $row['asset_id']; ?>">
<button class="delete-btn">Delete</button>
</form>
</td>

</tr>
<?php } ?>
</table>


</div>
</div>
</body>
</html>
