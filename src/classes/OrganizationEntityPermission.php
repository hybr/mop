<?php

namespace App\Classes;

/**
 * OrganizationEntityPermission Entity
 * Manages permissions for OrganizationPositions to perform actions on entities
 *
 * Reads as: <OrganizationPosition> can <Action> the entity <Entity>
 * Example: "Senior Software Engineer can Create the entity Project"
 *
 * When a user is hired through OrganizationVacancy, they get assigned
 * an OrganizationPosition which determines their permissions
 *
 * Following entity_creation_instructions.md guidelines
 */
class OrganizationEntityPermission {
    private $id;

    // Core permission attributes
    private $organization_position_id;  // Required: The position this permission applies to
    private $entity_name;                // Required: Entity class name (e.g., "Organization", "OrganizationVacancy")
    private $action;                     // Required: Action allowed (create, read, update, delete, approve, reject)

    // Optional constraints
    private $scope;                      // 'own', 'department', 'team', 'organization', 'all'
    private $conditions;                 // JSON field for additional conditions
    private $description;                // Human-readable description of this permission

    // Status and metadata
    private $is_active;                  // Active status (default: 1)
    private $priority;                   // Priority for conflicting permissions (higher = takes precedence)

    // Default audit fields (per entity_creation_instructions.md #10)
    private $created_by;
    private $created_at;
    private $updated_by;
    private $updated_at;
    private $deleted_by;
    private $deleted_at;

    // Constants for actions
    const ACTION_CREATE = 'create';
    const ACTION_READ = 'read';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_APPROVE = 'approve';
    const ACTION_REJECT = 'reject';
    const ACTION_PUBLISH = 'publish';
    const ACTION_ARCHIVE = 'archive';

    // Constants for scopes
    const SCOPE_OWN = 'own';              // Only records created by the user
    const SCOPE_TEAM = 'team';            // Records within the user's team
    const SCOPE_DEPARTMENT = 'department'; // Records within the user's department
    const SCOPE_ORGANIZATION = 'organization'; // Records within the user's organization
    const SCOPE_ALL = 'all';              // All records (super admin level)

