<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/index.php');
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    // Only delete if the task actually belongs to this user
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
}

header('Location: my_tasks.php?msg=Task deleted successfully');
exit;
?>