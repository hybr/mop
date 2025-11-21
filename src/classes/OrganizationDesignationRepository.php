<?php

namespace App\Classes;

use App\Config\Database;

class OrganizationDesignationRepository {
    private $db;
    private $tableName = 'organization_designations';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new designation
     */
    public function create(OrganizationDesignation $designation, $userId, $userEmail) {
        // Check code uniqueness
        if ($this->codeExists($designation->getCode())) {
            throw new \Exception("Designation code '{$designation->getCode()}' already exists. Please choose another.");
        }

        $data = [
            'name' => $designation->getName(),
            'code' => $designation->getCode(),
            'description' => $designation->getDescription(),
            'level' => $designation->getLevel(),
            'organization_id' => $designation->getOrganizationId(),
            'organization_department_id' => $designation->getOrganizationDepartmentId(),
            'is_active' => $designation->getIsActive() !== null ? (int)$designation->getIsActive() : 1,
            'sort_order' => $designation->getSortOrder() ?? 0,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Remove null values but keep 0 and empty strings
        $data = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });

        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'INSERT', [], $data);
        } else {
            $response = $this->db->request('POST', $this->tableName, $data);
        }

        if ($response['success'] && !empty($response['data'])) {
            $designation->hydrate($response['data'][0]);
            return $designation;
        }

        throw new \Exception("Failed to create designation: " . json_encode($response['data'] ?? 'Unknown error'));
    }

    /**
     * Find designation by ID (only non-deleted)
     */
    public function findById($id) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$id]);
            $data = $stmt->fetch();

            if ($data) {
                return new OrganizationDesignation($data);
            }
        } else {
            $response = $this->db->request('GET', $this->tableName . '?id=eq.' . $id . '&deleted_at=is.null');

            if ($response['success'] && !empty($response['data'])) {
                return new OrganizationDesignation($response['data'][0]);
            }
        }

        return null;
    }

    /**
     * Get all designations (non-deleted only)
     */
    public function findAll($limit = 1000, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE deleted_at IS NULL
                                   ORDER BY sort_order ASC, name ASC
                                   LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $data = $stmt->fetchAll();

            $designations = [];
            foreach ($data as $designationData) {
                $designations[] = new OrganizationDesignation($designationData);
            }
            return $designations;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?deleted_at=is.null&limit=' . $limit . '&offset=' . $offset . '&order=sort_order.asc,name.asc');

            if ($response['success']) {
                $designations = [];
                foreach ($response['data'] as $designationData) {
                    $designations[] = new OrganizationDesignation($designationData);
                }
                return $designations;
            }
        }

        return [];
    }

    /**
     * Update designation
     */
    public function update(OrganizationDesignation $designation, $userId, $userEmail) {
        if (!$designation->getId()) {
            throw new \Exception("Designation ID is required for update");
        }

        // Check if designation exists
        $existing = $this->findById($designation->getId());
        if (!$existing) {
            throw new \Exception("Designation not found");
        }

        // Check if user can edit (Super Admin only)
        if (!$this->canEdit($userEmail)) {
            throw new \Exception("Only Super Admin can edit designations");
        }

        // Check code uniqueness (excluding current designation)
        if ($this->codeExists($designation->getCode(), $designation->getId())) {
            throw new \Exception("Designation code '{$designation->getCode()}' already exists. Please choose another.");
        }

        $data = [
            'name' => $designation->getName(),
            'code' => $designation->getCode(),
            'description' => $designation->getDescription(),
            'level' => $designation->getLevel(),
            'organization_id' => $designation->getOrganizationId(),
            'organization_department_id' => $designation->getOrganizationDepartmentId(),
            'is_active' => $designation->getIsActive(),
            'sort_order' => $designation->getSortOrder(),
            'updated_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Remove null values except where we want to explicitly set null
        $filteredData = [];
        foreach ($data as $key => $value) {
            if ($value !== null || in_array($key, ['organization_id', 'organization_department_id'])) {
                $filteredData[$key] = $value;
            }
        }

        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();

            $setClauses = [];
            $params = [];
            foreach ($filteredData as $key => $value) {
                $setClauses[] = "$key = ?";
                $params[] = $value;
            }

            $params[] = $designation->getId();

            $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setClauses) . "
                    WHERE id = ? AND deleted_at IS NULL";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $response = $this->findById($designation->getId());
            if ($response) {
                return $response;
            }
        } else {
            $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $designation->getId() . '&deleted_at=is.null', $filteredData);

            if ($response['success'] && !empty($response['data'])) {
                $designation->hydrate($response['data'][0]);
                return $designation;
            }
        }

        throw new \Exception("Failed to update designation");
    }

    /**
     * Soft delete designation (Super Admin only)
     */
    public function softDelete($id, $userId, $userEmail) {
        // Check if user can delete (Super Admin only)
        if (!$this->canEdit($userEmail)) {
            throw new \Exception("Only Super Admin can delete designations");
        }

        $existing = $this->findById($id);
        if (!$existing) {
            throw new \Exception("Designation not found");
        }

        $data = [
            'deleted_by' => $userId,
            'deleted_at' => date('Y-m-d H:i:s')
        ];

        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("UPDATE {$this->tableName}
                                   SET deleted_by = ?, deleted_at = ?
                                   WHERE id = ? AND deleted_at IS NULL");
            $result = $stmt->execute([$userId, $data['deleted_at'], $id]);
            return $result && $stmt->rowCount() > 0;
        } else {
            $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $id . '&deleted_at=is.null', $data);
            return $response['success'];
        }
    }

    /**
     * Get deleted designations (for trash/restore functionality)
     */
    public function findDeleted($userEmail, $limit = 100) {
        // Only Super Admin can see deleted items
        if (!$this->isSuperAdmin($userEmail)) {
            return [];
        }

        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE deleted_at IS NOT NULL
                                   ORDER BY deleted_at DESC
                                   LIMIT ?");
            $stmt->execute([$limit]);
            $data = $stmt->fetchAll();

            $designations = [];
            foreach ($data as $designationData) {
                $designations[] = new OrganizationDesignation($designationData);
            }
            return $designations;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?deleted_at=not.is.null&limit=' . $limit . '&order=deleted_at.desc');

            if ($response['success']) {
                $designations = [];
                foreach ($response['data'] as $designationData) {
                    $designations[] = new OrganizationDesignation($designationData);
                }
                return $designations;
            }
        }

        return [];
    }

    /**
     * Restore soft-deleted designation (Super Admin only)
     */
    public function restore($id, $userEmail) {
        // Only Super Admin can restore
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can restore designations");
        }

        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("UPDATE {$this->tableName}
                                   SET deleted_by = NULL, deleted_at = NULL
                                   WHERE id = ? AND deleted_at IS NOT NULL");
            $result = $stmt->execute([$id]);
            return $result && $stmt->rowCount() > 0;
        } else {
            $data = ['deleted_by' => null, 'deleted_at' => null];
            $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $id . '&deleted_at=not.is.null', $data);
            return $response['success'];
        }
    }

    /**
     * Count designations
     */
    public function count($includeDeleted = false) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName}";
            if (!$includeDeleted) {
                $sql .= " WHERE deleted_at IS NULL";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } else {
            $endpoint = $this->tableName;
            if (!$includeDeleted) {
                $endpoint .= '?deleted_at=is.null';
            }
            $endpoint .= ($includeDeleted ? '?' : '&') . 'select=count';

            $response = $this->db->request('GET', $endpoint, null, ['Prefer: count=exact']);

            if ($response['success']) {
                return $response['data'][0]['count'] ?? 0;
            }
        }

        return 0;
    }

    /**
     * Check if code already exists
     */
    public function codeExists($code, $excludeId = null) {
        if ($this->db->getDriver() === 'sqlite') {
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
        } else {
            $endpoint = $this->tableName . '?code=eq.' . urlencode($code) . '&deleted_at=is.null';
            if ($excludeId) {
                $endpoint .= '&id=neq.' . $excludeId;
            }
            $response = $this->db->request('GET', $endpoint);
            return !empty($response['data']);
        }
    }

    /**
     * Check if user is Super Admin
     */
    public function isSuperAdmin($email) {
        return $email === 'sharma.yogesh.1234@gmail.com';
    }

    /**
     * Check if user can edit designation (Super Admin only)
     */
    public function canEdit($userEmail) {
        return $this->isSuperAdmin($userEmail);
    }

    /**
     * Get designations by level
     */
    public function findByLevel($level, $limit = 100) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE level = ? AND deleted_at IS NULL AND is_active = 1
                                   ORDER BY sort_order ASC, name ASC
                                   LIMIT ?");
            $stmt->execute([$level, $limit]);
            $data = $stmt->fetchAll();

            $designations = [];
            foreach ($data as $designationData) {
                $designations[] = new OrganizationDesignation($designationData);
            }
            return $designations;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?level=eq.' . $level . '&deleted_at=is.null&is_active=eq.true&limit=' . $limit . '&order=sort_order.asc,name.asc');

            if ($response['success']) {
                $designations = [];
                foreach ($response['data'] as $designationData) {
                    $designations[] = new OrganizationDesignation($designationData);
                }
                return $designations;
            }
        }

        return [];
    }

    /**
     * Get active designations for dropdowns
     */
    public function findActive($limit = 1000) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE deleted_at IS NULL AND is_active = 1
                                   ORDER BY sort_order ASC, name ASC
                                   LIMIT ?");
            $stmt->execute([$limit]);
            $data = $stmt->fetchAll();

            $designations = [];
            foreach ($data as $designationData) {
                $designations[] = new OrganizationDesignation($designationData);
            }
            return $designations;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?deleted_at=is.null&is_active=eq.true&limit=' . $limit . '&order=sort_order.asc,name.asc');

            if ($response['success']) {
                $designations = [];
                foreach ($response['data'] as $designationData) {
                    $designations[] = new OrganizationDesignation($designationData);
                }
                return $designations;
            }
        }

        return [];
    }
}
