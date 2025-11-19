<?php

namespace App\Classes;

use App\Config\Database;

/**
 * FacilityTeamRepository
 * Handles CRUD operations for Facility Teams
 * Implements permissions per permissions.md
 */
class FacilityTeamRepository {
    private $db;
    private $tableName = 'department_teams';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new facility team
     * Per permissions.md: Organization Admin and Organization Workers can create
     */
    public function create(DepartmentTeam $team, $userId, $userEmail) {
        // Check code uniqueness
        if ($this->codeExists($team->getCode())) {
            throw new \Exception("Facility team code '{$team->getCode()}' is already taken. Please choose another.");
        }

        // Generate unique ID
        $id = $this->generateId();

        $data = [
            'id' => $id,
            'name' => $team->getName(),
            'code' => $team->getCode(),
            'description' => $team->getDescription(),
            'parent_team_id' => $team->getParentTeamId(),
            'organization_department_id' => $team->getOrganizationDepartmentId(),
            'organization_id' => $team->getOrganizationId(),
            'is_active' => $team->getIsActive() ?? true,
            'sort_order' => $team->getSortOrder() ?? 0,
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
            $team->hydrate($response['data'][0]);
            return $team;
        }

        throw new \Exception("Failed to create facility team: " . json_encode($response['data'] ?? 'Unknown error'));
    }

