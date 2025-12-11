<?php
// start session for all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DB connection
$host = 'localhost';
$user = 'root';      // change if your MySQL username is different
$pass = '';          // change if your MySQL password is not empty
$db   = 'codesprint_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// simple helper functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_user() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// flash message helpers
function set_flash($message, $type = 'success') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
