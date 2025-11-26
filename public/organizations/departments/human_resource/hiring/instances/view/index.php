<?php
require_once __DIR__ . '/../../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\WorkflowInstanceRepository;
use App\Classes\WorkflowTaskRepository;
use App\Classes\OrganizationVacancyRepository;
use App\Classes\OrganizationPositionRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$instanceId = $_GET['id'] ?? '';

if (empty($instanceId)) {
    header('Location: /organizations/departments/human_resource/hiring/instances/?error=' . urlencode('Instance ID required'));
    exit;
}

$instanceRepo = new WorkflowInstanceRepository();
$taskRepo = new WorkflowTaskRepository();

$instance = $instanceRepo->findById($instanceId);
if (!$instance) {
    header('Location: /organizations/departments/human_resource/hiring/instances/?error=' . urlencode('Workflow instance not found'));
    exit;
}

// Get workflow nodes and edges
$nodes = $instanceRepo->getWorkflowNodes($instance->getWorkflowId());
$edges = $instanceRepo->getWorkflowEdges($instance->getWorkflowId());

// Get execution log
$executionLog = $instanceRepo->getExecutionLog($instanceId);

// Get active tasks
$activeTasks = $taskRepo->findByInstance($instanceId, 'pending');
$inProgressTasks = $taskRepo->findByInstance($instanceId, 'in_progress');
$completedTasks = $taskRepo->findByInstance($instanceId, 'completed');

// Calculate progress
$executedNodes = [];
foreach ($executionLog as $log) {
    $executedNodes[$log['node_id']] = true;
}
$completedCount = count($executedNodes);
$totalCount = count($nodes);
$percentage = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;

// Get vacancy details if entity is OrganizationVacancy
$vacancy = null;
$position = null;
if ($instance->getEntityType() === 'OrganizationVacancy') {
    $vacancyRepo = new OrganizationVacancyRepository();
    $positionRepo = new OrganizationPositionRepository();

    $vacancy = $vacancyRepo->findById($instance->getEntityId(), $user->getId());
    if ($vacancy && $vacancy->getOrganizationPositionId()) {
        $position = $positionRepo->findById($vacancy->getOrganizationPositionId());
    }
}

