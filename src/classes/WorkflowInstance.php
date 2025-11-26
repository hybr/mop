<?php

namespace App\Classes;

/**
 * WorkflowInstance Entity
 * Represents an individual execution of a workflow
 */
class WorkflowInstance {
    private $id;
    private $workflow_id;
    private $instance_name;
    private $entity_id;
    private $entity_type;
    private $current_node_id;
    private $status; // 'active', 'completed', 'cancelled', 'failed'
    private $initiated_by;
    private $started_at;
    private $completed_at;
    private $cancelled_at;
    private $cancelled_by;
    private $cancellation_reason;
    private $metadata;
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
        if (isset($data['workflow_id'])) $this->workflow_id = $data['workflow_id'];
        if (isset($data['instance_name'])) $this->instance_name = $data['instance_name'];
        if (isset($data['entity_id'])) $this->entity_id = $data['entity_id'];
        if (isset($data['entity_type'])) $this->entity_type = $data['entity_type'];
        if (isset($data['current_node_id'])) $this->current_node_id = $data['current_node_id'];
        if (isset($data['status'])) $this->status = $data['status'];
        if (isset($data['initiated_by'])) $this->initiated_by = $data['initiated_by'];
        if (isset($data['started_at'])) $this->started_at = $data['started_at'];
        if (isset($data['completed_at'])) $this->completed_at = $data['completed_at'];
        if (isset($data['cancelled_at'])) $this->cancelled_at = $data['cancelled_at'];
        if (isset($data['cancelled_by'])) $this->cancelled_by = $data['cancelled_by'];
        if (isset($data['cancellation_reason'])) $this->cancellation_reason = $data['cancellation_reason'];
        if (isset($data['metadata'])) $this->metadata = $data['metadata'];
        if (isset($data['created_by'])) $this->created_by = $data['created_by'];
        if (isset($data['created_at'])) $this->created_at = $data['created_at'];
        if (isset($data['updated_by'])) $this->updated_by = $data['updated_by'];
        if (isset($data['updated_at'])) $this->updated_at = $data['updated_at'];
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'workflow_id' => $this->workflow_id,
            'instance_name' => $this->instance_name,
            'entity_id' => $this->entity_id,
            'entity_type' => $this->entity_type,
            'current_node_id' => $this->current_node_id,
            'status' => $this->status,
            'initiated_by' => $this->initiated_by,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'cancelled_at' => $this->cancelled_at,
            'cancelled_by' => $this->cancelled_by,
            'cancellation_reason' => $this->cancellation_reason,
            'metadata' => $this->metadata,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at
        ];
    }

    // Getters
    public function getId() { return $this->id; }
    public function getWorkflowId() { return $this->workflow_id; }
    public function getInstanceName() { return $this->instance_name; }
    public function getEntityId() { return $this->entity_id; }
    public function getEntityType() { return $this->entity_type; }
    public function getCurrentNodeId() { return $this->current_node_id; }
    public function getStatus() { return $this->status; }
    public function getInitiatedBy() { return $this->initiated_by; }
    public function getStartedAt() { return $this->started_at; }
    public function getCompletedAt() { return $this->completed_at; }
    public function getCancelledAt() { return $this->cancelled_at; }
    public function getCancelledBy() { return $this->cancelled_by; }
    public function getCancellationReason() { return $this->cancellation_reason; }
    public function getMetadata() { return $this->metadata; }
    public function getCreatedBy() { return $this->created_by; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedBy() { return $this->updated_by; }
    public function getUpdatedAt() { return $this->updated_at; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setWorkflowId($workflow_id) { $this->workflow_id = $workflow_id; }
    public function setInstanceName($instance_name) { $this->instance_name = $instance_name; }
    public function setEntityId($entity_id) { $this->entity_id = $entity_id; }
    public function setEntityType($entity_type) { $this->entity_type = $entity_type; }
    public function setCurrentNodeId($current_node_id) { $this->current_node_id = $current_node_id; }
    public function setStatus($status) { $this->status = $status; }
    public function setInitiatedBy($initiated_by) { $this->initiated_by = $initiated_by; }
    public function setStartedAt($started_at) { $this->started_at = $started_at; }
    public function setCompletedAt($completed_at) { $this->completed_at = $completed_at; }
    public function setCancelledAt($cancelled_at) { $this->cancelled_at = $cancelled_at; }
    public function setCancelledBy($cancelled_by) { $this->cancelled_by = $cancelled_by; }
    public function setCancellationReason($cancellation_reason) { $this->cancellation_reason = $cancellation_reason; }
    public function setMetadata($metadata) { $this->metadata = $metadata; }
    public function setCreatedBy($created_by) { $this->created_by = $created_by; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setUpdatedBy($updated_by) { $this->updated_by = $updated_by; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }

    // Helper methods
    public function getMetadataArray() {
        if (empty($this->metadata)) {
            return [];
        }
        $decoded = json_decode($this->metadata, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function setMetadataArray($metadata) {
        $this->metadata = json_encode($metadata);
    }

    public function getElapsedTime() {
        if (!$this->started_at) {
            return 0;
        }

        $start = new \DateTime($this->started_at);
        $end = $this->completed_at ? new \DateTime($this->completed_at) : new \DateTime();

        return $start->diff($end);
    }

    public function getElapsedDays() {
        $diff = $this->getElapsedTime();
        return $diff->days;
    }

    public function isActive() {
        return $this->status === 'active';
    }

    public function isCompleted() {
        return $this->status === 'completed';
    }

    public function isCancelled() {
        return $this->status === 'cancelled';
    }

    public function getStatusBadgeClass() {
        switch ($this->status) {
            case 'active':
                return 'background: #10b981; color: white;';
            case 'completed':
                return 'background: #3b82f6; color: white;';
            case 'cancelled':
                return 'background: #6b7280; color: white;';
            case 'failed':
                return 'background: #ef4444; color: white;';
            default:
                return 'background: var(--bg-light); color: var(--text-color);';
        }
    }
}
