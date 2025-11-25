<?php

namespace App\Classes;

use App\Config\Database;

/**
 * OrganizationVacancyRepository
 * Handles CRUD operations for Organization Vacancies
 * Vacancies belong to organizations and can be viewed publicly when published
 * Following ENTITY_IMPLEMENTATION_SUMMARY.md guidelines
 */
class OrganizationVacancyRepository {
    private $db;
    private $tableName = 'organization_vacancies';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new vacancy
     * User must own the organization
     */
    public function create(OrganizationVacancy $vacancy, $userId) {
        // Verify user owns the organization
        $orgRepo = new OrganizationRepository();
        if (!$orgRepo->findById($vacancy->getOrganizationId(), $userId)) {
            throw new \Exception("Access denied: You don't have permission to create vacancies for this organization");
        }

        // Check code uniqueness if provided
        if ($vacancy->getCode() && $this->codeExists($vacancy->getCode())) {
            throw new \Exception("Vacancy code '{$vacancy->getCode()}' already exists. Please choose another.");
        }

        $id = $this->generateId();

        $data = [
            'id' => $id,
            'title' => $vacancy->getTitle(),
            'code' => $vacancy->getCode(),
            'description' => $vacancy->getDescription(),
            'organization_id' => $vacancy->getOrganizationId(),
            'organization_position_id' => $vacancy->getOrganizationPositionId(),
            'organization_workstation_id' => $vacancy->getOrganizationWorkstationId(),
            'reports_to_user_id' => $vacancy->getReportsToUserId(),
            'vacancy_type' => $vacancy->getVacancyType() ?: 'new',
            'priority' => $vacancy->getPriority() ?: 'medium',
            'openings_count' => $vacancy->getOpeningsCount() ?: 1,
            'posted_date' => $vacancy->getPostedDate() ?: date('Y-m-d'),
            'application_deadline' => $vacancy->getApplicationDeadline(),
            'target_start_date' => $vacancy->getTargetStartDate(),
            'target_end_date' => $vacancy->getTargetEndDate(),
            'salary_offered_min' => $vacancy->getSalaryOfferedMin(),
            'salary_offered_max' => $vacancy->getSalaryOfferedMax(),
            'salary_currency' => $vacancy->getSalaryCurrency() ?: 'INR',
            'benefits' => $vacancy->getBenefits(),
            'application_method' => $vacancy->getApplicationMethod() ?: 'both',
            'application_url' => $vacancy->getApplicationUrl(),
            'contact_person' => $vacancy->getContactPerson(),
            'contact_email' => $vacancy->getContactEmail(),
            'contact_phone' => $vacancy->getContactPhone(),
            'status' => $vacancy->getStatus() ?: 'draft',
            'is_published' => $vacancy->getIsPublished() ? 1 : 0,
            'published_at' => $vacancy->getPublishedAt(),
            'is_active' => $vacancy->getIsActive() !== null ? (int)$vacancy->getIsActive() : 1,
            'sort_order' => $vacancy->getSortOrder() ?? 0,
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
            $vacancy->hydrate($response['data'][0]);
            return $vacancy;
        }

        throw new \Exception("Failed to create vacancy");
    }

    /**
     * Find vacancy by ID (only if user owns the organization)
     */
    public function findById($id, $userId) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT v.* FROM {$this->tableName} v
                               INNER JOIN organizations o ON v.organization_id = o.id
                               WHERE v.id = ? AND o.created_by = ? AND v.deleted_at IS NULL");
        $stmt->execute([$id, $userId]);
        $data = $stmt->fetch();

        if ($data) {
            return new OrganizationVacancy($data);
        }

