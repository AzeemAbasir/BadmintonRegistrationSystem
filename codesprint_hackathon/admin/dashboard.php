<?php
require '../config.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../index.php');
}

// counts
$totalUsers = 0;
$totalRegs = 0;
$totalCats = 0;

$res = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'user'");
if ($row = $res->fetch_assoc()) $totalUsers = (int)$row['c'];

$res = $conn->query("SELECT COUNT(*) AS c FROM registrations");
if ($row = $res->fetch_assoc()) $totalRegs = (int)$row['c'];

$res = $conn->query("SELECT COUNT(*) AS c FROM categories");
if ($row = $res->fetch_assoc()) $totalCats = (int)$row['c'];

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - CodeSprint</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<header class="header">
    <div class="header-title">Admin Panel - CodeSprint Hackathon</div>
    <nav class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="categories.php">Manage Tracks</a>
        <a href="participants.php">Participants</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <?php if ($flash): ?>
        <div class="flash flash-<?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="flex">
        <div class="card" style="flex:1;">
            <h2>Total Participants</h2>
            <p class="hero-title"><?php echo $totalUsers; ?></p>
            <p class="text-muted">Registered user accounts.</p>
        </div>
        <div class="card" style="flex:1;">
            <h2>Total Registrations</h2>
            <p class="hero-title"><?php echo $totalRegs; ?></p>
            <p class="text-muted">Track registrations.</p>
        </div>
        <div class="card" style="flex:1;">
            <h2>Hackathon Tracks</h2>
            <p class="hero-title"><?php echo $totalCats; ?></p>
            <p class="text-muted">Active categories.</p>
        </div>
    </div>

    <div class="card">
        <h2>Welcome, Admin</h2>
        <p class="mt-2">
            Use this panel to manage hackathon tracks (categories) and view participants.
            You can also monitor quotas to ensure each track does not exceed capacity.
        </p>
    </div>

    <div class="footer">
        CodeSprint Hackathon 2025 &copy; Admin Panel
    </div>
</div>
</body>
</html>
