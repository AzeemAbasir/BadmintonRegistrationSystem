<?php
require '../config.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../index.php');
}

// handle delete registration
if (isset($_GET['delete_reg'])) {
    $regId = (int)$_GET['delete_reg'];
    if ($regId > 0) {
        $stmt = $conn->prepare("DELETE FROM registrations WHERE id = ?");
        $stmt->bind_param('i', $regId);
        if ($stmt->execute()) {
            set_flash('Registration deleted.');
        } else {
            set_flash('Error deleting registration.', 'error');
        }
        $stmt->close();
    }
    redirect('participants.php');
}

// load categories for filter
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

$selectedCat = (int)($_GET['category_id'] ?? 0);
$search = trim($_GET['q'] ?? '');

$sql = "SELECT r.id AS reg_id, u.full_name, u.username, u.team_name, u.email, u.phone,
               c.name AS category_name
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        JOIN categories c ON r.category_id = c.id
        WHERE 1=1";

$params = [];
$types = '';

if ($selectedCat > 0) {
    $sql .= " AND c.id = ?";
    $types .= 'i';
    $params[] = $selectedCat;
}

if ($search !== '') {
    $sql .= " AND (u.full_name LIKE ? OR u.username LIKE ? OR u.team_name LIKE ? OR c.name LIKE ?)";
    $types .= 'ssss';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY c.name, u.full_name";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Participants - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script>
        function confirmDeleteReg() {
            return confirm('Delete this registration?');
        }
    </script>
</head>
<body>
<header class="header">
    <div class="header-title">Admin Panel - Participants</div>
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

    <div class="card">
        <h2>Search Participants</h2>
        <form method="get" class="mt-2 flex" style="align-items:flex-end;">
            <div style="flex:1;">
                <label for="category_id">Filter by Track</label>
                <select name="category_id" id="category_id">
                    <option value="0">All Tracks</option>
                    <?php while ($c = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $c['id']; ?>" <?php if ($selectedCat === (int)$c['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($c['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="flex:1;">
                <label for="q">Search (leader, username, team, track)</label>
                <input type="text" name="q" id="q" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="participants.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="flex-between">
            <h2>Participant Registrations</h2>
            <span class="text-muted">Total: <?php echo $result->num_rows; ?></span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Team Leader</th>
                    <th>Username</th>
                    <th>Team / Members</th>
                    <th>Contact Email</th>
                    <th>Contact Phone</th>
                    <th>Track</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows === 0): ?>
                    <tr><td colspan="8">No registrations found.</td></tr>
                <?php else: ?>
                    <?php $i=1; while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($row['team_name'])); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td>
                                <a class="btn btn-small btn-secondary"
                                   href="participants.php?delete_reg=<?php echo $row['reg_id']; ?>"
                                   onclick="return confirmDeleteReg();">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        CodeSprint Hackathon 2025 &copy; Admin Panel
    </div>
</div>
</body>
</html>
