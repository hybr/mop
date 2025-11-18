<?php

namespace App\Classes;

use App\Config\Database;

class Auth {
    private $db;
    private $userRepository;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->userRepository = new UserRepository();

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Register a new user with Supabase Auth
     */
    public function register($username, $email, $password, $fullName, $phone = null) {
        // Validate input
        if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
            throw new \Exception("Username, email, password, and full name are required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid email format");
        }

        if (strlen($password) < 6) {
            throw new \Exception("Password must be at least 6 characters long");
        }

        // Validate username
        $username = strtolower(trim($username));
        if (!preg_match('/^[a-z0-9_-]+$/', $username)) {
            throw new \Exception("Username can only contain lowercase letters, numbers, underscores, and hyphens");
        }
        if (strlen($username) < 3 || strlen($username) > 30) {
            throw new \Exception("Username must be between 3 and 30 characters");
        }

        if ($this->db->getDriver() === 'sqlite') {
            // Check if username exists
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()['count'] > 0) {
                throw new \Exception("Username already taken");
            }

            // Check if email exists
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()['count'] > 0) {
                throw new \Exception("Email already registered");
            }
        }

        // Register with Supabase Auth or SQLite
        $authData = [
            'email' => $email,
            'password' => $password,
            'data' => [
                'username' => $username,
                'full_name' => $fullName,
                'phone' => $phone
            ]
        ];

        $response = $this->db->authRequest('POST', 'signup', $authData);

        if (!$response['success']) {
            $errorMsg = $response['data']['msg'] ?? $response['data']['error_description'] ?? 'Registration failed';
            throw new \Exception($errorMsg);
        }

        // Create user record in users table
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setFullName($fullName);
        if ($phone) {
            $user->setPhone($phone);
        }
        $user->setRole('user');
        $user->setIsActive(true);

        try {
            $this->userRepository->create($user);
        } catch (\Exception $e) {
            // User might already exist in table, that's ok
        }

        return [
            'success' => true,
            'message' => 'Registration successful! Please check your email to verify your account.',
            'user' => $response['data']['user'] ?? null
        ];
    }

    /**
     * Login user with username/email/phone
     */
    public function login($identifier, $password) {
        if (empty($identifier) || empty($password)) {
            throw new \Exception("Username/email/phone and password are required");
        }

        if ($this->db->getDriver() === 'sqlite') {
            $pdo = $this->db->getPdo();
            // Try to find user by username, email, or phone
            $stmt = $pdo->prepare("SELECT * FROM users
                                   WHERE (username = ? OR email = ? OR phone = ?)");
            $stmt->execute([$identifier, $identifier, $identifier]);
            $userData = $stmt->fetch();

            if (!$userData) {
                throw new \Exception("Invalid credentials");
            }

            if (!password_verify($password, $userData['password_hash'])) {
                throw new \Exception("Invalid credentials");
            }

            // Store session data
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['user_email'] = $userData['email'];
            $_SESSION['user_username'] = $userData['username'];
            $_SESSION['logged_in'] = true;
            $_SESSION['user_data'] = $userData;

            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $userData
            ];
        } else {
            // For Supabase, we need to use email for auth
            // First, try to find the user's email if they provided username/phone

            // Check if identifier is email format
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $email = $identifier;
            } else {
                // Try to find user by username or phone
                $user = $this->userRepository->findByUsername($identifier);
                if (!$user) {
                    $user = $this->userRepository->findByPhone($identifier);
                }

                if (!$user) {
                    throw new \Exception("Invalid credentials");
                }

                $email = $user->getEmail();
            }

            // Use email to login with Supabase
            $authData = [
                'email' => $email,
                'password' => $password
            ];

            $response = $this->db->authRequest('POST', 'token?grant_type=password', $authData);

            if (!$response['success']) {
                throw new \Exception("Invalid credentials");
            }

            // Store session data
            $_SESSION['user_id'] = $response['data']['user']['id'];
            $_SESSION['user_email'] = $response['data']['user']['email'];
            $_SESSION['access_token'] = $response['data']['access_token'];
            $_SESSION['refresh_token'] = $response['data']['refresh_token'];
            $_SESSION['logged_in'] = true;

            // Get user details from database
            $user = $this->userRepository->findByEmail($email);
            if ($user) {
                $_SESSION['user_data'] = $user->toArray();
                $_SESSION['user_username'] = $user->getUsername();
            }

            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $response['data']['user']
            ];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        // Clear session
        session_unset();
        session_destroy();

        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Get current logged in user
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        if (isset($_SESSION['user_data'])) {
            return new User($_SESSION['user_data']);
        }

        // Fetch from database
        if (isset($_SESSION['user_email'])) {
            $user = $this->userRepository->findByEmail($_SESSION['user_email']);
            if ($user) {
                $_SESSION['user_data'] = $user->toArray();
            }
            return $user;
        }

        return null;
    }

    /**
     * Require authentication - redirect if not logged in
     */
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user->getRole() === $role;
    }

    /**
     * Require specific role
     */
    public function requireRole($role) {
        $this->requireAuth();
        if (!$this->hasRole($role)) {
            header('HTTP/1.1 403 Forbidden');
            die('Access denied');
        }
    }

    /**
     * Reset password request
     */
    public function resetPasswordRequest($email) {
        $response = $this->db->authRequest('POST', 'recover', ['email' => $email]);

        if (!$response['success']) {
            throw new \Exception("Failed to send reset email");
        }

        return [
            'success' => true,
            'message' => 'Password reset email sent'
        ];
    }

    /**
     * Update password
     */
    public function updatePassword($newPassword) {
        if (!$this->isLoggedIn()) {
            throw new \Exception("User not logged in");
        }

        if (strlen($newPassword) < 6) {
            throw new \Exception("Password must be at least 6 characters long");
        }

        $headers = [
            'Authorization: Bearer ' . $_SESSION['access_token']
        ];

        $response = $this->db->authRequest('PUT', 'user', ['password' => $newPassword]);

        if (!$response['success']) {
            throw new \Exception("Failed to update password");
        }

        return [
            'success' => true,
            'message' => 'Password updated successfully'
        ];
    }

    /**
     * Set current organization for session
     */
    public function setCurrentOrganization($organizationId) {
        if (!$this->isLoggedIn()) {
            throw new \Exception("User not logged in");
        }

        $user = $this->getCurrentUser();

        // Verify user has access to this organization
        $orgRepo = new OrganizationRepository();
        $org = $orgRepo->findById($organizationId, $user->getId());

        if (!$org) {
            throw new \Exception("Organization not found or access denied");
        }

        $_SESSION['current_organization_id'] = $organizationId;
        $_SESSION['current_organization'] = $org->toArray();

        return $org;
    }

    /**
     * Get current selected organization
     */
    public function getCurrentOrganization() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        if (isset($_SESSION['current_organization'])) {
            return new Organization($_SESSION['current_organization']);
        }

        return null;
    }

    /**
     * Get current organization ID
     */
    public function getCurrentOrganizationId() {
        return $_SESSION['current_organization_id'] ?? null;
    }

    /**
     * Clear current organization
     */
    public function clearCurrentOrganization() {
        unset($_SESSION['current_organization_id']);
        unset($_SESSION['current_organization']);
    }

    /**
     * Auto-select organization based on user's organizations
     * Returns true if organization was selected, false otherwise
     */
    public function autoSelectOrganization() {
        if (!$this->isLoggedIn()) {
            return false;
        }

        // If already selected, keep it
        if ($this->getCurrentOrganizationId()) {
            return true;
        }

        $user = $this->getCurrentUser();
        $orgRepo = new OrganizationRepository();

        // Try subdomain detection first
        $subdomain = $this->detectSubdomain();
        if ($subdomain) {
            $org = $orgRepo->findBySubdomain($subdomain, $user->getId());
            if ($org) {
                $this->setCurrentOrganization($org->getId());
                return true;
            }
        }

        // Get user's organizations
        $organizations = $orgRepo->findAllByUser($user->getId());

        // If only one organization, select it automatically
        if (count($organizations) === 1) {
            $this->setCurrentOrganization($organizations[0]->getId());
            return true;
        }

        return false;
    }

    /**
     * Detect subdomain from current request
     * Returns subdomain or null if not applicable
     */
    private function detectSubdomain() {
        $host = $_SERVER['HTTP_HOST'] ?? '';

        // Remove port if present
        $host = explode(':', $host)[0];

        // Split by dots
        $parts = explode('.', $host);

        // If we have at least 3 parts (subdomain.domain.tld) and it's not 'www'
        if (count($parts) >= 3 && $parts[0] !== 'www') {
            return $parts[0];
        }

        return null;
    }

    /**
     * Require organization to be selected
     * Redirects to organization selection page if not selected
     */
    public function requireOrganization() {
        $this->requireAuth();

        // Try auto-select first
        if (!$this->autoSelectOrganization()) {
            // Redirect to organization selection
            $currentUrl = $_SERVER['REQUEST_URI'];
            header('Location: /select-organization.php?redirect=' . urlencode($currentUrl));
            exit;
        }
    }
}
