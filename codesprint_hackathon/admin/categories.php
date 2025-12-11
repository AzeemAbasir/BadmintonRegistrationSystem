<?php
require '../config.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../index.php');
}

// handle add / update / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $quota = (int)($_POST['quota'] ?? 0);

    if ($action === 'add') {
        if ($name === '' || $quota <= 0) {
            set_flash('Name and quota are required.', 'error');
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, description, quota) VALUES (?,?,?)");
            $stmt->bind_param('ssi', $name, $description, $quota);
            if ($stmt->execute()) {
                set_flash('Track added successfully.');
            } else {
                set_flash('Error adding track.', 'error');
            }
            $stmt->close();
        }
        redirect('categories.php');
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0 || $name === '' || $quota <= 0) {
            set_flash('Invalid data for update.', 'error');
        } else {
            $stmt = $conn->prepare("UPDATE categories SET name=?, description=?, quota=? WHERE id=?");
            $stmt->bind_param('ssii', $name, $description, $quota, $id);
            if ($stmt->execute()) {
                set_flash('Track updated successfully.');
            } else {
                set_flash('Error updating track.', 'error');
            }
            $stmt->close();
        }
        redirect('categories.php');
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            set_flash('Track deleted.');
        } else {
            set_flash('Error deleting track.', 'error');
        }
        $stmt->close();
    }
    redirect('categories.php');
}

// get all categories with count of registrations
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM registrations r WHERE r.category_id = c.id) AS current_count
        FROM categories c
        ORDER BY c.id ASC";
$result = $conn->query($sql);

$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editCategory = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tracks - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this track? Registrations in this track will also be removed.');
        }
    </script>
</head>
<body>
<header class="header">
    <div class="header-title">Admin Panel - Manage Tracks</div>
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
        <h2><?php echo $editCategory ? 'Edit Track' : 'Add New Track'; ?></h2>
        <form method="post" class="mt-2">
            <input type="hidden" name="action" value="<?php echo $editCategory ? 'update' : 'add'; ?>">
            <?php if ($editCategory): ?>
                <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
            <?php endif; ?>

            <label for="name">Track Name *</label>
            <input type="text" name="name" id="name" required
                   value="<?php echo $editCategory ? htmlspecialchars($editCategory['name']) : ''; ?>">

            <label for="description">Description</label>
            <textarea name="description" id="description"><?php
                echo $editCategory ? htmlspecialchars($editCategory['description']) : '';
            ?></textarea>

            <label for="quota">Quota (max participants) *</label>
            <input type="number" name="quota" id="quota" min="1" required
                   value="<?php echo $editCategory ? (int)$editCategory['quota'] : 20; ?>">

            <button type="submit" class="btn btn-primary mt-2">
                <?php echo $editCategory ? 'Update Track' : 'Add Track'; ?>
            </button>
            <?php if ($editCategory): ?>
                <a href="categories.php" class="btn btn-secondary mt-2">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <div class="flex-between">
            <h2>Existing Tracks</h2>
            <span class="text-muted">Total: <?php echo $result->num_rows; ?></span>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Track Name</th>
                    <th>Description</th>
                    <th>Quota</th>
                    <th>Current</th>
                    <th>Remaining</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): 
                    $remaining = $row['quota'] - $row['current_count'];
                    if ($remaining < 0) $remaining = 0;
                ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
                        <td><?php echo $row['quota']; ?></td>
                        <td><?php echo $row['current_count']; ?></td>
                        <td><?php echo $remaining; ?></td>
                        <td>
                            <a class="btn btn-small btn-secondary" href="categories.php?edit=<?php echo $row['id']; ?>">Edit</a>
                            <a class="btn btn-small btn-secondary" href="categories.php?delete=<?php echo $row['id']; ?>" onclick="return confirmDelete();">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows === 0): ?>
                    <tr><td colspan="7">No tracks found.</td></tr>
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
