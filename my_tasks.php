<?php $activePage = 'my_tasks'; ?>
<?php include 'includes/header.php'; ?>

<div class="page-header-row">
    <h2>My Tasks</h2>
    <a href="add_task.php" class="btn btn-primary">+ Add Task</a>
</div>

<form method="GET" action="my_tasks.php" class="search-filter-bar">
    <input type="text" name="search" placeholder="Search by title..."
           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">

    <select name="status_filter">
        <option value="">All Statuses</option>
        <option value="Pending" <?php echo (($_GET['status_filter'] ?? '') === 'Pending') ? 'selected' : ''; ?>>Pending</option>
        <option value="Completed" <?php echo (($_GET['status_filter'] ?? '') === 'Completed') ? 'selected' : ''; ?>>Completed</option>
    </select>

    <button type="submit" class="btn btn-primary">Search</button>
    <a href="my_tasks.php" class="btn btn-small">Reset</a>
</form>

<?php
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status_filter'] ?? '');

// Always restrict to the logged-in user's own tasks
$where = ["user_id = :user_id"];
$params = [':user_id' => $_SESSION['user_id']];

if ($search !== '') {
    $where[] = "title LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

if ($statusFilter !== '' && in_array($statusFilter, ['Pending', 'Completed'])) {
    $where[] = "status = :status";
    $params[':status'] = $statusFilter;
}

$whereSql = "WHERE " . implode(" AND ", $where);

$limit = 5;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$countStmt = $conn->prepare("SELECT COUNT(*) FROM tasks $whereSql");
$countStmt->execute($params);
$totalTasks = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalTasks / $limit));

$sql = "SELECT * FROM tasks $whereSql ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

function buildPageLink($pageNum) {
    $params = $_GET;
    $params['page'] = $pageNum;
    return 'my_tasks.php?' . http_build_query($params);
}
?>

<table class="task-table">
    <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($tasks) === 0): ?>
            <tr>
                <td colspan="5" class="empty-state">No tasks found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                    <td><?php echo htmlspecialchars($task['description']); ?></td>
                    <td><?php echo htmlspecialchars($task['due_date']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($task['status']); ?>">
                            <?php echo htmlspecialchars($task['status']); ?>
                        </span>
                    </td>
                   <td class="actions">
                        <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-small">Edit</a>
                        <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn btn-small btn-danger"
                           data-title="<?php echo htmlspecialchars($task['title']); ?>"
                           onclick="return showDeleteModal(event, this)">Delete</a>
                    </td>

                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="<?php echo buildPageLink($page - 1); ?>" class="btn btn-small">&laquo; Prev</a>
    <?php endif; ?>

    <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>

    <?php if ($page < $totalPages): ?>
        <a href="<?php echo buildPageLink($page + 1); ?>" class="btn btn-small">Next &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>