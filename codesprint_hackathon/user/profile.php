<?php
require '../config.php';

if (!is_logged_in() || !is_user()) {
    redirect('../index.php');
}

$userId = $_SESSION['user_id'];

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $team_name = trim($_POST['team_name'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $confirm   = trim($_POST['confirm'] ?? '');

    if ($full_name === '' || $email === '' || $phone === '') {
        $errors[] = 'Full name, email and phone are required.';
    }

    if ($password !== '' || $confirm !== '') {
        if ($password !== $confirm) {
            $errors[] = 'New password and confirm password do not match.';
        }
    }

    if (empty($errors)) {
        if ($password !== '') {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, team_name=?, password=? WHERE id=?");
            $stmt->bind_param('sssssi', $full_name, $email, $phone, $team_name, $password, $userId);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, team_name=? WHERE id=?");
            $stmt->bind_param('ssssi', $full_name, $email, $phone, $team_name, $userId);
        }

        if ($stmt->execute()) {
            $success = 'Profile updated successfully.';
        } else {
            $errors[] = 'Error updating profile.';
        }
        $stmt->close();
    }
}

// get current user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile - CodeSprint</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<header class="header">
    <div class="header-title">Participant Panel - Profile</div>
    <nav class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <div class="card">
        <h2>Your Profile</h2>
        <p class="text-muted mt-2">Username cannot be changed.</p>

        <?php if (!empty($errors)): ?>
            <div class="flash flash-error mt-2">
                <?php foreach ($errors as $e): ?>
                    <div><?php echo htmlspecialchars($e); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="flash flash-success mt-2">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="mt-2">
            <label>Username</label>
            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>

            <label for="full_name">Team Leader Name *</label>
            <input type="text" name="full_name" id="full_name"
                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

            <label for="email">Contact Email *</label>
            <input type="email" name="email" id="email"
                   value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="phone">Contact Phone *</label>
            <input type="text" name="phone" id="phone"
                   value="<?php echo htmlspecialchars($user['phone']); ?>" required>

            <label for="team_name">Team Name / Members</label>
            <textarea name="team_name" id="team_name"><?php echo htmlspecialchars($user['team_name']); ?></textarea>

            <hr class="mt-3" style="border-color:#374151;">

            <p class="text-muted mt-2">
                Leave password fields empty if you do not want to change your password.
            </p>

            <label for="password">New Password</label>
            <input type="password" name="password" id="password">

            <label for="confirm">Confirm New Password</label>
            <input type="password" name="confirm" id="confirm">

            <button type="submit" class="btn btn-primary mt-3">Update Profile</button>
        </form>
    </div>

    <div class="footer">
        CodeSprint Hackathon 2025 &copy; Participant Panel
    </div>
</div>
</body>
</html>
