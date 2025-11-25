<?php

namespace App\Classes;

use App\Config\Database;

/**
 * OrganizationPositionRepository
 * Handles CRUD operations for Organization Positions
 * Positions are global (common to all organizations)
 * Following ENTITY_IMPLEMENTATION_SUMMARY.md guidelines
 */
class OrganizationPositionRepository {
    private $db;
    private $tableName = 'organization_positions';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new position
     * Per permissions.md: Super Admin can create global positions
     */
    public function create(OrganizationPosition $position, $userId, $userEmail) {
        // Only Super Admin can create positions (since they're global)
        Authorization::requireSuperAdmin($userEmail, 'create positions');

        // Check code uniqueness
        if ($this->codeExists($position->getCode())) {
            throw new \Exception("Position code '{$position->getCode()}' already exists. Please choose another.");
        }

        $id = $this->generateId();

        $data = [
            'id' => $id,
            'name' => $position->getName(),
            'code' => $position->getCode(),
            'description' => $position->getDescription(),
            'organization_department_id' => $position->getOrganizationDepartmentId(),
            'organization_department_team_id' => $position->getOrganizationDepartmentTeamId(),
            'organization_designation_id' => $position->getOrganizationDesignationId(),
            'min_education' => $position->getMinEducation(),
            'min_education_field' => $position->getMinEducationField(),
            'min_experience_years' => $position->getMinExperienceYears(),
            'skills_required' => $position->getSkillsRequired(),
            'skills_preferred' => $position->getSkillsPreferred(),
            'certifications_required' => $position->getCertificationsRequired(),
            'certifications_preferred' => $position->getCertificationsPreferred(),
            'employment_type' => $position->getEmploymentType(),
            'reports_to_position_id' => $position->getReportsToPositionId(),
            'headcount' => $position->getHeadcount(),
            'salary_range_min' => $position->getSalaryRangeMin(),
            'salary_range_max' => $position->getSalaryRangeMax(),
            'salary_currency' => $position->getSalaryCurrency() ?: 'INR',
            'is_active' => $position->getIsActive() !== null ? (int)$position->getIsActive() : 1,
            'sort_order' => $position->getSortOrder() ?? 0,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Remove null values
        $data = array_filter($data, function($value) {
            return $value !== null;
        });

        $pdo = $this->db->getPdo();
        $response = $this->db->query($this->tableName, 'INSERT', [], $data);

        if ($response['success'] && !empty($response['data'])) {
            $position->hydrate($response['data'][0]);
            return $position;
        }

        throw new \Exception("Failed to create position");
    }

    /**
     * Find position by ID
     */
    public function findById($id) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName} WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if ($data) {
            return new OrganizationPosition($data);
        }

