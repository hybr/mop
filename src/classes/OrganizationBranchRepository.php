<?php

namespace App\Classes;

use App\Config\Database;

/**
 * OrganizationBranchRepository
 * Handles CRUD operations for Organization Branches
 * Implements permissions per permissions.md
 */
class OrganizationBranchRepository {
    private $db;
    private $tableName = 'organization_branches';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new branch
     * Per permissions.md: Organization Admin and Facility Manager can create
     */
    public function create(OrganizationBranch $branch, $userId, $userEmail) {
        $id = $this->generateId();

        $data = [
            'id' => $id,
            'organization_id' => $branch->getOrganizationId(),
            'name' => $branch->getName(),
            'code' => $branch->getCode(),
            'description' => $branch->getDescription(),
            'phone' => $branch->getPhone(),
            'email' => $branch->getEmail(),
            'website' => $branch->getWebsite(),
            'contact_person_name' => $branch->getContactPersonName(),
            'contact_person_phone' => $branch->getContactPersonPhone(),
            'contact_person_email' => $branch->getContactPersonEmail(),
            'is_active' => $branch->getIsActive() ?? true,
            'branch_type' => $branch->getBranchType(),
            'size_category' => $branch->getSizeCategory(),
            'opening_date' => $branch->getOpeningDate(),
            'sort_order' => $branch->getSortOrder() ?? 0,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $data = array_filter($data, function($value) {
            return $value !== null;
        });

        $pdo = $this->db->getPdo();
        $response = $this->db->query($this->tableName, 'INSERT', [], $data);

        if ($response['success'] && !empty($response['data'])) {
            $branch->hydrate($response['data'][0]);
            return $branch;
        }

        throw new \Exception("Failed to create branch");
    }

    /**
     * Find branch by ID
     */
    public function findById($id) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName} WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if ($data) {
            return new OrganizationBranch($data);
        }

