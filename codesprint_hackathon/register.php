<?php
require 'config.php';

if (is_logged_in() && is_user()) {
    redirect('user/dashboard.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = trim($_POST['full_name'] ?? '');      // team leader
    $member2     = trim($_POST['member2'] ?? '');
    $member3     = trim($_POST['member3'] ?? '');
    $member4     = trim($_POST['member4'] ?? '');
    $username    = trim($_POST['username'] ?? '');
    $password    = trim($_POST['password'] ?? '');
    $confirm     = trim($_POST['confirm'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $team_label  = trim($_POST['team_label'] ?? '');     // optional team name

    if ($full_name === '' || $member2 === '' || $username === '' || $password === '' || $email === '' || $phone === '') {
        $errors[] = 'Please fill in all required fields (*). Team leader and at least Member 2 are required.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Password and confirm password do not match.';
    }

    if (empty($errors)) {
        // check unique username
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Username is already taken.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        // Build team members info string for storage in team_name column
        $members = [];
        $members[] = 'Leader: ' . $full_name;
        $members[] = 'Member 2: ' . $member2;
        if ($member3 !== '') $members[] = 'Member 3: ' . $member3;
        if ($member4 !== '') $members[] = 'Member 4: ' . $member4;

        $members_text = implode(' | ', $members);

        if ($team_label !== '') {
            $team_info = $team_label . ' (' . $members_text . ')';
        } else {
            $team_info = $members_text;
        }

        $role        = 'user';
        $institution = ''; // not used anymore

        $stmt = $conn->prepare("INSERT INTO users (username, password, role, full_name, email, phone, institution, team_name) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssssssss', $username, $password, $role, $full_name, $email, $phone, $institution, $team_info);
        if ($stmt->execute()) {
            $success = 'Team registered successfully! You can now login using the chosen username and password.';
        } else {
            $errors[] = 'Error while registering. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Registration - CodeSprint</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="header">
    <div class="header-title">CodeSprint Hackathon 2025</div>
    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="register.php">Team Registration</a>
    </nav>
</header>

<div class="container">
    <div class="card">
        <h2>Team Registration</h2>
        <p class="text-muted mt-2">
            Register 2–4 members. Team leader and Member 2 are required.  
            Fields marked with * are required.
        </p>

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

        <form method="post" autocomplete="off" class="mt-2">
            <h3 class="mt-2">Team Members</h3>

            <label for="full_name">Team Leader Name *</label>
            <input type="text" name="full_name" id="full_name" required>

            <label for="member2">Member 2 Name *</label>
            <input type="text" name="member2" id="member2" required>

            <label for="member3">Member 3 Name (optional)</label>
            <input type="text" name="member3" id="member3">

            <label for="member4">Member 4 Name (optional)</label>
            <input type="text" name="member4" id="member4">

            <label for="team_label">Team Name (optional, e.g. &quot;Team ByteForce&quot;)</label>
            <input type="text" name="team_label" id="team_label">

            <hr class="mt-3" style="border-color:#374151;">

            <h3 class="mt-2">Login & Contact Details</h3>

            <label for="username">Username * (used for login)</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password *</label>
            <input type="password" name="password" id="password" required>

            <label for="confirm">Confirm Password *</label>
            <input type="password" name="confirm" id="confirm" required>

            <label for="email">Contact Email *</label>
            <input type="email" name="email" id="email" required>

            <label for="phone">Contact Phone *</label>
            <input type="text" name="phone" id="phone" required>

            <button type="submit" class="btn btn-primary mt-3">Register Team</button>
            <a href="index.php" class="btn btn-secondary mt-3">Back to Login</a>
        </form>
    </div>

    <div class="footer">
        CodeSprint Hackathon 2025 &copy; Demo System
    </div>
</div>
</body>
</html>
