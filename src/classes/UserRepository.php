<?php

namespace App\Classes;

use App\Config\Database;

class UserRepository {
    private $db;
    private $tableName = 'users';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new user
     */
    public function create(User $user) {
        $data = [
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'full_name' => $user->getFullName(),
            'phone' => $user->getPhone(),
            'avatar_url' => $user->getAvatarUrl(),
            'role' => $user->getRole() ?? 'user',
            'is_active' => $user->getIsActive() ?? true
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
            $user->hydrate($response['data'][0]);
            return $user;
        }

        throw new \Exception("Failed to create user: " . json_encode($response['data'] ?? 'Unknown error'));
    }

    /**
     * Find user by ID
     */
    public function findById($id) {
        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'SELECT', ['id' => $id]);
        } else {
            $response = $this->db->request('GET', $this->tableName . '?id=eq.' . $id);
        }

        if ($response['success'] && !empty($response['data'])) {
            return new User($response['data'][0]);
        }

        return null;
    }

    /**
     * Find user by email
     */
    public function findByEmail($email) {
        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'SELECT', ['email' => $email]);
        } else {
            $response = $this->db->request('GET', $this->tableName . '?email=eq.' . urlencode($email));
        }

        if ($response['success'] && !empty($response['data'])) {
            return new User($response['data'][0]);
        }

        return null;
    }

    /**
     * Find user by username
     */
    public function findByUsername($username) {
        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'SELECT', ['username' => $username]);
        } else {
            $response = $this->db->request('GET', $this->tableName . '?username=eq.' . urlencode($username));
        }

        if ($response['success'] && !empty($response['data'])) {
            return new User($response['data'][0]);
        }

        return null;
    }

    /**
     * Find user by phone
     */
    public function findByPhone($phone) {
        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'SELECT', ['phone' => $phone]);
        } else {
            $response = $this->db->request('GET', $this->tableName . '?phone=eq.' . urlencode($phone));
        }

        if ($response['success'] && !empty($response['data'])) {
            return new User($response['data'][0]);
        }

        return null;
    }

    /**
     * Get all users
     */
    public function findAll($limit = 100, $offset = 0) {
        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'SELECT', [
                '_limit' => $limit,
                '_offset' => $offset,
                '_order' => 'created_at DESC'
            ]);
        } else {
            $response = $this->db->request('GET', $this->tableName . '?limit=' . $limit . '&offset=' . $offset . '&order=created_at.desc');
        }

        if ($response['success']) {
            $users = [];
            foreach ($response['data'] as $userData) {
                $users[] = new User($userData);
            }
            return $users;
        }

        return [];
    }

    /**
     * Update user
     */
    public function update(User $user) {
        if (!$user->getId()) {
            throw new \Exception("User ID is required for update");
        }

        $data = [
            'full_name' => $user->getFullName(),
            'phone' => $user->getPhone(),
            'avatar_url' => $user->getAvatarUrl(),
            'role' => $user->getRole(),
            'is_active' => $user->getIsActive()
        ];

        // Remove null values
        $data = array_filter($data, function($value) {
            return $value !== null;
        });

        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'UPDATE', ['id' => $user->getId()], $data);
        } else {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $user->getId(), $data);
        }

        if ($response['success'] && !empty($response['data'])) {
            $user->hydrate($response['data'][0]);
            return $user;
        }

        throw new \Exception("Failed to update user");
    }

    /**
     * Delete user
     */
    public function delete($id) {
        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'DELETE', ['id' => $id]);
        } else {
            $response = $this->db->request('DELETE', $this->tableName . '?id=eq.' . $id);
        }
        return $response['success'];
    }

    /**
     * Search users by name or email
     */
    public function search($query, $limit = 20) {
        if ($this->db->getDriver() === 'sqlite') {
            // SQLite search using LIKE
            $pdo = $this->db->getPdo();
            $sql = "SELECT * FROM {$this->tableName}
                    WHERE full_name LIKE ? OR email LIKE ?
                    LIMIT ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$query%", "%$query%", $limit]);
            $data = $stmt->fetchAll();

            $users = [];
            foreach ($data as $userData) {
                $users[] = new User($userData);
            }
            return $users;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?or=(full_name.ilike.*' . urlencode($query) . '*,email.ilike.*' . urlencode($query) . '*)&limit=' . $limit);

            if ($response['success']) {
                $users = [];
                foreach ($response['data'] as $userData) {
                    $users[] = new User($userData);
                }
                return $users;
            }
        }

        return [];
    }

    /**
     * Get users by role
     */
    public function findByRole($role, $limit = 100) {
        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'SELECT', [
                'role' => $role,
                '_limit' => $limit
            ]);
        } else {
            $response = $this->db->request('GET', $this->tableName . '?role=eq.' . urlencode($role) . '&limit=' . $limit);
        }

        if ($response['success']) {
            $users = [];
            foreach ($response['data'] as $userData) {
                $users[] = new User($userData);
            }
            return $users;
        }

        return [];
    }

    /**
     * Count total users
     */
    public function count() {
        if ($this->db->getDriver() === 'sqlite') {
            $response = $this->db->query($this->tableName, 'COUNT');
            return $response['count'] ?? 0;
        } else {
            $response = $this->db->request('GET', $this->tableName . '?select=count', null, ['Prefer: count=exact']);

            if ($response['success']) {
                return $response['data'][0]['count'] ?? 0;
            }
        }

        return 0;
    }
}
