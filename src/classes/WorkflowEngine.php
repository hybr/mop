<?php

namespace App\Classes;

use App\Config\Database;

/**
 * WorkflowEngine
 * Core engine that orchestrates workflow execution, task assignment, and transitions
 */
class WorkflowEngine {
    private $db;
    private $instanceRepo;
    private $taskRepo;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->instanceRepo = new WorkflowInstanceRepository();
        $this->taskRepo = new WorkflowTaskRepository();
    }

    /**
     * Start a new workflow instance
     */
    public function startWorkflow($workflowId, $instanceName, $entityId, $entityType, $userId) {
        // Get workflow nodes to find the first node
        $nodes = $this->instanceRepo->getWorkflowNodes($workflowId);
        if (empty($nodes)) {
            throw new \Exception("Workflow not found or has no nodes");
        }

        // First node is the starting point
        $firstNode = $nodes[0];

        // Create workflow instance
        $instance = new WorkflowInstance();
        $instance->setWorkflowId($workflowId);
        $instance->setInstanceName($instanceName);
        $instance->setEntityId($entityId);
        $instance->setEntityType($entityType);
        $instance->setCurrentNodeId($firstNode['node_id']);
        $instance->setStatus('active');
        $instance->setInitiatedBy($userId);

        $instance = $this->instanceRepo->create($instance, $userId);

        // Create tasks for the first node
        $this->createTasksForNode($instance->getId(), $firstNode, $userId);

        // Log the start
        $this->logExecution(
            $instance->getId(),
            $firstNode['node_id'],
            $userId,
            'start',
            'workflow_started',
            'Workflow instance started'
        );

        return $instance;
    }

    /**
     * Complete a task and transition workflow if needed
     */
    public function completeTask($taskId, $executionResult, $comments, $userId) {
        // Get the task
        $task = $this->taskRepo->findById($taskId);
        if (!$task) {
            throw new \Exception("Task not found");
        }

        // Get the workflow instance
        $instance = $this->instanceRepo->findById($task->getWorkflowInstanceId());
        if (!$instance) {
            throw new \Exception("Workflow instance not found");
        }

        // Mark task as completed
        $this->taskRepo->complete($taskId, $executionResult, $comments, $userId);

        // Log the execution
        $this->logExecution(
            $instance->getId(),
            $task->getNodeId(),
            $userId,
            'complete',
            $executionResult,
            $comments
        );

        // Check if this was the last task for the current node
        $pendingTasks = $this->taskRepo->findByInstance($instance->getId(), 'pending');
        $inProgressTasks = $this->taskRepo->findByInstance($instance->getId(), 'in_progress');

        // If there are still pending/in-progress tasks for this node, don't transition yet
        $currentNodeTasks = array_filter(array_merge($pendingTasks, $inProgressTasks), function($t) use ($task) {
            return $t['node_id'] === $task->getNodeId();
        });

        if (!empty($currentNodeTasks)) {
            return [
                'status' => 'task_completed',
                'message' => 'Task completed. Waiting for other tasks in this node.',
                'instance' => $instance
            ];
        }

        // All tasks for this node are complete, evaluate transition
        return $this->evaluateTransition($instance, $task->getNodeId(), $executionResult, $userId);
    }

    /**
     * Evaluate workflow transition based on execution result
     */
    private function evaluateTransition($instance, $currentNodeId, $executionResult, $userId) {
        // Get all edges from the current node
        $edges = $this->getEdgesFromNode($instance->getWorkflowId(), $currentNodeId);

        if (empty($edges)) {
            // No outgoing edges - workflow is complete
            $this->instanceRepo->complete($instance->getId(), $userId);
            return [
                'status' => 'workflow_completed',
                'message' => 'Workflow completed successfully!',
                'instance' => $instance
            ];
        }

        // Find matching edge based on execution result
        $matchingEdge = null;
        foreach ($edges as $edge) {
            if ($edge['condition'] === $executionResult) {
                $matchingEdge = $edge;
                break;
            }
        }

        if (!$matchingEdge) {
            throw new \Exception("No matching transition found for result: {$executionResult}");
        }

        // Get target node details
        $targetNode = $this->getNodeByNodeId($instance->getWorkflowId(), $matchingEdge['target_node_id']);
        if (!$targetNode) {
            throw new \Exception("Target node not found: " . $matchingEdge['target_node_id']);
        }

        // Move workflow to next node
        $this->instanceRepo->moveToNode($instance->getId(), $targetNode['node_id'], $userId);

        // Create tasks for the new node
        $this->createTasksForNode($instance->getId(), $targetNode, $userId);

        // Log the transition
        $this->logExecution(
            $instance->getId(),
            $targetNode['node_id'],
            $userId,
            'transition',
            'node_entered',
            "Transitioned from {$currentNodeId} to {$targetNode['node_id']}"
        );

        return [
            'status' => 'transitioned',
            'message' => "Workflow moved to: {$targetNode['label']}",
            'next_node' => $targetNode,
            'instance' => $instance
        ];
    }

    /**
     * Create tasks for a node
     */
    private function createTasksForNode($instanceId, $node, $userId) {
        // Get required positions from node
        $requiredPositions = json_decode($node['required_positions'], true);
        if (empty($requiredPositions)) {
            throw new \Exception("No required positions defined for node: " . $node['label']);
        }

        // Find users with these positions
        $eligibleUsers = $this->findUsersWithPositions($requiredPositions);

        if (empty($eligibleUsers)) {
            throw new \Exception("No users found with required positions: " . implode(', ', $requiredPositions));
        }

        // Calculate due date based on SLA
        $dueDate = $this->calculateDueDate($node['sla']);

        // Create task for each eligible user
        foreach ($eligibleUsers as $user) {
            $task = new WorkflowTask();
            $task->setWorkflowInstanceId($instanceId);
            $task->setNodeId($node['node_id']);
            $task->setAssignedToUserId($user['id']);
            $task->setTaskName($node['label']);
            $task->setTaskDescription("Complete: " . $node['label']);
            $task->setStatus('pending');
            $task->setPriority(5);
            $task->setDueDate($dueDate);

            $this->taskRepo->create($task, $userId);

            // TODO: Send notification to user
        }

        return count($eligibleUsers);
    }

    /**
     * Find users with specific positions
     */
    private function findUsersWithPositions($positionNames) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();

            // For now, return all active users
            // TODO: Implement proper position-based user lookup
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.id, u.full_name as name, u.email
                FROM users u
                WHERE u.is_active = 1
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        }

        return [];
    }

    /**
     * Get edges originating from a node
     */
    private function getEdgesFromNode($workflowId, $nodeId) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("
                SELECT * FROM workflow_edges
                WHERE workflow_id = ? AND source_node_id = ?
                ORDER BY priority DESC
            ");
            $stmt->execute([$workflowId, $nodeId]);
            return $stmt->fetchAll();
        }

        return [];
    }

    /**
     * Get node by node_id
     */
    private function getNodeByNodeId($workflowId, $nodeId) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("
                SELECT * FROM workflow_nodes
                WHERE workflow_id = ? AND node_id = ?
            ");
            $stmt->execute([$workflowId, $nodeId]);
            return $stmt->fetch();
        }

        return null;
    }

    /**
     * Log workflow execution
     */
    private function logExecution($instanceId, $nodeId, $userId, $action, $result, $comments) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("
                INSERT INTO workflow_execution_log (id, workflow_instance_id, node_id, user_id, action, execution_result, comments, executed_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $logId = 'wel_' . bin2hex(random_bytes(16));
            $stmt->execute([
                $logId,
                $instanceId,
                $nodeId,
                $userId,
                $action,
                $result,
                $comments,
                date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Calculate due date based on SLA string
     */
    private function calculateDueDate($sla) {
        if (empty($sla)) {
            return date('Y-m-d H:i:s', strtotime('+7 days'));
        }

        // Parse SLA (e.g., "2 days", "7 days", "10 days")
        if (preg_match('/(\d+)\s*day/i', $sla, $matches)) {
            $days = (int)$matches[1];
            return date('Y-m-d H:i:s', strtotime("+{$days} days"));
        }

        return date('Y-m-d H:i:s', strtotime('+7 days'));
    }

    /**
     * Cancel workflow instance
     */
    public function cancelWorkflow($instanceId, $userId, $reason) {
        return $this->instanceRepo->cancel($instanceId, $userId, $reason);
    }

    /**
     * Get workflow progress
     */
    public function getProgress($instanceId) {
        $instance = $this->instanceRepo->findById($instanceId);
        if (!$instance) {
            return null;
        }

        $nodes = $this->instanceRepo->getWorkflowNodes($instance->getWorkflowId());
        $executionLog = $this->instanceRepo->getExecutionLog($instanceId);

        // Count unique nodes that have been executed
        $executedNodes = [];
        foreach ($executionLog as $log) {
            $executedNodes[$log['node_id']] = true;
        }

        $completedCount = count($executedNodes);
        $totalCount = count($nodes);
        $percentage = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;

        return [
            'instance' => $instance,
            'completed_nodes' => $completedCount,
            'total_nodes' => $totalCount,
            'percentage' => $percentage,
            'current_node' => $instance->getCurrentNodeId(),
            'status' => $instance->getStatus()
        ];
    }
}
