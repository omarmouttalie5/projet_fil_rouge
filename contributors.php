<?php
require_once 'config/database.php';

// CREATE Contribution & Update Raised Amount
if (isset($_POST['create_contribution'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $amount = $_POST['amount'];
    $project_id = $_POST['project_id'];
    
    // Insert Contributor
    $stmt = $pdo->prepare("INSERT INTO contributeur (full_name, email, amount, project_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $amount, $project_id]);
    
    // Automatically increment total project raised_amount
    $updateStmt = $pdo->prepare("UPDATE project SET raised_amount = raised_amount + ? WHERE id = ?");
    $updateStmt->execute([$amount, $project_id]);

    header("Location: contributors.php");
    exit;
}

// UPDATE Contribution (adjusting differences in target project)
if (isset($_POST['update_contribution'])) {
    $id = $_POST['id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $new_amount = $_POST['amount'];
    $project_id = $_POST['project_id'];

    // Get previous contribution amount
    $oldStmt = $pdo->prepare("SELECT amount FROM contributeur WHERE id = ?");
    $oldStmt->execute([$id]);
    $old_amount = $oldStmt->fetchColumn();

    // Calculate difference
    $diff = $new_amount - $old_amount;

    // Update contributor
    $stmt = $pdo->prepare("UPDATE contributeur SET full_name = ?, email = ?, amount = ? WHERE id = ?");
    $stmt->execute([$full_name, $email, $new_amount, $id]);

    // Apply difference to project balance
    $updateStmt = $pdo->prepare("UPDATE project SET raised_amount = raised_amount + ? WHERE id = ?");
    $updateStmt->execute([$diff, $project_id]);

    header("Location: contributors.php");
    exit;
}

// DELETE Contribution & Deduct Project Balance
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Fetch details before deletion
    $stmt = $pdo->prepare("SELECT amount, project_id FROM contributeur WHERE id = ?");
    $stmt->execute([$id]);
    $contrib = $stmt->fetch();

    if ($contrib) {
        // Remove contribution
        $del = $pdo->prepare("DELETE FROM contributeur WHERE id = ?");
        $del->execute([$id]);

        // Deduct from project target
        $updateStmt = $pdo->prepare("UPDATE project SET raised_amount = raised_amount - ? WHERE id = ?");
        $updateStmt->execute([$contrib['amount'], $contrib['project_id']]);
    }

    header("Location: contributors.php");
    exit;
}

// READ Contributions with Project Titles
$sql = "SELECT c.*, p.title AS project_title 
        FROM contributeur c 
        JOIN project p ON c.project_id = p.id 
        ORDER BY c.id DESC";
$contributions = $pdo->query($sql)->fetchAll();

// READ Active Projects list
$projects = $pdo->query("SELECT id, title FROM project")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contributors - Crowdfunding</title>
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
            <h1 class="page-title">Project Contributors</h1>
            <button class="btn btn-primary" onclick="openModal('addContribModal')">+ New Contribution</button>
        </div>

        <div class="glass-table-container">
            <table>
                <thead>
                    <tr>
                        <th>Contributor Name</th>
                        <th>Email</th>
                        <th>Project</th>
                        <th>Pledged Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contributions as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['full_name']) ?></strong></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= htmlspecialchars($c['project_title']) ?></td>
                        <td>$<?= number_format($c['amount'], 2) ?></td>
                        <td>
                            <button class="btn btn-warning" onclick="editContrib(<?= $c['id'] ?>, '<?= addslashes(htmlspecialchars($c['full_name'])) ?>', '<?= addslashes(htmlspecialchars($c['email'])) ?>', <?= $c['amount'] ?>, <?= $c['project_id'] ?>)">Edit</button>
                            <a href="contributors.php?delete=<?= $c['id'] ?>" class="btn btn-danger" onclick="return confirm('Remove contribution?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal CREATE Contribution -->
    <div id="addContribModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('addContribModal')">&times;</span>
            <h2>New Contribution</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Target Project</label>
                    <select name="project_id" required>
                        <?php foreach($projects as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Amount ($)</label>
                    <input type="number" step="0.01" name="amount" required>
                </div>
                <button type="submit" name="create_contribution" class="btn btn-primary">Pledge Amount</button>
            </form>
        </div>
    </div>

    <!-- Modal EDIT Contribution -->
    <div id="editContribModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('editContribModal')">&times;</span>
            <h2>Edit Contribution</h2>
            <form method="POST">
                <input type="hidden" name="id" id="edit_c_id">
                <input type="hidden" name="project_id" id="edit_c_project_id">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="edit_c_name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_c_email" required>
                </div>
                <div class="form-group">
                    <label>Amount ($)</label>
                    <input type="number" step="0.01" name="amount" id="edit_c_amount" required>
                </div>
                <button type="submit" name="update_contribution" class="btn btn-primary">Update Contribution</button>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        function editContrib(id, name, email, amount, projectId) {
            document.getElementById('edit_c_id').value = id;
            document.getElementById('edit_c_name').value = name;
            document.getElementById('edit_c_email').value = email;
            document.getElementById('edit_c_amount').value = amount;
            document.getElementById('edit_c_project_id').value = projectId;
            openModal('editContribModal');
        }
    </script>
</body>
</html>