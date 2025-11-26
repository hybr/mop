<?php
require_once __DIR__ . '/../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\WorkflowTaskRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$taskRepo = new WorkflowTaskRepository();

// Get user's tasks
$pendingTasks = $taskRepo->findByUser($user->getId(), 'pending');
$inProgressTasks = $taskRepo->findByUser($user->getId(), 'in_progress');
$completedTasks = $taskRepo->findByUser($user->getId(), 'completed', 10);
$overdueTasks = $taskRepo->findOverdueByUser($user->getId());

// Count tasks
$pendingCount = $taskRepo->countByUser($user->getId(), 'pending');
$inProgressCount = $taskRepo->countByUser($user->getId(), 'in_progress');

$pageTitle = 'My Tasks';
include __DIR__ . '/../../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="margin: 0;">My Workflow Tasks</h1>
            <p class="text-muted" style="margin: 0.5rem 0 0 0;">Tasks assigned to you across all workflows</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Task Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div class="card" style="text-align: center;">
            <h3 style="margin: 0; font-size: 2rem; color: #f59e0b;"><?php echo $pendingCount; ?></h3>
            <p class="text-muted" style="margin: 0.5rem 0 0 0;">Pending</p>
        </div>
        <div class="card" style="text-align: center;">
            <h3 style="margin: 0; font-size: 2rem; color: #3b82f6;"><?php echo $inProgressCount; ?></h3>
            <p class="text-muted" style="margin: 0.5rem 0 0 0;">In Progress</p>
        </div>
        <div class="card" style="text-align: center;">
            <h3 style="margin: 0; font-size: 2rem; color: #ef4444;"><?php echo count($overdueTasks); ?></h3>
            <p class="text-muted" style="margin: 0.5rem 0 0 0;">Overdue</p>
        </div>
    </div>

    <!-- Overdue Tasks Alert -->
    <?php if (!empty($overdueTasks)): ?>
        <div class="alert alert-error" style="margin-bottom: 2rem;">
            <strong>⚠ You have <?php echo count($overdueTasks); ?> overdue task(s)!</strong>
            <p style="margin: 0.5rem 0 0 0;">Please review and complete them as soon as possible.</p>
        </div>
    <?php endif; ?>

    <!-- Pending Tasks -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2 class="card-title">Pending Tasks (<?php echo count($pendingTasks); ?>)</h2>

        <?php if (empty($pendingTasks)): ?>
            <div style="text-align: center; padding: 2rem;">
                <p class="text-muted">No pending tasks. Great job!</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Task</th>
                            <th style="padding: 1rem;">Workflow</th>
                            <th style="padding: 1rem;">Due Date</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingTasks as $task): ?>
                            <?php
                            $dueDate = $task['due_date'] ? new DateTime($task['due_date']) : null;
                            $now = new DateTime();
                            $isOverdue = $dueDate && $now > $dueDate;
                            $daysUntilDue = $dueDate ? $now->diff($dueDate)->days : null;
                            ?>
                            <tr style="border-bottom: 1px solid var(--border-color); <?php echo $isOverdue ? 'background: #fee2e2;' : ''; ?>">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($task['task_name']); ?></strong>
                                    <?php if ($task['task_description']): ?>
                                        <br><span class="text-muted" style="font-size: 0.875rem;"><?php echo htmlspecialchars($task['task_description']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <span class="text-muted"><?php echo htmlspecialchars($task['instance_name']); ?></span>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($dueDate): ?>
                                        <?php echo $dueDate->format('M j, Y'); ?>
                                        <?php if ($isOverdue): ?>
                                            <br><span style="color: #ef4444; font-size: 0.875rem;">⚠ Overdue</span>
                                        <?php elseif ($daysUntilDue <= 2): ?>
                                            <br><span style="color: #f59e0b; font-size: 0.875rem;">⚡ Due soon</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; background: #f59e0b; color: white;">
                                        PENDING
                                    </span>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/tasks/complete/?id=<?php echo $task['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem;">Complete Task</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- In Progress Tasks -->
    <?php if (!empty($inProgressTasks)): ?>
        <div class="card" style="margin-bottom: 2rem;">
            <h2 class="card-title">In Progress (<?php echo count($inProgressTasks); ?>)</h2>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Task</th>
                            <th style="padding: 1rem;">Workflow</th>
                            <th style="padding: 1rem;">Started</th>
                            <th style="padding: 1rem;">Due Date</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inProgressTasks as $task): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($task['task_name']); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <span class="text-muted"><?php echo htmlspecialchars($task['instance_name']); ?></span>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $task['started_at'] ? date('M j, Y', strtotime($task['started_at'])) : '-'; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $task['due_date'] ? date('M j, Y', strtotime($task['due_date'])) : '-'; ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/tasks/complete/?id=<?php echo $task['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem;">Complete Task</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recently Completed Tasks -->
    <?php if (!empty($completedTasks)): ?>
        <div class="card">
            <h2 class="card-title">Recently Completed (Last 10)</h2>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Task</th>
                            <th style="padding: 1rem;">Workflow</th>
                            <th style="padding: 1rem;">Completed</th>
                            <th style="padding: 1rem;">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completedTasks as $task): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); opacity: 0.7;">
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($task['task_name']); ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <span class="text-muted"><?php echo htmlspecialchars($task['instance_name']); ?></span>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo date('M j, Y', strtotime($task['completed_at'])); ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($task['execution_result']): ?>
                                        <code style="background: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">
                                            <?php echo htmlspecialchars($task['execution_result']); ?>
                                        </code>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../views/footer.php'; ?>
