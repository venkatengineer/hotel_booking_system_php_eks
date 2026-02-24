<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
session_start();
include 'config.php';

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ================= ROLE CHECK (PHP 5 SAFE) ================= */
$role = '';
if (isset($_SESSION['role'])) {
    $role = strtolower(trim($_SESSION['role']));
}

if ($role != 'admin') {
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
body{
background:linear-gradient(135deg,#d8f3dc,#ffd6e8);
font-family:'Segoe UI',sans-serif;
margin:0;
}

.wrapper{
width:90%;
margin:40px auto;
}

.card{
background:#fff;
padding:30px;
border-radius:25px;
box-shadow:0 20px 40px rgba(0,0,0,.1);
}

h2{
color:#2d6a4f;
margin-bottom:20px;
}

input{
padding:12px;
border-radius:20px;
border:none;
background:#f5f5f5;
margin:6px;
width:220px;
}

button{
background:linear-gradient(90deg,#b9fbc0,#ffc6d9);
border:none;
padding:12px 20px;
border-radius:25px;
cursor:pointer;
font-weight:600;
}

table{
width:100%;
margin-top:25px;
border-collapse:collapse;
}

th{
background:#b9fbc0;
padding:12px;
}

td{
padding:12px;
text-align:center;
border-bottom:1px solid #eee;
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
<button style="background:#ffccd5;">Delete</button>
</form>
</td>

</tr>
<?php } ?>
</table>


</div>
</div>
</body>
</html>
