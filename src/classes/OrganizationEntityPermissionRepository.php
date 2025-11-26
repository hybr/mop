<?php

namespace App\Classes;

use App\Config\Database;

/**
 * OrganizationEntityPermissionRepository
 * Manages CRUD operations for entity permissions
 * Handles permission checking for positions
 */
class OrganizationEntityPermissionRepository {
    private $db;
    private $tableName = 'organization_entity_permissions';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new permission
     */
    public function create(OrganizationEntityPermission $permission, $userId) {
        // Check for duplicate permission
        if ($this->permissionExists(
            $permission->getOrganizationPositionId(),
            $permission->getEntityName(),
            $permission->getAction()
        )) {
            throw new \Exception("This permission already exists for this position.");
        }

        $data = [
            'organization_position_id' => $permission->getOrganizationPositionId(),
            'entity_name' => $permission->getEntityName(),
            'action' => $permission->getAction(),
            'scope' => $permission->getScope(),
            'conditions' => $permission->getConditions(),
            'description' => $permission->getDescription(),
            'is_active' => $permission->getIsActive() ?? 1,
            'priority' => $permission->getPriority() ?? 0,
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
            $permission->hydrate($response['data'][0]);
            return $permission;
        }

        throw new \Exception("Failed to create permission: " . json_encode($response['data'] ?? 'Unknown error'));
    }

