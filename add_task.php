<?php
$activePage = 'add_task';
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

    if (empty($title)) {
        $error = 'Title is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO tasks (title, description, due_date, status, user_id) 
                                 VALUES (:title, :description, :due_date, :status, :user_id)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':due_date', $due_date);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();

        header('Location: my_tasks.php?msg=Task added successfully');
        exit;
    }
}

// Quick stats to make the side panel feel useful, not just decorative
$totalStmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = :user_id");
$totalStmt->execute([':user_id' => $_SESSION['user_id']]);
$totalTaskCount = (int)$totalStmt->fetchColumn();

include 'includes/header.php';
?>

<div class="page-header-row">
    <h2>Add New Task</h2>
</div>

<div class="add-task-layout">

    <!-- Left: the actual form -->
    <div class="form-container">
        <?php if ($error): ?>
            <p class="error-msg"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="add_task.php" id="taskForm">
            <label for="title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                </svg>
                Title
            </label>
            <input type="text" id="title" name="title" placeholder="e.g. Finish project proposal" required autofocus oninput="updatePreview()">

            <label for="description">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="21" y1="10" x2="3" y2="10"></line>
                    <line x1="21" y1="6" x2="3" y2="6"></line>
                    <line x1="21" y1="14" x2="3" y2="14"></line>
                    <line x1="21" y1="18" x2="3" y2="18"></line>
                </svg>
                Description
            </label>
            <textarea id="description" name="description" rows="5" placeholder="Add any extra details or notes about this task..." oninput="updatePreview()"></textarea>

            <div class="form-row">
                <div class="form-col">
                    <label for="due_date">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        Due Date
                    </label>
                    <input type="date" id="due_date" name="due_date" oninput="updatePreview()">
                </div>

                <div class="form-col">
                    <label for="status">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        Status
                    </label>
                    <select id="status" name="status" onchange="updatePreview()">
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Task</button>
                <a href="my_tasks.php" class="btn btn-small">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Right: live preview + quick tips -->
    <div class="add-task-side">
        <div class="card preview-card">
            <div class="card-header">
                <h3>Preview</h3>
            </div>
            <div class="task-preview-box">
                <div class="task-preview-title" id="previewTitle">Untitled Task</div>
                <div class="task-preview-desc" id="previewDesc">Your description will show up here...</div>
                <div class="task-preview-footer">
                    <span class="task-preview-date" id="previewDate">No due date set</span>
                    <span class="status-badge status-pending" id="previewStatus">Pending</span>
                </div>
            </div>
        </div>

        <div class="card tips-card">
            <div class="card-header">
                <h3>Quick Tips</h3>
            </div>
            <ul class="tips-list">
                <li>Keep titles short and specific so they're easy to scan later.</li>
                <li>Use the description for context, links, or sub-steps.</li>
                <li>Setting a due date lets it show up in your Schedule and notifications.</li>
            </ul>
        </div>

        <div class="card stat-card">
            <span class="stat-number"><?php echo $totalTaskCount; ?></span>
            <span class="stat-label">tasks tracked so far</span>
        </div>
    </div>

</div>

<script>
    function updatePreview() {
        const title = document.getElementById('title').value.trim();
        const desc = document.getElementById('description').value.trim();
        const dueDate = document.getElementById('due_date').value;
        const status = document.getElementById('status').value;

        document.getElementById('previewTitle').textContent = title || 'Untitled Task';
        document.getElementById('previewDesc').textContent = desc || 'Your description will show up here...';

        const dateEl = document.getElementById('previewDate');
        if (dueDate) {
            const d = new Date(dueDate + 'T00:00:00');
            dateEl.textContent = d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
        } else {
            dateEl.textContent = 'No due date set';
        }

        const statusEl = document.getElementById('previewStatus');
        statusEl.textContent = status;
        statusEl.className = 'status-badge ' + (status === 'Pending' ? 'status-pending' : 'status-completed');
    }
    </script>
    <?php include 'includes/footer.php'; ?>
