<?php

namespace App\Classes;

use App\Config\Database;

/**
 * WorkflowTaskRepository
 * Manages CRUD operations for workflow tasks
 */
class WorkflowTaskRepository {
    private $db;
    private $tableName = 'workflow_tasks';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new task
     */
    public function create(WorkflowTask $task, $userId) {
        $data = [
            'id' => $this->generateId(),
            'workflow_instance_id' => $task->getWorkflowInstanceId(),
            'node_id' => $task->getNodeId(),
            'assigned_to_user_id' => $task->getAssignedToUserId(),
            'task_name' => $task->getTaskName(),
            'task_description' => $task->getTaskDescription(),
            'status' => $task->getStatus() ?? 'pending',
            'priority' => $task->getPriority() ?? 0,
            'due_date' => $task->getDueDate(),
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $data = array_filter($data, function($value) {
            return $value !== null;
        });

        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'INSERT', [], $data);
        } else {
            $response = $this->db->request('POST', $this->tableName, $data);
        }

        if ($response['success'] && !empty($response['data'])) {
            $task->hydrate($response['data'][0]);
            return $task;
        }

        throw new \Exception("Failed to create workflow task");
    }

    /**
     * Find task by ID
     */
    public function findById($id) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName} WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();

            if ($data) {
                return new WorkflowTask($data);
            }
        }

        return null;
    }

    /**
     * Find tasks by user ID
     */
    public function findByUser($userId, $status = null, $limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();

            $sql = "
                SELECT
                    wt.*,
                    wi.instance_name,
                    wi.workflow_id,
                    wi.status as instance_status
                FROM {$this->tableName} wt
                JOIN workflow_instances wi ON wt.workflow_instance_id = wi.id
                WHERE wt.assigned_to_user_id = ?
            ";
            $params = [$userId];

            if ($status !== null) {
                $sql .= " AND wt.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY wt.due_date ASC, wt.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }

        return [];
    }

    /**
     * Find tasks by workflow instance
     */
    public function findByInstance($instanceId, $status = null) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();

            $sql = "
                SELECT
                    wt.*,
                    u.full_name as assigned_to_name,
                    u.email as assigned_to_email
                FROM {$this->tableName} wt
                LEFT JOIN users u ON wt.assigned_to_user_id = u.id
                WHERE wt.workflow_instance_id = ?
            ";
            $params = [$instanceId];

            if ($status !== null) {
                $sql .= " AND wt.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY wt.created_at DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }

        return [];
    }

    /**
     * Update task
     */
    public function update(WorkflowTask $task, $userId) {
        if (!$task->getId()) {
            throw new \Exception("Task ID is required for update");
        }

        $data = [
            'status' => $task->getStatus(),
            'started_at' => $task->getStartedAt(),
            'completed_at' => $task->getCompletedAt(),
            'completed_by' => $task->getCompletedBy(),
            'execution_result' => $task->getExecutionResult(),
            'comments' => $task->getComments(),
            'updated_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $data = array_filter($data, function($value) {
            return $value !== null;
        });

        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'UPDATE', ['id' => $task->getId()], $data);
        } else {
            $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $task->getId(), $data);
        }

        if ($response['success']) {
            return true;
        }

        throw new \Exception("Failed to update workflow task");
    }

    /**
     * Complete task
     */
    public function complete($taskId, $executionResult, $comments, $userId) {
        $task = $this->findById($taskId);
        if (!$task) {
            throw new \Exception("Task not found");
        }

        $task->setStatus('completed');
        $task->setCompletedAt(date('Y-m-d H:i:s'));
        $task->setCompletedBy($userId);
        $task->setExecutionResult($executionResult);
        $task->setComments($comments);

        return $this->update($task, $userId);
    }

    /**
     * Start task (mark as in progress)
     */
    public function start($taskId, $userId) {
        $task = $this->findById($taskId);
        if (!$task) {
            throw new \Exception("Task not found");
        }

        if ($task->getStatus() !== 'pending') {
            throw new \Exception("Task cannot be started - current status: " . $task->getStatus());
        }

        $task->setStatus('in_progress');
        $task->setStartedAt(date('Y-m-d H:i:s'));

        return $this->update($task, $userId);
    }

    /**
     * Count tasks for user
     */
    public function countByUser($userId, $status = null) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();

            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE assigned_to_user_id = ?";
            $params = [$userId];

            if ($status !== null) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();

            return (int)$result['count'];
        }

        return 0;
    }

    /**
     * Get overdue tasks for user
     */
    public function findOverdueByUser($userId) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("
                SELECT
                    wt.*,
                    wi.instance_name,
                    wi.workflow_id
                FROM {$this->tableName} wt
                JOIN workflow_instances wi ON wt.workflow_instance_id = wi.id
                WHERE wt.assigned_to_user_id = ?
                  AND wt.status IN ('pending', 'in_progress')
                  AND wt.due_date < datetime('now')
                ORDER BY wt.due_date ASC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        }

        return [];
    }

    /**
     * Generate unique ID
     */
    private function generateId() {
        return 'wft_' . bin2hex(random_bytes(16));
    }
}
