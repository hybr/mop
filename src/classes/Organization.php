<?php

namespace App\Classes;

class Organization {
    private $id;
    private $short_name;        // Short name (e.g., "Acme Corp")
    private $legal_structure;   // Legal structure (e.g., "Private Limited", "LLC")
    private $subdomain;         // Unique subdomain for v4l.app
    private $description;
    private $email;
    private $phone;
    private $address;
    private $website;
    private $logo_url;
    private $is_active;

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
        if (isset($data['short_name'])) $this->short_name = $data['short_name'];
        if (isset($data['legal_structure'])) $this->legal_structure = $data['legal_structure'];
        if (isset($data['subdomain'])) $this->subdomain = $data['subdomain'];
        if (isset($data['description'])) $this->description = $data['description'];
        if (isset($data['email'])) $this->email = $data['email'];
        if (isset($data['phone'])) $this->phone = $data['phone'];
        if (isset($data['address'])) $this->address = $data['address'];
        if (isset($data['website'])) $this->website = $data['website'];
        if (isset($data['logo_url'])) $this->logo_url = $data['logo_url'];
        if (isset($data['is_active'])) $this->is_active = $data['is_active'];
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
            'short_name' => $this->short_name,
            'legal_structure' => $this->legal_structure,
            'subdomain' => $this->subdomain,
            'description' => $this->description,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'website' => $this->website,
            'logo_url' => $this->logo_url,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at,
            'deleted_by' => $this->deleted_by,
            'deleted_at' => $this->deleted_at
        ];
    }

    /**
     * Get full organization name (short name + legal structure)
     */
    public function getFullName() {
        if ($this->legal_structure) {
            return $this->short_name . ' ' . $this->legal_structure;
        }
        return $this->short_name;
    }

    /**
     * Get organization URL (subdomain.v4l.app)
     */
    public function getUrl() {
        if ($this->subdomain) {
            return 'https://' . $this->subdomain . '.v4l.app';
        }
        return null;
    }

    /**
     * Get label for foreign key display
     * Returns the label fields when this entity is used as a foreign key
     */
    public function getLabel() {
        return $this->getFullName();
    }

    /**
     * Get public fields (visible to all users including guests)
     * Per permissions.md: All users can view public fields of any organization
     */
    public function getPublicFields() {
        return [
            'id' => $this->id,
            'short_name' => $this->short_name,
            'legal_structure' => $this->legal_structure,
            'subdomain' => $this->subdomain,
            'description' => $this->description,
            'website' => $this->website,
            'logo_url' => $this->logo_url,
            'is_active' => $this->is_active
        ];
    }

    /**
     * Check if a field is public (visible to all)
     */
    public static function isPublicField($fieldName) {
        $publicFields = ['id', 'short_name', 'legal_structure', 'subdomain', 'description', 'website', 'logo_url', 'is_active'];
        return in_array($fieldName, $publicFields);
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getShortName() {
        return $this->short_name;
    }

    public function getLegalStructure() {
        return $this->legal_structure;
    }

    public function getSubdomain() {
        return $this->subdomain;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getWebsite() {
        return $this->website;
    }

    public function getLogoUrl() {
        return $this->logo_url;
    }

    public function getIsActive() {
        return $this->is_active;
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

    public function setShortName($short_name) {
        $this->short_name = $short_name;
    }

    public function setLegalStructure($legal_structure) {
        $this->legal_structure = $legal_structure;
    }

    public function setSubdomain($subdomain) {
        // Validate subdomain format
        if (!empty($subdomain)) {
            $subdomain = strtolower(trim($subdomain));
            if (!preg_match('/^[a-z0-9-]+$/', $subdomain)) {
                throw new \Exception("Subdomain can only contain lowercase letters, numbers, and hyphens");
            }
            if (strlen($subdomain) < 3 || strlen($subdomain) > 63) {
                throw new \Exception("Subdomain must be between 3 and 63 characters");
            }
        }
        $this->subdomain = $subdomain;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setEmail($email) {
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid email format");
        }
        $this->email = $email;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }

    public function setAddress($address) {
        $this->address = $address;
    }

    public function setWebsite($website) {
        if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
            throw new \Exception("Invalid website URL");
        }
        $this->website = $website;
    }

    public function setLogoUrl($logo_url) {
        $this->logo_url = $logo_url;
    }

    public function setIsActive($is_active) {
        $this->is_active = (bool)$is_active;
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
     * Validate organization data
     */
    public function validate() {
        $errors = [];

        if (empty($this->short_name)) {
            $errors[] = "Organization short name is required";
        }

        if (empty($this->subdomain)) {
            $errors[] = "Subdomain is required";
        } elseif (!preg_match('/^[a-z0-9-]+$/', $this->subdomain)) {
            $errors[] = "Subdomain can only contain lowercase letters, numbers, and hyphens";
        } elseif (strlen($this->subdomain) < 3 || strlen($this->subdomain) > 63) {
            $errors[] = "Subdomain must be between 3 and 63 characters";
        }

        if (!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        if (!empty($this->website) && !filter_var($this->website, FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid website URL";
        }

        return $errors;
    }
}
