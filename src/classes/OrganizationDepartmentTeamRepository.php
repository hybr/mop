<?php

namespace App\Classes;

use App\Config\Database;

class OrganizationDepartmentTeamRepository {
    private $db;
    private $tableName = 'organization_department_teams';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new organization department team
     * Per permissions.md: HR Manager position required to create
     */
    public function create(OrganizationDepartmentTeam $team, $userId, $userEmail) {
        // Check code uniqueness within the department
        if ($this->codeExists($team->getCode(), $team->getOrganizationDepartmentId())) {
            throw new \Exception("Team code '{$team->getCode()}' already exists in this department. Please choose another.");
        }

        $data = [
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

        throw new \Exception("Failed to create department team: " . json_encode($response['data'] ?? 'Unknown error'));
    }

    /**
     * Find department team by ID (any user can view)
     * Per permissions.md: All users can view organization department teams
     */
    public function findById($id) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$id]);
            $data = $stmt->fetch();

            if ($data) {
                return new OrganizationDepartmentTeam($data);
            }
        } else {
            $response = $this->db->request('GET', $this->tableName . '?id=eq.' . $id . '&deleted_at=is.null');

            if ($response['success'] && !empty($response['data'])) {
                return new OrganizationDepartmentTeam($response['data'][0]);
            }
        }

        return null;
    }

    /**
     * Get all active department teams (non-deleted)
     * Per permissions.md: All users can view organization department teams
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
                $teams[] = new OrganizationDepartmentTeam($teamData);
            }
            return $teams;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?deleted_at=is.null&is_active=eq.true&limit=' . $limit . '&offset=' . $offset . '&order=sort_order.asc,name.asc');

            if ($response['success']) {
                $teams = [];
                foreach ($response['data'] as $teamData) {
                    $teams[] = new OrganizationDepartmentTeam($teamData);
                }
                return $teams;
            }
        }

        return [];
    }

    /**
     * Get all teams for a specific department
     * Per permissions.md: All users can view organization department teams
     */
    public function findByDepartment($departmentId, $limit = 100) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                    WHERE deleted_at IS NULL AND is_active = 1
                    AND organization_department_id = ?
                    ORDER BY sort_order ASC, name ASC LIMIT ?");
            $stmt->execute([$departmentId, $limit]);
            $data = $stmt->fetchAll();

            $teams = [];
            foreach ($data as $teamData) {
                $teams[] = new OrganizationDepartmentTeam($teamData);
            }
            return $teams;
        } else {
            $endpoint = $this->tableName . '?deleted_at=is.null&is_active=eq.true&organization_department_id=eq.' . $departmentId;
            $endpoint .= '&limit=' . $limit . '&order=sort_order.asc,name.asc';

            $response = $this->db->request('GET', $endpoint);

            if ($response['success']) {
                $teams = [];
                foreach ($response['data'] as $teamData) {
                    $teams[] = new OrganizationDepartmentTeam($teamData);
                }
                return $teams;
            }
        }

        return [];
    }

    /**
     * Get all teams for a specific organization
     * Per permissions.md: All users can view organization department teams
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
                $teams[] = new OrganizationDepartmentTeam($teamData);
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
                    $teams[] = new OrganizationDepartmentTeam($teamData);
                }
                return $teams;
            }
        }

        return [];
    }

    /**
     * Update department team
     * Per permissions.md: Only Super Admin can update
     */
    public function update(OrganizationDepartmentTeam $team, $userId, $userEmail) {
        if (!$team->getId()) {
            throw new \Exception("Department team ID is required for update");
        }

        // Check Super Admin permission
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can update department teams");
        }

        // Verify team exists
        $existing = $this->findById($team->getId());
        if (!$existing) {
            throw new \Exception("Department team not found");
        }

        // Check code uniqueness (excluding current team)
        if ($this->codeExists($team->getCode(), $team->getOrganizationDepartmentId(), $team->getId())) {
            throw new \Exception("Team code '{$team->getCode()}' already exists in this department. Please choose another.");
        }

        $data = [
            'name' => $team->getName(),
            'code' => $team->getCode(),
            'description' => $team->getDescription(),
            'parent_team_id' => $team->getParentTeamId(),
            'organization_department_id' => $team->getOrganizationDepartmentId(),
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

        throw new \Exception("Failed to update department team");
    }

    /**
     * Soft delete department team
     * Per permissions.md: Only Super Admin can delete
     */
    public function softDelete($id, $userId, $userEmail) {
        // Check Super Admin permission
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can delete department teams");
        }

        // Verify team exists
        $existing = $this->findById($id);
        if (!$existing) {
            throw new \Exception("Department team not found");
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
     * Permanently delete department team
     * Per permissions.md: Only Super Admin can delete
     */
    public function hardDelete($id, $userEmail) {
        // Check Super Admin permission
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can delete department teams");
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
     * Get deleted department teams (for trash/restore functionality)
     * Per permissions.md: Only Super Admin can view deleted
     */
    public function findDeleted($userEmail, $limit = 100) {
        // Check Super Admin permission
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can view deleted department teams");
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
                $teams[] = new OrganizationDepartmentTeam($teamData);
            }
            return $teams;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?deleted_at=not.is.null&limit=' . $limit . '&order=deleted_at.desc');

            if ($response['success']) {
                $teams = [];
                foreach ($response['data'] as $teamData) {
                    $teams[] = new OrganizationDepartmentTeam($teamData);
                }
                return $teams;
            }
        }

        return [];
    }

    /**
     * Restore soft-deleted department team
     * Per permissions.md: Only Super Admin can restore
     */
    public function restore($id, $userEmail) {
        // Check Super Admin permission
        if (!$this->isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can restore department teams");
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
     * Search department teams by name or code
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
                $teams[] = new OrganizationDepartmentTeam($teamData);
            }
            return $teams;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?deleted_at=is.null&is_active=eq.true&or=(name.ilike.*' . urlencode($query) . '*,code.ilike.*' . urlencode($query) . '*,description.ilike.*' . urlencode($query) . '*)&limit=' . $limit . '&order=sort_order.asc,name.asc');

            if ($response['success']) {
                $teams = [];
                foreach ($response['data'] as $teamData) {
                    $teams[] = new OrganizationDepartmentTeam($teamData);
                }
                return $teams;
            }
        }

        return [];
    }

    /**
     * Count all department teams
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
     * Check if team code already exists within a department
     */
    public function codeExists($code, $departmentId = null, $excludeId = null) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName}
                    WHERE code = ? AND deleted_at IS NULL";
            $params = [$code];

            if ($departmentId !== null) {
                $sql .= " AND organization_department_id = ?";
                $params[] = $departmentId;
            }

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
            if ($departmentId !== null) {
                $endpoint .= '&organization_department_id=eq.' . $departmentId;
            }
            if ($excludeId) {
                $endpoint .= '&id=neq.' . $excludeId;
            }
            $response = $this->db->request('GET', $endpoint);
            return !empty($response['data']);
        }
    }

    /**
     * Get department teams as options for dropdown (for foreign key usage)
     * Returns array of ['value' => id, 'label' => label]
     */
    public function getAsOptions($departmentId = null, $organizationId = null) {
        if ($departmentId !== null) {
            $teams = $this->findByDepartment($departmentId);
        } elseif ($organizationId !== null) {
            $teams = $this->findByOrganization($organizationId);
        } else {
            $teams = $this->findAll();
        }

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
     * Check if user is Super Admin
     * Per permissions.md: sharma.yogesh.1234@gmail.com is the Super Admin
     */
    public function isSuperAdmin($email) {
        return $email === 'sharma.yogesh.1234@gmail.com';
    }

    /**
     * Check if user can edit department team
     * Per permissions.md: Only Super Admin can edit
     */
    public function canEdit($userEmail) {
        return $this->isSuperAdmin($userEmail);
    }

    /**
     * Check if user can delete department team
     * Per permissions.md: Only Super Admin can delete
     */
    public function canDelete($userEmail) {
        return $this->isSuperAdmin($userEmail);
    }
}
