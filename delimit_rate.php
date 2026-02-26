session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], 'admin') !== 0) {
    header("Location: dashboard.php");
    exit();
}

if (!isset($_POST['rate_id'])) {
    die("Missing ID");
}

$id = (int)$_POST['rate_id'];

$stmt = $conn->prepare("
UPDATE tbl_rates
SET effective_to = CURDATE()
WHERE rate_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: view_rates.php");
