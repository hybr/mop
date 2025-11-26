<?php

namespace App\Classes;

/**
 * OrganizationVacancy Entity
 * Represents a job vacancy combining OrganizationPosition and OrganizationWorkstation
 *
 * A vacancy is created when an organization needs to fill a position at a specific workstation.
 * Following entity_creation_instructions.md guidelines
 */
class OrganizationVacancy {
    private $id;
    private $title;                         // Vacancy title (e.g., "Senior Frontend Developer - Tech Team")
    private $code;                          // Unique code (e.g., "VAC-2024-001")
    private $description;                   // Job description

    // Foreign keys
    private $organization_id;               // Required: Organization posting the vacancy
    private $organization_position_id;      // Required: Position to fill
    private $organization_workstation_id;   // Optional: Specific workstation for this vacancy
    private $reports_to_user_id;            // Optional: User this position reports to

    // Vacancy details
    private $vacancy_type;                  // 'new', 'replacement', 'expansion'
    private $priority;                      // 'low', 'medium', 'high', 'urgent'
    private $openings_count;                // Number of positions to fill (default 1)

    // Timeline
    private $posted_date;                   // When vacancy was posted
    private $application_deadline;          // Last date to apply
    private $target_start_date;             // Expected joining date
    private $target_end_date;               // Expected completion date (for contract positions)

    // Salary and benefits
    private $salary_offered_min;            // Minimum salary offered
    private $salary_offered_max;            // Maximum salary offered
    private $salary_currency;               // Currency code (e.g., "INR", "USD")
    private $benefits;                      // JSON or text describing benefits

    // Application details
    private $application_method;            // 'internal', 'external', 'both'
    private $application_url;               // External application URL
    private $contact_person;                // Contact person name
    private $contact_email;                 // Contact email
    private $contact_phone;                 // Contact phone

    // Status tracking
    private $status;                        // 'draft', 'open', 'on_hold', 'filled', 'cancelled'
    private $is_published;                  // Whether vacancy is publicly visible
    private $published_at;                  // When vacancy was published
    private $filled_at;                     // When vacancy was filled
    private $filled_by_user_id;             // User who filled the position

    // Metadata
    private $views_count;                   // Number of views
    private $applications_count;            // Number of applications received
    private $is_active;                     // Active status
    private $sort_order;                    // Display order

    // Default audit fields (per entity_creation_instructions.md #10)
    private $created_by;
    private $created_at;
    private $updated_by;
    private $updated_at;
    private $deleted_by;
    private $deleted_at;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * Populate object from array
     */
    public function hydrate($data) {
        if (isset($data['id'])) $this->id = $data['id'];
        if (isset($data['title'])) $this->title = $data['title'];
        if (isset($data['code'])) $this->code = $data['code'];
        if (isset($data['description'])) $this->description = $data['description'];

        // Foreign keys
        if (isset($data['organization_id'])) $this->organization_id = $data['organization_id'];
        if (isset($data['organization_position_id'])) $this->organization_position_id = $data['organization_position_id'];
        if (isset($data['organization_workstation_id'])) $this->organization_workstation_id = $data['organization_workstation_id'];
        if (isset($data['reports_to_user_id'])) $this->reports_to_user_id = $data['reports_to_user_id'];

        // Vacancy details
        if (isset($data['vacancy_type'])) $this->vacancy_type = $data['vacancy_type'];
        if (isset($data['priority'])) $this->priority = $data['priority'];
        if (isset($data['openings_count'])) $this->openings_count = $data['openings_count'];

        // Timeline
        if (isset($data['posted_date'])) $this->posted_date = $data['posted_date'];
        if (isset($data['application_deadline'])) $this->application_deadline = $data['application_deadline'];
        if (isset($data['target_start_date'])) $this->target_start_date = $data['target_start_date'];
        if (isset($data['target_end_date'])) $this->target_end_date = $data['target_end_date'];

        // Salary and benefits
        if (isset($data['salary_offered_min'])) $this->salary_offered_min = $data['salary_offered_min'];
        if (isset($data['salary_offered_max'])) $this->salary_offered_max = $data['salary_offered_max'];
        if (isset($data['salary_currency'])) $this->salary_currency = $data['salary_currency'];
        if (isset($data['benefits'])) $this->benefits = $data['benefits'];

        // Application details
        if (isset($data['application_method'])) $this->application_method = $data['application_method'];
        if (isset($data['application_url'])) $this->application_url = $data['application_url'];
        if (isset($data['contact_person'])) $this->contact_person = $data['contact_person'];
        if (isset($data['contact_email'])) $this->contact_email = $data['contact_email'];
        if (isset($data['contact_phone'])) $this->contact_phone = $data['contact_phone'];

        // Status tracking
        if (isset($data['status'])) $this->status = $data['status'];
        if (isset($data['is_published'])) $this->is_published = $data['is_published'];
        if (isset($data['published_at'])) $this->published_at = $data['published_at'];
        if (isset($data['filled_at'])) $this->filled_at = $data['filled_at'];
        if (isset($data['filled_by_user_id'])) $this->filled_by_user_id = $data['filled_by_user_id'];

        // Metadata
        if (isset($data['views_count'])) $this->views_count = $data['views_count'];
        if (isset($data['applications_count'])) $this->applications_count = $data['applications_count'];
        if (isset($data['is_active'])) $this->is_active = $data['is_active'];
        if (isset($data['sort_order'])) $this->sort_order = $data['sort_order'];

        // Audit fields
        if (isset($data['created_by'])) $this->created_by = $data['created_by'];
        if (isset($data['created_at'])) $this->created_at = $data['created_at'];
        if (isset($data['updated_by'])) $this->updated_by = $data['updated_by'];
        if (isset($data['updated_at'])) $this->updated_at = $data['updated_at'];
        if (isset($data['deleted_by'])) $this->deleted_by = $data['deleted_by'];
        if (isset($data['deleted_at'])) $this->deleted_at = $data['deleted_at'];
    }

