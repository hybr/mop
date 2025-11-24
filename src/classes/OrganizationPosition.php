<?php

namespace App\Classes;

/**
 * OrganizationPosition Entity
 * Represents a position that combines Department + Team + Designation
 * with additional requirements for Education, Skills, and Experience
 *
 * Positions are common to all organizations (global entity)
 * Following entity_creation_instructions.md guidelines
 */
class OrganizationPosition {
    private $id;
    private $name;                          // Position name (e.g., "Senior Software Engineer")
    private $code;                          // Unique code (e.g., "SR_SWE")
    private $description;                   // Position description

    // Foreign keys - combination of Department + Team + Designation
    private $organization_department_id;    // Required: Department this position belongs to
    private $organization_department_team_id; // Optional: Team within the department
    private $organization_designation_id;   // Required: Designation level for this position

    // Requirements
    private $min_education;                 // Minimum education required (e.g., "Bachelor's Degree", "Master's Degree")
    private $min_education_field;           // Field of study (e.g., "Computer Science", "Engineering")
    private $min_experience_years;          // Minimum years of experience required
    private $skills_required;               // JSON or comma-separated list of required skills
    private $skills_preferred;              // JSON or comma-separated list of preferred skills
    private $certifications_required;       // Required certifications
    private $certifications_preferred;      // Preferred certifications

    // Position details
    private $employment_type;               // full_time, part_time, contract, internship
    private $reports_to_position_id;        // Reporting position (for hierarchy)
    private $headcount;                     // Number of positions available
    private $salary_range_min;              // Minimum salary
    private $salary_range_max;              // Maximum salary
    private $salary_currency;               // Currency code (e.g., "INR", "USD")

    // Status and metadata
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
        if (isset($data['name'])) $this->name = $data['name'];
        if (isset($data['code'])) $this->code = $data['code'];
        if (isset($data['description'])) $this->description = $data['description'];

        // Foreign keys
        if (isset($data['organization_department_id'])) $this->organization_department_id = $data['organization_department_id'];
        if (isset($data['organization_department_team_id'])) $this->organization_department_team_id = $data['organization_department_team_id'];
        if (isset($data['organization_designation_id'])) $this->organization_designation_id = $data['organization_designation_id'];

        // Requirements
        if (isset($data['min_education'])) $this->min_education = $data['min_education'];
        if (isset($data['min_education_field'])) $this->min_education_field = $data['min_education_field'];
        if (isset($data['min_experience_years'])) $this->min_experience_years = $data['min_experience_years'];
        if (isset($data['skills_required'])) $this->skills_required = $data['skills_required'];
        if (isset($data['skills_preferred'])) $this->skills_preferred = $data['skills_preferred'];
        if (isset($data['certifications_required'])) $this->certifications_required = $data['certifications_required'];
        if (isset($data['certifications_preferred'])) $this->certifications_preferred = $data['certifications_preferred'];

        // Position details
        if (isset($data['employment_type'])) $this->employment_type = $data['employment_type'];
        if (isset($data['reports_to_position_id'])) $this->reports_to_position_id = $data['reports_to_position_id'];
        if (isset($data['headcount'])) $this->headcount = $data['headcount'];
        if (isset($data['salary_range_min'])) $this->salary_range_min = $data['salary_range_min'];
        if (isset($data['salary_range_max'])) $this->salary_range_max = $data['salary_range_max'];
        if (isset($data['salary_currency'])) $this->salary_currency = $data['salary_currency'];

