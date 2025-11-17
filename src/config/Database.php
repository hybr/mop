<?php

namespace App\Config;

class Database {
    private static $instance = null;
    private $driver;
    private $pdo = null;
    private $supabaseUrl;
    private $supabaseKey;

    private function __construct() {
        Env::load();
        $this->driver = Env::getDbDriver();

        if ($this->driver === 'sqlite') {
            $this->initSQLite();
        } else {
            $this->initSupabase();
        }
    }

    /**
     * Initialize SQLite connection
     */
    private function initSQLite() {
        $dbPath = __DIR__ . '/../../' . Env::get('SQLITE_DB_PATH', 'database/app.db');
        $dbDir = dirname($dbPath);

        // Create database directory if it doesn't exist
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        try {
            $this->pdo = new \PDO('sqlite:' . $dbPath);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            // Create tables if they don't exist
            $this->createTables();
        } catch (\PDOException $e) {
            throw new \Exception("SQLite Connection Error: " . $e->getMessage());
        }
    }

    /**
     * Initialize Supabase configuration
     */
    private function initSupabase() {
        $this->supabaseUrl = Env::get('SUPABASE_URL');
        $this->supabaseKey = Env::get('SUPABASE_ANON_KEY');

        if (empty($this->supabaseUrl) || empty($this->supabaseKey)) {
            throw new \Exception("Supabase credentials not configured");
        }
    }

    /**
     * Create database tables for SQLite
     */
    private function createTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id TEXT PRIMARY KEY,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT,
            full_name TEXT NOT NULL,
            phone TEXT,
            avatar_url TEXT,
            role TEXT DEFAULT 'user',
            is_active INTEGER DEFAULT 1,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE UNIQUE INDEX IF NOT EXISTS idx_users_username ON users(username);
        CREATE UNIQUE INDEX IF NOT EXISTS idx_users_email ON users(email);
        CREATE INDEX IF NOT EXISTS idx_users_phone ON users(phone);
        CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
        CREATE INDEX IF NOT EXISTS idx_users_is_active ON users(is_active);

