<?php
require_once 'config/database.php';

// 1. CREATE Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO project_category (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
    }
    header("Location: categories.php");
    exit;
}

// 2. UPDATE Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    if ($id > 0 && !empty($name)) {
        $stmt = $pdo->prepare("UPDATE project_category SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $id]);
    }
    header("Location: categories.php");
    exit;
}

// 3. DELETE Category
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM project_category WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: categories.php");
    exit;
}

// 4. READ Categories
$stmt = $pdo->query("SELECT * FROM project_category ORDER BY id DESC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Crowdfunding</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="glass-nav">
        <nav class="container">
            <div class="logo">Crowd<span>Fund</span></div>
            <ul>
                <li><a href="index.php">Projects</a></li>
                <li><a href="categories.php" class="active">Categories</a></li>
                <li><a href="contributors.php">Contributors</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <div class="page-header">
            <h1 class="page-title">Project Categories</h1>
            <button class="btn btn-primary" onclick="openModal('addModal')">+ New Category</button>
        </div>

        <div class="glass-table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted);">No categories available. Please add one.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>#<?= $cat['id'] ?></td>
                            <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                            <td><?= htmlspecialchars($cat['description'] ?? 'N/A') ?></td>
                            <td>
                                <button class="btn btn-warning" onclick="editCategory(<?= $cat['id'] ?>, '<?= addslashes(htmlspecialchars($cat['name'])) ?>', '<?= addslashes(htmlspecialchars($cat['description'] ?? '')) ?>')">Edit</button>
                                <a href="categories.php?delete=<?= $cat['id'] ?>" class="btn btn-danger" onclick="return confirm('Deleting this category will also remove all connected projects and contributions. Proceed?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal: CREATE -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('addModal')">&times;</span>
            <h2>Create New Category</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" required placeholder="e.g. Technology, Art">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" placeholder="Short description of this category..."></textarea>
                </div>
                <button type="submit" name="create_category" class="btn btn-primary">Save Category</button>
            </form>
        </div>
    </div>

    <!-- Modal: EDIT -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
            <h2>Edit Category</h2>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description" rows="4"></textarea>
                </div>
                <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        function editCategory(id, name, description) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            openModal('editModal');
        }
    </script>
</body>
</html>