    /**
     * Find permission by ID (only non-deleted)
     */
    public function findById($id, $userId = null) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$id]);
            $data = $stmt->fetch();

            if ($data) {
                return new OrganizationEntityPermission($data);
            }
        } else {
            $response = $this->db->request('GET', $this->tableName . '?id=eq.' . $id . '&deleted_at=is.null');

            if ($response['success'] && !empty($response['data'])) {
                return new OrganizationEntityPermission($response['data'][0]);
            }
        }

        return null;
    }

    /**
     * Get all permissions (non-deleted only)
     */
    public function findAll($limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE deleted_at IS NULL
                                   ORDER BY priority DESC, created_at DESC
                                   LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $data = $stmt->fetchAll();

            $permissions = [];
            foreach ($data as $permData) {
                $permissions[] = new OrganizationEntityPermission($permData);
            }
            return $permissions;
        }

        return [];
    }

    /**
     * Get all permissions with position details
     */
    public function findAllWithRelations($limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("
                SELECT
                    oep.*,
                    op.name as position_name,
                    op.code as position_code
                FROM {$this->tableName} oep
                LEFT JOIN organization_positions op ON oep.organization_position_id = op.id
                WHERE oep.deleted_at IS NULL
                ORDER BY oep.priority DESC, oep.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $data = $stmt->fetchAll();

            return $data;
        }

        return [];
    }

    /**
     * Get permissions for a specific position
     */
    public function findByPosition($positionId, $limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE organization_position_id = ? AND deleted_at IS NULL
                                   ORDER BY priority DESC, entity_name ASC, action ASC
                                   LIMIT ? OFFSET ?");
            $stmt->execute([$positionId, $limit, $offset]);
            $data = $stmt->fetchAll();

            $permissions = [];
            foreach ($data as $permData) {
                $permissions[] = new OrganizationEntityPermission($permData);
            }
            return $permissions;
        }

        return [];
    }

    /**
     * Get permissions for a specific entity
     */
    public function findByEntity($entityName, $limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE entity_name = ? AND deleted_at IS NULL
                                   ORDER BY priority DESC, created_at DESC
                                   LIMIT ? OFFSET ?");
            $stmt->execute([$entityName, $limit, $offset]);
            $data = $stmt->fetchAll();

            $permissions = [];
            foreach ($data as $permData) {
                $permissions[] = new OrganizationEntityPermission($permData);
            }
            return $permissions;
        }

        return [];
    }

    /**
     * Check if a position has permission to perform action on entity
     * This is the main permission checking method
     */
    public function hasPermission($positionId, $entityName, $action, $scope = null) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();

            $sql = "SELECT * FROM {$this->tableName}
                    WHERE organization_position_id = ?
                    AND entity_name = ?
                    AND action = ?
                    AND is_active = 1
                    AND deleted_at IS NULL";

            $params = [$positionId, $entityName, $action];

            if ($scope !== null) {
                $sql .= " AND scope = ?";
                $params[] = $scope;
            }

            $sql .= " ORDER BY priority DESC LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetch();

            return $data !== false ? new OrganizationEntityPermission($data) : null;
        }

        return null;
    }

    /**
     * Get all permissions for a position grouped by entity
     */
    public function getPermissionMatrix($positionId) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("
                SELECT
                    entity_name,
                    action,
                    scope,
                    priority
                FROM {$this->tableName}
                WHERE organization_position_id = ?
                AND is_active = 1
                AND deleted_at IS NULL
                ORDER BY entity_name ASC, priority DESC, action ASC
            ");
            $stmt->execute([$positionId]);
            $data = $stmt->fetchAll();

            // Group by entity
            $matrix = [];
            foreach ($data as $row) {
                $entity = $row['entity_name'];
                if (!isset($matrix[$entity])) {
                    $matrix[$entity] = [];
                }
                $matrix[$entity][] = [
                    'action' => $row['action'],
                    'scope' => $row['scope'],
                    'priority' => $row['priority']
                ];
            }

            return $matrix;
        }

        return [];
    }

    /**
     * Update permission
     */
    public function update(OrganizationEntityPermission $permission, $userId) {
        if (!$permission->getId()) {
            throw new \Exception("Permission ID is required for update");
        }

        // Verify permission exists
        $existing = $this->findById($permission->getId());
        if (!$existing) {
            throw new \Exception("Permission not found");
        }

        $data = [
            'organization_position_id' => $permission->getOrganizationPositionId(),
            'entity_name' => $permission->getEntityName(),
            'action' => $permission->getAction(),
            'scope' => $permission->getScope(),
            'conditions' => $permission->getConditions(),
            'description' => $permission->getDescription(),
            'is_active' => $permission->getIsActive(),
            'priority' => $permission->getPriority(),
            'updated_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Remove null values
        $data = array_filter($data, function($value) {
            return $value !== null;
        });

        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'UPDATE', ['id' => $permission->getId()], $data);
        } else {
            $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $permission->getId(), $data);
        }

        if ($response['success']) {
            return true;
        }

        throw new \Exception("Failed to update permission");
    }

    /**
     * Soft delete permission
     */
    public function softDelete($id, $userId) {
        $permission = $this->findById($id);
        if (!$permission) {
            throw new \Exception("Permission not found");
        }

        $data = [
            'deleted_by' => $userId,
            'deleted_at' => date('Y-m-d H:i:s')
        ];

        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'UPDATE', ['id' => $id], $data);
        } else {
            $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $id, $data);
        }

        return $response['success'];
    }

    /**
     * Restore deleted permission
     */
    public function restore($id, $userId) {
        $data = [
            'deleted_by' => null,
            'deleted_at' => null,
            'updated_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'UPDATE', ['id' => $id], $data);
        } else {
            $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $id, $data);
        }

        return $response['success'];
    }

    /**
     * Get deleted permissions (trash)
     */
    public function findDeleted($limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE deleted_at IS NOT NULL
                                   ORDER BY deleted_at DESC
                                   LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $data = $stmt->fetchAll();

            $permissions = [];
            foreach ($data as $permData) {
                $permissions[] = new OrganizationEntityPermission($permData);
            }
            return $permissions;
        }

        return [];
    }

    /**
     * Check if permission already exists
     */
    private function permissionExists($positionId, $entityName, $action, $excludeId = null) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();

            $sql = "SELECT COUNT(*) as count FROM {$this->tableName}
                    WHERE organization_position_id = ?
                    AND entity_name = ?
                    AND action = ?
                    AND deleted_at IS NULL";

            $params = [$positionId, $entityName, $action];

            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();

            return $result['count'] > 0;
        }

        return false;
    }

    /**
     * Count permissions
     */
    public function count($filters = []) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();

            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE deleted_at IS NULL";
            $params = [];

            if (!empty($filters['organization_position_id'])) {
                $sql .= " AND organization_position_id = ?";
                $params[] = $filters['organization_position_id'];
            }

            if (!empty($filters['entity_name'])) {
                $sql .= " AND entity_name = ?";
                $params[] = $filters['entity_name'];
            }

            if (!empty($filters['action'])) {
                $sql .= " AND action = ?";
                $params[] = $filters['action'];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();

            return (int)$result['count'];
        }

        return 0;
    }

    /**
     * Bulk create permissions for a position
     * Useful when creating default permissions for a new position
     */
    public function bulkCreate($positionId, $permissions, $userId) {
        $created = [];

        foreach ($permissions as $permData) {
            $permission = new OrganizationEntityPermission();
            $permission->setOrganizationPositionId($positionId);
            $permission->setEntityName($permData['entity_name']);
            $permission->setAction($permData['action']);
            $permission->setScope($permData['scope'] ?? OrganizationEntityPermission::SCOPE_OWN);
            $permission->setDescription($permData['description'] ?? null);
            $permission->setPriority($permData['priority'] ?? 0);

            try {
                $created[] = $this->create($permission, $userId);
            } catch (\Exception $e) {
                // Skip duplicates or log error
                continue;
            }
        }

        return $created;
    }

    /**
     * Copy permissions from one position to another
     */
    public function copyPermissions($fromPositionId, $toPositionId, $userId) {
        $permissions = $this->findByPosition($fromPositionId);
        $copied = [];

        foreach ($permissions as $perm) {
            $newPerm = new OrganizationEntityPermission();
            $newPerm->setOrganizationPositionId($toPositionId);
            $newPerm->setEntityName($perm->getEntityName());
            $newPerm->setAction($perm->getAction());
            $newPerm->setScope($perm->getScope());
            $newPerm->setConditions($perm->getConditions());
            $newPerm->setDescription($perm->getDescription());
            $newPerm->setPriority($perm->getPriority());

            try {
                $copied[] = $this->create($newPerm, $userId);
            } catch (\Exception $e) {
                // Skip duplicates or log error
                continue;
            }
        }

        return $copied;
    }

    /**
     * Check if user is Super Admin
     */
    public function isSuperAdmin($email) {
        return Authorization::isSuperAdmin($email);
    }
}
