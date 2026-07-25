<?php $activePage = 'dashboard'; ?>
<?php include 'includes/header.php'; ?>

<?php
// ===== Stats used by both the new stats row and the progress rings card =====
$todayStr = date('Y-m-d');

$totalStmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = :user_id");
$totalStmt->execute([':user_id' => $_SESSION['user_id']]);
$totalCount = (int)$totalStmt->fetchColumn();

$completedStmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE status = 'Completed' AND user_id = :user_id");
$completedStmt->execute([':user_id' => $_SESSION['user_id']]);
$completedCountAll = (int)$completedStmt->fetchColumn();

$pendingCountAll = $totalCount - $completedCountAll;

$overdueStmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE status = 'Pending' AND due_date < :today AND user_id = :user_id");
$overdueStmt->execute([':today' => $todayStr, ':user_id' => $_SESSION['user_id']]);
$overdueCount = (int)$overdueStmt->fetchColumn();
?>

<div class="hero-banner">
    <div class="hero-banner-text">
        <h1>Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <p>Ready to tackle your tasks today?</p>
    </div>
    <div class="hero-banner-image">
        <img src="/task_manager/assets/img/banner-img.png" alt="">
    </div>
</div>

<!-- Quick Stats Row -->
<div class="stats-row">
    <div class="stat-card">
        <span class="stat-number"><?php echo $totalCount; ?></span>
        <span class="stat-label">Total Tasks</span>
    </div>
    <div class="stat-card stat-card-pending">
        <span class="stat-number"><?php echo $pendingCountAll; ?></span>
        <span class="stat-label">Pending</span>
    </div>
    <div class="stat-card stat-card-completed">
        <span class="stat-number"><?php echo $completedCountAll; ?></span>
        <span class="stat-label">Completed</span>
    </div>
    <div class="stat-card stat-card-overdue">
        <span class="stat-number"><?php echo $overdueCount; ?></span>
        <span class="stat-label">Overdue</span>
    </div>
</div>

<div class="dashboard-grid-top">

    <!-- Upcoming Tasks Card -->
    <div class="card upcoming-tasks-card">
        <div class="card-header">
            <h3>Upcoming Tasks</h3>
            <a href="my_tasks.php" class="see-more">See more &rarr;</a>
        </div>
        <?php
        $upcomingStmt = $conn->prepare("SELECT * FROM tasks WHERE status = 'Pending' AND user_id = :user_id ORDER BY due_date ASC LIMIT 5");
        $upcomingStmt->bindParam(':user_id', $_SESSION['user_id']);
        $upcomingStmt->execute();
        $upcomingTasks = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <ul class="mini-task-list">
            <?php if (count($upcomingTasks) === 0): ?>
                <li class="empty-state">No upcoming tasks.</li>
            <?php else: ?>
                <?php foreach ($upcomingTasks as $task): ?>
                    <li class="mini-task-item">
                        <div class="mini-task-info">
                            <span class="mini-task-title"><?php echo htmlspecialchars($task['title']); ?></span>
                            <span class="mini-task-date"><?php echo date('d M Y', strtotime($task['due_date'])); ?></span>
                        </div>
                        <span class="status-badge status-pending">Pending</span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <!-- My Schedule Card (interactive calendar) -->
    <div class="card schedule-card">
        <div class="card-header">
            <h3>My Schedule</h3>
        </div>

        <?php
        // Which month to display: from ?month=YYYY-MM in the URL, or the current month by default
        $monthParam = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            $monthParam = date('Y-m');
        }

        $monthStart = new DateTime($monthParam . '-01');
        $monthLabel = $monthStart->format('F Y');
        $daysInMonth = (int)$monthStart->format('t');
        $firstWeekday = (int)$monthStart->format('N');

        $prevMonth = (clone $monthStart)->modify('-1 month')->format('Y-m');
        $nextMonth = (clone $monthStart)->modify('+1 month')->format('Y-m');
        ?>
        <?php
        $monthTasksStmt = $conn->prepare("SELECT * FROM tasks WHERE due_date BETWEEN :start AND :end AND user_id = :user_id ORDER BY due_date ASC");
        $monthTasksStmt->execute([
            ':start' => $monthStart->format('Y-m-01'),
            ':end'   => $monthStart->format('Y-m-t'),
            ':user_id' => $_SESSION['user_id']
        ]);
        $monthTasksRaw = $monthTasksStmt->fetchAll(PDO::FETCH_ASSOC);

        $tasksByDateMonth = [];
        foreach ($monthTasksRaw as $t) {
            $tasksByDateMonth[$t['due_date']][] = [
                'title' => $t['title'],
                'status' => $t['status']
            ];
        }
        ?>

        <div class="mini-calendar">
            <div class="calendar-month-nav">
                <a href="dashboard.php?month=<?php echo $prevMonth; ?>" class="calendar-nav-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </a>
                <span class="calendar-month-label"><?php echo $monthLabel; ?></span>
                <a href="dashboard.php?month=<?php echo $nextMonth; ?>" class="calendar-nav-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>
            <div class="calendar-grid calendar-weekdays">
                <span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
            </div>
            <div class="calendar-grid">
                <?php for ($i = 1; $i < $firstWeekday; $i++): ?>
                    <span class="calendar-day empty"></span>
                <?php endfor; ?>

                <?php for ($day = 1; $day <= $daysInMonth; $day++):
                    $dateStr = $monthStart->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);
                    $hasTask = isset($tasksByDateMonth[$dateStr]);
                    $isToday = ($dateStr === $todayStr);
                ?>
                    <span class="calendar-day <?php echo $hasTask ? 'has-task' : ''; ?> <?php echo $isToday ? 'is-today' : ''; ?>"
                          onclick="showDayTasks('<?php echo $dateStr; ?>', this)">
                        <?php echo $day; ?>
                    </span>
                <?php endfor; ?>
            </div>
        </div>

        <div id="selectedDayTasks" class="selected-day-tasks"></div>
    </div>

