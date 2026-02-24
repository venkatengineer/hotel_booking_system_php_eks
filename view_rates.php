<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], 'admin') !== 0) {
    header("Location: dashboard.php");
    exit();
}

/* FETCH ASSETS FOR DROPDOWN */
$assets_res = $conn->query("SELECT asset_id, asset_name FROM tbl_assets WHERE is_active = 1 ORDER BY asset_name");

/* FETCH ALL RATE RECORDS */
$query = "
SELECT 
    r.rate_id,
    a.asset_name, 
    r.rate_per_day, 
    r.rate_weekend,
    r.rate_weekday,
    r.rate_consession,
    r.rate_long_stay,
    r.effective_from, 
    r.effective_to,
    r.updated_at
FROM tbl_rates r
JOIN tbl_assets a ON a.asset_id = r.asset_id
ORDER BY a.asset_name ASC, r.effective_from DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Room Tariffs</title>
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
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
            background: #b9fbc0;
        }

        .back-btn:hover, .add-btn:hover {
            background: #ffc6d9;
            transform: translateY(-2px);
        }

        .new-rate-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: none;
            border: 1px solid #eee;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            align-items: flex-end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        input[type="number"], input[type="date"], select {
            padding: 8px;
            border-radius: 10px;
            border: 1px solid #ddd;
            background: #fdfdfd;
        }

        .btn-update, .btn-save {
            background: #b9fbc0;
            border: none;
            padding: 8px 15px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: bold;
            color: #2d6a4f;
            transition: 0.3s;
        }

        .btn-save {
            padding: 12px 25px;
            background: #b9fbc0;
        }

        .btn-update:hover, .btn-save:hover {
            background: #ffc6d9;
            transform: translateY(-2px);
        }

        /* COLUMN SEARCH STYLES */
        thead input.col-search {
            width: 100%;
            padding: 5px 6px;
            box-sizing: border-box;
            border-radius: 8px;
            border: 1px solid #cce8d4;
            background: #f0fdf4;
            font-size: 11px;
            color: #2d6a4f;
            text-align: center;
        }
        thead input.col-search:focus {
            outline: none;
            border-color: #b9fbc0;
            background: #fff;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="header-actions">
        <a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
        <button class="add-btn" onclick="toggleForm()">➕ Add New Rate</button>
    </div>

    <!-- NEW RATE FORM -->
    <div id="addRateForm" class="new-rate-form">
        <h3 style="color: #2d6a4f; margin-top: 0;">Add New Price Record</h3>
        <form action="save_rate_view.php" method="POST" class="form-grid">
            <div class="form-group">
                <label>Room Type</label>
                <select name="asset_id" required>
                    <option value="">Select Room...</option>
                    <?php while($a = $assets_res->fetch_assoc()): ?>
                        <option value="<?= $a['asset_id'] ?>"><?= htmlspecialchars($a['asset_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Price / Day</label>
                <input type="number" step="0.01" name="rate_per_day" required>
            </div>
            <div class="form-group">
                <label>Weekend</label>
                <input type="number" step="0.01" name="rate_weekend" value="0.00">
            </div>
            <div class="form-group">
                <label>Weekday</label>
                <input type="number" step="0.01" name="rate_weekday" value="0.00">
            </div>
            <div class="form-group">
                <label>Concession</label>
                <input type="number" step="0.01" name="rate_consession" value="0.00">
            </div>
            <div class="form-group">
                <label>Long Stay</label>
                <input type="number" step="0.01" name="rate_long_stay" value="0.00">
            </div>
            <div class="form-group">
                <label>Starting</label>
                <input type="date" name="effective_from" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Ended On</label>
                <input type="date" name="effective_to" value="2036-12-31" required>
            </div>
            <button type="submit" class="btn-save">Save Rate</button>
        </form>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <p style="color: #2d6a4f; background: #b9fbc0; padding: 10px; border-radius: 10px; text-align: center; font-weight: bold; margin-bottom: 20px;">
            Rate updated successfully! ✨
        </p>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] == 'date_mismatch'): ?>
        <p style="color: #8e3b63; background: #ffc6d9; padding: 10px; border-radius: 10px; text-align: center; font-weight: bold; margin-bottom: 20px;">
            Error: Start Date cannot be later than End Date! ❌
        </p>
    <?php endif; ?>

    <h1>Room Tariffs 💰</h1>
    
    <table id="ratesTable" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th>Room Type</th>
                <th>Price / Day</th>
                <th>Weekend</th>
                <th>Weekday</th>
                <th>Concession</th>
                <th>Long Stay</th>
                <th>Starting</th>
                <th>Ended On</th>
                <th>Last Updated</th>
                <th>Action</th>
            </tr>
            <tr class="search-row header-row-2">
                <th><input type="text" class="col-search" placeholder="Search Room"></th>
                <th><input type="text" class="col-search" placeholder="Search Price"></th>
                <th><input type="text" class="col-search" placeholder="Search Weekend"></th>
                <th><input type="text" class="col-search" placeholder="Search Weekday"></th>
                <th><input type="text" class="col-search" placeholder="Search Concession"></th>
                <th><input type="text" class="col-search" placeholder="Search Long Stay"></th>
                <th><input type="text" class="col-search" placeholder="DD-MM-YYYY"></th>
                <th><input type="text" class="col-search" placeholder="DD-MM-YYYY"></th>
                <th><input type="text" class="col-search" placeholder="Search Date"></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php $formId = "form_" . $row['rate_id']; ?>
                    <tr>
                        <td>
                            <form id="<?= $formId ?>" method="POST" action="update_rate_full.php"></form>
                            <input type="hidden" name="rate_id" value="<?= $row['rate_id'] ?>" form="<?= $formId ?>">
                            <strong><?= htmlspecialchars($row['asset_name']) ?></strong>
                        </td>
                        <td>
                            <span style="display:none;"><?= $row['rate_per_day'] ?></span>
                            <input type="number" step="0.01" name="rate_per_day" value="<?= $row['rate_per_day'] ?>" required form="<?= $formId ?>" style="width: 80px;">
                        </td>
                        <td>
                            <span style="display:none;"><?= (float)$row['rate_weekend'] ?></span>
                            <input type="number" step="0.01" name="rate_weekend" value="<?= (float)$row['rate_weekend'] ?>" form="<?= $formId ?>" style="width: 80px;">
                        </td>
                        <td>
                            <span style="display:none;"><?= (float)$row['rate_weekday'] ?></span>
                            <input type="number" step="0.01" name="rate_weekday" value="<?= (float)$row['rate_weekday'] ?>" form="<?= $formId ?>" style="width: 80px;">
                        </td>
                        <td>
                            <span style="display:none;"><?= (float)$row['rate_consession'] ?></span>
                            <input type="number" step="0.01" name="rate_consession" value="<?= (float)$row['rate_consession'] ?>" form="<?= $formId ?>" style="width: 80px;">
                        </td>
                        <td>
                            <span style="display:none;"><?= (float)$row['rate_long_stay'] ?></span>
                            <input type="number" step="0.01" name="rate_long_stay" value="<?= (float)$row['rate_long_stay'] ?>" form="<?= $formId ?>" style="width: 80px;">
                        </td>
                        <td>
                            <span style="display:none;"><?= date('d-m-Y', strtotime($row['effective_from'])) ?></span>
                            <input type="date" name="effective_from" value="<?= $row['effective_from'] ?>" required form="<?= $formId ?>">
                        </td>
                        <td>
                            <span style="display:none;"><?= date('d-m-Y', strtotime($row['effective_to'])) ?></span>
                            <input type="date" name="effective_to" value="<?= $row['effective_to'] ?>" required form="<?= $formId ?>">
                        </td>
                        <td>
                            <small><?= date('d-m-Y H:i', strtotime($row['updated_at'])) ?></small>
                        </td>
                        <td>
                            <button type="submit" class="btn-update" form="<?= $formId ?>">Update</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#ratesTable').DataTable({
        pageLength: 25,
        scrollX: true,
        autoWidth: false,
        order: [[8, 'desc']], // Default: Sort by Last Updated DESC
        orderCellsTop: true,
        columnDefs: [
            { width: '180px', targets: 0 }, // Room Type
            { width: '90px', targets: 1 },  // Price
            { width: '90px', targets: 2 },  // Weekend
            { width: '90px', targets: 3 },  // Weekday
            { width: '90px', targets: 4 },  // Concession
            { width: '90px', targets: 5 },  // Long Stay
            { width: '130px', targets: 6 }, // Starting
            { width: '130px', targets: 7 }, // Ended On
            { width: '130px', targets: 8 }, // Last Updated
            { width: '100px', targets: 9 }, // Action
            { orderable: false, searchable: false, targets: 9 }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search all fields..."
        },
        initComplete: function() {
            var api = this.api();
            // DataTables scrollX creates .dataTables_scrollHead
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
            }, 300);
        }
    });
});

function toggleForm() {
    var form = document.getElementById('addRateForm');
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}
</script>

</body>
</html>
