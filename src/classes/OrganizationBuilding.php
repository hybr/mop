<?php

namespace App\Classes;

/**
 * OrganizationBuilding Entity
 * Manages buildings under branch locations
 * Following entity_creation_instructions.md guidelines
 * Per navigation.md: One branch may have more than one building. One building is a must.
 */
class OrganizationBuilding {
    private $id;
    private $branch_id;
    private $organization_id;
    private $name;
    private $code;
    private $description;

    // Address fields (required per navigation.md)
    private $street_address;
    private $city;
    private $state;
    private $postal_code;
    private $country;

    // Geo coordinates (required per navigation.md)
    private $latitude;
    private $longitude;

    // Contact fields
    private $phone;
    private $email;

    // Building details
    private $building_type; // office, warehouse, retail, mixed_use, etc.
    private $total_floors;
    private $total_area_sqft;
    private $year_built;
    private $ownership_type; // owned, leased, rented

    // Status and metadata
    private $is_active;
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
        if (isset($data['branch_id'])) $this->branch_id = $data['branch_id'];
        if (isset($data['organization_id'])) $this->organization_id = $data['organization_id'];
        if (isset($data['name'])) $this->name = $data['name'];
        if (isset($data['code'])) $this->code = $data['code'];
        if (isset($data['description'])) $this->description = $data['description'];

        if (isset($data['street_address'])) $this->street_address = $data['street_address'];
        if (isset($data['city'])) $this->city = $data['city'];
        if (isset($data['state'])) $this->state = $data['state'];
        if (isset($data['postal_code'])) $this->postal_code = $data['postal_code'];
        if (isset($data['country'])) $this->country = $data['country'];
        if (isset($data['latitude'])) $this->latitude = $data['latitude'];
        if (isset($data['longitude'])) $this->longitude = $data['longitude'];

        if (isset($data['phone'])) $this->phone = $data['phone'];
        if (isset($data['email'])) $this->email = $data['email'];

        if (isset($data['building_type'])) $this->building_type = $data['building_type'];
        if (isset($data['total_floors'])) $this->total_floors = $data['total_floors'];
        if (isset($data['total_area_sqft'])) $this->total_area_sqft = $data['total_area_sqft'];
        if (isset($data['year_built'])) $this->year_built = $data['year_built'];
        if (isset($data['ownership_type'])) $this->ownership_type = $data['ownership_type'];

        if (isset($data['is_active'])) $this->is_active = $data['is_active'];
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
            'branch_id' => $this->branch_id,
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'street_address' => $this->street_address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'phone' => $this->phone,
            'email' => $this->email,
            'building_type' => $this->building_type,
            'total_floors' => $this->total_floors,
            'total_area_sqft' => $this->total_area_sqft,
            'year_built' => $this->year_built,
            'ownership_type' => $this->ownership_type,
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
     */
    public function getLabel() {
        $label = $this->name;
        if ($this->code) {
            $label .= ' (' . $this->code . ')';
        }
        return $label;
    }