    /**
     * Find facility team by ID
     * Per permissions.md: Organization Admin and Organization Workers can view
     */
    public function findById($id) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$id]);
            $data = $stmt->fetch();

            if ($data) {
                return new DepartmentTeam($data);
            }
        } else {
            $response = $this->db->request('GET', $this->tableName . '?id=eq.' . $id . '&deleted_at=is.null');

            if ($response['success'] && !empty($response['data'])) {
                return new DepartmentTeam($response['data'][0]);
            }
        }

        return null;
    }

    /**
     * Get all active facility teams (non-deleted)
     * Per permissions.md: Organization Admin and Organization Workers can view
     */
    public function findAll($limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE deleted_at IS NULL AND is_active = 1
                                   ORDER BY sort_order ASC, name ASC
                                   LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $data = $stmt->fetchAll();

            $teams = [];
            foreach ($data as $teamData) {
                $teams[] = new DepartmentTeam($teamData);
            }
            return $teams;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?deleted_at=is.null&is_active=eq.true&limit=' . $limit . '&offset=' . $offset . '&order=sort_order.asc,name.asc');

            if ($response['success']) {
                $teams = [];
                foreach ($response['data'] as $teamData) {
                    $teams[] = new DepartmentTeam($teamData);
                }
                return $teams;
            }
        }

        return [];
    }

    /**
     * Get all facility teams for a specific organization
     * Per permissions.md: Organization Admin and Organization Workers can view
     */
    public function findByOrganization($organizationId, $limit = 100) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $sql = "SELECT * FROM {$this->tableName}
                    WHERE deleted_at IS NULL AND is_active = 1";

            $params = [];
            if ($organizationId !== null) {
                $sql .= " AND (organization_id = ? OR organization_id IS NULL)";
                $params[] = $organizationId;
            } else {
                $sql .= " AND organization_id IS NULL";
            }

            $sql .= " ORDER BY sort_order ASC, name ASC LIMIT ?";
            $params[] = $limit;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll();

            $teams = [];
            foreach ($data as $teamData) {
                $teams[] = new DepartmentTeam($teamData);
            }
            return $teams;
        } else {
            $endpoint = $this->tableName . '?deleted_at=is.null&is_active=eq.true';
            if ($organizationId !== null) {
                $endpoint .= '&or=(organization_id.eq.' . $organizationId . ',organization_id.is.null)';
            } else {
                $endpoint .= '&organization_id=is.null';
            }
            $endpoint .= '&limit=' . $limit . '&order=sort_order.asc,name.asc';

            $response = $this->db->request('GET', $endpoint);

            if ($response['success']) {
                $teams = [];
                foreach ($response['data'] as $teamData) {
                    $teams[] = new DepartmentTeam($teamData);
                }
                return $teams;
            }
        }

        return [];
    }

    /**
     * Update facility team
     * Per permissions.md: Organization Admin can update
     */
    public function update(DepartmentTeam $team, $userId, $userEmail) {
        if (!$team->getId()) {
            throw new \Exception("Facility department ID is required for update");
        }

        // Verify department exists
        $existing = $this->findById($team->getId());
        if (!$existing) {
            throw new \Exception("Facility department not found");
        }

        // Check code uniqueness (excluding current dept)
        if ($this->codeExists($team->getCode(), $team->getId())) {
            throw new \Exception("Facility department code '{$team->getCode()}' is already taken. Please choose another.");
        }

        $data = [
            'name' => $team->getName(),
            'code' => $team->getCode(),
            'description' => $team->getDescription(),
            'parent_team_id' => $team->getParentTeamId(),
            'organization_id' => $team->getOrganizationId(),
            'is_active' => $team->getIsActive(),
            'sort_order' => $team->getSortOrder(),
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

            $params[] = $team->getId();

            $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setClauses) . "
                    WHERE id = ? AND deleted_at IS NULL";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Fetch updated record
            $response = $this->findById($team->getId());
            if ($response) {
                return $response;
            }
        } else {
            $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $team->getId() . '&deleted_at=is.null', $data);

            if ($response['success'] && !empty($response['data'])) {
                $team->hydrate($response['data'][0]);
                return $team;
            }
        }

        throw new \Exception("Failed to update facility team");
    }

    /**
     * Soft delete facility team
     * Per permissions.md: Super Admin can delete
     */
    public function softDelete($id, $userId, $userEmail) {
        // Check Super Admin permission
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can delete facility teams");
        }

        // Verify department exists
        $existing = $this->findById($id);
        if (!$existing) {
            throw new \Exception("Facility department not found");
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
     * Permanently delete facility team
     * Per permissions.md: Super Admin can delete
     */
    public function hardDelete($id, $userEmail) {
        // Check Super Admin permission
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can permanently delete facility teams");
        }

        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("DELETE FROM {$this->tableName} WHERE id = ?");
            $result = $stmt->execute([$id]);
            return $result && $stmt->rowCount() > 0;
        } else {
            $response = $this->db->request('DELETE', $this->tableName . '?id=eq.' . $id);
            return $response['success'];
        }
    }

    /**
     * Get deleted facility teams (for trash/restore functionality)
     * Per permissions.md: Super Admin can view deleted
     */
    public function findDeleted($userEmail, $limit = 100) {
        // Check Super Admin permission
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can view deleted facility teams");
        }

        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE deleted_at IS NOT NULL
                                   ORDER BY deleted_at DESC
                                   LIMIT ?");
            $stmt->execute([$limit]);
            $data = $stmt->fetchAll();

            $teams = [];
            foreach ($data as $teamData) {
                $teams[] = new DepartmentTeam($teamData);
            }
            return $teams;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?deleted_at=not.is.null&limit=' . $limit . '&order=deleted_at.desc');

            if ($response['success']) {
                $teams = [];
                foreach ($response['data'] as $teamData) {
                    $teams[] = new DepartmentTeam($teamData);
                }
                return $teams;
            }
        }

        return [];
    }

    /**
     * Restore soft-deleted facility team
     * Per permissions.md: Super Admin can restore
     */
    public function restore($id, $userEmail) {
        // Check Super Admin permission
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can restore facility teams");
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
     * Search facility teams by name or code
     */
    public function search($query, $limit = 20) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE deleted_at IS NULL AND is_active = 1
                                   AND (name LIKE ? OR code LIKE ? OR description LIKE ?)
                                   ORDER BY sort_order ASC, name ASC
                                   LIMIT ?");
            $stmt->execute(["%$query%", "%$query%", "%$query%", $limit]);
            $data = $stmt->fetchAll();

            $teams = [];
            foreach ($data as $teamData) {
                $teams[] = new DepartmentTeam($teamData);
            }
            return $teams;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?deleted_at=is.null&is_active=eq.true&or=(name.ilike.*' . urlencode($query) . '*,code.ilike.*' . urlencode($query) . '*,description.ilike.*' . urlencode($query) . '*)&limit=' . $limit . '&order=sort_order.asc,name.asc');

            if ($response['success']) {
                $teams = [];
                foreach ($response['data'] as $teamData) {
                    $teams[] = new DepartmentTeam($teamData);
                }
                return $teams;
            }
        }

        return [];
    }

    /**
     * Count all facility teams
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
            $endpoint .= '&select=count';

            $response = $this->db->request('GET', $endpoint, null, ['Prefer: count=exact']);

            if ($response['success']) {
                return $response['data'][0]['count'] ?? 0;
            }
        }

        return 0;
    }

    /**
     * Check if facility team code already exists
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
     * Get facility teams as options for dropdown (for foreign key usage)
     * Returns array of ['value' => id, 'label' => label]
     */
    public function getAsOptions($organizationId = null) {
        $teams = $organizationId !== null
            ? $this->findByOrganization($organizationId)
            : $this->findAll();

        $options = [];
        foreach ($teams as $team) {
            $options[] = [
                'value' => $team->getId(),
                'label' => $team->getLabel()
            ];
        }
        return $options;
    }

    /**
     * Generate unique ID
     */
    private function generateId() {
        // Generate UUID v4-like ID
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
     * Per permissions.md: sharma.yogesh.1234@gmail.com is the Super Admin
     */
    public function isSuperAdmin($email) {
        return $email === 'sharma.yogesh.1234@gmail.com';
    }

    /**
     * Check if user can edit facility team
     */
    public function canEdit($userEmail) {
        // Organization Admin or Super Admin can edit
        return true; // TODO: Implement organization admin check
    }

    /**
     * Check if user can delete facility team
     * Per permissions.md: Only Super Admin can delete
     */
    public function canDelete($userEmail) {
        return $this->isSuperAdmin($userEmail);
    }
}
