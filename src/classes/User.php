<?php

namespace App\Classes;

class User {
    private $id;
    private $username;  // Unique username for login
    private $email;
    private $fullName;
    private $phone;
    private $avatarUrl;
    private $role;
    private $isActive;
    private $createdAt;
    private $updatedAt;

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
        if (isset($data['username'])) $this->username = $data['username'];
        if (isset($data['email'])) $this->email = $data['email'];
        if (isset($data['full_name'])) $this->fullName = $data['full_name'];
        if (isset($data['phone'])) $this->phone = $data['phone'];
        if (isset($data['avatar_url'])) $this->avatarUrl = $data['avatar_url'];
        if (isset($data['role'])) $this->role = $data['role'];
        if (isset($data['is_active'])) $this->isActive = $data['is_active'];
        if (isset($data['created_at'])) $this->createdAt = $data['created_at'];
        if (isset($data['updated_at'])) $this->updatedAt = $data['updated_at'];
    }

    /**
     * Convert object to array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'full_name' => $this->fullName,
            'phone' => $this->phone,
            'avatar_url' => $this->avatarUrl,
            'role' => $this->role,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getFullName() {
        return $this->fullName;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function getAvatarUrl() {
        return $this->avatarUrl;
    }

    public function getRole() {
        return $this->role;
    }

    public function getIsActive() {
        return $this->isActive;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setUsername($username) {
        if (!empty($username)) {
            $username = strtolower(trim($username));
            if (!preg_match('/^[a-z0-9_-]+$/', $username)) {
                throw new \Exception("Username can only contain lowercase letters, numbers, underscores, and hyphens");
            }
            if (strlen($username) < 3 || strlen($username) > 30) {
                throw new \Exception("Username must be between 3 and 30 characters");
            }
        }
        $this->username = $username;
    }

    public function setEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid email format");
        }
        $this->email = $email;
    }

    public function setFullName($fullName) {
        $this->fullName = $fullName;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }

    public function setAvatarUrl($avatarUrl) {
        $this->avatarUrl = $avatarUrl;
    }

    public function setRole($role) {
        $allowedRoles = ['user', 'admin', 'moderator'];
        if (!in_array($role, $allowedRoles)) {
            throw new \Exception("Invalid role");
        }
        $this->role = $role;
    }

    public function setIsActive($isActive) {
        $this->isActive = (bool)$isActive;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Validate user data
     */
    public function validate() {
        $errors = [];

        if (empty($this->username)) {
            $errors[] = "Username is required";
        } elseif (!preg_match('/^[a-z0-9_-]+$/', $this->username)) {
            $errors[] = "Username can only contain lowercase letters, numbers, underscores, and hyphens";
        } elseif (strlen($this->username) < 3 || strlen($this->username) > 30) {
            $errors[] = "Username must be between 3 and 30 characters";
        }

        if (empty($this->email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        if (empty($this->fullName)) {
            $errors[] = "Full name is required";
        }

        return $errors;
    }
}