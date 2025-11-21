<?php

namespace App\Classes;

class OrganizationDesignation {
    private $id;
    private $name;                      // Designation name (e.g., "Manager", "Senior Developer")
    private $code;                      // Short code (e.g., "MGR", "SR_DEV")
    private $description;               // Description of the designation
    private $level;                     // Hierarchy level (1=entry, 2=mid, 3=senior, 4=lead, 5=manager, 6=executive)
    private $organization_id;           // Optional: null = global across all orgs
    private $organization_department_id; // Optional: null = applies to all departments
    private $is_active;                 // Active status
    private $sort_order;                // Display order

    // Audit fields
    private $created_by;
    private $created_at;
    private $updated_by;
    private $updated_at;
    private $deleted_by;
    private $deleted_at;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * Populate object from array
     */
    public function hydrate($data) {
        if (isset($data['id'])) $this->id = $data['id'];
        if (isset($data['name'])) $this->name = $data['name'];
        if (isset($data['code'])) $this->code = $data['code'];
        if (isset($data['description'])) $this->description = $data['description'];
        if (isset($data['level'])) $this->level = $data['level'];
        if (isset($data['organization_id'])) $this->organization_id = $data['organization_id'];
        if (isset($data['organization_department_id'])) $this->organization_department_id = $data['organization_department_id'];
        if (isset($data['is_active'])) $this->is_active = $data['is_active'];
        if (isset($data['sort_order'])) $this->sort_order = $data['sort_order'];
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
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'level' => $this->level,
            'organization_id' => $this->organization_id,
            'organization_department_id' => $this->organization_department_id,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
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
     */
    public function getLabel() {
        if ($this->code) {
            return $this->name . ' (' . $this->code . ')';
        }
        return $this->name;
    }

    /**
     * Get level name from level number
     */
    public function getLevelName() {
        $levels = [
            1 => 'Entry Level',
            2 => 'Mid Level',
            3 => 'Senior Level',
            4 => 'Lead Level',
            5 => 'Manager Level',
            6 => 'Executive Level'
        ];
        return $levels[$this->level] ?? 'Unknown';
    }

    /**
     * Get public fields (visible to all users)
     */
    public function getPublicFields() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'level' => $this->level,
            'is_active' => $this->is_active
        ];
    }

    /**
     * Check if a field is public
     */
    public static function isPublicField($fieldName) {
        $publicFields = ['id', 'name', 'code', 'description', 'level', 'is_active'];
        return in_array($fieldName, $publicFields);
    }

    /**
     * Validate designation data
     */
    public function validate() {
        $errors = [];

        if (empty($this->name)) {
            $errors[] = "Designation name is required";
        }

        if (empty($this->code)) {
            $errors[] = "Designation code is required";
        } elseif (!preg_match('/^[A-Z0-9_]+$/', $this->code)) {
            $errors[] = "Code must contain only uppercase letters, numbers, and underscores";
        } elseif (strlen($this->code) < 2 || strlen($this->code) > 20) {
            $errors[] = "Code must be between 2 and 20 characters";
        }

        if (!empty($this->level) && ($this->level < 1 || $this->level > 6)) {
            $errors[] = "Level must be between 1 and 6";
        }

        return $errors;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getCode() {
        return $this->code;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getLevel() {
        return $this->level;
    }

    public function getOrganizationId() {
        return $this->organization_id;
    }

    public function getOrganizationDepartmentId() {
        return $this->organization_department_id;
    }

    public function getIsActive() {
        return $this->is_active;
    }

    public function getSortOrder() {
        return $this->sort_order;
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

    public function isDeleted() {
        return !empty($this->deleted_at);
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setCode($code) {
        $this->code = strtoupper(trim($code));
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setLevel($level) {
        $this->level = $level ? (int)$level : null;
    }

    public function setOrganizationId($organization_id) {
        $this->organization_id = $organization_id ?: null;
    }

    public function setOrganizationDepartmentId($organization_department_id) {
        $this->organization_department_id = $organization_department_id ?: null;
    }

    public function setIsActive($is_active) {
        $this->is_active = (bool)$is_active;
    }

    public function setSortOrder($sort_order) {
        $this->sort_order = (int)$sort_order;
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