    /**
     * Get public fields
     */
    public function getPublicFields() {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'building_type' => $this->building_type,
            'is_active' => $this->is_active
        ];
    }

    /**
     * Validate building data
     */
    public function validate() {
        $errors = [];

        if (empty($this->name)) {
            $errors[] = "Building name is required";
        }

        if (empty($this->branch_id)) {
            $errors[] = "Branch is required";
        }

        if (empty($this->organization_id)) {
            $errors[] = "Organization is required";
        }

        // Geo coordinates validation (required per navigation.md)
        if (empty($this->latitude)) {
            $errors[] = "Latitude is required";
        }

        if (empty($this->longitude)) {
            $errors[] = "Longitude is required";
        }

        if ($this->latitude && !is_numeric($this->latitude)) {
            $errors[] = "Latitude must be a valid number";
        }

        if ($this->longitude && !is_numeric($this->longitude)) {
            $errors[] = "Longitude must be a valid number";
        }

        if ($this->latitude && ($this->latitude < -90 || $this->latitude > 90)) {
            $errors[] = "Latitude must be between -90 and 90";
        }

        if ($this->longitude && ($this->longitude < -180 || $this->longitude > 180)) {
            $errors[] = "Longitude must be between -180 and 180";
        }

        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        if ($this->total_floors && !is_numeric($this->total_floors)) {
            $errors[] = "Total floors must be a number";
        }

        if ($this->total_area_sqft && !is_numeric($this->total_area_sqft)) {
            $errors[] = "Total area must be a number";
        }

        if ($this->year_built && !is_numeric($this->year_built)) {
            $errors[] = "Year built must be a valid year";
        }

        return $errors;
    }

    /**
     * Check if a field is public (visible to all)
     * Per ENTITY_IMPLEMENTATION_SUMMARY.md
     */
    public static function isPublicField($fieldName) {
        $publicFields = [
            'id', 'branch_id', 'organization_id', 'name', 'code', 'description',
            'city', 'state', 'country', 'phone', 'email', 'building_type', 'is_active'
        ];
        return in_array($fieldName, $publicFields);
    }

    // Getters
    public function getId() { return $this->id; }
    public function getBranchId() { return $this->branch_id; }
    public function getOrganizationId() { return $this->organization_id; }
    public function getName() { return $this->name; }
    public function getCode() { return $this->code; }
    public function getDescription() { return $this->description; }
    public function getStreetAddress() { return $this->street_address; }
    public function getCity() { return $this->city; }
    public function getState() { return $this->state; }
    public function getPostalCode() { return $this->postal_code; }
    public function getCountry() { return $this->country; }
    public function getLatitude() { return $this->latitude; }
    public function getLongitude() { return $this->longitude; }
    public function getPhone() { return $this->phone; }
    public function getEmail() { return $this->email; }
    public function getBuildingType() { return $this->building_type; }
    public function getTotalFloors() { return $this->total_floors; }
    public function getTotalAreaSqft() { return $this->total_area_sqft; }
    public function getYearBuilt() { return $this->year_built; }
    public function getOwnershipType() { return $this->ownership_type; }
    public function getIsActive() { return $this->is_active; }
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
    public function setBranchId($branch_id) { $this->branch_id = $branch_id; }
    public function setOrganizationId($organization_id) { $this->organization_id = $organization_id; }
    public function setName($name) { $this->name = $name; }
    public function setCode($code) { $this->code = $code; }
    public function setDescription($description) { $this->description = $description; }
    public function setStreetAddress($street_address) { $this->street_address = $street_address; }
    public function setCity($city) { $this->city = $city; }
    public function setState($state) { $this->state = $state; }
    public function setPostalCode($postal_code) { $this->postal_code = $postal_code; }
    public function setCountry($country) { $this->country = $country; }
    public function setLatitude($latitude) { $this->latitude = $latitude; }
    public function setLongitude($longitude) { $this->longitude = $longitude; }
    public function setPhone($phone) { $this->phone = $phone; }
    public function setEmail($email) { $this->email = $email; }
    public function setBuildingType($building_type) { $this->building_type = $building_type; }
    public function setTotalFloors($total_floors) { $this->total_floors = (int)$total_floors; }
    public function setTotalAreaSqft($total_area_sqft) { $this->total_area_sqft = (float)$total_area_sqft; }
    public function setYearBuilt($year_built) { $this->year_built = (int)$year_built; }
    public function setOwnershipType($ownership_type) { $this->ownership_type = $ownership_type; }
    public function setIsActive($is_active) { $this->is_active = (bool)$is_active; }
    public function setSortOrder($sort_order) { $this->sort_order = (int)$sort_order; }
    public function setCreatedBy($created_by) { $this->created_by = $created_by; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setUpdatedBy($updated_by) { $this->updated_by = $updated_by; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    public function setDeletedBy($deleted_by) { $this->deleted_by = $deleted_by; }
    public function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }
}
