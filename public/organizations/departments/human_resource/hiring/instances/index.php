<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\WorkflowInstanceRepository;
use App\Classes\WorkflowEngine;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$instanceRepo = new WorkflowInstanceRepository();
$workflowEngine = new WorkflowEngine();

// Get all hiring workflow instances
$activeInstances = $instanceRepo->findByWorkflowId('hiring_workflow_v1', 'active');
$completedInstances = $instanceRepo->findByWorkflowId('hiring_workflow_v1', 'completed', 10);
$cancelledInstances = $instanceRepo->findByWorkflowId('hiring_workflow_v1', 'cancelled', 10);

// Count instances
$activeCount = $instanceRepo->count(['workflow_id' => 'hiring_workflow_v1', 'status' => 'active']);
$completedCount = $instanceRepo->count(['workflow_id' => 'hiring_workflow_v1', 'status' => 'completed']);

$pageTitle = 'Hiring Workflow Instances';
include __DIR__ . '/../../../../../../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <a href="/organizations/departments/human_resource/hiring/" class="text-muted" style="text-decoration: none;">&larr; Back to Hiring</a>
            <h1 style="margin-top: 0.5rem;">Hiring Workflow Instances</h1>
            <p class="text-muted" style="margin: 0.5rem 0 0 0;">Track all hiring processes</p>
        </div>
    </div>

    <!-- Instance Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div class="card" style="text-align: center;">
            <h3 style="margin: 0; font-size: 2rem; color: #10b981;"><?php echo $activeCount; ?></h3>
            <p class="text-muted" style="margin: 0.5rem 0 0 0;">Active Hires</p>
        </div>
        <div class="card" style="text-align: center;">
            <h3 style="margin: 0; font-size: 2rem; color: #3b82f6;"><?php echo $completedCount; ?></h3>
            <p class="text-muted" style="margin: 0.5rem 0 0 0;">Completed</p>
        </div>
        <div class="card" style="text-align: center;">
            <h3 style="margin: 0; font-size: 2rem; color: var(--text-muted);">-</h3>
            <p class="text-muted" style="margin: 0.5rem 0 0 0;">Avg. Time</p>
        </div>
    </div>

    <!-- Active Instances -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2 class="card-title">Active Hiring Processes (<?php echo count($activeInstances); ?>)</h2>

        <?php if (empty($activeInstances)): ?>
            <div style="text-align: center; padding: 3rem 1rem;">
                <p class="text-muted" style="font-size: 1.2rem; margin-bottom: 1rem;">No Active Hiring Processes</p>
                <p class="text-muted" style="margin-bottom: 2rem;">Start a hiring workflow from the Vacancies page</p>
                <a href="/organizations/departments/human_resource/vacancies/" class="btn btn-primary">Go to Vacancies</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Instance</th>
                            <th style="padding: 1rem;">Current Stage</th>
                            <th style="padding: 1rem;">Progress</th>
                            <th style="padding: 1rem;">Started</th>
                            <th style="padding: 1rem;">Elapsed</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeInstances as $instance): ?>
                            <?php $progress = $workflowEngine->getProgress($instance->getId()); ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($instance->getInstanceName()); ?></strong>
                                    <br>
                                    <span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; margin-top: 0.25rem; <?php echo $instance->getStatusBadgeClass(); ?>">
                                        <?php echo strtoupper($instance->getStatus()); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <code style="background: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        <?php echo htmlspecialchars($instance->getCurrentNodeId()); ?>
                                    </code>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($progress): ?>
                                        <div style="margin-bottom: 0.25rem;">
                                            <span style="font-size: 0.875rem;"><?php echo $progress['percentage']; ?>%</span>
                                        </div>
                                        <div style="background: var(--bg-light); border-radius: 4px; height: 8px; overflow: hidden; width: 100px;">
                                            <div style="background: var(--primary-color); height: 100%; width: <?php echo $progress['percentage']; ?>%;"></div>
                                        </div>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">
                                            <?php echo $progress['completed_nodes']; ?>/<?php echo $progress['total_nodes']; ?> nodes
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo date('M j, Y', strtotime($instance->getStartedAt())); ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $instance->getElapsedDays(); ?> days
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/human_resource/hiring/instances/view/?id=<?php echo $instance->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem;">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Completed Instances -->
    <?php if (!empty($completedInstances)): ?>
        <div class="card">
            <h2 class="card-title">Recently Completed (Last 10)</h2>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Instance</th>
                            <th style="padding: 1rem;">Started</th>
                            <th style="padding: 1rem;">Completed</th>
                            <th style="padding: 1rem;">Duration</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completedInstances as $instance): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); opacity: 0.7;">
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($instance->getInstanceName()); ?>
                                    <br>
                                    <span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; margin-top: 0.25rem; <?php echo $instance->getStatusBadgeClass(); ?>">
                                        COMPLETED
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo date('M j, Y', strtotime($instance->getStartedAt())); ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo date('M j, Y', strtotime($instance->getCompletedAt())); ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $instance->getElapsedDays(); ?> days
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/human_resource/hiring/instances/view/?id=<?php echo $instance->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem;">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../../../../views/footer.php'; ?>