    /**
     * Convert object to array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'code' => $this->code,
            'description' => $this->description,
            'organization_id' => $this->organization_id,
            'organization_position_id' => $this->organization_position_id,
            'organization_workstation_id' => $this->organization_workstation_id,
            'reports_to_user_id' => $this->reports_to_user_id,
            'vacancy_type' => $this->vacancy_type,
            'priority' => $this->priority,
            'openings_count' => $this->openings_count,
            'posted_date' => $this->posted_date,
            'application_deadline' => $this->application_deadline,
            'target_start_date' => $this->target_start_date,
            'target_end_date' => $this->target_end_date,
            'salary_offered_min' => $this->salary_offered_min,
            'salary_offered_max' => $this->salary_offered_max,
            'salary_currency' => $this->salary_currency,
            'benefits' => $this->benefits,
            'application_method' => $this->application_method,
            'application_url' => $this->application_url,
            'contact_person' => $this->contact_person,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'status' => $this->status,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'filled_at' => $this->filled_at,
            'filled_by_user_id' => $this->filled_by_user_id,
            'views_count' => $this->views_count,
            'applications_count' => $this->applications_count,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at,
            'deleted_by' => $this->deleted_by,
            'deleted_at' => $this->deleted_at
        ];
    }

    /**
     * Get label for foreign key display (#11 in entity_creation_instructions.md)
     * Returns the label fields when this entity is used as a foreign key
     */
    public function getLabel() {
        if ($this->code) {
            return $this->title . ' (' . $this->code . ')';
        }
        return $this->title;
    }