        return null;
    }

    /**
     * Get all vacancies for user's organizations
     */
    public function findAllByUser($userId, $limit = 100, $offset = 0) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT v.* FROM {$this->tableName} v
                               INNER JOIN organizations o ON v.organization_id = o.id
                               WHERE o.created_by = ? AND v.deleted_at IS NULL
                               ORDER BY v.posted_date DESC, v.created_at DESC
                               LIMIT ? OFFSET ?");
        $stmt->execute([$userId, $limit, $offset]);
        $data = $stmt->fetchAll();

        $vacancies = [];
        foreach ($data as $vacancyData) {
            $vacancies[] = new OrganizationVacancy($vacancyData);
        }
        return $vacancies;
    }

    /**
     * Get vacancies by organization
     */
    public function findByOrganization($organizationId, $userId, $limit = 100, $offset = 0) {
        // Verify user owns the organization
        $orgRepo = new OrganizationRepository();
        if (!$orgRepo->findById($organizationId, $userId)) {
            throw new \Exception("Access denied: You don't have permission to view vacancies for this organization");
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE organization_id = ? AND deleted_at IS NULL
                               ORDER BY posted_date DESC
                               LIMIT ? OFFSET ?");
        $stmt->execute([$organizationId, $limit, $offset]);
        $data = $stmt->fetchAll();

        $vacancies = [];
        foreach ($data as $vacancyData) {
            $vacancies[] = new OrganizationVacancy($vacancyData);
        }
        return $vacancies;
    }

    /**
     * Get vacancies by position
     */
    public function findByPosition($positionId, $userId, $limit = 100) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT v.* FROM {$this->tableName} v
                               INNER JOIN organizations o ON v.organization_id = o.id
                               WHERE v.organization_position_id = ? AND o.created_by = ? AND v.deleted_at IS NULL
                               ORDER BY v.posted_date DESC
                               LIMIT ?");
        $stmt->execute([$positionId, $userId, $limit]);
        $data = $stmt->fetchAll();

        $vacancies = [];
        foreach ($data as $vacancyData) {
            $vacancies[] = new OrganizationVacancy($vacancyData);
        }
        return $vacancies;
    }

    /**
     * Get vacancies by status
     */
    public function findByStatus($status, $userId, $limit = 100) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT v.* FROM {$this->tableName} v
                               INNER JOIN organizations o ON v.organization_id = o.id
                               WHERE v.status = ? AND o.created_by = ? AND v.deleted_at IS NULL
                               ORDER BY v.posted_date DESC
                               LIMIT ?");
        $stmt->execute([$status, $userId, $limit]);
        $data = $stmt->fetchAll();

        $vacancies = [];
        foreach ($data as $vacancyData) {
            $vacancies[] = new OrganizationVacancy($vacancyData);
        }
        return $vacancies;
    }

    /**
     * Update vacancy (only if user owns the organization)
     */
    public function update(OrganizationVacancy $vacancy, $userId) {
        if (!$vacancy->getId()) {
            throw new \Exception("Vacancy ID is required for update");
        }

        // Verify ownership
        $existing = $this->findById($vacancy->getId(), $userId);
        if (!$existing) {
            throw new \Exception("Vacancy not found or access denied");
        }

        // Check code uniqueness (excluding current vacancy)
        if ($vacancy->getCode() && $this->codeExists($vacancy->getCode(), $vacancy->getId())) {
            throw new \Exception("Vacancy code '{$vacancy->getCode()}' already exists. Please choose another.");
        }

        $data = [
            'title' => $vacancy->getTitle(),
            'code' => $vacancy->getCode(),
            'description' => $vacancy->getDescription(),
            'organization_position_id' => $vacancy->getOrganizationPositionId(),
            'organization_workstation_id' => $vacancy->getOrganizationWorkstationId(),
            'reports_to_user_id' => $vacancy->getReportsToUserId(),
            'vacancy_type' => $vacancy->getVacancyType(),
            'priority' => $vacancy->getPriority(),
            'openings_count' => $vacancy->getOpeningsCount(),
            'posted_date' => $vacancy->getPostedDate(),
            'application_deadline' => $vacancy->getApplicationDeadline(),
            'target_start_date' => $vacancy->getTargetStartDate(),
            'target_end_date' => $vacancy->getTargetEndDate(),
            'salary_offered_min' => $vacancy->getSalaryOfferedMin(),
            'salary_offered_max' => $vacancy->getSalaryOfferedMax(),
            'salary_currency' => $vacancy->getSalaryCurrency(),
            'benefits' => $vacancy->getBenefits(),
            'application_method' => $vacancy->getApplicationMethod(),
            'application_url' => $vacancy->getApplicationUrl(),
            'contact_person' => $vacancy->getContactPerson(),
            'contact_email' => $vacancy->getContactEmail(),
            'contact_phone' => $vacancy->getContactPhone(),
            'status' => $vacancy->getStatus(),
            'is_published' => $vacancy->getIsPublished(),
            'published_at' => $vacancy->getPublishedAt(),
            'filled_at' => $vacancy->getFilledAt(),
            'filled_by_user_id' => $vacancy->getFilledByUserId(),
            'is_active' => $vacancy->getIsActive(),
            'sort_order' => $vacancy->getSortOrder(),
            'updated_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Build SET clause
        $pdo = $this->db->getPdo();
        $setClauses = [];
        $params = [];
        foreach ($data as $key => $value) {
            $setClauses[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $vacancy->getId();

        $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setClauses) . "
                WHERE id = ? AND deleted_at IS NULL";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $this->findById($vacancy->getId(), $userId);
    }

    /**
     * Publish vacancy
     */
    public function publish($id, $userId) {
        $vacancy = $this->findById($id, $userId);
        if (!$vacancy) {
            throw new \Exception("Vacancy not found or access denied");
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("UPDATE {$this->tableName}
                               SET is_published = 1, published_at = ?, status = 'open', updated_by = ?, updated_at = ?
                               WHERE id = ? AND deleted_at IS NULL");
        $now = date('Y-m-d H:i:s');
        $result = $stmt->execute([$now, $userId, $now, $id]);
        return $result && $stmt->rowCount() > 0;
    }

    /**
     * Unpublish vacancy
     */
    public function unpublish($id, $userId) {
        $vacancy = $this->findById($id, $userId);
        if (!$vacancy) {
            throw new \Exception("Vacancy not found or access denied");
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("UPDATE {$this->tableName}
                               SET is_published = 0, updated_by = ?, updated_at = ?
                               WHERE id = ? AND deleted_at IS NULL");
        $now = date('Y-m-d H:i:s');
        $result = $stmt->execute([$userId, $now, $id]);
        return $result && $stmt->rowCount() > 0;
    }

    /**
     * Mark vacancy as filled
     */
    public function markAsFilled($id, $userId, $filledByUserId = null) {
        $vacancy = $this->findById($id, $userId);
        if (!$vacancy) {
            throw new \Exception("Vacancy not found or access denied");
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("UPDATE {$this->tableName}
                               SET status = 'filled', filled_at = ?, filled_by_user_id = ?, updated_by = ?, updated_at = ?
                               WHERE id = ? AND deleted_at IS NULL");
        $now = date('Y-m-d H:i:s');
        $result = $stmt->execute([$now, $filledByUserId, $userId, $now, $id]);
        return $result && $stmt->rowCount() > 0;
    }

    /**
     * Increment views count
     */
    public function incrementViews($id) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("UPDATE {$this->tableName}
                               SET views_count = views_count + 1
                               WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Soft delete vacancy (only if user owns the organization)
     */
    public function softDelete($id, $userId) {
        // Verify ownership
        $existing = $this->findById($id, $userId);
        if (!$existing) {
            throw new \Exception("Vacancy not found or access denied");
        }

        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("UPDATE {$this->tableName}
                               SET deleted_by = ?, deleted_at = ?
                               WHERE id = ? AND deleted_at IS NULL");
        $result = $stmt->execute([$userId, date('Y-m-d H:i:s'), $id]);
        return $result && $stmt->rowCount() > 0;
    }

    /**
     * Restore soft-deleted vacancy
     */
    public function restore($id, $userId) {
        // Verify ownership through organization
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT v.* FROM {$this->tableName} v
                               INNER JOIN organizations o ON v.organization_id = o.id
                               WHERE v.id = ? AND o.created_by = ? AND v.deleted_at IS NOT NULL");
        $stmt->execute([$id, $userId]);
        if (!$stmt->fetch()) {
            throw new \Exception("Vacancy not found or access denied");
        }

        $stmt = $pdo->prepare("UPDATE {$this->tableName}
                               SET deleted_by = NULL, deleted_at = NULL
                               WHERE id = ? AND deleted_at IS NOT NULL");
        $result = $stmt->execute([$id]);
        return $result && $stmt->rowCount() > 0;
    }

    /**
     * Get deleted vacancies (for trash/restore functionality)
     */
    public function findDeletedByUser($userId, $limit = 100) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT v.* FROM {$this->tableName} v
                               INNER JOIN organizations o ON v.organization_id = o.id
                               WHERE o.created_by = ? AND v.deleted_at IS NOT NULL
                               ORDER BY v.deleted_at DESC
                               LIMIT ?");
        $stmt->execute([$userId, $limit]);
        $data = $stmt->fetchAll();

        $vacancies = [];
        foreach ($data as $vacancyData) {
            $vacancies[] = new OrganizationVacancy($vacancyData);
        }
        return $vacancies;
    }

    /**
     * Search vacancies by title/code for a user
     */
    public function searchByUser($query, $userId, $limit = 20) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT v.* FROM {$this->tableName} v
                               INNER JOIN organizations o ON v.organization_id = o.id
                               WHERE o.created_by = ? AND v.deleted_at IS NULL
                               AND (v.title LIKE ? OR v.code LIKE ? OR v.description LIKE ?)
                               ORDER BY v.posted_date DESC
                               LIMIT ?");
        $searchTerm = "%$query%";
        $stmt->execute([$userId, $searchTerm, $searchTerm, $searchTerm, $limit]);
        $data = $stmt->fetchAll();

        $vacancies = [];
        foreach ($data as $vacancyData) {
            $vacancies[] = new OrganizationVacancy($vacancyData);
        }
        return $vacancies;
    }

    /**
     * Count user's vacancies
     */
    public function countByUser($userId, $includeDeleted = false) {
        $pdo = $this->db->getPdo();
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} v
                INNER JOIN organizations o ON v.organization_id = o.id
                WHERE o.created_by = ?";
        if (!$includeDeleted) {
            $sql .= " AND v.deleted_at IS NULL";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
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

    // ============= PUBLIC METHODS (FOR GUEST USERS) =============

    /**
     * Find vacancy by ID (PUBLIC - returns only public fields)
     */
    public function findByIdPublic($id) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE id = ? AND deleted_at IS NULL AND is_published = 1 AND status = 'open'");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if ($data) {
            // Increment views
            $this->incrementViews($id);

            $vacancy = new OrganizationVacancy($data);
            return $vacancy->getPublicFields();
        }

        return null;
    }

    /**
     * Get all published vacancies (PUBLIC - returns only public fields)
     */
    public function findAllPublic($limit = 100, $offset = 0, $filters = []) {
        $pdo = $this->db->getPdo();

        $where = ["deleted_at IS NULL", "is_published = 1", "status = 'open'"];
        $params = [];

        // Apply filters
        if (!empty($filters['organization_id'])) {
            $where[] = "organization_id = ?";
            $params[] = $filters['organization_id'];
        }

        if (!empty($filters['position_id'])) {
            $where[] = "organization_position_id = ?";
            $params[] = $filters['position_id'];
        }

        if (!empty($filters['priority'])) {
            $where[] = "priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(title LIKE ? OR description LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $params[] = $limit;
        $params[] = $offset;

        $sql = "SELECT * FROM {$this->tableName}
                WHERE " . implode(' AND ', $where) . "
                ORDER BY priority DESC, posted_date DESC
                LIMIT ? OFFSET ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        $vacancies = [];
        foreach ($data as $vacancyData) {
            $vacancy = new OrganizationVacancy($vacancyData);
            $vacancies[] = $vacancy->getPublicFields();
        }
        return $vacancies;
    }

    /**
     * Count published vacancies (PUBLIC)
     */
    public function countPublic($filters = []) {
        $pdo = $this->db->getPdo();

        $where = ["deleted_at IS NULL", "is_published = 1", "status = 'open'"];
        $params = [];

        // Apply same filters as findAllPublic
        if (!empty($filters['organization_id'])) {
            $where[] = "organization_id = ?";
            $params[] = $filters['organization_id'];
        }

        if (!empty($filters['position_id'])) {
            $where[] = "organization_position_id = ?";
            $params[] = $filters['position_id'];
        }

        if (!empty($filters['priority'])) {
            $where[] = "priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(title LIKE ? OR description LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql = "SELECT COUNT(*) as count FROM {$this->tableName}
                WHERE " . implode(' AND ', $where);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get vacancies with related data (position, organization names)
     */
    public function findAllWithRelations($userId, $limit = 100, $offset = 0) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("
            SELECT v.*,
                   o.short_name as organization_name,
                   p.name as position_name,
                   p.code as position_code,
                   w.name as workstation_name
            FROM {$this->tableName} v
            INNER JOIN organizations o ON v.organization_id = o.id
            LEFT JOIN organization_positions p ON v.organization_position_id = p.id
            LEFT JOIN organization_workstations w ON v.organization_workstation_id = w.id
            WHERE o.created_by = ? AND v.deleted_at IS NULL
            ORDER BY v.posted_date DESC, v.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll();
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
     * Check if user can edit vacancy
     * Returns true if user owns the organization
     */
    public function canEdit($vacancyId, $userId) {
        $vacancy = $this->findById($vacancyId, $userId);
        return $vacancy !== null;
    }
}
