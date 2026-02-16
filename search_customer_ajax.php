<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

/* CHECK LOGIN */
if (!isset($_SESSION['user_id'])) {
    exit();
}

/* GET SEARCH TEXT (PHP 5 SAFE WAY) */
$search = '';
if (isset($_GET['q'])) {
    $search = trim($_GET['q']);
}

if ($search == '') {
    exit();
}

/* PREPARE QUERY */
if (ctype_digit($search)) {

    // Aadhaar / Passport search
    $sql = "SELECT customer_id, full_name, aadhaar_no, passport_no
            FROM tbl_customers
            WHERE TRIM(aadhaar_no) LIKE CONCAT(?, '%')
               OR TRIM(passport_no) LIKE CONCAT(?, '%')
            ORDER BY full_name ASC
            LIMIT 10";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        exit();
    }

    $stmt->bind_param("ss", $search, $search);

} else {

    // Name search
    $like = "%" . $search . "%";

    $sql = "SELECT customer_id, full_name, aadhaar_no, passport_no
            FROM tbl_customers
            WHERE full_name LIKE ?
            ORDER BY full_name ASC
            LIMIT 10";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        exit();
    }

    $stmt->bind_param("s", $like);
}

/* EXECUTE */
$stmt->execute();

/* PHP 5 DOES NOT SUPPORT get_result(), so use bind_result */
$stmt->bind_result($customer_id, $full_name, $aadhaar_no, $passport_no);

$found = false;

while ($stmt->fetch()) {
    $found = true;

    echo "<div class='suggestion' data-id='" . intval($customer_id) . "'>";
    echo "<strong>" . htmlspecialchars($full_name) . "</strong><br>";
    echo "<small>Aadhaar: " . htmlspecialchars($aadhaar_no) .
         " | Passport: " . htmlspecialchars($passport_no) . "</small>";
    echo "</div>";
}

if (!$found) {
    echo "<div class='suggestion'>No matching customers found</div>";
}

$stmt->close();
$conn->close();
?>