        // Status
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
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'organization_department_id' => $this->organization_department_id,
            'organization_department_team_id' => $this->organization_department_team_id,
            'organization_designation_id' => $this->organization_designation_id,
            'min_education' => $this->min_education,
            'min_education_field' => $this->min_education_field,
            'min_experience_years' => $this->min_experience_years,
            'skills_required' => $this->skills_required,
            'skills_preferred' => $this->skills_preferred,
            'certifications_required' => $this->certifications_required,
            'certifications_preferred' => $this->certifications_preferred,
            'employment_type' => $this->employment_type,
            'reports_to_position_id' => $this->reports_to_position_id,
            'headcount' => $this->headcount,
            'salary_range_min' => $this->salary_range_min,
            'salary_range_max' => $this->salary_range_max,
            'salary_currency' => $this->salary_currency,
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
            return $this->name . ' (' . $this->code . ')';
        }
        return $this->name;
    }

    /**
     * Get public fields (visible to all users including guests)
     * Per permissions.md: Organization positions are viewable by all
     */
    public function getPublicFields() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'organization_department_id' => $this->organization_department_id,
            'organization_department_team_id' => $this->organization_department_team_id,
            'organization_designation_id' => $this->organization_designation_id,
            'min_education' => $this->min_education,
            'min_education_field' => $this->min_education_field,
            'min_experience_years' => $this->min_experience_years,
            'skills_required' => $this->skills_required,
            'employment_type' => $this->employment_type,
            'is_active' => $this->is_active
        ];
    }

    /**
     * Check if a field is public (visible to all)
     */
    public static function isPublicField($fieldName) {
        $publicFields = [
            'id', 'name', 'code', 'description',
            'organization_department_id', 'organization_department_team_id', 'organization_designation_id',
            'min_education', 'min_education_field', 'min_experience_years', 'skills_required',
            'employment_type', 'is_active'
        ];
        return in_array($fieldName, $publicFields);
    }

    /**
     * Get education level options
     */
    public static function getEducationLevels() {
        return [
            'none' => 'No Formal Education',
            'high_school' => 'High School / 10th',
            'higher_secondary' => 'Higher Secondary / 12th',
            'diploma' => 'Diploma',
            'bachelors' => "Bachelor's Degree",
            'masters' => "Master's Degree",
            'doctorate' => 'Doctorate / PhD',
            'professional' => 'Professional Degree (MD, JD, etc.)'
        ];
    }

    /**
     * Get employment type options
     */
    public static function getEmploymentTypes() {
        return [
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            'internship' => 'Internship',
            'freelance' => 'Freelance',
            'temporary' => 'Temporary'
        ];
    }

    /**
     * Get education level display name
     */
    public function getEducationLevelName() {
        $levels = self::getEducationLevels();
        return $levels[$this->min_education] ?? $this->min_education;
    }

    /**
     * Get employment type display name
     */
    public function getEmploymentTypeName() {
        $types = self::getEmploymentTypes();
        return $types[$this->employment_type] ?? $this->employment_type;
    }

    /**
     * Get skills required as array
     */
    public function getSkillsRequiredArray() {
        if (empty($this->skills_required)) {
            return [];
        }
        // Try JSON decode first, fallback to comma-separated
        $decoded = json_decode($this->skills_required, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return array_map('trim', explode(',', $this->skills_required));
    }

    /**
     * Get skills preferred as array
     */
    public function getSkillsPreferredArray() {
        if (empty($this->skills_preferred)) {
            return [];
        }
        $decoded = json_decode($this->skills_preferred, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return array_map('trim', explode(',', $this->skills_preferred));
    }

    /**
     * Get salary range formatted
     */
    public function getSalaryRangeFormatted() {
        if (empty($this->salary_range_min) && empty($this->salary_range_max)) {
            return 'Not specified';
        }
        $currency = $this->salary_currency ?: 'INR';
        if ($this->salary_range_min && $this->salary_range_max) {
            return $currency . ' ' . number_format($this->salary_range_min) . ' - ' . number_format($this->salary_range_max);
        }
        if ($this->salary_range_min) {
            return $currency . ' ' . number_format($this->salary_range_min) . '+';
        }
        return 'Up to ' . $currency . ' ' . number_format($this->salary_range_max);
    }

    /**
     * Validate position data
     */
    public function validate() {
        $errors = [];

        if (empty($this->name)) {
            $errors[] = "Position name is required";
        }

        if (empty($this->code)) {
            $errors[] = "Position code is required";
        } elseif (!preg_match('/^[A-Z0-9_]+$/', $this->code)) {
            $errors[] = "Position code can only contain uppercase letters, numbers, and underscores";
        } elseif (strlen($this->code) < 2 || strlen($this->code) > 20) {
            $errors[] = "Position code must be between 2 and 20 characters";
        }

        if (empty($this->organization_department_id)) {
            $errors[] = "Department is required";
        }

        if (empty($this->organization_designation_id)) {
            $errors[] = "Designation is required";
        }

        if (!empty($this->min_experience_years) && (!is_numeric($this->min_experience_years) || $this->min_experience_years < 0)) {
            $errors[] = "Minimum experience years must be a positive number";
        }

        if (!empty($this->headcount) && (!is_numeric($this->headcount) || $this->headcount < 1)) {
            $errors[] = "Headcount must be at least 1";
        }

        if (!empty($this->salary_range_min) && !empty($this->salary_range_max)) {
            if ($this->salary_range_min > $this->salary_range_max) {
                $errors[] = "Minimum salary cannot be greater than maximum salary";
            }
        }

        return $errors;
    }

    // ============= Getters =============
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getCode() { return $this->code; }
    public function getDescription() { return $this->description; }
    public function getOrganizationDepartmentId() { return $this->organization_department_id; }
    public function getOrganizationDepartmentTeamId() { return $this->organization_department_team_id; }
    public function getOrganizationDesignationId() { return $this->organization_designation_id; }
    public function getMinEducation() { return $this->min_education; }
    public function getMinEducationField() { return $this->min_education_field; }
    public function getMinExperienceYears() { return $this->min_experience_years; }
    public function getSkillsRequired() { return $this->skills_required; }
    public function getSkillsPreferred() { return $this->skills_preferred; }
    public function getCertificationsRequired() { return $this->certifications_required; }
    public function getCertificationsPreferred() { return $this->certifications_preferred; }
    public function getEmploymentType() { return $this->employment_type; }
    public function getReportsToPositionId() { return $this->reports_to_position_id; }
    public function getHeadcount() { return $this->headcount; }
    public function getSalaryRangeMin() { return $this->salary_range_min; }
    public function getSalaryRangeMax() { return $this->salary_range_max; }
    public function getSalaryCurrency() { return $this->salary_currency; }
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

    public function setName($name) { $this->name = trim($name); }

    public function setCode($code) {
        if (!empty($code)) {
            $code = strtoupper(trim($code));
            if (!preg_match('/^[A-Z0-9_]+$/', $code)) {
                throw new \Exception("Position code can only contain uppercase letters, numbers, and underscores");
            }
            if (strlen($code) < 2 || strlen($code) > 20) {
                throw new \Exception("Position code must be between 2 and 20 characters");
            }
        }
        $this->code = $code;
    }

    public function setDescription($description) { $this->description = $description; }
    public function setOrganizationDepartmentId($id) { $this->organization_department_id = $id ?: null; }
    public function setOrganizationDepartmentTeamId($id) { $this->organization_department_team_id = $id ?: null; }
    public function setOrganizationDesignationId($id) { $this->organization_designation_id = $id ?: null; }
    public function setMinEducation($education) { $this->min_education = $education; }
    public function setMinEducationField($field) { $this->min_education_field = $field; }
    public function setMinExperienceYears($years) { $this->min_experience_years = $years !== null && $years !== '' ? (int)$years : null; }

    public function setSkillsRequired($skills) {
        // Accept array or string
        if (is_array($skills)) {
            $this->skills_required = json_encode($skills);
        } else {
            $this->skills_required = $skills;
        }
    }

    public function setSkillsPreferred($skills) {
        if (is_array($skills)) {
            $this->skills_preferred = json_encode($skills);
        } else {
            $this->skills_preferred = $skills;
        }
    }

    public function setCertificationsRequired($certs) { $this->certifications_required = $certs; }
    public function setCertificationsPreferred($certs) { $this->certifications_preferred = $certs; }
    public function setEmploymentType($type) { $this->employment_type = $type; }
    public function setReportsToPositionId($id) { $this->reports_to_position_id = $id ?: null; }
    public function setHeadcount($count) { $this->headcount = $count !== null && $count !== '' ? (int)$count : null; }
    public function setSalaryRangeMin($min) { $this->salary_range_min = $min !== null && $min !== '' ? (float)$min : null; }
    public function setSalaryRangeMax($max) { $this->salary_range_max = $max !== null && $max !== '' ? (float)$max : null; }
    public function setSalaryCurrency($currency) { $this->salary_currency = $currency ?: 'INR'; }
    public function setIsActive($is_active) { $this->is_active = (bool)$is_active; }
    public function setSortOrder($sort_order) { $this->sort_order = (int)$sort_order; }
    public function setCreatedBy($created_by) { $this->created_by = $created_by; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setUpdatedBy($updated_by) { $this->updated_by = $updated_by; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    public function setDeletedBy($deleted_by) { $this->deleted_by = $deleted_by; }
    public function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }
}
