<?php
require_once 'config/database.php';

// CREATE Project
if (isset($_POST['create_project'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $goal_amount = $_POST['goal_amount'];
    $category_id = $_POST['category_id'];
    
    $stmt = $pdo->prepare("INSERT INTO project (title, description, goal_amount, category_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $goal_amount, $category_id]);
    header("Location: index.php");
    exit;
}

// UPDATE Project
if (isset($_POST['update_project'])) {
    $id = $_POST['id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $goal_amount = $_POST['goal_amount'];
    $category_id = $_POST['category_id'];

    $stmt = $pdo->prepare("UPDATE project SET title = ?, description = ?, goal_amount = ?, category_id = ? WHERE id = ?");
    $stmt->execute([$title, $description, $goal_amount, $category_id, $id]);
    header("Location: index.php");
    exit;
}

// DELETE Project
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM project WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php");
    exit;
}

// READ Projects with Category Name JOIN
$sql = "SELECT p.*, c.name AS category_name 
        FROM project p 
        JOIN project_category c ON p.category_id = c.id 
        ORDER BY p.id DESC";
$projects = $pdo->query($sql)->fetchAll();

// READ Categories for select options
$categories = $pdo->query("SELECT * FROM project_category")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Projects - Crowdfunding</title>
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
        <div style="display:flex; justify-style:space-between; align-items:center;">
            <h1 class="page-title">Crowdfunding Projects</h1>
            <button class="btn btn-primary" onclick="openModal('addProjectModal')">+ Create Project</button>
        </div>

        <div class="glass-table-container">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Goal</th>
                        <th>Raised</th>
                        <th>Progress</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $proj): 
                        $pct = $proj['goal_amount'] > 0 ? min(100, round(($proj['raised_amount'] / $proj['goal_amount']) * 100)) : 0;
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($proj['title']) ?></strong></td>
                        <td><?= htmlspecialchars($proj['category_name']) ?></td>
                        <td>$<?= number_format($proj['goal_amount'], 2) ?></td>
                        <td>$<?= number_format($proj['raised_amount'], 2) ?></td>
                        <td style="width: 150px;">
                            <span><?= $pct ?>%</span>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: <?= $pct ?>%;"></div>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-warning" onclick="editProject(<?= $proj['id'] ?>, '<?= addslashes(htmlspecialchars($proj['title'])) ?>', '<?= addslashes(htmlspecialchars($proj['description'])) ?>', <?= $proj['goal_amount'] ?>, <?= $proj['category_id'] ?>)">Edit</button>
                            <a href="index.php?delete=<?= $proj['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete project?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal CREATE Project -->
    <div id="addProjectModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('addProjectModal')">&times;</span>
            <h2>New Project</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Project Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Goal Amount ($)</label>
                    <input type="number" step="0.01" name="goal_amount" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" required></textarea>
                </div>
                <button type="submit" name="create_project" class="btn btn-primary">Create Project</button>
            </form>
        </div>
    </div>

    <!-- Modal EDIT Project -->
    <div id="editProjectModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('editProjectModal')">&times;</span>
            <h2>Edit Project</h2>
            <form method="POST">
                <input type="hidden" name="id" id="edit_p_id">
                <div class="form-group">
                    <label>Project Title</label>
                    <input type="text" name="title" id="edit_p_title" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" id="edit_p_category" required>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Goal Amount ($)</label>
                    <input type="number" step="0.01" name="goal_amount" id="edit_p_goal" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_p_description" rows="3" required></textarea>
                </div>
                <button type="submit" name="update_project" class="btn btn-primary">Update Project</button>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        function editProject(id, title, description, goal, categoryId) {
            document.getElementById('edit_p_id').value = id;
            document.getElementById('edit_p_title').value = title;
            document.getElementById('edit_p_description').value = description;
            document.getElementById('edit_p_goal').value = goal;
            document.getElementById('edit_p_category').value = categoryId;
            openModal('editProjectModal');
        }
    </script>
</body>
</html>