$pageTitle = 'Workflow Instance Details';
include __DIR__ . '/../../../../../../../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <a href="/organizations/departments/human_resource/hiring/instances/" class="text-muted" style="text-decoration: none;">
                ← Back to Instances
            </a>
            <h1 style="margin: 0.5rem 0 0 0;"><?php echo htmlspecialchars($instance->getInstanceName()); ?></h1>
        </div>
        <div>
            <span class="badge <?php echo $instance->getStatusBadgeClass(); ?>">
                <?php echo strtoupper($instance->getStatus()); ?>
            </span>
        </div>
    </div>

    <!-- Progress Overview -->
    <div class="card">
        <h3 class="card-title">Progress Overview</h3>

        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span>Workflow Progress</span>
                <span><strong><?php echo $percentage; ?>%</strong> (<?php echo $completedCount; ?>/<?php echo $totalCount; ?> nodes)</span>
            </div>
            <div style="background: #e2e8f0; border-radius: 9999px; height: 1rem; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100%; width: <?php echo $percentage; ?>%; transition: width 0.3s;"></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <p class="text-muted text-small mb-1">Current Stage</p>
                <p><strong><?php
                    $currentNode = array_filter($nodes, fn($n) => $n['node_id'] === $instance->getCurrentNodeId());
                    echo $currentNode ? htmlspecialchars(array_values($currentNode)[0]['label']) : 'N/A';
                ?></strong></p>
            </div>

            <div>
                <p class="text-muted text-small mb-1">Started</p>
                <p><?php echo $instance->getStartedAt() ? date('M j, Y g:i A', strtotime($instance->getStartedAt())) : 'N/A'; ?></p>
            </div>

            <div>
                <p class="text-muted text-small mb-1">Elapsed Time</p>
                <p><?php echo $instance->getElapsedDays(); ?> days</p>
            </div>

            <?php if ($instance->getCompletedAt()): ?>
            <div>
                <p class="text-muted text-small mb-1">Completed</p>
                <p><?php echo date('M j, Y g:i A', strtotime($instance->getCompletedAt())); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Vacancy Details (if applicable) -->
    <?php if ($vacancy): ?>
    <div class="card">
        <h3 class="card-title">Vacancy Details</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <div>
                <p class="text-muted text-small mb-1">Position</p>
                <p><strong><?php echo $position ? htmlspecialchars($position->getName()) : 'N/A'; ?></strong></p>
            </div>
            <div>
                <p class="text-muted text-small mb-1">Openings</p>
                <p><?php echo $vacancy->getNumberOfOpenings(); ?></p>
            </div>
            <div>
                <p class="text-muted text-small mb-1">Employment Type</p>
                <p><?php echo htmlspecialchars($vacancy->getEmploymentType()); ?></p>
            </div>
            <div>
                <p class="text-muted text-small mb-1">Location</p>
                <p><?php echo htmlspecialchars($vacancy->getLocation()); ?></p>
            </div>
        </div>
        <div style="margin-top: 1rem;">
            <a href="/organizations/departments/human_resource/vacancies/view/?id=<?php echo $vacancy->getId(); ?>"
               class="btn btn-secondary">
                View Full Vacancy →
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Active Tasks -->
    <?php if (!empty($activeTasks) || !empty($inProgressTasks)): ?>
    <div class="card">
        <h3 class="card-title">Active Tasks (<?php echo count($activeTasks) + count($inProgressTasks); ?>)</h3>

        <?php if (!empty($inProgressTasks)): ?>
            <h4 style="font-size: 0.9rem; color: #3b82f6; margin-bottom: 0.5rem;">In Progress</h4>
            <div style="margin-bottom: 1rem;">
                <?php foreach ($inProgressTasks as $task): ?>
                <div style="padding: 1rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; margin-bottom: 0.5rem; background: #eff6ff;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <p style="margin: 0 0 0.25rem 0; font-weight: 600;"><?php echo htmlspecialchars($task['task_name']); ?></p>
                            <p style="margin: 0; font-size: 0.875rem; color: #64748b;">
                                Assigned to: <?php echo htmlspecialchars($task['user_name'] ?? 'Unknown'); ?>
                            </p>
                            <?php if ($task['due_date']): ?>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #64748b;">
                                Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <span class="badge badge-primary">In Progress</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($activeTasks)): ?>
            <h4 style="font-size: 0.9rem; color: #64748b; margin-bottom: 0.5rem;">Pending</h4>
            <div>
                <?php foreach ($activeTasks as $task): ?>
                <div style="padding: 1rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <p style="margin: 0 0 0.25rem 0; font-weight: 600;"><?php echo htmlspecialchars($task['task_name']); ?></p>
                            <p style="margin: 0; font-size: 0.875rem; color: #64748b;">
                                Assigned to: <?php echo htmlspecialchars($task['user_name'] ?? 'Unknown'); ?>
                            </p>
                            <?php if ($task['due_date']): ?>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #64748b;">
                                Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <span class="badge badge-warning">Pending</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Workflow Visualization -->
    <div class="card">
        <h3 class="card-title">Workflow Diagram</h3>
        <div id="cy" style="width: 100%; height: 600px; border: 1px solid #e2e8f0; border-radius: 0.5rem;"></div>
        <div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: 0.5rem;">
            <div style="display: flex; gap: 2rem; flex-wrap: wrap; font-size: 0.875rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 20px; height: 20px; background: #10b981; border-radius: 50%;"></div>
                    <span>Completed</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 20px; height: 20px; background: #3b82f6; border-radius: 50%;"></div>
                    <span>Current Node</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 20px; height: 20px; background: #94a3b8; border-radius: 50%;"></div>
                    <span>Pending</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Execution Timeline -->
    <div class="card">
        <h3 class="card-title">Execution Timeline (<?php echo count($executionLog); ?> events)</h3>

        <?php if (!empty($executionLog)): ?>
        <div style="position: relative; padding-left: 2rem;">
            <!-- Timeline line -->
            <div style="position: absolute; left: 0.6rem; top: 0; bottom: 0; width: 2px; background: #e2e8f0;"></div>

            <?php foreach (array_reverse($executionLog) as $log): ?>
            <div style="position: relative; margin-bottom: 1.5rem;">
                <!-- Timeline dot -->
                <div style="position: absolute; left: -1.4rem; top: 0.25rem; width: 12px; height: 12px; background: #667eea; border-radius: 50%; border: 2px solid white;"></div>

                <div style="padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem; border-left: 3px solid #667eea;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                        <div>
                            <p style="margin: 0; font-weight: 600; color: #1e293b;">
                                <?php
                                $actionLabels = [
                                    'start' => '🚀 Workflow Started',
                                    'complete' => '✅ Task Completed',
                                    'transition' => '➡️ Transitioned',
                                    'cancel' => '❌ Cancelled',
                                    'fail' => '⚠️ Failed'
                                ];
                                echo $actionLabels[$log['action']] ?? ucfirst($log['action']);
                                ?>
                            </p>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #64748b;">
                                Node: <strong><?php
                                    $nodeData = array_filter($nodes, fn($n) => $n['node_id'] === $log['node_id']);
                                    echo $nodeData ? htmlspecialchars(array_values($nodeData)[0]['label']) : $log['node_id'];
                                ?></strong>
                            </p>
                        </div>
                        <span style="font-size: 0.75rem; color: #64748b;">
                            <?php echo date('M j, Y g:i A', strtotime($log['executed_at'])); ?>
                        </span>
                    </div>

                    <?php if ($log['execution_result']): ?>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;">
                        <span style="color: #64748b;">Result:</span>
                        <code style="background: #e2e8f0; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.8rem;">
                            <?php echo htmlspecialchars($log['execution_result']); ?>
                        </code>
                    </p>
                    <?php endif; ?>

                    <?php if ($log['comments']): ?>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #475569;">
                        💬 <?php echo htmlspecialchars($log['comments']); ?>
                    </p>
                    <?php endif; ?>

                    <p style="margin: 0.5rem 0 0 0; font-size: 0.75rem; color: #94a3b8;">
                        By: <?php echo htmlspecialchars($log['user_name'] ?? 'System'); ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted">No execution events recorded yet.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://unpkg.com/cytoscape@3.26.0/dist/cytoscape.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nodes = <?php echo json_encode($nodes); ?>;
    const edges = <?php echo json_encode($edges); ?>;
    const currentNodeId = <?php echo json_encode($instance->getCurrentNodeId()); ?>;
    const executedNodes = <?php echo json_encode(array_keys($executedNodes)); ?>;

    // Build Cytoscape elements
    const cyNodes = nodes.map((node, index) => {
        let bgColor = '#94a3b8'; // Default: pending (gray)

        if (executedNodes.includes(node.node_id)) {
            bgColor = '#10b981'; // Completed (green)
        }

        if (node.node_id === currentNodeId) {
            bgColor = '#3b82f6'; // Current (blue)
        }

        return {
            data: {
                id: node.node_id,
                label: node.label,
                bgColor: bgColor
            }
        };
    });

    const cyEdges = edges.map(edge => ({
        data: {
            id: `${edge.source_node_id}_${edge.target_node_id}`,
            source: edge.source_node_id,
            target: edge.target_node_id,
            label: edge.label || edge.condition
        }
    }));

    const cy = cytoscape({
        container: document.getElementById('cy'),
        elements: [...cyNodes, ...cyEdges],
        style: [
            {
                selector: 'node',
                style: {
                    'background-color': 'data(bgColor)',
                    'label': 'data(label)',
                    'color': '#fff',
                    'text-valign': 'center',
                    'text-halign': 'center',
                    'font-size': '12px',
                    'font-weight': 'bold',
                    'width': '120px',
                    'height': '50px',
                    'shape': 'roundrectangle',
                    'text-wrap': 'wrap',
                    'text-max-width': '110px'
                }
            },
            {
                selector: 'edge',
                style: {
                    'width': 2,
                    'line-color': '#cbd5e1',
                    'target-arrow-color': '#cbd5e1',
                    'target-arrow-shape': 'triangle',
                    'curve-style': 'bezier',
                    'label': 'data(label)',
                    'font-size': '10px',
                    'color': '#64748b',
                    'text-rotation': 'autorotate',
                    'text-margin-y': -10
                }
            }
        ],
        layout: {
            name: 'breadthfirst',
            directed: true,
            spacingFactor: 1.5,
            padding: 30
        }
    });
});
</script>

<?php include __DIR__ . '/../../../../../../../views/footer.php'; ?>