    public function __construct($data = []) {
        // Set defaults
        $this->is_active = 1;
        $this->priority = 0;
        $this->scope = self::SCOPE_OWN;

        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * Populate object from array
     */
    public function hydrate($data) {
        if (isset($data['id'])) $this->id = $data['id'];
        if (isset($data['organization_position_id'])) $this->organization_position_id = $data['organization_position_id'];
        if (isset($data['entity_name'])) $this->entity_name = $data['entity_name'];
        if (isset($data['action'])) $this->action = $data['action'];
        if (isset($data['scope'])) $this->scope = $data['scope'];
        if (isset($data['conditions'])) $this->conditions = $data['conditions'];
        if (isset($data['description'])) $this->description = $data['description'];
        if (isset($data['is_active'])) $this->is_active = $data['is_active'];
        if (isset($data['priority'])) $this->priority = $data['priority'];

        // Audit fields
        if (isset($data['created_by'])) $this->created_by = $data['created_by'];
        if (isset($data['created_at'])) $this->created_at = $data['created_at'];
        if (isset($data['updated_by'])) $this->updated_by = $data['updated_by'];
        if (isset($data['updated_at'])) $this->updated_at = $data['updated_at'];
        if (isset($data['deleted_by'])) $this->deleted_by = $data['deleted_by'];
        if (isset($data['deleted_at'])) $this->deleted_at = $data['deleted_at'];
    }

    /**
     * Convert object to array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'organization_position_id' => $this->organization_position_id,
            'entity_name' => $this->entity_name,
            'action' => $this->action,
            'scope' => $this->scope,
            'conditions' => $this->conditions,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'priority' => $this->priority,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at,
            'deleted_by' => $this->deleted_by,
            'deleted_at' => $this->deleted_at
        ];
    }

    /**
     * Get label for foreign key display
     * Returns: "Position can Action Entity"
     */
    public function getLabel() {
        return $this->getReadablePermission();
    }

    /**
     * Get human-readable permission string
     * Example: "Senior Software Engineer can Create Projects"
     */
    public function getReadablePermission() {
        $positionName = $this->organization_position_id ?? 'Position';
        $action = ucfirst($this->action ?? 'action');
        $entity = $this->entity_name ?? 'Entity';
        $scope = $this->scope ? " ({$this->scope})" : '';

        return "{$positionName} can {$action} {$entity}{$scope}";
    }

    /**
     * Get public fields (visible to all users)
     * Permissions are internal, so limited public access
     */
    public function getPublicFields() {
        return [
            'id' => $this->id,
            'entity_name' => $this->entity_name,
            'action' => $this->action,
            'description' => $this->description
        ];
    }

    /**
     * Get all available actions
     */
    public static function getAvailableActions() {
        return [
            self::ACTION_CREATE => 'Create',
            self::ACTION_READ => 'Read',
            self::ACTION_UPDATE => 'Update',
            self::ACTION_DELETE => 'Delete',
            self::ACTION_APPROVE => 'Approve',
            self::ACTION_REJECT => 'Reject',
            self::ACTION_PUBLISH => 'Publish',
            self::ACTION_ARCHIVE => 'Archive'
        ];
    }

    /**
     * Get all available scopes
     */
    public static function getAvailableScopes() {
        return [
            self::SCOPE_OWN => 'Own Records Only',
            self::SCOPE_TEAM => 'Team Records',
            self::SCOPE_DEPARTMENT => 'Department Records',
            self::SCOPE_ORGANIZATION => 'Organization Records',
            self::SCOPE_ALL => 'All Records'
        ];
    }

    /**
     * Get common entity names
     */
    public static function getCommonEntities() {
        return [
            'Organization' => 'Organization',
            'OrganizationBranch' => 'Organization Branch',
            'OrganizationDepartment' => 'Organization Department',
            'OrganizationDepartmentTeam' => 'Organization Team',
            'OrganizationDesignation' => 'Organization Designation',
            'OrganizationPosition' => 'Organization Position',
            'OrganizationWorkstation' => 'Organization Workstation',
            'OrganizationVacancy' => 'Organization Vacancy',
            'OrganizationEntityPermission' => 'Entity Permission',
            'User' => 'User'
        ];
    }

    /**
     * Get conditions as array
     */
    public function getConditionsArray() {
        if (empty($this->conditions)) {
            return [];
        }

        $decoded = json_decode($this->conditions, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Set conditions from array
     */
    public function setConditionsArray($conditions) {
        $this->conditions = json_encode($conditions);
    }

    /**
     * Get action badge class for styling
     */
    public function getActionBadgeClass() {
        switch ($this->action) {
            case self::ACTION_CREATE:
                return 'background: #10b981; color: white;'; // Green
            case self::ACTION_READ:
                return 'background: #3b82f6; color: white;'; // Blue
            case self::ACTION_UPDATE:
                return 'background: #f59e0b; color: white;'; // Orange
            case self::ACTION_DELETE:
                return 'background: #ef4444; color: white;'; // Red
            case self::ACTION_APPROVE:
                return 'background: #8b5cf6; color: white;'; // Purple
            case self::ACTION_REJECT:
                return 'background: #6b7280; color: white;'; // Gray
            case self::ACTION_PUBLISH:
                return 'background: #06b6d4; color: white;'; // Cyan
            case self::ACTION_ARCHIVE:
                return 'background: #64748b; color: white;'; // Slate
            default:
                return 'background: var(--bg-light); color: var(--text-color);';
        }
    }

    /**
     * Get scope badge class for styling
     */
    public function getScopeBadgeClass() {
        switch ($this->scope) {
            case self::SCOPE_OWN:
                return 'background: #fbbf24; color: white;'; // Amber
            case self::SCOPE_TEAM:
                return 'background: #3b82f6; color: white;'; // Blue
            case self::SCOPE_DEPARTMENT:
                return 'background: #8b5cf6; color: white;'; // Purple
            case self::SCOPE_ORGANIZATION:
                return 'background: #10b981; color: white;'; // Green
            case self::SCOPE_ALL:
                return 'background: #ef4444; color: white;'; // Red (admin level)
            default:
                return 'background: var(--bg-light); color: var(--text-color);';
        }
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getOrganizationPositionId() {
        return $this->organization_position_id;
    }

    public function getEntityName() {
        return $this->entity_name;
    }

    public function getAction() {
        return $this->action;
    }

    public function getScope() {
        return $this->scope;
    }

    public function getConditions() {
        return $this->conditions;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getIsActive() {
        return $this->is_active;
    }

    public function getPriority() {
        return $this->priority;
    }

    public function getCreatedBy() {
        return $this->created_by;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedBy() {
        return $this->updated_by;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    public function getDeletedBy() {
        return $this->deleted_by;
    }

    public function getDeletedAt() {
        return $this->deleted_at;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setOrganizationPositionId($organization_position_id) {
        $this->organization_position_id = $organization_position_id;
    }

    public function setEntityName($entity_name) {
        $this->entity_name = $entity_name;
    }

    public function setAction($action) {
        $this->action = $action;
    }

    public function setScope($scope) {
        $this->scope = $scope;
    }

    public function setConditions($conditions) {
        $this->conditions = $conditions;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setIsActive($is_active) {
        $this->is_active = $is_active;
    }

    public function setPriority($priority) {
        $this->priority = $priority;
    }

    public function setCreatedBy($created_by) {
        $this->created_by = $created_by;
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }

    public function setUpdatedBy($updated_by) {
        $this->updated_by = $updated_by;
    }

    public function setUpdatedAt($updated_at) {
        $this->updated_at = $updated_at;
    }

    public function setDeletedBy($deleted_by) {
        $this->deleted_by = $deleted_by;
    }

    public function setDeletedAt($deleted_at) {
        $this->deleted_at = $deleted_at;
    }
}
