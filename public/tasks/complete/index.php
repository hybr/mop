<?php
require_once __DIR__ . '/../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\WorkflowTaskRepository;
use App\Classes\WorkflowInstanceRepository;
use App\Classes\WorkflowEngine;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$taskRepo = new WorkflowTaskRepository();
$instanceRepo = new WorkflowInstanceRepository();
$workflowEngine = new WorkflowEngine();

// Get task ID
if (!isset($_GET['id'])) {
    header('Location: /tasks/?error=' . urlencode('Task ID required'));
    exit;
}

$task = $taskRepo->findById($_GET['id']);
if (!$task) {
    header('Location: /tasks/?error=' . urlencode('Task not found'));
    exit;
}

// Verify task is assigned to current user
if ($task->getAssignedToUserId() !== $user->getId()) {
    header('Location: /tasks/?error=' . urlencode('You are not authorized to complete this task'));
    exit;
}

// Get workflow instance
$instance = $instanceRepo->findById($task->getWorkflowInstanceId());
if (!$instance) {
    header('Location: /tasks/?error=' . urlencode('Workflow instance not found'));
    exit;
}

// Get possible execution results based on node
$edges = $instanceRepo->getWorkflowEdges($instance->getWorkflowId());
$possibleResults = [];
foreach ($edges as $edge) {
    if ($edge['source_node_id'] === $task->getNodeId()) {
        $possibleResults[$edge['condition']] = $edge['label'];
    }
}

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $executionResult = $_POST['execution_result'] ?? '';
        $comments = $_POST['comments'] ?? '';

        if (empty($executionResult)) {
            throw new Exception('Please select an execution result');
        }

        // Complete the task using workflow engine
        $result = $workflowEngine->completeTask(
            $task->getId(),
            $executionResult,
            $comments,
            $user->getId()
        );

        $successMessage = urlencode($result['message']);
        header("Location: /tasks/?success={$successMessage}");
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Complete Task';
include __DIR__ . '/../../../views/header.php';
?>

<div class="py-4">
    <div style="margin-bottom: 2rem;">
        <a href="/tasks/" class="text-muted" style="text-decoration: none;">&larr; Back to My Tasks</a>
        <h1 style="margin-top: 0.5rem;">Complete Task</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Task Details -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2 class="card-title">Task Details</h2>

        <table style="width: 100%;">
            <tr>
                <td style="padding: 0.5rem 0; color: var(--text-light); width: 30%;">Task Name</td>
                <td style="padding: 0.5rem 0;">
                    <strong><?php echo htmlspecialchars($task->getTaskName()); ?></strong>
                </td>
            </tr>
            <tr>
                <td style="padding: 0.5rem 0; color: var(--text-light);">Workflow</td>
                <td style="padding: 0.5rem 0;">
                    <?php echo htmlspecialchars($instance->getInstanceName()); ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 0.5rem 0; color: var(--text-light);">Current Node</td>
                <td style="padding: 0.5rem 0;">
                    <code style="background: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 4px;">
                        <?php echo htmlspecialchars($task->getNodeId()); ?>
                    </code>
                </td>
            </tr>
            <tr>
                <td style="padding: 0.5rem 0; color: var(--text-light);">Due Date</td>
                <td style="padding: 0.5rem 0;">
                    <?php if ($task->getDueDate()): ?>
                        <?php
                        $dueDate = new DateTime($task->getDueDate());
                        $now = new DateTime();
                        $isOverdue = $now > $dueDate;
                        ?>
                        <?php echo $dueDate->format('F j, Y'); ?>
                        <?php if ($isOverdue): ?>
                            <span style="color: #ef4444; margin-left: 0.5rem;">⚠ Overdue</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">No due date</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($task->getTaskDescription()): ?>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light); vertical-align: top;">Description</td>
                    <td style="padding: 0.5rem 0;">
                        <?php echo nl2br(htmlspecialchars($task->getTaskDescription())); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Completion Form -->
    <form method="POST" class="card">
        <h2 class="card-title">Complete Task</h2>

        <div class="form-group">
            <label for="execution_result" class="form-label">Execution Result *</label>
            <select id="execution_result" name="execution_result" class="form-input" required>
                <option value="">Select outcome...</option>
                <?php foreach ($possibleResults as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars($value); ?>">
                        <?php echo htmlspecialchars($label . ' (' . $value . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted text-small">Select the outcome of this task to determine the next step in the workflow</small>
        </div>

        <div class="form-group">
            <label for="comments" class="form-label">Comments (Optional)</label>
            <textarea
                id="comments"
                name="comments"
                class="form-input"
                rows="4"
                placeholder="Add any notes or comments about completing this task..."
            ></textarea>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Complete Task</button>
            <a href="/tasks/" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <!-- Workflow Progress -->
    <div class="card" style="margin-top: 2rem;">
        <h2 class="card-title">Workflow Progress</h2>

        <?php
        $progress = $workflowEngine->getProgress($instance->getId());
        if ($progress):
        ?>
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Progress: <?php echo $progress['completed_nodes']; ?> / <?php echo $progress['total_nodes']; ?> nodes</span>
                    <span><?php echo $progress['percentage']; ?>%</span>
                </div>
                <div style="background: var(--bg-light); border-radius: 8px; height: 20px; overflow: hidden;">
                    <div style="background: var(--primary-color); height: 100%; width: <?php echo $progress['percentage']; ?>%;"></div>
                </div>
            </div>

            <p class="text-muted" style="margin: 0;">
                Current Status: <strong><?php echo ucfirst($progress['status']); ?></strong>
            </p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../../views/footer.php'; ?>
