# V4L - Remaining Updates Required

## ✅ Completed So Far
1. ✅ Branding updated to V4L
2. ✅ Organization entity updated (short_name, legal_structure, subdomain)
3. ✅ User entity updated (username added)
4. ✅ SQLite database schemas updated

---

## 🔧 Update Supabase Schema (database_setup.sql)

Add this to the database_setup.sql file AFTER the users table creation:

```sql
-- Update users table for username support
ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(30) UNIQUE;
CREATE UNIQUE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_phone ON users(phone);

-- Update organizations table
ALTER TABLE organizations
    DROP COLUMN IF EXISTS name CASCADE,
    ADD COLUMN IF NOT EXISTS short_name VARCHAR(255) NOT NULL DEFAULT 'Organization',
    ADD COLUMN IF NOT EXISTS legal_structure VARCHAR(100),
    ADD COLUMN IF NOT EXISTS subdomain VARCHAR(63) UNIQUE;

-- Make subdomain NOT NULL after adding default values
UPDATE organizations SET subdomain = LOWER(REGEXP_REPLACE(COALESCE(short_name, 'org'), '[^a-z0-9]', '-', 'g')) || '-' || SUBSTRING(id::text, 1, 8)
WHERE subdomain IS NULL;

ALTER TABLE organizations ALTER COLUMN subdomain SET NOT NULL;

-- Create unique index for subdomain
CREATE UNIQUE INDEX IF NOT EXISTS idx_organizations_subdomain ON organizations(subdomain);
DROP INDEX IF EXISTS idx_organizations_name;
CREATE INDEX IF NOT EXISTS idx_organizations_short_name ON organizations(short_name);
```

---

## 🔧 Update OrganizationRepository.php

### 1. Update create() method:

```php
public function create(Organization $org, $userId) {
    // Check subdomain uniqueness
    if ($this->subdomainExists($org->getSubdomain())) {
        throw new \Exception("Subdomain '{$org->getSubdomain()}' is already taken. Please choose another.");
    }

    $data = [
        'short_name' => $org->getShortName(),
        'legal_structure' => $org->getLegalStructure(),
        'subdomain' => $org->getSubdomain(),
        'description' => $org->getDescription(),
        'email' => $org->getEmail(),
        'phone' => $org->getPhone(),
        'address' => $org->getAddress(),
        'website' => $org->getWebsite(),
        'logo_url' => $org->getLogoUrl(),
        'is_active' => $org->getIsActive() ?? true,
        'created_by' => $userId,
        'created_at' => date('Y-m-d H:i:s')
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
        $org->hydrate($response['data'][0]);
        return $org;
    }

    throw new \Exception("Failed to create organization: " . json_encode($response['data'] ?? 'Unknown error'));
}
```

### 2. Update update() method:

```php
public function update(Organization $org, $userId) {
    if (!$org->getId()) {
        throw new \Exception("Organization ID is required for update");
    }

    // Verify ownership
    $existing = $this->findById($org->getId(), $userId);
    if (!$existing) {
        throw new \Exception("Organization not found or access denied");
    }

    // Check subdomain uniqueness (excluding current org)
    if ($this->subdomainExists($org->getSubdomain(), $org->getId())) {
        throw new \Exception("Subdomain '{$org->getSubdomain()}' is already taken. Please choose another.");
    }

    $data = [
        'short_name' => $org->getShortName(),
        'legal_structure' => $org->getLegalStructure(),
        'subdomain' => $org->getSubdomain(),
        'description' => $org->getDescription(),
        'email' => $org->getEmail(),
        'phone' => $org->getPhone(),
        'address' => $org->getAddress(),
        'website' => $org->getWebsite(),
        'logo_url' => $org->getLogoUrl(),
        'is_active' => $org->getIsActive(),
        'updated_by' => $userId,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Remove null values
    $data = array_filter($data, function($value) {
        return $value !== null;
    });

    if ($this->db->getDriver() === 'sqlite') {
        $pdo = $this->db->getPdo();

        $setClauses = [];
        $params = [];
        foreach ($data as $key => $value) {
            $setClauses[] = "$key = ?";
            $params[] = $value;
        }

        $params[] = $org->getId();
        $params[] = $userId;

        $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setClauses) . "
                WHERE id = ? AND created_by = ? AND deleted_at IS NULL";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $response = $this->findById($org->getId(), $userId);
        if ($response) {
            return $response;
        }
    } else {
        $response = $this->db->request('PATCH', $this->tableName . '?id=eq.' . $org->getId() . '&created_by=eq.' . $userId . '&deleted_at=is.null', $data);

        if ($response['success'] && !empty($response['data'])) {
            $org->hydrate($response['data'][0]);
            return $org;
        }
    }

    throw new \Exception("Failed to update organization");
}
```

### 3. Add subdomainExists() method:

```php
/**
 * Check if subdomain already exists
 */
public function subdomainExists($subdomain, $excludeId = null) {
    if ($this->db->getDriver() === 'sqlite') {
        $pdo = $this->db->getPdo();
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName}
                WHERE subdomain = ? AND deleted_at IS NULL";
        $params = [$subdomain];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    } else {
        $endpoint = $this->tableName . '?subdomain=eq.' . urlencode($subdomain) . '&deleted_at=is.null';
        if ($excludeId) {
            $endpoint .= '&id=neq.' . $excludeId;
        }
        $response = $this->db->request('GET', $endpoint);
        return !empty($response['data']);
    }
}
```

### 4. Update searchByUser() method to search short_name:

```php
public function searchByUser($query, $userId, $limit = 20) {
    if ($this->db->getDriver() === 'sqlite') {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM {$this->tableName}
                               WHERE created_by = ? AND deleted_at IS NULL
                               AND (short_name LIKE ? OR description LIKE ? OR subdomain LIKE ?)
                               LIMIT ?");
        $stmt->execute([$userId, "%$query%", "%$query%", "%$query%", $limit]);
        $data = $stmt->fetchAll();

        $organizations = [];
        foreach ($data as $orgData) {
            $organizations[] = new Organization($orgData);
        }
        return $organizations;
    } else {
        $response = $this->db->request('GET', $this->tableName . '?created_by=eq.' . $userId . '&deleted_at=is.null&or=(short_name.ilike.*' . urlencode($query) . '*,description.ilike.*' . urlencode($query) . '*,subdomain.ilike.*' . urlencode($query) . '*)&limit=' . $limit);

        if ($response['success']) {
            $organizations = [];
            foreach ($response['data'] as $orgData) {
                $organizations[] = new Organization($orgData);
            }
            return $organizations;
        }
    }

    return [];
}
```

---

## 🔧 Update Auth.php for Username Support

### Update register() method:

```php
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
    // ... rest of registration logic including username
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
```

### Update login() method to support username/email/phone:

```php
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
        $userRepo = new \App\Classes\UserRepository();

        // Check if identifier is email format
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $email = $identifier;
        } else {
            // Try to find user by username or phone
            $user = $userRepo->findByUsername($identifier);
            if (!$user) {
                $user = $userRepo->findByPhone($identifier);
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
        $user = $userRepo->findByEmail($email);
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
```

---

## 🔧 Update UserRepository.php

### Add findByUsername() method:

```php
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
```

### Add findByPhone() method:

```php
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
```

### Update create() method:

```php
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

    throw new \Exception("Failed to create user: " . json_encode($response['data']));
}
```

---

**See V4L_FORMS_UPDATE.md for form updates**
