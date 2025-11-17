<?php

namespace App\Classes;

use App\Config\Database;

class OrganizationRepository {
    private $db;
    private $tableName = 'organizations';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new organization
     */
    public function create(Organization $org, $userId) {
        // Check subdomain uniqueness
        if ($this->subdomainExists($org->getSubdomain())) {
            throw new \Exception("Subdomain '{$org->getSubdomain()}' is already taken. Please choose another.");
        }

        $data = [
            'short_name' => $org->getShortName(),
            'legal_structure' => $org->getLegalStructure(),
            'subdomain' => $org->getSubdomain(),
            'description' => $org->getDescription(),
            'email' => $org->getEmail(),
            'phone' => $org->getPhone(),
            'address' => $org->getAddress(),
            'website' => $org->getWebsite(),
            'logo_url' => $org->getLogoUrl(),
            'is_active' => $org->getIsActive() ?? true,
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
            $org->hydrate($response['data'][0]);
            return $org;
        }

        throw new \Exception("Failed to create organization: " . json_encode($response['data'] ?? 'Unknown error'));
    }

    /**
     * Find organization by ID (only non-deleted)
     */
    public function findById($id, $userId) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE id = ? AND created_by = ? AND deleted_at IS NULL");
            $stmt->execute([$id, $userId]);
            $data = $stmt->fetch();

