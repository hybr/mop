<?php

namespace App\Classes;

use App\Config\Database;

/**
 * WorkflowInstanceRepository
 * Manages CRUD operations for workflow instances
 */
class WorkflowInstanceRepository {
    private $db;
    private $tableName = 'workflow_instances';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new workflow instance
     */
    public function create(WorkflowInstance $instance, $userId) {
        $data = [
            'id' => $this->generateId(),
            'workflow_id' => $instance->getWorkflowId(),
            'instance_name' => $instance->getInstanceName(),
            'entity_id' => $instance->getEntityId(),
            'entity_type' => $instance->getEntityType(),
            'current_node_id' => $instance->getCurrentNodeId(),
            'status' => $instance->getStatus() ?? 'active',
            'initiated_by' => $instance->getInitiatedBy(),
            'started_at' => date('Y-m-d H:i:s'),
            'metadata' => $instance->getMetadata(),
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
            $instance->hydrate($response['data'][0]);
            return $instance;
        }

        throw new \Exception("Failed to create workflow instance");
    }

    /**
     * Find instance by ID
     */
    public function findById($id) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName} WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();

            if ($data) {
                return new WorkflowInstance($data);
            }
        }

        return null;
    }

    /**
     * Find all active instances
     */
    public function findActive($limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("
                SELECT * FROM {$this->tableName}
                WHERE status = 'active'
                ORDER BY started_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $data = $stmt->fetchAll();

            $instances = [];
            foreach ($data as $row) {
                $instances[] = new WorkflowInstance($row);
            }
            return $instances;
        }

        return [];
    }

    /**
     * Find instances by workflow type
     */
    public function findByWorkflowId($workflowId, $status = null, $limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();

            $sql = "SELECT * FROM {$this->tableName} WHERE workflow_id = ?";
            $params = [$workflowId];

            if ($status !== null) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY started_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll();

            $instances = [];
            foreach ($data as $row) {
                $instances[] = new WorkflowInstance($row);
            }
            return $instances;
        }

        return [];
    }

    /**
     * Find instances by entity
     */
    public function findByEntity($entityType, $entityId) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("
                SELECT * FROM {$this->tableName}
                WHERE entity_type = ? AND entity_id = ?
                ORDER BY started_at DESC
            ");
            $stmt->execute([$entityType, $entityId]);
            $data = $stmt->fetchAll();

            $instances = [];
            foreach ($data as $row) {
                $instances[] = new WorkflowInstance($row);
            }
            return $instances;
        }

        return [];
    }

    /**
     * Update workflow instance
     */
    public function update(WorkflowInstance $instance, $userId) {
        if (!$instance->getId()) {
            throw new \Exception("Instance ID is required for update");
        }

        $data = [
            'current_node_id' => $instance->getCurrentNodeId(),
            'status' => $instance->getStatus(),
            'completed_at' => $instance->getCompletedAt(),
            'cancelled_at' => $instance->getCancelledAt(),
            'cancelled_by' => $instance->getCancelledBy(),
            'cancellation_reason' => $instance->getCancellationReason(),
            'metadata' => $instance->getMetadata(),
            'updated_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $data = array_filter($data, function($value) {
            return $value !== null;
        });

        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'UPDATE', ['id' => $instance->getId()], $data);
        } else {
            $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $instance->getId(), $data);
        }

        if ($response['success']) {
            return true;
        }

        throw new \Exception("Failed to update workflow instance");
    }

    /**
     * Move workflow to next node
     */
    public function moveToNode($instanceId, $nodeId, $userId) {
        $instance = $this->findById($instanceId);
        if (!$instance) {
            throw new \Exception("Instance not found");
        }

        $instance->setCurrentNodeId($nodeId);
        return $this->update($instance, $userId);
    }

    /**
     * Complete workflow instance
     */
    public function complete($instanceId, $userId) {
        $instance = $this->findById($instanceId);
        if (!$instance) {
            throw new \Exception("Instance not found");
        }

        $instance->setStatus('completed');
        $instance->setCompletedAt(date('Y-m-d H:i:s'));
        return $this->update($instance, $userId);
    }

    /**
     * Cancel workflow instance
     */
    public function cancel($instanceId, $userId, $reason = null) {
        $instance = $this->findById($instanceId);
        if (!$instance) {
            throw new \Exception("Instance not found");
        }

        $instance->setStatus('cancelled');
        $instance->setCancelledAt(date('Y-m-d H:i:s'));
        $instance->setCancelledBy($userId);
        $instance->setCancellationReason($reason);
        return $this->update($instance, $userId);
    }

    /**
     * Count instances
     */
    public function count($filters = []) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();

            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE 1=1";
            $params = [];

            if (!empty($filters['workflow_id'])) {
                $sql .= " AND workflow_id = ?";
                $params[] = $filters['workflow_id'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();

            return (int)$result['count'];
        }

        return 0;
    }

    /**
     * Get execution history for instance
     */
    public function getExecutionLog($instanceId) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("
                SELECT
                    wel.*,
                    u.name as user_name,
                    u.email as user_email
                FROM workflow_execution_log wel
                LEFT JOIN users u ON wel.user_id = u.id
                WHERE wel.workflow_instance_id = ?
                ORDER BY wel.executed_at ASC
            ");
            $stmt->execute([$instanceId]);
            return $stmt->fetchAll();
        }

        return [];
    }

    /**
     * Get nodes for workflow
     */
    public function getWorkflowNodes($workflowId) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("
                SELECT * FROM workflow_nodes
                WHERE workflow_id = ?
                ORDER BY sort_order ASC
            ");
            $stmt->execute([$workflowId]);
            return $stmt->fetchAll();
        }

        return [];
    }

    /**
     * Get edges for workflow
     */
    public function getWorkflowEdges($workflowId) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("
                SELECT * FROM workflow_edges
                WHERE workflow_id = ?
                ORDER BY priority DESC
            ");
            $stmt->execute([$workflowId]);
            return $stmt->fetchAll();
        }

        return [];
    }

    /**
     * Generate unique ID
     */
    private function generateId() {
        return 'wfi_' . bin2hex(random_bytes(16));
    }
}
