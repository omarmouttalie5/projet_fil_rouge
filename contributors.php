<?php
require_once 'config/database.php';

// 1. CREATE Contribution (With Safe Transaction)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_contribution'])) {
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $amount     = floatval($_POST['amount']);
    $project_id = intval($_POST['project_id']);
    
    if (!empty($full_name) && !empty($email) && $amount > 0 && $project_id > 0) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO contributeur (full_name, email, amount, project_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $amount, $project_id]);
            
            $updateStmt = $pdo->prepare("UPDATE project SET raised_amount = raised_amount + ? WHERE id = ?");
            $updateStmt->execute([$amount, $project_id]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Transaction Failed: " . htmlspecialchars($e->getMessage()));
        }
    }
    header("Location: contributors.php");
    exit;
}

// 2. UPDATE Contribution (Recalculating Dynamic Totals)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contribution'])) {
    $id         = intval($_POST['id']);
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $new_amount = floatval($_POST['amount']);
    $project_id = intval($_POST['project_id']);

    if ($id > 0 && !empty($full_name) && $new_amount > 0) {
        try {
            $pdo->beginTransaction();

            $oldStmt = $pdo->prepare("SELECT amount FROM contributeur WHERE id = ?");
            $oldStmt->execute([$id]);
            $old_amount = floatval($oldStmt->fetchColumn());

            $diff = $new_amount - $old_amount;

            $stmt = $pdo->prepare("UPDATE contributeur SET full_name = ?, email = ?, amount = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $new_amount, $id]);

            $updateStmt = $pdo->prepare("UPDATE project SET raised_amount = raised_amount + ? WHERE id = ?");
            $updateStmt->execute([$diff, $project_id]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Transaction Failed: " . htmlspecialchars($e->getMessage()));
        }
    }
    header("Location: contributors.php");
    exit;
}

// 3. DELETE Contribution
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT amount, project_id FROM contributeur WHERE id = ?");
            $stmt->execute([$id]);
            $contrib = $stmt->fetch();

            if ($contrib) {
                $del = $pdo->prepare("DELETE FROM contributeur WHERE id = ?");
                $del->execute([$id]);

                $updateStmt = $pdo->prepare("UPDATE project SET raised_amount = raised_amount - ? WHERE id = ?");
                $updateStmt->execute([$contrib['amount'], $contrib['project_id']]);
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Transaction Failed: " . htmlspecialchars($e->getMessage()));
        }
    }
    header("Location: contributors.php");
    exit;
}

// 4. READ Contributions
$sql = "SELECT c.*, p.title AS project_title 
        FROM contributeur c 
        INNER JOIN project p ON c.project_id = p.id 
        ORDER BY c.id DESC";
$contributions = $pdo->query($sql)->fetchAll();

// 5. READ Projects for Dropdown
$projects = $pdo->query("SELECT id, title FROM project ORDER BY title ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contributors - Crowdfunding</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="glass-nav">
        <nav class="container">
            <div class="logo">Crowd<span>Fund</span></div>
            <ul>
                <li><a href="index.php">Projects</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="contributors.php" class="active">Contributors</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <div class="page-header">
            <h1 class="page-title">Project Contributors</h1>
            <button class="btn btn-primary" onclick="openModal('addContribModal')">+ Add Contribution</button>
        </div>

        <?php if (empty($projects)): ?>
            <div class="alert-warning">
                ⚠️ <strong>Note:</strong> No active projects found. You must <a href="index.php" style="color:#fff; text-decoration:underline;">create a project</a> before recording contributions.
            </div>
        <?php endif; ?>

        <div class="glass-table-container">
            <table>
                <thead>
                    <tr>
                        <th>Contributor</th>
                        <th>Email</th>
                        <th>Assigned Project</th>
                        <th>Amount Pledged</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contributions)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted);">No contributions recorded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contributions as $c): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($c['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['project_title']) ?></td>
                            <td style="color: var(--light-tone); font-weight: 600;">$<?= number_format($c['amount'], 2) ?></td>
                            <td>
                                <button class="btn btn-warning" onclick="editContrib(<?= $c['id'] ?>, '<?= addslashes(htmlspecialchars($c['full_name'])) ?>', '<?= addslashes(htmlspecialchars($c['email'])) ?>', <?= $c['amount'] ?>, <?= $c['project_id'] ?>)">Edit</button>
                                <a href="contributors.php?delete=<?= $c['id'] ?>" class="btn btn-danger" onclick="return confirm('Cancel contribution?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal: CREATE Contribution -->
    <div id="addContribModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('addContribModal')">&times;</span>
            <h2>New Contribution Pledge</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="john@example.com">
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
                    <label>Contribution Amount ($)</label>
                    <input type="number" step="0.01" name="amount" required placeholder="100.00">
                </div>
                <button type="submit" name="create_contribution" class="btn btn-primary" <?= empty($projects) ? 'disabled' : '' ?>>Submit Contribution</button>
            </form>
        </div>
    </div>

    <!-- Modal: EDIT Contribution -->
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
                    <label>Email Address</label>
                    <input type="email" name="email" id="edit_c_email" required>
                </div>
                <div class="form-group">
                    <label>Contribution Amount ($)</label>
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