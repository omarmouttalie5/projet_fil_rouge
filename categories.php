<?php
require_once 'config/database.php';

// CREATE Category
if (isset($_POST['create_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    $stmt = $pdo->prepare("INSERT INTO project_category (name, description) VALUES (?, ?)");
    $stmt->execute([$name, $description]);
    header("Location: categories.php");
    exit;
}

// UPDATE Category
if (isset($_POST['update_category'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    $stmt = $pdo->prepare("UPDATE project_category SET name = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $description, $id]);
    header("Location: categories.php");
    exit;
}

// DELETE Category
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM project_category WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: categories.php");
    exit;
}

// READ Categories
$categories = $pdo->query("SELECT * FROM project_category ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Categories - Crowdfunding</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="glass-nav">
        <nav class="container">
            <div class="logo">CrowdFund</div>
            <ul>
                <li><a href="index.php">Projects</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="contributors.php">Contributors</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h1 class="page-title">Project Categories</h1>
            <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Category</button>
        </div>

        <div class="glass-table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= $cat['id'] ?></td>
                        <td><?= htmlspecialchars($cat['name']) ?></td>
                        <td><?= htmlspecialchars($cat['description']) ?></td>
                        <td>
                            <button class="btn btn-warning" onclick="editCategory(<?= $cat['id'] ?>, '<?= addslashes(htmlspecialchars($cat['name'])) ?>', '<?= addslashes(htmlspecialchars($cat['description'])) ?>')">Edit</button>
                            <a href="categories.php?delete=<?= $cat['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete category?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal CREATE -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('addModal')">&times;</span>
            <h2>New Category</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"></textarea>
                </div>
                <button type="submit" name="create_category" class="btn btn-primary">Save Category</button>
            </form>
        </div>
    </div>

    <!-- Modal EDIT -->
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