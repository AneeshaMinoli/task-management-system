<?php
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /task_manager/auth/index.php');
    exit;
}

// Get THIS user's tasks due within the next 3 days, for the notification bell
$dueSoonStmt = $conn->prepare("SELECT * FROM tasks WHERE status = 'Pending' AND due_date <= CURDATE() + INTERVAL 3 DAY AND user_id = :user_id ORDER BY due_date ASC");
$dueSoonStmt->bindParam(':user_id', $_SESSION['user_id']);
$dueSoonStmt->execute();
$dueSoonTasks = $dueSoonStmt->fetchAll(PDO::FETCH_ASSOC);
$dueSoonCount = count($dueSoonTasks);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <link rel="stylesheet" href="/task_manager/assets/css/style.css">
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.add('light-mode');
        }
    </script>
</head>
<body>
<div class="app-layout">

    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </span>
            <span class="logo-text">TaskManager</span>
        </div>

        <nav class="sidebar-nav">
            <a href="/task_manager/dashboard.php" class="sidebar-link <?php echo ($activePage ?? '') === 'dashboard' ? 'active' : ''; ?>">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9.5 12 3l9 6.5"></path>
                        <path d="M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10"></path>
                    </svg>
                </span>
                Dashboard
            </a>
            <a href="/task_manager/add_task.php" class="sidebar-link <?php echo ($activePage ?? '') === 'add_task' ? 'active' : ''; ?>">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </span>
                Add Task
            </a>
            <a href="/task_manager/my_tasks.php" class="sidebar-link <?php echo ($activePage ?? '') === 'my_tasks' ? 'active' : ''; ?>">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                </span>
                My Tasks
            </a>
        </nav>

<a href="#" class="sidebar-logout" onclick="showLogoutModal(event)">
                    <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </span>
            Logout
        </a>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <h2 class="greeting">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

            <div class="topbar-right">
                <button class="icon-btn" onclick="toggleDarkMode()" title="Toggle theme">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>

                <div class="notification-wrapper">
                    <button class="icon-btn notification-btn" onclick="toggleNotifications(event)" title="Tasks due soon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <?php if ($dueSoonCount > 0): ?>
                            <span class="notif-badge"><?php echo $dueSoonCount; ?></span>
                        <?php endif; ?>
                    </button>

                    <div id="notificationDropdown" class="notification-dropdown">
                        <div class="notification-dropdown-header">Tasks Due Soon</div>
                        <?php if ($dueSoonCount === 0): ?>
                            <div class="notification-empty">Nothing due soon.</div>
                        <?php else: ?>
                            <?php foreach ($dueSoonTasks as $t): ?>
                                <a href="my_tasks.php" class="notification-item">
                                    <span class="notification-item-title"><?php echo htmlspecialchars($t['title']); ?></span>
                                    <span class="notification-item-date"><?php echo date('d M', strtotime($t['due_date'])); ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">