        CREATE TABLE IF NOT EXISTS auth_sessions (
            id TEXT PRIMARY KEY,
            user_id TEXT NOT NULL,
            email TEXT NOT NULL,
            access_token TEXT NOT NULL,
            refresh_token TEXT,
            expires_at TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS organizations (
            id TEXT PRIMARY KEY,
            short_name TEXT NOT NULL,
            legal_structure TEXT,
            subdomain TEXT UNIQUE NOT NULL,
            description TEXT,
            email TEXT,
            phone TEXT,
            address TEXT,
            website TEXT,
            logo_url TEXT,
            is_active INTEGER DEFAULT 1,
            created_by TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_by TEXT,
            updated_at TEXT,
            deleted_by TEXT,
            deleted_at TEXT,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE UNIQUE INDEX IF NOT EXISTS idx_organizations_subdomain ON organizations(subdomain);
        CREATE INDEX IF NOT EXISTS idx_organizations_created_by ON organizations(created_by);
        CREATE INDEX IF NOT EXISTS idx_organizations_deleted_at ON organizations(deleted_at);
        CREATE INDEX IF NOT EXISTS idx_organizations_short_name ON organizations(short_name);

        CREATE TABLE IF NOT EXISTS organization_departments (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            code TEXT UNIQUE NOT NULL,
            description TEXT,
            parent_department_id TEXT,
            organization_id TEXT,
            is_active INTEGER DEFAULT 1,
            sort_order INTEGER DEFAULT 0,
            created_by TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_by TEXT,
            updated_at TEXT,
            deleted_by TEXT,
            deleted_at TEXT,
            FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
            FOREIGN KEY (parent_department_id) REFERENCES organization_departments(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        );

        CREATE UNIQUE INDEX IF NOT EXISTS idx_org_departments_code ON organization_departments(code);
        CREATE INDEX IF NOT EXISTS idx_org_departments_organization_id ON organization_departments(organization_id);
        CREATE INDEX IF NOT EXISTS idx_org_departments_parent_id ON organization_departments(parent_department_id);
        CREATE INDEX IF NOT EXISTS idx_org_departments_deleted_at ON organization_departments(deleted_at);
        CREATE INDEX IF NOT EXISTS idx_org_departments_name ON organization_departments(name);
        CREATE INDEX IF NOT EXISTS idx_org_departments_created_by ON organization_departments(created_by);
        ";

        $this->pdo->exec($sql);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get database driver
     */
    public function getDriver() {
        return $this->driver;
    }

    /**
     * Get PDO connection (SQLite only)
     */
    public function getPdo() {
        if ($this->driver !== 'sqlite') {
            throw new \Exception("PDO is only available for SQLite driver");
        }
        return $this->pdo;
    }

    /**
     * Get Supabase URL
     */
    public function getSupabaseUrl() {
        return $this->supabaseUrl;
    }

    /**
     * Get Supabase API Key
     */
    public function getSupabaseKey() {
        return $this->supabaseKey;
    }

    /**
     * Execute a query (SQLite) or API request (Supabase)
     */
    public function query($table, $method, $conditions = [], $data = null) {
        if ($this->driver === 'sqlite') {
            return $this->querySQLite($table, $method, $conditions, $data);
        } else {
            return $this->querySupabase($table, $method, $conditions, $data);
        }
    }

    /**
     * SQLite query execution
     */
    private function querySQLite($table, $method, $conditions = [], $data = null) {
        switch (strtoupper($method)) {
            case 'SELECT':
                return $this->selectSQLite($table, $conditions);
            case 'INSERT':
                return $this->insertSQLite($table, $data);
            case 'UPDATE':
                return $this->updateSQLite($table, $conditions, $data);
            case 'DELETE':
                return $this->deleteSQLite($table, $conditions);
            case 'COUNT':
                return $this->countSQLite($table, $conditions);
            default:
                throw new \Exception("Unsupported query method: $method");
        }
    }

    private function selectSQLite($table, $conditions = []) {
        $sql = "SELECT * FROM $table";
        $params = [];

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                if (is_array($value)) {
                    // Handle operators like ['>=', 5]
                    $whereClauses[] = "$key {$value[0]} ?";
                    $params[] = $value[1];
                } else {
                    $whereClauses[] = "$key = ?";
                    $params[] = $value;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        if (isset($conditions['_order'])) {
            $sql .= " ORDER BY " . $conditions['_order'];
        }

        if (isset($conditions['_limit'])) {
            $sql .= " LIMIT " . (int)$conditions['_limit'];
        }

        if (isset($conditions['_offset'])) {
            $sql .= " OFFSET " . (int)$conditions['_offset'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'success' => true,
            'data' => $stmt->fetchAll()
        ];
    }

    private function insertSQLite($table, $data) {
        // Generate UUID for id if not provided
        if (!isset($data['id'])) {
            $data['id'] = $this->generateUUID();
        }

        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = "INSERT INTO $table (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));

        // Return the inserted row
        return $this->selectSQLite($table, ['id' => $data['id']]);
    }

    private function updateSQLite($table, $conditions, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $setClauses = [];
        $params = [];

        foreach ($data as $key => $value) {
            $setClauses[] = "$key = ?";
            $params[] = $value;
        }

        $sql = "UPDATE $table SET " . implode(', ', $setClauses);

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                $whereClauses[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        // Return the updated row
        return $this->selectSQLite($table, $conditions);
    }

    private function deleteSQLite($table, $conditions) {
        $sql = "DELETE FROM $table";
        $params = [];

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                $whereClauses[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($params);

        return [
            'success' => $result,
            'affected_rows' => $stmt->rowCount()
        ];
    }

    private function countSQLite($table, $conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM $table";
        $params = [];

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                $whereClauses[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch();
        return [
            'success' => true,
            'count' => $result['count']
        ];
    }

    /**
     * Supabase query execution (existing implementation)
     */
    private function querySupabase($table, $method, $conditions = [], $data = null) {
        // Build endpoint URL based on conditions
        $endpoint = $table;

        if (!empty($conditions)) {
            $query = [];
            foreach ($conditions as $key => $value) {
                if (strpos($key, '_') === 0) {
                    // Skip internal parameters
                    continue;
                }
                $query[] = "$key=eq.$value";
            }
            if (!empty($query)) {
                $endpoint .= '?' . implode('&', $query);
            }
        }

        return $this->request($method, $endpoint, $data);
    }

    /**
     * Make a request to Supabase REST API
     */
    public function request($method, $endpoint, $data = null, $headers = []) {
        if ($this->driver === 'sqlite') {
            throw new \Exception("Direct API requests are not supported in SQLite mode");
        }

        $url = $this->supabaseUrl . '/rest/v1/' . $endpoint;

        $defaultHeaders = [
            'apikey: ' . $this->supabaseKey,
            'Authorization: Bearer ' . $this->supabaseKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        $allHeaders = array_merge($defaultHeaders, $headers);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data !== null && in_array($method, ['POST', 'PATCH', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL Error: " . $error);
        }

        $result = json_decode($response, true);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'code' => $httpCode,
            'data' => $result
        ];
    }

    /**
     * Supabase Auth API request
     */
    public function authRequest($method, $endpoint, $data = null) {
        if ($this->driver === 'sqlite') {
            // Handle auth internally for SQLite
            return $this->authRequestSQLite($method, $endpoint, $data);
        }

        $url = $this->supabaseUrl . '/auth/v1/' . $endpoint;

        $headers = [
            'apikey: ' . $this->supabaseKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL Error: " . $error);
        }

        $result = json_decode($response, true);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'code' => $httpCode,
            'data' => $result
        ];
    }

    /**
     * Handle authentication for SQLite
     */
    private function authRequestSQLite($method, $endpoint, $data) {
        if ($endpoint === 'signup') {
            return $this->signupSQLite($data);
        } elseif (strpos($endpoint, 'token') !== false) {
            return $this->loginSQLite($data);
        }

        return [
            'success' => false,
            'code' => 400,
            'data' => ['error' => 'Unsupported auth operation']
        ];
    }

    private function signupSQLite($data) {
        $email = $data['email'];
        $password = $data['password'];

        // Check if user exists
        $existing = $this->selectSQLite('users', ['email' => $email]);
        if (!empty($existing['data'])) {
            return [
                'success' => false,
                'code' => 400,
                'data' => ['error_description' => 'User already registered']
            ];
        }

        $userId = $this->generateUUID();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $userData = [
            'id' => $userId,
            'email' => $email,
            'password_hash' => $passwordHash
        ];

        // Add metadata if provided
        if (isset($data['data'])) {
            if (isset($data['data']['username'])) {
                $userData['username'] = $data['data']['username'];
            }
            if (isset($data['data']['full_name'])) {
                $userData['full_name'] = $data['data']['full_name'];
            }
            if (isset($data['data']['phone'])) {
                $userData['phone'] = $data['data']['phone'];
            }
        }

        $this->insertSQLite('users', $userData);

        return [
            'success' => true,
            'code' => 200,
            'data' => [
                'user' => [
                    'id' => $userId,
                    'email' => $email
                ]
            ]
        ];
    }

    private function loginSQLite($data) {
        $email = $data['email'];
        $password = $data['password'];

        $result = $this->selectSQLite('users', ['email' => $email]);

        if (empty($result['data'])) {
            return [
                'success' => false,
                'code' => 400,
                'data' => ['error' => 'Invalid login credentials']
            ];
        }

        $user = $result['data'][0];

        if (!password_verify($password, $user['password_hash'])) {
            return [
                'success' => false,
                'code' => 400,
                'data' => ['error' => 'Invalid login credentials']
            ];
        }

        // Generate tokens
        $accessToken = bin2hex(random_bytes(32));
        $refreshToken = bin2hex(random_bytes(32));

        // Store session
        $this->insertSQLite('auth_sessions', [
            'id' => $this->generateUUID(),
            'user_id' => $user['id'],
            'email' => $user['email'],
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600)
        ]);

        return [
            'success' => true,
            'code' => 200,
            'data' => [
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email']
                ],
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken
            ]
        ];
    }

    /**
     * Generate UUID v4
     */
    private function generateUUID() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
