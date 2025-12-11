<?php
require 'config.php';

if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('user/dashboard.php');
    }
}

// handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        set_flash('Please enter both username and password.', 'error');
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // simple plain-text comparison (for assignment only)
            if ($row['password'] === $password) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];

                if ($row['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('user/dashboard.php');
                }
            } else {
                set_flash('Invalid password.', 'error');
            }
        } else {
            set_flash('User not found.', 'error');
        }
        $stmt->close();
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CodeSprint Hackathon 2025 - Login</title>
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
        <span class="badge">24-Hour Smart Campus Challenge</span>
        <h1 class="hero-title">Build Smart Campus Solutions.</h1>
        <p class="hero-subtitle">
            Form your team, choose your track and start hacking in CodeSprint Hackathon 2025.
        </p>
        <div class="flex mt-2">
            <a href="register.php" class="btn btn-primary">Register Team</a>
            <a href="#login" class="btn btn-secondary">Login</a>
        </div>
        <p class="mt-3 text-muted">
            Date: 10–11 May 2025 &nbsp; | &nbsp; Venue: Main Hall, UNITEN
        </p>
    </div>

    <div class="card" id="login">
        <h2>Login</h2>

        <?php if ($flash): ?>
            <div class="flash flash-<?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" class="btn btn-primary mt-2">Login</button>
        </form>
        <p class="mt-3 text-muted">
            Don&apos;t have an account? <a href="register.php">Register your team here</a>.
        </p>
    </div>

    <div class="footer">
        Group 6 &copy; Web Programming
    </div>
</div>
</body>
</html>