    /**
     * Get public fields (visible to all users including guests)
     * Per permissions.md: Job vacancies are viewable by all when published
     */
    public function getPublicFields() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'code' => $this->code,
            'description' => $this->description,
            'organization_id' => $this->organization_id,
            'organization_position_id' => $this->organization_position_id,
            'vacancy_type' => $this->vacancy_type,
            'priority' => $this->priority,
            'openings_count' => $this->openings_count,
            'posted_date' => $this->posted_date,
            'application_deadline' => $this->application_deadline,
            'target_start_date' => $this->target_start_date,
            'salary_offered_min' => $this->salary_offered_min,
            'salary_offered_max' => $this->salary_offered_max,
            'salary_currency' => $this->salary_currency,
            'benefits' => $this->benefits,
            'application_method' => $this->application_method,
            'application_url' => $this->application_url,
            'contact_person' => $this->contact_person,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'status' => $this->status,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at
        ];
    }

    /**
     * Check if a field is public (visible to all)
     */
    public static function isPublicField($fieldName) {
        $publicFields = [
            'id', 'title', 'code', 'description', 'organization_id', 'organization_position_id',
            'vacancy_type', 'priority', 'openings_count', 'posted_date', 'application_deadline',
            'target_start_date', 'salary_offered_min', 'salary_offered_max', 'salary_currency',
            'benefits', 'application_method', 'application_url', 'contact_person', 'contact_email',
            'contact_phone', 'status', 'is_published', 'published_at'
        ];
        return in_array($fieldName, $publicFields);
    }

    /**
     * Get vacancy type options
     */
    public static function getVacancyTypes() {
        return [
            'new' => 'New Position',
            'replacement' => 'Replacement',
            'expansion' => 'Team Expansion'
        ];
    }

    /**
     * Get priority options
     */
    public static function getPriorityLevels() {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];
    }

    /**
     * Get status options
     */
    public static function getStatusOptions() {
        return [
            'draft' => 'Draft',
            'open' => 'Open',
            'on_hold' => 'On Hold',
            'filled' => 'Filled',
            'cancelled' => 'Cancelled'
        ];
    }

    /**
     * Get application method options
     */
    public static function getApplicationMethods() {
        return [
            'internal' => 'Internal Only',
            'external' => 'External Only',
            'both' => 'Both Internal & External'
        ];
    }

    /**
     * Get salary range formatted
     */
    public function getSalaryRangeFormatted() {
        if (empty($this->salary_offered_min) && empty($this->salary_offered_max)) {
            return 'Not specified';
        }
        $currency = $this->salary_currency ?: 'INR';
        if ($this->salary_offered_min && $this->salary_offered_max) {
            return $currency . ' ' . number_format($this->salary_offered_min) . ' - ' . number_format($this->salary_offered_max);
        }
        if ($this->salary_offered_min) {
            return $currency . ' ' . number_format($this->salary_offered_min) . '+';
        }
        return 'Up to ' . $currency . ' ' . number_format($this->salary_offered_max);
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeClass() {
        $classes = [
            'low' => 'bg-gray-100 text-gray-800',
            'medium' => 'bg-blue-100 text-blue-800',
            'high' => 'bg-orange-100 text-orange-800',
            'urgent' => 'bg-red-100 text-red-800'
        ];
        return $classes[$this->priority] ?? $classes['medium'];
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass() {
        $classes = [
            'draft' => 'bg-gray-100 text-gray-800',
            'open' => 'bg-green-100 text-green-800',
            'on_hold' => 'bg-yellow-100 text-yellow-800',
            'filled' => 'bg-blue-100 text-blue-800',
            'cancelled' => 'bg-red-100 text-red-800'
        ];
        return $classes[$this->status] ?? $classes['draft'];
    }

    /**
     * Check if vacancy is still accepting applications
     */
    public function isAcceptingApplications() {
        if ($this->status !== 'open' || !$this->is_published) {
            return false;
        }
        if ($this->application_deadline) {
            return strtotime($this->application_deadline) >= strtotime('today');
        }
        return true;
    }

    /**
     * Get days until deadline
     */
    public function getDaysUntilDeadline() {
        if (!$this->application_deadline) {
            return null;
        }
        $deadline = strtotime($this->application_deadline);
        $today = strtotime('today');
        $days = ceil(($deadline - $today) / 86400);
        return $days;
    }

    /**
     * Validate vacancy data
     */
    public function validate() {
        $errors = [];

        if (empty($this->title)) {
            $errors[] = "Vacancy title is required";
        }

        if (empty($this->organization_id)) {
            $errors[] = "Organization is required";
        }

        if (empty($this->organization_position_id)) {
            $errors[] = "Position is required";
        }

        if (!empty($this->code) && strlen($this->code) < 3) {
            $errors[] = "Vacancy code must be at least 3 characters";
        }

        $validVacancyTypes = array_keys(self::getVacancyTypes());
        if (!empty($this->vacancy_type) && !in_array($this->vacancy_type, $validVacancyTypes)) {
            $errors[] = "Invalid vacancy type";
        }

        $validPriorities = array_keys(self::getPriorityLevels());
        if (!empty($this->priority) && !in_array($this->priority, $validPriorities)) {
            $errors[] = "Invalid priority level";
        }

        $validStatuses = array_keys(self::getStatusOptions());
        if (!empty($this->status) && !in_array($this->status, $validStatuses)) {
            $errors[] = "Invalid status";
        }

        if (!empty($this->openings_count) && $this->openings_count < 1) {
            $errors[] = "Openings count must be at least 1";
        }

        if (!empty($this->salary_offered_min) && !empty($this->salary_offered_max)) {
            if ($this->salary_offered_min > $this->salary_offered_max) {
                $errors[] = "Minimum salary cannot be greater than maximum salary";
            }
        }

        if (!empty($this->application_deadline) && !empty($this->posted_date)) {
            if (strtotime($this->application_deadline) < strtotime($this->posted_date)) {
                $errors[] = "Application deadline cannot be before posted date";
            }
        }

        return $errors;
    }

    // ============= Getters =============
    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getCode() { return $this->code; }
    public function getDescription() { return $this->description; }
    public function getOrganizationId() { return $this->organization_id; }
    public function getOrganizationPositionId() { return $this->organization_position_id; }
    public function getOrganizationWorkstationId() { return $this->organization_workstation_id; }
    public function getReportsToUserId() { return $this->reports_to_user_id; }
    public function getVacancyType() { return $this->vacancy_type; }
    public function getPriority() { return $this->priority; }
    public function getOpeningsCount() { return $this->openings_count; }
    public function getPostedDate() { return $this->posted_date; }
    public function getApplicationDeadline() { return $this->application_deadline; }
    public function getTargetStartDate() { return $this->target_start_date; }
    public function getTargetEndDate() { return $this->target_end_date; }
    public function getSalaryOfferedMin() { return $this->salary_offered_min; }
    public function getSalaryOfferedMax() { return $this->salary_offered_max; }
    public function getSalaryCurrency() { return $this->salary_currency; }
    public function getBenefits() { return $this->benefits; }
    public function getApplicationMethod() { return $this->application_method; }
    public function getApplicationUrl() { return $this->application_url; }
    public function getContactPerson() { return $this->contact_person; }
    public function getContactEmail() { return $this->contact_email; }
    public function getContactPhone() { return $this->contact_phone; }
    public function getStatus() { return $this->status; }
    public function getIsPublished() { return $this->is_published; }
    public function getPublishedAt() { return $this->published_at; }
    public function getFilledAt() { return $this->filled_at; }
    public function getFilledByUserId() { return $this->filled_by_user_id; }
    public function getViewsCount() { return $this->views_count; }
    public function getApplicationsCount() { return $this->applications_count; }
    public function getIsActive() { return $this->is_active; }
    public function getSortOrder() { return $this->sort_order; }
    public function getCreatedBy() { return $this->created_by; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedBy() { return $this->updated_by; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getDeletedBy() { return $this->deleted_by; }
    public function getDeletedAt() { return $this->deleted_at; }
    public function isDeleted() { return !empty($this->deleted_at); }

    // ============= Setters =============
    public function setId($id) { $this->id = $id; }
    public function setTitle($title) { $this->title = trim($title); }
    public function setCode($code) { $this->code = strtoupper(trim($code)); }
    public function setDescription($description) { $this->description = $description; }
    public function setOrganizationId($organization_id) { $this->organization_id = $organization_id; }
    public function setOrganizationPositionId($organization_position_id) { $this->organization_position_id = $organization_position_id; }
    public function setOrganizationWorkstationId($organization_workstation_id) { $this->organization_workstation_id = $organization_workstation_id ?: null; }
    public function setReportsToUserId($reports_to_user_id) { $this->reports_to_user_id = $reports_to_user_id ?: null; }
    public function setVacancyType($vacancy_type) { $this->vacancy_type = $vacancy_type; }
    public function setPriority($priority) { $this->priority = $priority ?: 'medium'; }
    public function setOpeningsCount($openings_count) { $this->openings_count = $openings_count !== null && $openings_count !== '' ? (int)$openings_count : 1; }
    public function setPostedDate($posted_date) { $this->posted_date = $posted_date; }
    public function setApplicationDeadline($application_deadline) { $this->application_deadline = $application_deadline; }
    public function setTargetStartDate($target_start_date) { $this->target_start_date = $target_start_date; }
    public function setTargetEndDate($target_end_date) { $this->target_end_date = $target_end_date; }
    public function setSalaryOfferedMin($salary_offered_min) { $this->salary_offered_min = $salary_offered_min !== null && $salary_offered_min !== '' ? (float)$salary_offered_min : null; }
    public function setSalaryOfferedMax($salary_offered_max) { $this->salary_offered_max = $salary_offered_max !== null && $salary_offered_max !== '' ? (float)$salary_offered_max : null; }
    public function setSalaryCurrency($salary_currency) { $this->salary_currency = $salary_currency ?: 'INR'; }
    public function setBenefits($benefits) { $this->benefits = $benefits; }
    public function setApplicationMethod($application_method) { $this->application_method = $application_method ?: 'both'; }
    public function setApplicationUrl($application_url) { $this->application_url = $application_url; }
    public function setContactPerson($contact_person) { $this->contact_person = $contact_person; }
    public function setContactEmail($contact_email) { $this->contact_email = $contact_email; }
    public function setContactPhone($contact_phone) { $this->contact_phone = $contact_phone; }
    public function setStatus($status) { $this->status = $status ?: 'draft'; }
    public function setIsPublished($is_published) { $this->is_published = (bool)$is_published; }
    public function setPublishedAt($published_at) { $this->published_at = $published_at; }
    public function setFilledAt($filled_at) { $this->filled_at = $filled_at; }
    public function setFilledByUserId($filled_by_user_id) { $this->filled_by_user_id = $filled_by_user_id; }
    public function setViewsCount($views_count) { $this->views_count = (int)$views_count; }
    public function setApplicationsCount($applications_count) { $this->applications_count = (int)$applications_count; }
    public function setIsActive($is_active) { $this->is_active = (bool)$is_active; }
    public function setSortOrder($sort_order) { $this->sort_order = (int)$sort_order; }
    public function setCreatedBy($created_by) { $this->created_by = $created_by; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setUpdatedBy($updated_by) { $this->updated_by = $updated_by; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    public function setDeletedBy($deleted_by) { $this->deleted_by = $deleted_by; }
    public function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }
}
