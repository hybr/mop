<?php

namespace App\Classes;

use App\Config\Database;

class OrganizationWorkstationRepository {
    private $db;
    private $tableName = 'organization_workstations';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new workstation
     */
    public function create(OrganizationWorkstation $workstation, $userId) {
        // Verify user owns the organization
        $orgRepo = new OrganizationRepository();
        if (!$orgRepo->findById($workstation->getOrganizationId(), $userId)) {
            throw new \Exception("Access denied: You don't have permission to create workstations for this organization");
        }

        $data = [
            'building_id' => $workstation->getBuildingId(),
            'organization_id' => $workstation->getOrganizationId(),
            'name' => $workstation->getName(),
            'code' => $workstation->getCode(),
            'description' => $workstation->getDescription(),
            'floor' => $workstation->getFloor(),
            'room' => $workstation->getRoom(),
            'seat_number' => $workstation->getSeatNumber(),
            'workstation_type' => $workstation->getWorkstationType() ?? 'desk',
            'capacity' => $workstation->getCapacity() ?? 1,
            'area_sqft' => $workstation->getAreaSqft(),
            'has_computer' => $workstation->getHasComputer() ?? 0,
            'has_phone' => $workstation->getHasPhone() ?? 0,
            'has_printer' => $workstation->getHasPrinter() ?? 0,
            'amenities' => $workstation->getAmenities(),
            'is_occupied' => $workstation->getIsOccupied() ?? 0,
            'assigned_to' => $workstation->getAssignedTo(),
            'is_active' => $workstation->getIsActive() ?? 1,
            'sort_order' => $workstation->getSortOrder() ?? 0,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Remove null values
        $data = array_filter($data, function($value) {
            return $value !== null;
        });

        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'INSERT', [], $data);
        } else {
            $response = $this->db->request('POST', $this->tableName, $data);
        }

        if ($response['success'] && !empty($response['data'])) {
            $workstation->hydrate($response['data'][0]);
            return $workstation;
        }

