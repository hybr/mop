<?php

namespace App\Classes;

use App\Config\Database;

/**
 * OrganizationBuildingRepository
 * Handles CRUD operations for Organization Buildings
 * Implements permissions per permissions.md
 */
class OrganizationBuildingRepository {
    private $db;
    private $tableName = 'organization_buildings';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new building
     * Per permissions.md: Organization Admin and Facility Manager can create
     */
    public function create(OrganizationBuilding $building, $userId, $userEmail) {
        $id = $this->generateId();

        $data = [
            'id' => $id,
            'branch_id' => $building->getBranchId(),
            'organization_id' => $building->getOrganizationId(),
            'name' => $building->getName(),
            'code' => $building->getCode(),
            'description' => $building->getDescription(),
            'street_address' => $building->getStreetAddress(),
            'city' => $building->getCity(),
            'state' => $building->getState(),
            'postal_code' => $building->getPostalCode(),
            'country' => $building->getCountry(),
            'latitude' => $building->getLatitude(),
            'longitude' => $building->getLongitude(),
            'phone' => $building->getPhone(),
            'email' => $building->getEmail(),
            'building_type' => $building->getBuildingType(),
            'total_floors' => $building->getTotalFloors(),
            'total_area_sqft' => $building->getTotalAreaSqft(),
            'year_built' => $building->getYearBuilt(),
            'ownership_type' => $building->getOwnershipType(),
            'is_active' => $building->getIsActive() ?? true,
            'sort_order' => $building->getSortOrder() ?? 0,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $data = array_filter($data, function($value) {
            return $value !== null;
        });

        $pdo = $this->db->getPdo();
        $response = $this->db->query($this->tableName, 'INSERT', [], $data);

        if ($response['success'] && !empty($response['data'])) {
            $building->hydrate($response['data'][0]);
            return $building;
        }

        throw new \Exception("Failed to create building");
    }

    /**
     * Find building by ID
     */
    public function findById($id) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName} WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if ($data) {
            return new OrganizationBuilding($data);
        }

