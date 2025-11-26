<?php

namespace App\Classes;

/**
 * WorkflowTask Entity
 * Represents a task assigned to a user within a workflow instance
 */
class WorkflowTask {
    private $id;
    private $workflow_instance_id;
    private $node_id;
    private $assigned_to_user_id;
    private $task_name;
    private $task_description;
    private $status; // 'pending', 'in_progress', 'completed', 'skipped'
    private $priority;
    private $due_date;
    private $started_at;
    private $completed_at;
    private $completed_by;
    private $execution_result;
    private $comments;
    private $created_by;
    private $created_at;
    private $updated_by;
    private $updated_at;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    public function hydrate($data) {
        if (isset($data['id'])) $this->id = $data['id'];
        if (isset($data['workflow_instance_id'])) $this->workflow_instance_id = $data['workflow_instance_id'];
        if (isset($data['node_id'])) $this->node_id = $data['node_id'];
        if (isset($data['assigned_to_user_id'])) $this->assigned_to_user_id = $data['assigned_to_user_id'];
        if (isset($data['task_name'])) $this->task_name = $data['task_name'];
        if (isset($data['task_description'])) $this->task_description = $data['task_description'];
        if (isset($data['status'])) $this->status = $data['status'];
        if (isset($data['priority'])) $this->priority = $data['priority'];
        if (isset($data['due_date'])) $this->due_date = $data['due_date'];
        if (isset($data['started_at'])) $this->started_at = $data['started_at'];
        if (isset($data['completed_at'])) $this->completed_at = $data['completed_at'];
        if (isset($data['completed_by'])) $this->completed_by = $data['completed_by'];
        if (isset($data['execution_result'])) $this->execution_result = $data['execution_result'];
        if (isset($data['comments'])) $this->comments = $data['comments'];
        if (isset($data['created_by'])) $this->created_by = $data['created_by'];
        if (isset($data['created_at'])) $this->created_at = $data['created_at'];
        if (isset($data['updated_by'])) $this->updated_by = $data['updated_by'];
        if (isset($data['updated_at'])) $this->updated_at = $data['updated_at'];
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'workflow_instance_id' => $this->workflow_instance_id,
            'node_id' => $this->node_id,
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'task_name' => $this->task_name,
            'task_description' => $this->task_description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'completed_by' => $this->completed_by,
            'execution_result' => $this->execution_result,
            'comments' => $this->comments,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at
        ];
    }

    // Getters
    public function getId() { return $this->id; }
    public function getWorkflowInstanceId() { return $this->workflow_instance_id; }
    public function getNodeId() { return $this->node_id; }
    public function getAssignedToUserId() { return $this->assigned_to_user_id; }
    public function getTaskName() { return $this->task_name; }
    public function getTaskDescription() { return $this->task_description; }
    public function getStatus() { return $this->status; }
    public function getPriority() { return $this->priority; }
    public function getDueDate() { return $this->due_date; }
    public function getStartedAt() { return $this->started_at; }
    public function getCompletedAt() { return $this->completed_at; }
    public function getCompletedBy() { return $this->completed_by; }
    public function getExecutionResult() { return $this->execution_result; }
    public function getComments() { return $this->comments; }
    public function getCreatedBy() { return $this->created_by; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedBy() { return $this->updated_by; }
    public function getUpdatedAt() { return $this->updated_at; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setWorkflowInstanceId($workflow_instance_id) { $this->workflow_instance_id = $workflow_instance_id; }
    public function setNodeId($node_id) { $this->node_id = $node_id; }
    public function setAssignedToUserId($assigned_to_user_id) { $this->assigned_to_user_id = $assigned_to_user_id; }
    public function setTaskName($task_name) { $this->task_name = $task_name; }
    public function setTaskDescription($task_description) { $this->task_description = $task_description; }
    public function setStatus($status) { $this->status = $status; }
    public function setPriority($priority) { $this->priority = $priority; }
    public function setDueDate($due_date) { $this->due_date = $due_date; }
    public function setStartedAt($started_at) { $this->started_at = $started_at; }
    public function setCompletedAt($completed_at) { $this->completed_at = $completed_at; }
    public function setCompletedBy($completed_by) { $this->completed_by = $completed_by; }
    public function setExecutionResult($execution_result) { $this->execution_result = $execution_result; }
    public function setComments($comments) { $this->comments = $comments; }
    public function setCreatedBy($created_by) { $this->created_by = $created_by; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setUpdatedBy($updated_by) { $this->updated_by = $updated_by; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }

    // Helper methods
    public function isPending() {
        return $this->status === 'pending';
    }

    public function isInProgress() {
        return $this->status === 'in_progress';
    }

    public function isCompleted() {
        return $this->status === 'completed';
    }

    public function isOverdue() {
        if (!$this->due_date || $this->isCompleted()) {
            return false;
        }

        $dueDate = new \DateTime($this->due_date);
        $now = new \DateTime();

        return $now > $dueDate;
    }

    public function getDaysUntilDue() {
        if (!$this->due_date) {
            return null;
        }

        $dueDate = new \DateTime($this->due_date);
        $now = new \DateTime();
        $diff = $now->diff($dueDate);

        return $diff->invert ? -$diff->days : $diff->days;
    }

    public function getStatusBadgeClass() {
        switch ($this->status) {
            case 'pending':
                return 'background: #f59e0b; color: white;';
            case 'in_progress':
                return 'background: #3b82f6; color: white;';
            case 'completed':
                return 'background: #10b981; color: white;';
            case 'skipped':
                return 'background: #6b7280; color: white;';
            default:
                return 'background: var(--bg-light); color: var(--text-color);';
        }
    }

    public function getPriorityBadgeClass() {
        if ($this->priority >= 8) {
            return 'background: #ef4444; color: white;'; // High
        } elseif ($this->priority >= 5) {
            return 'background: #f59e0b; color: white;'; // Medium
        } else {
            return 'background: #10b981; color: white;'; // Low
        }
    }
}