        return null;
    }

    /**
     * Get all active branches (non-deleted)
     */
    public function findAll($limit = 100, $offset = 0) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE deleted_at IS NULL
                               ORDER BY sort_order ASC, name ASC
                               LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $data = $stmt->fetchAll();

        $branches = [];
        foreach ($data as $branchData) {
            $branches[] = new OrganizationBranch($branchData);
        }
        return $branches;
    }

    /**
     * Get all branches for an organization
     */
    public function findByOrganization($organizationId, $limit = 100) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE organization_id = ? AND deleted_at IS NULL
                               ORDER BY sort_order ASC, name ASC
                               LIMIT ?");
        $stmt->execute([$organizationId, $limit]);
        $data = $stmt->fetchAll();

        $branches = [];
        foreach ($data as $branchData) {
            $branches[] = new OrganizationBranch($branchData);
        }
        return $branches;
    }

    /**
     * Get all branches for user's organizations
     */
    public function findByUser($userId, $limit = 100) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT b.* FROM {$this->tableName} b
                               INNER JOIN organizations o ON b.organization_id = o.id
                               WHERE o.created_by = ? AND b.deleted_at IS NULL
                               ORDER BY b.sort_order ASC, b.name ASC
                               LIMIT ?");
        $stmt->execute([$userId, $limit]);
        $data = $stmt->fetchAll();

        $branches = [];
        foreach ($data as $branchData) {
            $branches[] = new OrganizationBranch($branchData);
        }
        return $branches;
    }

    /**
     * Update branch
     */
    public function update(OrganizationBranch $branch, $userId, $userEmail) {
        if (!$branch->getId()) {
            throw new \Exception("Branch ID is required for update");
        }

        $existing = $this->findById($branch->getId());
        if (!$existing) {
            throw new \Exception("Branch not found");
        }

        $data = [
            'organization_id' => $branch->getOrganizationId(),
            'name' => $branch->getName(),
            'code' => $branch->getCode(),
            'description' => $branch->getDescription(),
            'phone' => $branch->getPhone(),
            'email' => $branch->getEmail(),
            'website' => $branch->getWebsite(),
            'contact_person_name' => $branch->getContactPersonName(),
            'contact_person_phone' => $branch->getContactPersonPhone(),
            'contact_person_email' => $branch->getContactPersonEmail(),
            'is_active' => $branch->getIsActive(),
            'branch_type' => $branch->getBranchType(),
            'size_category' => $branch->getSizeCategory(),
            'opening_date' => $branch->getOpeningDate(),
            'sort_order' => $branch->getSortOrder(),
            'updated_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $data = array_filter($data, function($value) {
            return $value !== null;
        });

        $pdo = $this->db->getPdo();
        $setClauses = [];
        $params = [];
        foreach ($data as $key => $value) {
            $setClauses[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $branch->getId();

        $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setClauses) . "
                WHERE id = ? AND deleted_at IS NULL";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $this->findById($branch->getId());
    }

    /**
     * Soft delete branch
     */
    public function softDelete($id, $userId, $userEmail) {
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can delete branches");
        }

        $existing = $this->findById($id);
        if (!$existing) {
            throw new \Exception("Branch not found");
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("UPDATE {$this->tableName}
                               SET deleted_by = ?, deleted_at = ?
                               WHERE id = ? AND deleted_at IS NULL");
        $result = $stmt->execute([$userId, date('Y-m-d H:i:s'), $id]);
        return $result && $stmt->rowCount() > 0;
    }

    /**
     * Restore soft-deleted branch
     */
    public function restore($id, $userEmail) {
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can restore branches");
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("UPDATE {$this->tableName}
                               SET deleted_by = NULL, deleted_at = NULL
                               WHERE id = ? AND deleted_at IS NOT NULL");
        $result = $stmt->execute([$id]);
        return $result && $stmt->rowCount() > 0;
    }

    /**
     * Get deleted branches
     */
    public function findDeleted($userEmail, $limit = 100) {
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can view deleted branches");
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE deleted_at IS NOT NULL
                               ORDER BY deleted_at DESC
                               LIMIT ?");
        $stmt->execute([$limit]);
        $data = $stmt->fetchAll();

        $branches = [];
        foreach ($data as $branchData) {
            $branches[] = new OrganizationBranch($branchData);
        }
        return $branches;
    }

    /**
     * Search branches
     */
    public function search($query, $organizationId = null, $limit = 20) {
        $pdo = $this->db->getPdo();
        $sql = "SELECT * FROM {$this->tableName}
                WHERE deleted_at IS NULL AND is_active = 1
                AND (name LIKE ? OR city LIKE ? OR code LIKE ?)";

        $params = ["%$query%", "%$query%", "%$query%"];

        if ($organizationId) {
            $sql .= " AND organization_id = ?";
            $params[] = $organizationId;
        }

        $sql .= " ORDER BY sort_order ASC, name ASC LIMIT ?";
        $params[] = $limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        $branches = [];
        foreach ($data as $branchData) {
            $branches[] = new OrganizationBranch($branchData);
        }
        return $branches;
    }

    /**
     * Count branches
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
     */
    public function isSuperAdmin($email) {
        return $email === 'sharma.yogesh.1234@gmail.com';
    }

    /**
     * Check if user can edit branch
     * Per ENTITY_IMPLEMENTATION_SUMMARY: Super Admin or organization creator can edit
     */
    public function canEdit($branchId, $userId, $userEmail) {
        // Super Admin can edit any branch
        if ($this->isSuperAdmin($userEmail)) {
            return true;
        }

        // Check if user owns the organization that owns this branch
        $branch = $this->findById($branchId);
        if (!$branch) {
            return false;
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM organizations
                               WHERE id = ? AND created_by = ? AND deleted_at IS NULL");
        $stmt->execute([$branch->getOrganizationId(), $userId]);
        $result = $stmt->fetch();

        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Find branch by ID (public view)
     * Returns only public fields - accessible to all users
     */
    public function findByIdPublic($id) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName} WHERE id = ? AND deleted_at IS NULL AND is_active = 1");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if ($data) {
            $branch = new OrganizationBranch($data);
            return $branch;
        }

        return null;
    }

    /**
     * Find all active branches (public view)
     * Returns only public fields - accessible to all users
     */
    public function findAllPublic($limit = 100, $offset = 0) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE deleted_at IS NULL AND is_active = 1
                               ORDER BY sort_order ASC, name ASC
                               LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $data = $stmt->fetchAll();

        $branches = [];
        foreach ($data as $branchData) {
            $branches[] = new OrganizationBranch($branchData);
        }
        return $branches;
    }

    /**
     * Get branches as options for dropdown (for foreign key usage)
     * Returns array of ['value' => id, 'label' => label]
     */
    public function getAsOptions($organizationId = null) {
        $branches = $organizationId !== null
            ? $this->findByOrganization($organizationId)
            : $this->findAllPublic();

        $options = [];
        foreach ($branches as $branch) {
            $options[] = [
                'value' => $branch->getId(),
                'label' => $branch->getLabel()
            ];
        }
        return $options;
    }
}
