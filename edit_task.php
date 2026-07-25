<?php
$activePage = 'my_tasks';
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$error = '';
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: my_tasks.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

    if (empty($title)) {
        $error = 'Title is required.';
    } else {
        $stmt = $conn->prepare("UPDATE tasks 
                                 SET title = :title, description = :description, 
                                     due_date = :due_date, status = :status 
                                 WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':due_date', $due_date);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();

        header('Location: my_tasks.php?msg=Task updated successfully');
        exit;
    }
}

// Only fetch the task if it belongs to THIS user
$stmt = $conn->prepare("SELECT * FROM tasks WHERE id = :id AND user_id = :user_id");
$stmt->bindParam(':id', $id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    header('Location: my_tasks.php');
    exit;
}

include 'includes/header.php';
?>

<div class="form-container">
    <h2>Edit Task</h2>

    <?php if ($error): ?>
        <p class="error-msg"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="edit_task.php?id=<?php echo $task['id']; ?>">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required autofocus>

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($task['description']); ?></textarea>

        <label for="due_date">Due Date</label>
        <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($task['due_date']); ?>">

        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="Pending" <?php echo $task['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="Completed" <?php echo $task['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
        </select>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Task</button>
            <a href="my_tasks.php" class="btn btn-small">Cancel</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>