        throw new \Exception("Failed to create workstation: " . json_encode($response['data'] ?? 'Unknown error'));
    }

    /**
     * Find workstation by ID (only if user owns the organization)
     */
    public function findById($id, $userId) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT w.* FROM {$this->tableName} w
                                   INNER JOIN organizations o ON w.organization_id = o.id
                                   WHERE w.id = ? AND o.created_by = ? AND w.deleted_at IS NULL");
            $stmt->execute([$id, $userId]);
            $data = $stmt->fetch();

            if ($data) {
                return new OrganizationWorkstation($data);
            }
        } else {
            $response = $this->db->request('GET', $this->tableName . '?id=eq.' . $id . '&deleted_at=is.null');

            if ($response['success'] && !empty($response['data'])) {
                $workstation = new OrganizationWorkstation($response['data'][0]);
                // Verify user owns the organization
                $orgRepo = new OrganizationRepository();
                if ($orgRepo->findById($workstation->getOrganizationId(), $userId)) {
                    return $workstation;
                }
            }
        }

        return null;
    }

    /**
     * Get all workstations for user's organizations
     */
    public function findAllByUser($userId, $limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT w.* FROM {$this->tableName} w
                                   INNER JOIN organizations o ON w.organization_id = o.id
                                   WHERE o.created_by = ? AND w.deleted_at IS NULL
                                   ORDER BY w.created_at DESC
                                   LIMIT ? OFFSET ?");
            $stmt->execute([$userId, $limit, $offset]);
            $data = $stmt->fetchAll();

            $workstations = [];
            foreach ($data as $workstationData) {
                $workstations[] = new OrganizationWorkstation($workstationData);
            }
            return $workstations;
        }

        return [];
    }

    /**
     * Get workstations by organization
     */
    public function findByOrganization($organizationId, $userId, $limit = 100, $offset = 0) {
        // Verify user owns the organization
        $orgRepo = new OrganizationRepository();
        if (!$orgRepo->findById($organizationId, $userId)) {
            throw new \Exception("Access denied: You don't have permission to view workstations for this organization");
        }

        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE organization_id = ? AND deleted_at IS NULL
                                   ORDER BY floor, room, seat_number, name
                                   LIMIT ? OFFSET ?");
            $stmt->execute([$organizationId, $limit, $offset]);
            $data = $stmt->fetchAll();

            $workstations = [];
            foreach ($data as $workstationData) {
                $workstations[] = new OrganizationWorkstation($workstationData);
            }
            return $workstations;
        }

        return [];
    }

    /**
     * Get workstations by building
     */
    public function findByBuilding($buildingId, $userId, $limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT w.* FROM {$this->tableName} w
                                   INNER JOIN organizations o ON w.organization_id = o.id
                                   WHERE w.building_id = ? AND o.created_by = ? AND w.deleted_at IS NULL
                                   ORDER BY w.floor, w.room, w.seat_number, w.name
                                   LIMIT ? OFFSET ?");
            $stmt->execute([$buildingId, $userId, $limit, $offset]);
            $data = $stmt->fetchAll();

            $workstations = [];
            foreach ($data as $workstationData) {
                $workstations[] = new OrganizationWorkstation($workstationData);
            }
            return $workstations;
        }

        return [];
    }

    /**
     * Update workstation (only if user owns the organization)
     */
    public function update(OrganizationWorkstation $workstation, $userId) {
        if (!$workstation->getId()) {
            throw new \Exception("Workstation ID is required for update");
        }

        // Verify ownership
        $existing = $this->findById($workstation->getId(), $userId);
        if (!$existing) {
            throw new \Exception("Workstation not found or access denied");
        }

        $data = [
            'building_id' => $workstation->getBuildingId(),
            'name' => $workstation->getName(),
            'code' => $workstation->getCode(),
            'description' => $workstation->getDescription(),
            'floor' => $workstation->getFloor(),
            'room' => $workstation->getRoom(),
            'seat_number' => $workstation->getSeatNumber(),
            'workstation_type' => $workstation->getWorkstationType(),
            'capacity' => $workstation->getCapacity(),
            'area_sqft' => $workstation->getAreaSqft(),
            'has_computer' => $workstation->getHasComputer(),
            'has_phone' => $workstation->getHasPhone(),
            'has_printer' => $workstation->getHasPrinter(),
            'amenities' => $workstation->getAmenities(),
            'is_occupied' => $workstation->getIsOccupied(),
            'assigned_to' => $workstation->getAssignedTo(),
            'is_active' => $workstation->getIsActive(),
            'sort_order' => $workstation->getSortOrder(),
            'updated_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Remove null values
        $data = array_filter($data, function($value) {
            return $value !== null;
        });

        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();

            // Build SET clause
            $setClauses = [];
            $params = [];
            foreach ($data as $key => $value) {
                $setClauses[] = "$key = ?";
                $params[] = $value;
            }

            $params[] = $workstation->getId();

            $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setClauses) . "
                    WHERE id = ? AND deleted_at IS NULL";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Fetch updated record
            $response = $this->findById($workstation->getId(), $userId);
            if ($response) {
                return $response;
            }
        }

        throw new \Exception("Failed to update workstation");
    }

    /**
     * Soft delete workstation (only if user owns the organization)
     */
    public function softDelete($id, $userId) {
        // Verify ownership
        $existing = $this->findById($id, $userId);
        if (!$existing) {
            throw new \Exception("Workstation not found or access denied");
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
        }

        return false;
    }

    /**
     * Permanently delete workstation (only if user owns the organization)
     */
    public function hardDelete($id, $userId) {
        // Verify ownership first
        $existing = $this->findById($id, $userId);
        if (!$existing) {
            // Check if it exists in deleted items
            if ($this->db->getDriver() === 'sqlite') {
                $pdo = $this->db->getPdo();
                $stmt = $pdo->prepare("SELECT w.* FROM {$this->tableName} w
                                       INNER JOIN organizations o ON w.organization_id = o.id
                                       WHERE w.id = ? AND o.created_by = ?");
                $stmt->execute([$id, $userId]);
                if (!$stmt->fetch()) {
                    throw new \Exception("Workstation not found or access denied");
                }
            }
        }

        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("DELETE FROM {$this->tableName} WHERE id = ?");
            $result = $stmt->execute([$id]);
            return $result && $stmt->rowCount() > 0;
        }

        return false;
    }

    /**
     * Get deleted workstations (for trash/restore functionality)
     */
    public function findDeletedByUser($userId, $limit = 100) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT w.* FROM {$this->tableName} w
                                   INNER JOIN organizations o ON w.organization_id = o.id
                                   WHERE o.created_by = ? AND w.deleted_at IS NOT NULL
                                   ORDER BY w.deleted_at DESC
                                   LIMIT ?");
            $stmt->execute([$userId, $limit]);
            $data = $stmt->fetchAll();

            $workstations = [];
            foreach ($data as $workstationData) {
                $workstations[] = new OrganizationWorkstation($workstationData);
            }
            return $workstations;
        }

        return [];
    }

    /**
     * Restore soft-deleted workstation
     */
    public function restore($id, $userId) {
        // Verify ownership through organization
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT w.* FROM {$this->tableName} w
                                   INNER JOIN organizations o ON w.organization_id = o.id
                                   WHERE w.id = ? AND o.created_by = ? AND w.deleted_at IS NOT NULL");
            $stmt->execute([$id, $userId]);
            if (!$stmt->fetch()) {
                throw new \Exception("Workstation not found or access denied");
            }

            $stmt = $pdo->prepare("UPDATE {$this->tableName}
                                   SET deleted_by = NULL, deleted_at = NULL
                                   WHERE id = ? AND deleted_at IS NOT NULL");
            $result = $stmt->execute([$id]);
            return $result && $stmt->rowCount() > 0;
        }

        return false;
    }

    /**
     * Search workstations by name/code for a user
     */
    public function searchByUser($query, $userId, $limit = 20) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT w.* FROM {$this->tableName} w
                                   INNER JOIN organizations o ON w.organization_id = o.id
                                   WHERE o.created_by = ? AND w.deleted_at IS NULL
                                   AND (w.name LIKE ? OR w.code LIKE ? OR w.description LIKE ?)
                                   LIMIT ?");
            $stmt->execute([$userId, "%$query%", "%$query%", "%$query%", $limit]);
            $data = $stmt->fetchAll();

            $workstations = [];
            foreach ($data as $workstationData) {
                $workstations[] = new OrganizationWorkstation($workstationData);
            }
            return $workstations;
        }

        return [];
    }

    /**
     * Count user's workstations
     */
    public function countByUser($userId, $includeDeleted = false) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} w
                    INNER JOIN organizations o ON w.organization_id = o.id
                    WHERE o.created_by = ?";
            if (!$includeDeleted) {
                $sql .= " AND w.deleted_at IS NULL";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        }

        return 0;
    }

    /**
     * Find workstation by ID (PUBLIC - returns only public fields)
     */
    public function findByIdPublic($id) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE id = ? AND deleted_at IS NULL AND is_active = 1");
            $stmt->execute([$id]);
            $data = $stmt->fetch();

            if ($data) {
                $workstation = new OrganizationWorkstation($data);
                return $workstation->getPublicFields();
            }
        }

        return null;
    }

    /**
     * Get all active workstations (PUBLIC - returns only public fields)
     */
    public function findAllPublic($limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE deleted_at IS NULL AND is_active = 1
                                   ORDER BY created_at DESC
                                   LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $data = $stmt->fetchAll();

            $workstations = [];
            foreach ($data as $workstationData) {
                $workstation = new OrganizationWorkstation($workstationData);
                $workstations[] = $workstation->getPublicFields();
            }
            return $workstations;
        }

        return [];
    }

    /**
     * Check if user is Super Admin
     * @deprecated Use Authorization::isSuperAdmin() instead
     */
    public function isSuperAdmin($email) {
        return Authorization::isSuperAdmin($email);
    }

    /**
     * Check if user can edit workstation
     * Returns true if user owns the organization OR is Super Admin
     */
    public function canEdit($workstationId, $userId, $userEmail) {
        // Super Admin can edit any workstation
        if ($this->isSuperAdmin($userEmail)) {
            return true;
        }

        // Check if user owns the organization
        $workstation = $this->findById($workstationId, $userId);
        return $workstation !== null;
    }
}
