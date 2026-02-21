<?php
session_start();
include 'config.php';

$asset_id = (int)$_POST['asset_id'];
$new_rate = (float)$_POST['rate_per_day'];
$new_from = $_POST['effective_from'];

/* ================= GET LATEST RECORD FOR THIS ROOM ================= */
$res = $conn->query("
SELECT effective_from, effective_to
FROM tbl_rates
WHERE asset_id = $asset_id
ORDER BY effective_from DESC, rate_id DESC
LIMIT 1
");

$row = $res->fetch_assoc();
$last_from = $row ? $row['effective_from'] : null;
$last_to   = $row ? $row['effective_to'] : null;

/* ================= VALIDATION ================= */
if ($last_from) {
    if (strtotime($new_from) < strtotime($last_from)) {
        die("New effective date ($new_from) cannot be before the current rate's start date ($last_from).");
    }
}

/* ================= DELIMITING LOGIC ================= */
/* 
   If a new rate starts on date D:
   1. Find the current active record (ending in 2036-12-31).
   2. If D > last_from:
      - Update the old record's effective_to to (D - 1 day).
      - This preserves the old rate for all past bookings.
   3. If D == last_from:
      - This is a "correction" of today's or the latest rate.
      - We can either update the existing record or delimit the one before it.
      - For simplicity and traceability, we'll delimit any previous one and insert this.
      - But if there's an exact match on start date, we'll UPDATE it to avoid clutter.
*/

if ($last_from && $new_from == $last_from) {
    // Correcting or Reactivating the current rate record
    $stmt = $conn->prepare("UPDATE tbl_rates SET rate_per_day = ?, effective_to = '2036-12-31' WHERE asset_id = ? AND effective_from = ?");
    $stmt->bind_param("dis", $new_rate, $asset_id, $new_from);
    $stmt->execute();
} else {
    // Closing the previous record
    if ($last_from) {
        $yesterday = date('Y-m-d', strtotime($new_from . ' -1 day'));
        $conn->query("
            UPDATE tbl_rates 
            SET effective_to = '$yesterday' 
            WHERE asset_id = $asset_id AND effective_to = '2036-12-31'
        ");
    }
    
    // Inserting the new record
    $stmt = $conn->prepare("INSERT INTO tbl_rates (asset_id, rate_per_day, effective_from, effective_to) VALUES (?, ?, ?, '2036-12-31')");
    $stmt->bind_param("ids", $asset_id, $new_rate, $new_from);
    $stmt->execute();
}

/* ================= REDIRECT ================= */
header("Location: view_rates.php");
exit();
?>
