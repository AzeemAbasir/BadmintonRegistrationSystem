<?php
require '../config.php';

if (!is_logged_in() || !is_user()) {
    redirect('../index.php');
}

$userId = $_SESSION['user_id'];

// get all categories with counts and whether user registered
$sql = "SELECT c.*,
        (SELECT COUNT(*) FROM registrations r WHERE r.category_id = c.id) AS current_count,
        (SELECT COUNT(*) FROM registrations r2 WHERE r2.category_id = c.id AND r2.user_id = ?) AS user_registered
        FROM categories c
        ORDER BY c.id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$categories = $stmt->get_result();

// get user's registrations list
$sql2 = "SELECT c.name, c.description, c.quota, r.registered_at
         FROM registrations r
         JOIN categories c ON r.category_id = c.id
         WHERE r.user_id = ?
         ORDER BY r.registered_at DESC";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param('i', $userId);
$stmt2->execute();
$myRegs = $stmt2->get_result();

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - CodeSprint</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<header class="header">
    <div class="header-title">Participant Panel - CodeSprint</div>
    <nav class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <?php if ($flash): ?>
        <div class="flash flash-<?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</h2>
        <p class="mt-2">
            Choose your hackathon track and register. You can join more than one track as long as the quota is not full.
        </p>
    </div>

    <div class="card">
        <h2>Available Tracks</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>Track</th>
                    <th>Description</th>
                    <th>Quota</th>
                    <th>Current</th>
                    <th>Remaining</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($c = $categories->fetch_assoc()): 
                    $remaining = $c['quota'] - $c['current_count'];
                    if ($remaining < 0) $remaining = 0;
                    $isFull = $c['current_count'] >= $c['quota'];
                    $already = $c['user_registered'] > 0;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($c['description'])); ?></td>
                        <td><?php echo $c['quota']; ?></td>
                        <td><?php echo $c['current_count']; ?></td>
                        <td><?php echo $remaining; ?></td>
                        <td>
                            <?php if ($already): ?>
                                <span class="badge">Registered</span>
                            <?php elseif ($isFull): ?>
                                <span class="badge" style="background:#7f1d1d;color:#fee2e2;">Full</span>
                            <?php else: ?>
                                <form action="register_event.php" method="post" style="display:inline;">
                                    <input type="hidden" name="category_id" value="<?php echo $c['id']; ?>">
                                    <button type="submit" class="btn btn-small btn-primary">Register</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>Your Registrations</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>Track</th>
                    <th>Registered At</th>
                    <th>Description</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($myRegs->num_rows === 0): ?>
                    <tr><td colspan="3">You have not registered for any track yet.</td></tr>
                <?php else: ?>
                    <?php while ($r = $myRegs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['name']); ?></td>
                            <td><?php echo htmlspecialchars($r['registered_at']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($r['description'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        CodeSprint Hackathon 2025 &copy; Participant Panel
    </div>
</div>
</body>
</html>
