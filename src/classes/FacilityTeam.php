<?php

namespace App\Classes;

/**
 * FacilityTeam Entity
 * Manages teams within facilities
 * Facility = Department (from organization_departments table)
 * Each facility can have multiple teams managed by this entity
 * Following entity_creation_instructions.md guidelines
 */
class FacilityTeam {
    private $id;
    private $name;                   // Team name (e.g., "Operations Team", "Maintenance Team")
    private $code;                   // Unique code (e.g., "OPS_TEAM", "MAINT_TEAM")
    private $description;            // Team description
    private $parent_team_id;         // Parent team (for hierarchical structure)
    private $facility_id;            // Facility (department) this team belongs to
    private $organization_id;        // Organization this team belongs to
    private $is_active;              // Active status
    private $sort_order;             // Display order

    // Default audit fields (per entity_creation_instructions.md #10)
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
        if (isset($data['parent_team_id'])) $this->parent_team_id = $data['parent_team_id'];
        if (isset($data['facility_id'])) $this->facility_id = $data['facility_id'];
        if (isset($data['organization_id'])) $this->organization_id = $data['organization_id'];
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
            'parent_team_id' => $this->parent_team_id,
            'facility_id' => $this->facility_id,
            'organization_id' => $this->organization_id,
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
     * Get label for foreign key display (#11 in entity_creation_instructions.md)
     * Returns the label fields when this entity is used as a foreign key
     */
    public function getLabel() {
        // Return name and code for clear identification
        if ($this->code) {
            return $this->name . ' (' . $this->code . ')';
        }
        return $this->name;
    }

    /**
     * Get public fields (visible to all users including guests)
     * Per permissions.md: Organization Workers can view facility teams
     */
    public function getPublicFields() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'parent_team_id' => $this->parent_team_id,
            'facility_id' => $this->facility_id,
            'organization_id' => $this->organization_id,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order
        ];
    }

    /**
     * Check if a field is public (visible to all)
     */
    public static function isPublicField($fieldName) {
        $publicFields = ['id', 'name', 'code', 'description', 'parent_team_id', 'facility_id', 'organization_id', 'is_active', 'sort_order'];
        return in_array($fieldName, $publicFields);
    }

    // ============= Getters =============
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

    public function getParentTeamId() {
        return $this->parent_team_id;
    }

    public function getFacilityId() {
        return $this->facility_id;
    }

    public function getOrganizationId() {
        return $this->organization_id;
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

    // ============= Setters =============
    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setCode($code) {
        // Validate code format - uppercase letters, numbers, and underscores
        if (!empty($code)) {
            $code = strtoupper(trim($code));
            if (!preg_match('/^[A-Z0-9_]+$/', $code)) {
                throw new \Exception("Facility team code can only contain uppercase letters, numbers, and underscores");
            }
            if (strlen($code) < 2 || strlen($code) > 20) {
                throw new \Exception("Facility team code must be between 2 and 20 characters");
            }
        }
        $this->code = $code;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setParentTeamId($parent_team_id) {
        $this->parent_team_id = $parent_team_id;
    }

    public function setFacilityId($facility_id) {
        $this->facility_id = $facility_id;
    }

    public function setOrganizationId($organization_id) {
        $this->organization_id = $organization_id;
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

    /**
     * Validate facility team data
     */
    public function validate() {
        $errors = [];

        if (empty($this->name)) {
            $errors[] = "Facility team name is required";
        }

        if (empty($this->code)) {
            $errors[] = "Facility team code is required";
        } elseif (!preg_match('/^[A-Z0-9_]+$/', $this->code)) {
            $errors[] = "Facility team code can only contain uppercase letters, numbers, and underscores";
        } elseif (strlen($this->code) < 2 || strlen($this->code) > 20) {
            $errors[] = "Facility team code must be between 2 and 20 characters";
        }

        return $errors;
    }
}