            if ($data) {
                return new Organization($data);
            }
        } else {
            $response = $this->db->request('GET', $this->tableName . '?id=eq.' . $id . '&created_by=eq.' . $userId . '&deleted_at=is.null');

            if ($response['success'] && !empty($response['data'])) {
                return new Organization($response['data'][0]);
            }
        }

        return null;
    }

    /**
     * Get all organizations for a user (non-deleted only)
     */
    public function findAllByUser($userId, $limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE created_by = ? AND deleted_at IS NULL
                                   ORDER BY created_at DESC
                                   LIMIT ? OFFSET ?");
            $stmt->execute([$userId, $limit, $offset]);
            $data = $stmt->fetchAll();

            $organizations = [];
            foreach ($data as $orgData) {
                $organizations[] = new Organization($orgData);
            }
            return $organizations;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?created_by=eq.' . $userId . '&deleted_at=is.null&limit=' . $limit . '&offset=' . $offset . '&order=created_at.desc');

            if ($response['success']) {
                $organizations = [];
                foreach ($response['data'] as $orgData) {
                    $organizations[] = new Organization($orgData);
                }
                return $organizations;
            }
        }

        return [];
    }

    /**
     * Update organization (only if user owns it)
     */
    public function update(Organization $org, $userId) {
        if (!$org->getId()) {
            throw new \Exception("Organization ID is required for update");
        }

        // Verify ownership
        $existing = $this->findById($org->getId(), $userId);
        if (!$existing) {
            throw new \Exception("Organization not found or access denied");
        }

        // Check subdomain uniqueness (excluding current org)
        if ($this->subdomainExists($org->getSubdomain(), $org->getId())) {
            throw new \Exception("Subdomain '{$org->getSubdomain()}' is already taken. Please choose another.");
        }

        $data = [
            'short_name' => $org->getShortName(),
            'legal_structure' => $org->getLegalStructure(),
            'subdomain' => $org->getSubdomain(),
            'description' => $org->getDescription(),
            'email' => $org->getEmail(),
            'phone' => $org->getPhone(),
            'address' => $org->getAddress(),
            'website' => $org->getWebsite(),
            'logo_url' => $org->getLogoUrl(),
            'is_active' => $org->getIsActive(),
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

            $params[] = $org->getId();
            $params[] = $userId;

            $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setClauses) . "
                    WHERE id = ? AND created_by = ? AND deleted_at IS NULL";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Fetch updated record
            $response = $this->findById($org->getId(), $userId);
            if ($response) {
                return $response;
            }
        } else {
            $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $org->getId() . '&created_by=eq.' . $userId . '&deleted_at=is.null', $data);

            if ($response['success'] && !empty($response['data'])) {
                $org->hydrate($response['data'][0]);
                return $org;
            }
        }

        throw new \Exception("Failed to update organization");
    }

    /**
     * Soft delete organization (only if user owns it)
     */
    public function softDelete($id, $userId) {
        // Verify ownership
        $existing = $this->findById($id, $userId);
        if (!$existing) {
            throw new \Exception("Organization not found or access denied");
        }

        $data = [
            'deleted_by' => $userId,
            'deleted_at' => date('Y-m-d H:i:s')
        ];

        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("UPDATE {$this->tableName}
                                   SET deleted_by = ?, deleted_at = ?
                                   WHERE id = ? AND created_by = ? AND deleted_at IS NULL");
            $result = $stmt->execute([$userId, $data['deleted_at'], $id, $userId]);
            return $result && $stmt->rowCount() > 0;
        } else {
            $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $id . '&created_by=eq.' . $userId . '&deleted_at=is.null', $data);
            return $response['success'];
        }
    }

    /**
     * Permanently delete organization (only if user owns it)
     */
    public function hardDelete($id, $userId) {
        // Verify ownership
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("DELETE FROM {$this->tableName} WHERE id = ? AND created_by = ?");
            $result = $stmt->execute([$id, $userId]);
            return $result && $stmt->rowCount() > 0;
        } else {
            $response = $this->db->request('DELETE', $this->tableName . '?id=eq.' . $id . '&created_by=eq.' . $userId);
            return $response['success'];
        }
    }

    /**
     * Get deleted organizations (for trash/restore functionality)
     */
    public function findDeletedByUser($userId, $limit = 100) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE created_by = ? AND deleted_at IS NOT NULL
                                   ORDER BY deleted_at DESC
                                   LIMIT ?");
            $stmt->execute([$userId, $limit]);
            $data = $stmt->fetchAll();

            $organizations = [];
            foreach ($data as $orgData) {
                $organizations[] = new Organization($orgData);
            }
            return $organizations;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?created_by=eq.' . $userId . '&deleted_at=not.is.null&limit=' . $limit . '&order=deleted_at.desc');

            if ($response['success']) {
                $organizations = [];
                foreach ($response['data'] as $orgData) {
                    $organizations[] = new Organization($orgData);
                }
                return $organizations;
            }
        }

        return [];
    }

    /**
     * Restore soft-deleted organization
     */
    public function restore($id, $userId) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("UPDATE {$this->tableName}
                                   SET deleted_by = NULL, deleted_at = NULL
                                   WHERE id = ? AND created_by = ? AND deleted_at IS NOT NULL");
            $result = $stmt->execute([$id, $userId]);
            return $result && $stmt->rowCount() > 0;
        } else {
            $data = ['deleted_by' => null, 'deleted_at' => null];
            $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $id . '&created_by=eq.' . $userId . '&deleted_at=not.is.null', $data);
            return $response['success'];
        }
    }

    /**
     * Search organizations by short_name for a user
     */
    public function searchByUser($query, $userId, $limit = 20) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE created_by = ? AND deleted_at IS NULL
                                   AND (short_name LIKE ? OR description LIKE ? OR subdomain LIKE ?)
                                   LIMIT ?");
            $stmt->execute([$userId, "%$query%", "%$query%", "%$query%", $limit]);
            $data = $stmt->fetchAll();

            $organizations = [];
            foreach ($data as $orgData) {
                $organizations[] = new Organization($orgData);
            }
            return $organizations;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?created_by=eq.' . $userId . '&deleted_at=is.null&or=(short_name.ilike.*' . urlencode($query) . '*,description.ilike.*' . urlencode($query) . '*,subdomain.ilike.*' . urlencode($query) . '*)&limit=' . $limit);

            if ($response['success']) {
                $organizations = [];
                foreach ($response['data'] as $orgData) {
                    $organizations[] = new Organization($orgData);
                }
                return $organizations;
            }
        }

        return [];
    }

    /**
     * Count user's organizations
     */
    public function countByUser($userId, $includeDeleted = false) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE created_by = ?";
            if (!$includeDeleted) {
                $sql .= " AND deleted_at IS NULL";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } else {
            $endpoint = $this->tableName . '?created_by=eq.' . $userId;
            if (!$includeDeleted) {
                $endpoint .= '&deleted_at=is.null';
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
     * Check if subdomain already exists
     */
    public function subdomainExists($subdomain, $excludeId = null) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName}
                    WHERE subdomain = ? AND deleted_at IS NULL";
            $params = [$subdomain];

            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } else {
            $endpoint = $this->tableName . '?subdomain=eq.' . urlencode($subdomain) . '&deleted_at=is.null';
            if ($excludeId) {
                $endpoint .= '&id=neq.' . $excludeId;
            }
            $response = $this->db->request('GET', $endpoint);
            return !empty($response['data']);
        }
    }

    /**
     * Find organization by ID (PUBLIC - returns only public fields)
     * Per permissions.md: All users (including guests) can view public fields
     */
    public function findByIdPublic($id) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE id = ? AND deleted_at IS NULL AND is_active = 1");
            $stmt->execute([$id]);
            $data = $stmt->fetch();

            if ($data) {
                $org = new Organization($data);
                return $org->getPublicFields();
            }
        } else {
            $response = $this->db->request('GET', $this->tableName . '?id=eq.' . $id . '&deleted_at=is.null&is_active=eq.true');

            if ($response['success'] && !empty($response['data'])) {
                $org = new Organization($response['data'][0]);
                return $org->getPublicFields();
            }
        }

        return null;
    }

    /**
     * Find organization by subdomain (PUBLIC - returns only public fields)
     * Per permissions.md: All users (including guests) can view public fields
     */
    public function findBySubdomainPublic($subdomain) {
        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                                   WHERE subdomain = ? AND deleted_at IS NULL AND is_active = 1");
            $stmt->execute([$subdomain]);
            $data = $stmt->fetch();

            if ($data) {
                $org = new Organization($data);
                return $org->getPublicFields();
            }
        } else {
            $response = $this->db->request('GET', $this->tableName . '?subdomain=eq.' . urlencode($subdomain) . '&deleted_at=is.null&is_active=eq.true');

            if ($response['success'] && !empty($response['data'])) {
                $org = new Organization($response['data'][0]);
                return $org->getPublicFields();
            }
        }

        return null;
    }

    /**
     * Get all active organizations (PUBLIC - returns only public fields)
     * Per permissions.md: All users (including guests) can view public fields
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

            $organizations = [];
            foreach ($data as $orgData) {
                $org = new Organization($orgData);
                $organizations[] = $org->getPublicFields();
            }
            return $organizations;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?deleted_at=is.null&is_active=eq.true&limit=' . $limit . '&offset=' . $offset . '&order=created_at.desc');

            if ($response['success']) {
                $organizations = [];
                foreach ($response['data'] as $orgData) {
                    $org = new Organization($orgData);
                    $organizations[] = $org->getPublicFields();
                }
                return $organizations;
            }
        }

        return [];
    }

    /**
     * Check if user is Super Admin
     * Per permissions.md: sharma.yogesh.1234@gmail.com is the Super Admin
     */
    public function isSuperAdmin($email) {
        return $email === 'sharma.yogesh.1234@gmail.com';
    }

    /**
     * Check if user can edit organization
     * Returns true if user is creator OR Super Admin
     */
    public function canEdit($organizationId, $userId, $userEmail) {
        // Super Admin can edit any organization
        if ($this->isSuperAdmin($userEmail)) {
            return true;
        }

        // Check if user is the creator
        $org = $this->findById($organizationId, $userId);
        return $org !== null;
    }
}