</div>

<!-- Progress Rings Card (full width, horizontal) -->
<div class="card rings-card rings-card-horizontal">
    <?php
        $radius = 52;
        $circumference = 2 * M_PI * $radius;
        $pendingPercent = $totalCount > 0 ? round(($pendingCountAll / $totalCount) * 100) : 0;
        $completedPercent = $totalCount > 0 ? round(($completedCountAll / $totalCount) * 100) : 0;
        $pendingOffset = $circumference - ($circumference * $pendingPercent / 100);
        $completedOffset = $circumference - ($circumference * $completedPercent / 100);
        ?>

        <div class="progress-ring-item">
            <svg class="progress-ring-svg" viewBox="0 0 120 120">
                <circle class="ring-bg" cx="60" cy="60" r="<?php echo $radius; ?>"></circle>
                <circle class="ring-fill" cx="60" cy="60" r="<?php echo $radius; ?>"
                        stroke="var(--accent-pink)"
                        stroke-dasharray="<?php echo $circumference; ?>"
                        stroke-dashoffset="<?php echo $pendingOffset; ?>"></circle>
            </svg>
            <div class="progress-ring-center">
                <span class="progress-ring-value"><?php echo $pendingPercent; ?>%</span>
                <span class="progress-ring-label">Pending</span>
            </div>
        </div>

        <div class="progress-ring-item">
            <svg class="progress-ring-svg" viewBox="0 0 120 120">
                <circle class="ring-bg" cx="60" cy="60" r="<?php echo $radius; ?>"></circle>
                <circle class="ring-fill" cx="60" cy="60" r="<?php echo $radius; ?>"
                        stroke="var(--accent-green)"
                        stroke-dasharray="<?php echo $circumference; ?>"
                        stroke-dashoffset="<?php echo $completedOffset; ?>"></circle>
            </svg>
            <div class="progress-ring-center">
                <span class="progress-ring-value"><?php echo $completedPercent; ?>%</span>
                <span class="progress-ring-label">Completed</span>
            </div>
        </div>

        <div class="progress-ring-item">
            <svg class="progress-ring-svg" viewBox="0 0 120 120">
                <circle class="ring-bg" cx="60" cy="60" r="<?php echo $radius; ?>"></circle>
                <circle class="ring-fill" cx="60" cy="60" r="<?php echo $radius; ?>"
                        stroke="var(--accent-purple-light)"
                        stroke-dasharray="<?php echo $circumference; ?>"
                        stroke-dashoffset="0"></circle>
            </svg>
            <div class="progress-ring-center">
                <span class="progress-ring-value"><?php echo $totalCount; ?></span>
                <span class="progress-ring-label">Overall</span>
            </div>
        </div>
    </div>

<!-- Recent Activity Card -->
<div class="card recent-activity-card">
    <div class="card-header">
        <h3>Recent Activity</h3>
        <a href="my_tasks.php" class="see-more">See more &rarr;</a>
    </div>
    <?php
    // Most recently added tasks (assumes higher id = more recently created)
    $recentStmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = :user_id ORDER BY id DESC LIMIT 5");
    $recentStmt->execute([':user_id' => $_SESSION['user_id']]);
    $recentTasks = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <ul class="mini-task-list">
        <?php if (count($recentTasks) === 0): ?>
            <li class="empty-state">No tasks yet.</li>
        <?php else: ?>
            <?php foreach ($recentTasks as $task): ?>
                <li class="mini-task-item">
                    <div class="mini-task-info">
                        <span class="mini-task-title"><?php echo htmlspecialchars($task['title']); ?></span>
                        <span class="mini-task-date">Due <?php echo date('d M Y', strtotime($task['due_date'])); ?></span>
                    </div>
                    <span class="status-badge <?php echo $task['status'] === 'Pending' ? 'status-pending' : 'status-completed'; ?>">
                        <?php echo htmlspecialchars($task['status']); ?>
                    </span>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<script>
    const tasksByDate = <?php echo json_encode($tasksByDateMonth); ?>;

    function showDayTasks(dateStr, el) {
        document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
        if (el) el.classList.add('selected');

        const container = document.getElementById('selectedDayTasks');
        container.innerHTML = '';

        const tasks = tasksByDate[dateStr] || [];
        const dateObj = new Date(dateStr + 'T00:00:00');
        const formatted = dateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

        const label = document.createElement('div');
        label.className = 'selected-day-label';
        label.textContent = formatted;
        container.appendChild(label);

        if (tasks.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = 'No tasks on this day.';
            container.appendChild(empty);
            return;
        }

        tasks.forEach(t => {
            const row = document.createElement('div');
            row.className = 'selected-day-task';

            const titleSpan = document.createElement('span');
            titleSpan.textContent = t.title;

            const statusSpan = document.createElement('span');
            statusSpan.className = 'status-badge ' + (t.status === 'Pending' ? 'status-pending' : 'status-completed');
            statusSpan.textContent = t.status;

            row.appendChild(titleSpan);
            row.appendChild(statusSpan);
            container.appendChild(row);
        });
    }

   document.addEventListener('DOMContentLoaded', function () {
        const todayEl = document.querySelector('.calendar-day.is-today');
        if (todayEl) {
            showDayTasks('<?php echo $todayStr; ?>', todayEl);
        } else {
            // Viewing a different month - just show today's date label with no highlight
            showDayTasks('<?php echo $todayStr; ?>', null);
        }
    });
</script>

<?php include 'includes/footer.php'; ?>