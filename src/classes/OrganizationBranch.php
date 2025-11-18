<?php

namespace App\Classes;

/**
 * OrganizationBranch Entity
 * Manages branch locations for organizations
 * Following entity_creation_instructions.md guidelines
 */
class OrganizationBranch {
    private $id;
    private $organization_id;
    private $name;
    private $code;
    private $description;

    // Address fields
    private $address_line1;
    private $address_line2;
    private $city;
    private $state;
    private $country;
    private $postal_code;

    // Contact fields
    private $phone;
    private $email;
    private $website;

    // Branch contact person
    private $contact_person_name;
    private $contact_person_phone;
    private $contact_person_email;

    // Status and metadata
    private $is_active;
    private $branch_type;
    private $size_category;
    private $opening_date;
    private $sort_order;

    // Default audit fields
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

    public function hydrate($data) {
        if (isset($data['id'])) $this->id = $data['id'];
        if (isset($data['organization_id'])) $this->organization_id = $data['organization_id'];
        if (isset($data['name'])) $this->name = $data['name'];
        if (isset($data['code'])) $this->code = $data['code'];
        if (isset($data['description'])) $this->description = $data['description'];

        if (isset($data['address_line1'])) $this->address_line1 = $data['address_line1'];
        if (isset($data['address_line2'])) $this->address_line2 = $data['address_line2'];
        if (isset($data['city'])) $this->city = $data['city'];
        if (isset($data['state'])) $this->state = $data['state'];
        if (isset($data['country'])) $this->country = $data['country'];
        if (isset($data['postal_code'])) $this->postal_code = $data['postal_code'];

        if (isset($data['phone'])) $this->phone = $data['phone'];
        if (isset($data['email'])) $this->email = $data['email'];
        if (isset($data['website'])) $this->website = $data['website'];

        if (isset($data['contact_person_name'])) $this->contact_person_name = $data['contact_person_name'];
        if (isset($data['contact_person_phone'])) $this->contact_person_phone = $data['contact_person_phone'];
        if (isset($data['contact_person_email'])) $this->contact_person_email = $data['contact_person_email'];

        if (isset($data['is_active'])) $this->is_active = $data['is_active'];
        if (isset($data['branch_type'])) $this->branch_type = $data['branch_type'];
        if (isset($data['size_category'])) $this->size_category = $data['size_category'];
        if (isset($data['opening_date'])) $this->opening_date = $data['opening_date'];
        if (isset($data['sort_order'])) $this->sort_order = $data['sort_order'];

        if (isset($data['created_by'])) $this->created_by = $data['created_by'];
        if (isset($data['created_at'])) $this->created_at = $data['created_at'];
        if (isset($data['updated_by'])) $this->updated_by = $data['updated_by'];
        if (isset($data['updated_at'])) $this->updated_at = $data['updated_at'];
        if (isset($data['deleted_by'])) $this->deleted_by = $data['deleted_by'];
        if (isset($data['deleted_at'])) $this->deleted_at = $data['deleted_at'];
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'contact_person_name' => $this->contact_person_name,
            'contact_person_phone' => $this->contact_person_phone,
            'contact_person_email' => $this->contact_person_email,
            'is_active' => $this->is_active,
            'branch_type' => $this->branch_type,
            'size_category' => $this->size_category,
            'opening_date' => $this->opening_date,
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
     */
    public function getLabel() {
        $label = $this->name;
        if ($this->city) {
            $label .= ' - ' . $this->city;
        }
        if ($this->code) {
            $label .= ' (' . $this->code . ')';
        }
        return $label;
    }

    /**
     * Get full address formatted
     */
    public function getFullAddress() {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ]);
        return implode(', ', $parts);
    }

    /**
     * Get public fields
     */
    public function getPublicFields() {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'branch_type' => $this->branch_type
        ];
    }

    /**
     * Validate branch data
     */
    public function validate() {
        $errors = [];

        if (empty($this->name)) {
            $errors[] = "Branch name is required";
        }

        if (empty($this->organization_id)) {
            $errors[] = "Organization is required";
        }

        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        if ($this->contact_person_email && !filter_var($this->contact_person_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid contact person email format";
        }

        return $errors;
    }

    /**
     * Check if a field is public (visible to all)
     * Per ENTITY_IMPLEMENTATION_SUMMARY.md
     */
    public static function isPublicField($fieldName) {
        $publicFields = [
            'id', 'organization_id', 'name', 'code', 'description',
            'city', 'state', 'country', 'phone', 'email',
            'is_active', 'branch_type'
        ];
        return in_array($fieldName, $publicFields);
    }

    // Getters
    public function getId() { return $this->id; }
    public function getOrganizationId() { return $this->organization_id; }
    public function getName() { return $this->name; }
    public function getCode() { return $this->code; }
    public function getDescription() { return $this->description; }
    public function getAddressLine1() { return $this->address_line1; }
    public function getAddressLine2() { return $this->address_line2; }
    public function getCity() { return $this->city; }
    public function getState() { return $this->state; }
    public function getCountry() { return $this->country; }
    public function getPostalCode() { return $this->postal_code; }
    public function getPhone() { return $this->phone; }
    public function getEmail() { return $this->email; }
    public function getWebsite() { return $this->website; }
    public function getContactPersonName() { return $this->contact_person_name; }
    public function getContactPersonPhone() { return $this->contact_person_phone; }
    public function getContactPersonEmail() { return $this->contact_person_email; }
    public function getIsActive() { return $this->is_active; }
    public function getBranchType() { return $this->branch_type; }
    public function getSizeCategory() { return $this->size_category; }
    public function getOpeningDate() { return $this->opening_date; }
    public function getSortOrder() { return $this->sort_order; }
    public function getCreatedBy() { return $this->created_by; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedBy() { return $this->updated_by; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getDeletedBy() { return $this->deleted_by; }
    public function getDeletedAt() { return $this->deleted_at; }
    public function isDeleted() { return !empty($this->deleted_at); }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setOrganizationId($organization_id) { $this->organization_id = $organization_id; }
    public function setName($name) { $this->name = $name; }
    public function setCode($code) { $this->code = $code; }
    public function setDescription($description) { $this->description = $description; }
    public function setAddressLine1($address_line1) { $this->address_line1 = $address_line1; }
    public function setAddressLine2($address_line2) { $this->address_line2 = $address_line2; }
    public function setCity($city) { $this->city = $city; }
    public function setState($state) { $this->state = $state; }
    public function setCountry($country) { $this->country = $country; }
    public function setPostalCode($postal_code) { $this->postal_code = $postal_code; }
    public function setPhone($phone) { $this->phone = $phone; }
    public function setEmail($email) { $this->email = $email; }
    public function setWebsite($website) { $this->website = $website; }
    public function setContactPersonName($name) { $this->contact_person_name = $name; }
    public function setContactPersonPhone($phone) { $this->contact_person_phone = $phone; }
    public function setContactPersonEmail($email) { $this->contact_person_email = $email; }
    public function setIsActive($is_active) { $this->is_active = (bool)$is_active; }
    public function setBranchType($branch_type) { $this->branch_type = $branch_type; }
    public function setSizeCategory($size_category) { $this->size_category = $size_category; }
    public function setOpeningDate($opening_date) { $this->opening_date = $opening_date; }
    public function setSortOrder($sort_order) { $this->sort_order = (int)$sort_order; }
    public function setCreatedBy($created_by) { $this->created_by = $created_by; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setUpdatedBy($updated_by) { $this->updated_by = $updated_by; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    public function setDeletedBy($deleted_by) { $this->deleted_by = $deleted_by; }
    public function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }
}
