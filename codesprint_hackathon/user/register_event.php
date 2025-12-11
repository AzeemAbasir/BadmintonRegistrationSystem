<?php
require '../config.php';

if (!is_logged_in() || !is_user()) {
    redirect('../index.php');
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

$categoryId = (int)($_POST['category_id'] ?? 0);
if ($categoryId <= 0) {
    set_flash('Invalid track selected.', 'error');
    redirect('dashboard.php');
}

// check if already registered
$stmt = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND category_id = ?");
$stmt->bind_param('ii', $userId, $categoryId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    set_flash('You are already registered for this track.', 'error');
    redirect('dashboard.php');
}
$stmt->close();

// check quota
$stmt = $conn->prepare("SELECT quota,
       (SELECT COUNT(*) FROM registrations r WHERE r.category_id = c.id) AS current_count
       FROM categories c WHERE c.id = ?");
$stmt->bind_param('i', $categoryId);
$stmt->execute();
$result = $stmt->get_result();

if (!($cat = $result->fetch_assoc())) {
    $stmt->close();
    set_flash('Track not found.', 'error');
    redirect('dashboard.php');
}
$stmt->close();

if ((int)$cat['current_count'] >= (int)$cat['quota']) {
    set_flash('Sorry, this track is already full.', 'error');
    redirect('dashboard.php');
}

// insert registration
$stmt = $conn->prepare("INSERT INTO registrations (user_id, category_id) VALUES (?, ?)");
$stmt->bind_param('ii', $userId, $categoryId);
if ($stmt->execute()) {
    set_flash('Successfully registered for the track!');
} else {
    set_flash('Error registering for track.', 'error');
}
$stmt->close();

redirect('dashboard.php');
