<?php

namespace App\Classes;

class OrganizationWorkstation {
    private $id;
    private $building_id;
    private $organization_id;

    // Workstation identification
    private $name;
    private $code;
    private $description;

    // Location within building
    private $floor;
    private $room;
    private $seat_number;

    // Workstation details
    private $workstation_type;
    private $capacity;
    private $area_sqft;

    // Equipment and amenities
    private $has_computer;
    private $has_phone;
    private $has_printer;
    private $amenities;

    // Assignment status
    private $is_occupied;
    private $assigned_to;

    // Status and metadata
    private $is_active;
    private $sort_order;

    // Audit fields
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
        if (isset($data['building_id'])) $this->building_id = $data['building_id'];
        if (isset($data['organization_id'])) $this->organization_id = $data['organization_id'];
        if (isset($data['name'])) $this->name = $data['name'];
        if (isset($data['code'])) $this->code = $data['code'];
        if (isset($data['description'])) $this->description = $data['description'];
        if (isset($data['floor'])) $this->floor = $data['floor'];
        if (isset($data['room'])) $this->room = $data['room'];
        if (isset($data['seat_number'])) $this->seat_number = $data['seat_number'];
        if (isset($data['workstation_type'])) $this->workstation_type = $data['workstation_type'];
        if (isset($data['capacity'])) $this->capacity = $data['capacity'];
        if (isset($data['area_sqft'])) $this->area_sqft = $data['area_sqft'];
        if (isset($data['has_computer'])) $this->has_computer = $data['has_computer'];
        if (isset($data['has_phone'])) $this->has_phone = $data['has_phone'];
        if (isset($data['has_printer'])) $this->has_printer = $data['has_printer'];
        if (isset($data['amenities'])) $this->amenities = $data['amenities'];
        if (isset($data['is_occupied'])) $this->is_occupied = $data['is_occupied'];
        if (isset($data['assigned_to'])) $this->assigned_to = $data['assigned_to'];
        if (isset($data['is_active'])) $this->is_active = $data['is_active'];
        if (isset($data['sort_order'])) $this->sort_order = $data['sort_order'];
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
            'building_id' => $this->building_id,
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'floor' => $this->floor,
            'room' => $this->room,
            'seat_number' => $this->seat_number,
            'workstation_type' => $this->workstation_type,
            'capacity' => $this->capacity,
            'area_sqft' => $this->area_sqft,
            'has_computer' => $this->has_computer,
            'has_phone' => $this->has_phone,
            'has_printer' => $this->has_printer,
            'amenities' => $this->amenities,
            'is_occupied' => $this->is_occupied,
            'assigned_to' => $this->assigned_to,
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
     * Get workstation location (floor, room, seat)
     */
    public function getLocation() {
        $parts = [];
        if ($this->floor) $parts[] = "Floor {$this->floor}";
        if ($this->room) $parts[] = "Room {$this->room}";
        if ($this->seat_number) $parts[] = "Seat {$this->seat_number}";
        return implode(', ', $parts);
    }

    /**
     * Get full workstation identifier (name + location)
     */
    public function getFullName() {
        $location = $this->getLocation();
        if ($location) {
            return $this->name . ' (' . $location . ')';
        }
        return $this->name;
    }

    /**
     * Get label for foreign key display
     * Returns the label field when this entity is used as a foreign key
     */
    public function getLabel() {
        return $this->getFullName();
    }

    /**
     * Get public fields (visible to all users including guests)
     * Per permissions.md: All users can view public fields of workstations
     */
    public function getPublicFields() {
        return [
            'id' => $this->id,
            'building_id' => $this->building_id,
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'floor' => $this->floor,
            'room' => $this->room,
            'seat_number' => $this->seat_number,
            'workstation_type' => $this->workstation_type,
            'capacity' => $this->capacity,
            'is_occupied' => $this->is_occupied,
            'is_active' => $this->is_active
        ];
    }