        return null;
    }

    /**
     * Get all active buildings (non-deleted)
     */
    public function findAll($limit = 100, $offset = 0) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE deleted_at IS NULL
                               ORDER BY sort_order ASC, name ASC
                               LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $data = $stmt->fetchAll();

        $buildings = [];
        foreach ($data as $buildingData) {
            $buildings[] = new OrganizationBuilding($buildingData);
        }
        return $buildings;
    }

    /**
     * Get all buildings for a branch
     */
    public function findByBranch($branchId, $limit = 100) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE branch_id = ? AND deleted_at IS NULL
                               ORDER BY sort_order ASC, name ASC
                               LIMIT ?");
        $stmt->execute([$branchId, $limit]);
        $data = $stmt->fetchAll();

        $buildings = [];
        foreach ($data as $buildingData) {
            $buildings[] = new OrganizationBuilding($buildingData);
        }
        return $buildings;
    }

    /**
     * Get all buildings for an organization
     */
    public function findByOrganization($organizationId, $limit = 100) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE organization_id = ? AND deleted_at IS NULL
                               ORDER BY sort_order ASC, name ASC
                               LIMIT ?");
        $stmt->execute([$organizationId, $limit]);
        $data = $stmt->fetchAll();

        $buildings = [];
        foreach ($data as $buildingData) {
            $buildings[] = new OrganizationBuilding($buildingData);
        }
        return $buildings;
    }

    /**
     * Get all buildings for user's organizations
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

        $buildings = [];
        foreach ($data as $buildingData) {
            $buildings[] = new OrganizationBuilding($buildingData);
        }
        return $buildings;
    }

    /**
     * Update building
     */
    public function update(OrganizationBuilding $building, $userId, $userEmail) {
        if (!$building->getId()) {
            throw new \Exception("Building ID is required for update");
        }

        $existing = $this->findById($building->getId());
        if (!$existing) {
            throw new \Exception("Building not found");
        }

        $data = [
            'branch_id' => $building->getBranchId(),
            'organization_id' => $building->getOrganizationId(),
            'name' => $building->getName(),
            'code' => $building->getCode(),
            'description' => $building->getDescription(),
            'street_address' => $building->getStreetAddress(),
            'city' => $building->getCity(),
            'state' => $building->getState(),
            'postal_code' => $building->getPostalCode(),
            'country' => $building->getCountry(),
            'latitude' => $building->getLatitude(),
            'longitude' => $building->getLongitude(),
            'phone' => $building->getPhone(),
            'email' => $building->getEmail(),
            'building_type' => $building->getBuildingType(),
            'total_floors' => $building->getTotalFloors(),
            'total_area_sqft' => $building->getTotalAreaSqft(),
            'year_built' => $building->getYearBuilt(),
            'ownership_type' => $building->getOwnershipType(),
            'is_active' => $building->getIsActive(),
            'sort_order' => $building->getSortOrder(),
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
        $params[] = $building->getId();

        $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setClauses) . "
                WHERE id = ? AND deleted_at IS NULL";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $this->findById($building->getId());
    }

    /**
     * Soft delete building
     */
    public function softDelete($id, $userId, $userEmail) {
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can delete buildings");
        }

        $existing = $this->findById($id);
        if (!$existing) {
            throw new \Exception("Building not found");
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("UPDATE {$this->tableName}
                               SET deleted_by = ?, deleted_at = ?
                               WHERE id = ? AND deleted_at IS NULL");
        $result = $stmt->execute([$userId, date('Y-m-d H:i:s'), $id]);
        return $result && $stmt->rowCount() > 0;
    }

    /**
     * Restore soft-deleted building
     */
    public function restore($id, $userEmail) {
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can restore buildings");
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("UPDATE {$this->tableName}
                               SET deleted_by = NULL, deleted_at = NULL
                               WHERE id = ? AND deleted_at IS NOT NULL");
        $result = $stmt->execute([$id]);
        return $result && $stmt->rowCount() > 0;
    }

    /**
     * Get deleted buildings
     */
    public function findDeleted($userEmail, $limit = 100) {
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can view deleted buildings");
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE deleted_at IS NOT NULL
                               ORDER BY deleted_at DESC
                               LIMIT ?");
        $stmt->execute([$limit]);
        $data = $stmt->fetchAll();

        $buildings = [];
        foreach ($data as $buildingData) {
            $buildings[] = new OrganizationBuilding($buildingData);
        }
        return $buildings;
    }

    /**
     * Search buildings
     */
    public function search($query, $branchId = null, $limit = 20) {
        $pdo = $this->db->getPdo();
        $sql = "SELECT * FROM {$this->tableName}
                WHERE deleted_at IS NULL AND is_active = 1
                AND (name LIKE ? OR city LIKE ? OR code LIKE ? OR street_address LIKE ?)";

        $params = ["%$query%", "%$query%", "%$query%", "%$query%"];

        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }

        $sql .= " ORDER BY sort_order ASC, name ASC LIMIT ?";
        $params[] = $limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        $buildings = [];
        foreach ($data as $buildingData) {
            $buildings[] = new OrganizationBuilding($buildingData);
        }
        return $buildings;
    }

    /**
     * Count buildings
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
     * Count buildings by branch
     */
    public function countByBranch($branchId) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM {$this->tableName}
                               WHERE branch_id = ? AND deleted_at IS NULL");
        $stmt->execute([$branchId]);
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
     * Check if user can edit building
     * Per ENTITY_IMPLEMENTATION_SUMMARY: Super Admin or organization creator can edit
     */
    public function canEdit($buildingId, $userId, $userEmail) {
        // Super Admin can edit any building
        if ($this->isSuperAdmin($userEmail)) {
            return true;
        }

        // Check if user owns the organization that owns this building
        $building = $this->findById($buildingId);
        if (!$building) {
            return false;
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM organizations
                               WHERE id = ? AND created_by = ? AND deleted_at IS NULL");
        $stmt->execute([$building->getOrganizationId(), $userId]);
        $result = $stmt->fetch();

        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Find building by ID (public view)
     * Returns only public fields - accessible to all users
     */
    public function findByIdPublic($id) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName} WHERE id = ? AND deleted_at IS NULL AND is_active = 1");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if ($data) {
            $building = new OrganizationBuilding($data);
            return $building;
        }

        return null;
    }

    /**
     * Find all active buildings (public view)
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

        $buildings = [];
        foreach ($data as $buildingData) {
            $buildings[] = new OrganizationBuilding($buildingData);
        }
        return $buildings;
    }

    /**
     * Get buildings as options for dropdown (for foreign key usage)
     * Returns array of ['value' => id, 'label' => label]
     */
    public function getAsOptions($branchId = null) {
        $buildings = $branchId !== null
            ? $this->findByBranch($branchId)
            : $this->findAllPublic();

        $options = [];
        foreach ($buildings as $building) {
            $options[] = [
                'value' => $building->getId(),
                'label' => $building->getLabel()
            ];
        }
        return $options;
    }
}
