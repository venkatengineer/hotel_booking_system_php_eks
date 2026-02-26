<?php
session_start();
include 'config.php';

/* Validate input */
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    $_SESSION['login_error'] = "Please enter username and password.";
    header("Location: login.php");
    exit();
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = "Fields cannot be empty.";
    header("Location: login.php");
    exit();
}

/* Fetch user securely */
$stmt = $conn->prepare("
    SELECT user_id, username, password_hash, role
    FROM tbl_users
    WHERE username = ? AND is_active = 1
");

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password_hash'])) {

        /* Normalize role to lowercase */
        /* T */
        $role = strtolower(trim($user['role']));

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $role;
        
        /* Generate CSRF Token */
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }

        header("Location: dashboard.php");
        exit();

    } else {
        $_SESSION['login_error'] = "Invalid password.";
        header("Location: login.php");
        exit();
    }

} else {
    $_SESSION['login_error'] = "User not found or inactive.";
    header("Location: login.php");
    exit();
}

$stmt->close();
$conn->close();
?>