    /**
     * Check if a field is public (visible to all)
     */
    public static function isPublicField($fieldName) {
        $publicFields = ['id', 'building_id', 'organization_id', 'name', 'code', 'description',
                        'floor', 'room', 'seat_number', 'workstation_type', 'capacity',
                        'is_occupied', 'is_active'];
        return in_array($fieldName, $publicFields);
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getBuildingId() {
        return $this->building_id;
    }

    public function getOrganizationId() {
        return $this->organization_id;
    }

    public function getName() {
        return $this->name;
    }

    public function getCode() {
        return $this->code;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getFloor() {
        return $this->floor;
    }

    public function getRoom() {
        return $this->room;
    }

    public function getSeatNumber() {
        return $this->seat_number;
    }

    public function getWorkstationType() {
        return $this->workstation_type;
    }

    public function getCapacity() {
        return $this->capacity;
    }

    public function getAreaSqft() {
        return $this->area_sqft;
    }

    public function getHasComputer() {
        return $this->has_computer;
    }

    public function getHasPhone() {
        return $this->has_phone;
    }

    public function getHasPrinter() {
        return $this->has_printer;
    }

    public function getAmenities() {
        return $this->amenities;
    }

    public function getIsOccupied() {
        return $this->is_occupied;
    }

    public function getAssignedTo() {
        return $this->assigned_to;
    }

    public function getIsActive() {
        return $this->is_active;
    }

    public function getSortOrder() {
        return $this->sort_order;
    }

    public function getCreatedBy() {
        return $this->created_by;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedBy() {
        return $this->updated_by;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    public function getDeletedBy() {
        return $this->deleted_by;
    }

    public function getDeletedAt() {
        return $this->deleted_at;
    }

    public function isDeleted() {
        return !empty($this->deleted_at);
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setBuildingId($building_id) {
        $this->building_id = $building_id;
    }

    public function setOrganizationId($organization_id) {
        $this->organization_id = $organization_id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setCode($code) {
        $this->code = $code;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setFloor($floor) {
        $this->floor = $floor;
    }

    public function setRoom($room) {
        $this->room = $room;
    }

    public function setSeatNumber($seat_number) {
        $this->seat_number = $seat_number;
    }

    public function setWorkstationType($workstation_type) {
        $validTypes = ['desk', 'cubicle', 'private_office', 'hot_desk', 'meeting_room', 'lab', 'workshop', 'other'];
        if (!empty($workstation_type) && !in_array($workstation_type, $validTypes)) {
            throw new \Exception("Invalid workstation type. Must be one of: " . implode(', ', $validTypes));
        }
        $this->workstation_type = $workstation_type;
    }

    public function setCapacity($capacity) {
        $this->capacity = $capacity;
    }

    public function setAreaSqft($area_sqft) {
        $this->area_sqft = $area_sqft;
    }

    public function setHasComputer($has_computer) {
        $this->has_computer = (bool)$has_computer;
    }

    public function setHasPhone($has_phone) {
        $this->has_phone = (bool)$has_phone;
    }

    public function setHasPrinter($has_printer) {
        $this->has_printer = (bool)$has_printer;
    }

    public function setAmenities($amenities) {
        $this->amenities = $amenities;
    }

    public function setIsOccupied($is_occupied) {
        $this->is_occupied = (bool)$is_occupied;
    }

    public function setAssignedTo($assigned_to) {
        $this->assigned_to = $assigned_to;
    }

    public function setIsActive($is_active) {
        $this->is_active = (bool)$is_active;
    }

    public function setSortOrder($sort_order) {
        $this->sort_order = $sort_order;
    }

    public function setCreatedBy($created_by) {
        $this->created_by = $created_by;
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }

    public function setUpdatedBy($updated_by) {
        $this->updated_by = $updated_by;
    }

    public function setUpdatedAt($updated_at) {
        $this->updated_at = $updated_at;
    }

    public function setDeletedBy($deleted_by) {
        $this->deleted_by = $deleted_by;
    }

    public function setDeletedAt($deleted_at) {
        $this->deleted_at = $deleted_at;
    }

    /**
     * Validate workstation data
     */
    public function validate() {
        $errors = [];

        if (empty($this->building_id)) {
            $errors[] = "Building is required";
        }

        if (empty($this->organization_id)) {
            $errors[] = "Organization is required";
        }

        if (empty($this->name)) {
            $errors[] = "Workstation name is required";
        }

        if (empty($this->floor)) {
            $errors[] = "Floor is required";
        }

        $validTypes = ['desk', 'cubicle', 'private_office', 'hot_desk', 'meeting_room', 'lab', 'workshop', 'other'];
        if (!empty($this->workstation_type) && !in_array($this->workstation_type, $validTypes)) {
            $errors[] = "Invalid workstation type";
        }

        if (!empty($this->capacity) && $this->capacity < 1) {
            $errors[] = "Capacity must be at least 1";
        }

        return $errors;
    }
}