        return null;
    }

    /**
     * Get all positions (non-deleted)
     */
    public function findAll($limit = 1000, $offset = 0) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE deleted_at IS NULL
                               ORDER BY sort_order ASC, name ASC
                               LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $data = $stmt->fetchAll();

        $positions = [];
        foreach ($data as $positionData) {
            $positions[] = new OrganizationPosition($positionData);
        }
        return $positions;
    }

    /**
     * Get active positions for dropdowns
     */
    public function findActive($limit = 1000) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE deleted_at IS NULL AND is_active = 1
                               ORDER BY sort_order ASC, name ASC
                               LIMIT ?");
        $stmt->execute([$limit]);
        $data = $stmt->fetchAll();

        $positions = [];
        foreach ($data as $positionData) {
            $positions[] = new OrganizationPosition($positionData);
        }
        return $positions;
    }

    /**
     * Find positions by department
     */
    public function findByDepartment($departmentId, $limit = 100) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE organization_department_id = ? AND deleted_at IS NULL
                               ORDER BY sort_order ASC, name ASC
                               LIMIT ?");
        $stmt->execute([$departmentId, $limit]);
        $data = $stmt->fetchAll();

        $positions = [];
        foreach ($data as $positionData) {
            $positions[] = new OrganizationPosition($positionData);
        }
        return $positions;
    }

    /**
     * Find positions by team
     */
    public function findByTeam($teamId, $limit = 100) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE organization_department_team_id = ? AND deleted_at IS NULL
                               ORDER BY sort_order ASC, name ASC
                               LIMIT ?");
        $stmt->execute([$teamId, $limit]);
        $data = $stmt->fetchAll();

        $positions = [];
        foreach ($data as $positionData) {
            $positions[] = new OrganizationPosition($positionData);
        }
        return $positions;
    }

    /**
     * Find positions by designation
     */
    public function findByDesignation($designationId, $limit = 100) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE organization_designation_id = ? AND deleted_at IS NULL
                               ORDER BY sort_order ASC, name ASC
                               LIMIT ?");
        $stmt->execute([$designationId, $limit]);
        $data = $stmt->fetchAll();

        $positions = [];
        foreach ($data as $positionData) {
            $positions[] = new OrganizationPosition($positionData);
        }
        return $positions;
    }

    /**
     * Find positions by employment type
     */
    public function findByEmploymentType($employmentType, $limit = 100) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE employment_type = ? AND deleted_at IS NULL AND is_active = 1
                               ORDER BY sort_order ASC, name ASC
                               LIMIT ?");
        $stmt->execute([$employmentType, $limit]);
        $data = $stmt->fetchAll();

        $positions = [];
        foreach ($data as $positionData) {
            $positions[] = new OrganizationPosition($positionData);
        }
        return $positions;
    }

    /**
     * Update position
     */
    public function update(OrganizationPosition $position, $userId, $userEmail) {
        if (!$position->getId()) {
            throw new \Exception("Position ID is required for update");
        }

        // Check if position exists
        $existing = $this->findById($position->getId());
        if (!$existing) {
            throw new \Exception("Position not found");
        }

        // Check if user can edit (Super Admin only)
        Authorization::requireSuperAdmin($userEmail, 'edit positions');

        // Check code uniqueness (excluding current position)
        if ($this->codeExists($position->getCode(), $position->getId())) {
            throw new \Exception("Position code '{$position->getCode()}' already exists. Please choose another.");
        }

        $data = [
            'name' => $position->getName(),
            'code' => $position->getCode(),
            'description' => $position->getDescription(),
            'organization_department_id' => $position->getOrganizationDepartmentId(),
            'organization_department_team_id' => $position->getOrganizationDepartmentTeamId(),
            'organization_designation_id' => $position->getOrganizationDesignationId(),
            'min_education' => $position->getMinEducation(),
            'min_education_field' => $position->getMinEducationField(),
            'min_experience_years' => $position->getMinExperienceYears(),
            'skills_required' => $position->getSkillsRequired(),
            'skills_preferred' => $position->getSkillsPreferred(),
            'certifications_required' => $position->getCertificationsRequired(),
            'certifications_preferred' => $position->getCertificationsPreferred(),
            'employment_type' => $position->getEmploymentType(),
            'reports_to_position_id' => $position->getReportsToPositionId(),
            'headcount' => $position->getHeadcount(),
            'salary_range_min' => $position->getSalaryRangeMin(),
            'salary_range_max' => $position->getSalaryRangeMax(),
            'salary_currency' => $position->getSalaryCurrency(),
            'is_active' => $position->getIsActive(),
            'sort_order' => $position->getSortOrder(),
            'updated_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Build update query
        $pdo = $this->db->getPdo();
        $setClauses = [];
        $params = [];
        foreach ($data as $key => $value) {
            $setClauses[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $position->getId();

        $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setClauses) . "
                WHERE id = ? AND deleted_at IS NULL";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $this->findById($position->getId());
    }

    /**
     * Soft delete position
     */
    public function softDelete($id, $userId, $userEmail) {
        Authorization::requireSuperAdmin($userEmail, 'delete positions');

        $existing = $this->findById($id);
        if (!$existing) {
            throw new \Exception("Position not found");
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("UPDATE {$this->tableName}
                               SET deleted_by = ?, deleted_at = ?
                               WHERE id = ? AND deleted_at IS NULL");
        $result = $stmt->execute([$userId, date('Y-m-d H:i:s'), $id]);
        return $result && $stmt->rowCount() > 0;
    }

    /**
     * Restore soft-deleted position
     */
    public function restore($id, $userEmail) {
        Authorization::requireSuperAdmin($userEmail, 'restore positions');

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("UPDATE {$this->tableName}
                               SET deleted_by = NULL, deleted_at = NULL
                               WHERE id = ? AND deleted_at IS NOT NULL");
        $result = $stmt->execute([$id]);
        return $result && $stmt->rowCount() > 0;
    }

    /**
     * Get deleted positions (for trash/restore)
     */
    public function findDeleted($userEmail, $limit = 100) {
        if (!Authorization::isSuperAdmin($userEmail)) {
            return [];
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE deleted_at IS NOT NULL
                               ORDER BY deleted_at DESC
                               LIMIT ?");
        $stmt->execute([$limit]);
        $data = $stmt->fetchAll();

        $positions = [];
        foreach ($data as $positionData) {
            $positions[] = new OrganizationPosition($positionData);
        }
        return $positions;
    }

    /**
     * Search positions
     */
    public function search($query, $limit = 20) {
        $pdo = $this->db->getPdo();
        $searchTerm = "%$query%";
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE deleted_at IS NULL AND is_active = 1
                               AND (name LIKE ? OR code LIKE ? OR description LIKE ? OR skills_required LIKE ?)
                               ORDER BY sort_order ASC, name ASC
                               LIMIT ?");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
        $data = $stmt->fetchAll();

        $positions = [];
        foreach ($data as $positionData) {
            $positions[] = new OrganizationPosition($positionData);
        }
        return $positions;
    }

    /**
     * Count positions
     */
    public function count($includeDeleted = false) {
        $pdo = $this->db->getPdo();
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName}";
        if (!$includeDeleted) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Check if code already exists
     */
    public function codeExists($code, $excludeId = null) {
        $pdo = $this->db->getPdo();
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName}
                WHERE code = ? AND deleted_at IS NULL";
        $params = [$code];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Generate unique ID
     */
    private function generateId() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Check if user is Super Admin
     * @deprecated Use Authorization::isSuperAdmin() instead
     */
    public function isSuperAdmin($email) {
        return Authorization::isSuperAdmin($email);
    }

    /**
     * Check if user can edit position (Super Admin only since positions are global)
     */
    public function canEdit($userEmail) {
        return Authorization::canEditGlobalEntities($userEmail);
    }

    /**
     * Get positions as options for dropdown (for foreign key usage)
     * Returns array of ['value' => id, 'label' => label]
     */
    public function getAsOptions($departmentId = null, $teamId = null) {
        if ($teamId) {
            $positions = $this->findByTeam($teamId);
        } elseif ($departmentId) {
            $positions = $this->findByDepartment($departmentId);
        } else {
            $positions = $this->findActive();
        }

        $options = [];
        foreach ($positions as $position) {
            $options[] = [
                'value' => $position->getId(),
                'label' => $position->getLabel()
            ];
        }
        return $options;
    }

    /**
     * Get positions with related data (department, team, designation names)
     */
    public function findAllWithRelations($limit = 100, $offset = 0) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("
            SELECT p.*,
                   d.name as department_name,
                   t.name as team_name,
                   des.name as designation_name,
                   des.level as designation_level
            FROM {$this->tableName} p
            LEFT JOIN organization_departments d ON p.organization_department_id = d.id
            LEFT JOIN organization_department_teams t ON p.organization_department_team_id = t.id
            LEFT JOIN organization_designations des ON p.organization_designation_id = des.id
            WHERE p.deleted_at IS NULL
            ORDER BY p.sort_order ASC, p.name ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
}
