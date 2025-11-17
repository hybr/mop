# V4L (Vocal 4 Local) Implementation Summary

## ✅ Completed Changes

### 1. Branding Updated to V4L
- **Application Name**: V4L - Vocal 4 Local
- **Domain**: v4l.app
- **Tagline**: "Empowering Local Voices"
- **Header Logo**: Changed from "MyApp" to "V4L"

### 2. Organization Entity Enhanced

**New Fields Added:**
- `short_name` - Organization short name (e.g., "Acme Corp")
- `legal_structure` - Legal entity type (e.g., "Private Limited", "LLC", "Inc.")
- `subdomain` - Unique subdomain for v4l.app (e.g., "acmecorp" → acmecorp.v4l.app)

**Helper Methods Added:**
- `getFullName()` - Returns "Short Name + Legal Structure"
- `getUrl()` - Returns "https://subdomain.v4l.app"

**Subdomain Validation:**
- Must be unique
- 3-63 characters
- Lowercase letters, numbers, and hyphens only
- Auto-converted to lowercase

---

## 🚧 Remaining Tasks

### Task 1: Update Database Schemas

#### SQLite Schema (Database.php - createTables method)
```sql
CREATE TABLE IF NOT EXISTS organizations (
    id TEXT PRIMARY KEY,
    short_name TEXT NOT NULL,
    legal_structure TEXT,
    subdomain TEXT UNIQUE NOT NULL,  -- Must be unique!
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
```

#### Supabase Schema (database_setup.sql)
```sql
ALTER TABLE organizations
    DROP COLUMN name,
    ADD COLUMN short_name VARCHAR(255) NOT NULL,
    ADD COLUMN legal_structure VARCHAR(100),
    ADD COLUMN subdomain VARCHAR(63) UNIQUE NOT NULL;

CREATE UNIQUE INDEX idx_organizations_subdomain ON organizations(subdomain);
```

---

### Task 2: Update OrganizationRepository

#### Update create() method:
```php
public function create(Organization $org, $userId) {
    $data = [
        'short_name' => $org->getShortName(),
        'legal_structure' => $org->getLegalStructure(),
        'subdomain' => $org->getSubdomain(),
        'description' => $org->getDescription(),
        // ... rest of fields
    ];

    // Check subdomain uniqueness before creating
    if ($this->subdomainExists($org->getSubdomain())) {
        throw new \Exception("Subdomain already taken");
    }

    // ... rest of method
}
```

#### Add subdomain uniqueness check:
```php
public function subdomainExists($subdomain, $excludeId = null) {
    if ($this->db->getDriver() === 'sqlite') {
        $pdo = $this->db->getPdo();
        $sql = "SELECT COUNT(*) as count FROM organizations
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
        // Supabase query
        $endpoint = 'organizations?subdomain=eq.' . urlencode($subdomain) . '&deleted_at=is.null';
        if ($excludeId) {
            $endpoint .= '&id=neq.' . $excludeId;
        }
        $response = $this->db->request('GET', $endpoint);
        return !empty($response['data']);
    }
}
```

---

### Task 3: Add Username to User Entity

#### Update User.php:
```php
class User {
    private $id;
    private $username;  // NEW: Unique username for login
    private $email;
    private $phone;     // Can be used for password recovery
    private $password_hash;
    private $full_name;
    // ... other fields

    // Add getters/setters
    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        // Validate username
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
}
```

#### Update users table schema:
```sql
-- SQLite
ALTER TABLE users ADD COLUMN username TEXT UNIQUE;
CREATE UNIQUE INDEX idx_users_username ON users(username);

-- Supabase
ALTER TABLE users ADD COLUMN username VARCHAR(30) UNIQUE;
CREATE UNIQUE INDEX idx_users_username ON users(username);
```

---

### Task 4: Update Authentication

#### Update Auth.php login() method:
```php
public function login($identifier, $password) {
    // $identifier can be username, email, or phone

    if ($this->db->getDriver() === 'sqlite') {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM users
                               WHERE (username = ? OR email = ? OR phone = ?)
                               AND deleted_at IS NULL");
        $stmt->execute([$identifier, $identifier, $identifier]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new \Exception("Invalid credentials");
        }

        // Create session...
    } else {
        // Supabase: Try username/email/phone
        // Note: Supabase Auth doesn't support username by default
        // You may need to use email-based auth and map username in your users table
    }
}
```

---

### Task 5: Update Registration Form

#### Update register.php:
```html
<div class="form-group">
    <label for="username" class="form-label">Username *</label>
    <input
        type="text"
        id="username"
        name="username"
        class="form-input"
        required
        pattern="[a-z0-9_-]+"
        minlength="3"
        maxlength="30"
        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
        placeholder="johndoe"
    >
    <small class="text-muted text-small">
        3-30 characters. Letters, numbers, underscores, and hyphens only.
    </small>
</div>

<div class="form-group">
    <label for="email" class="form-label">Email Address *</label>
    <input type="email" id="email" name="email" class="form-input" required>
    <small class="text-muted text-small">Used for password recovery</small>
</div>

<div class="form-group">
    <label for="phone" class="form-label">Phone Number</label>
    <input type="tel" id="phone" name="phone" class="form-input">
    <small class="text-muted text-small">Optional. Can be used for password recovery</small>
</div>
```

---

### Task 6: Update Login Form

#### Update login.php:
```html
<div class="form-group">
    <label for="identifier" class="form-label">Username, Email, or Phone</label>
    <input
        type="text"
        id="identifier"
        name="identifier"
        class="form-input"
        required
        placeholder="username, email@example.com, or phone"
    >
    <small class="text-muted text-small">
        You can login with your username, email, or phone number
    </small>
</div>
```

---

### Task 7: Update Organization Form

#### Update organization-form.php:
```html
<div class="form-group">
    <label for="short_name" class="form-label">Organization Short Name *</label>
    <input
        type="text"
        id="short_name"
        name="short_name"
        class="form-input"
        required
        value="<?php echo $organization ? htmlspecialchars($organization->getShortName()) : ''; ?>"
        placeholder="Acme Corp"
    >
</div>

<div class="form-group">
    <label for="legal_structure" class="form-label">Legal Structure</label>
    <select id="legal_structure" name="legal_structure" class="form-input">
        <option value="">Select...</option>
        <option value="Private Limited">Private Limited (India)</option>
        <option value="LLC">LLC (USA)</option>
        <option value="Inc.">Inc. (USA)</option>
        <option value="Ltd.">Ltd. (UK)</option>
        <option value="GmbH">GmbH (Germany)</option>
        <option value="Proprietorship">Proprietorship</option>
        <option value="Partnership">Partnership</option>
        <option value="NGO">NGO</option>
        <option value="Trust">Trust</option>
    </select>
    <small class="text-muted text-small">
        Full name will be: Short Name + Legal Structure
    </small>
</div>

<div class="form-group">
    <label for="subdomain" class="form-label">Subdomain *</label>
    <div style="display: flex; align-items: center; gap: 0.5rem;">
        <input
            type="text"
            id="subdomain"
            name="subdomain"
            class="form-input"
            required
            pattern="[a-z0-9-]+"
            minlength="3"
            maxlength="63"
            value="<?php echo $organization ? htmlspecialchars($organization->getSubdomain()) : ''; ?>"
            placeholder="acmecorp"
            style="flex: 1;"
        >
        <span>.v4l.app</span>
    </div>
    <small class="text-muted text-small">
        Your organization will be accessible at https://subdomain.v4l.app
    </small>
    <small class="text-muted text-small">
        3-63 characters. Lowercase letters, numbers, and hyphens only.
    </small>
</div>
```

---

### Task 8: Update Organizations List Display

#### Update organizations.php:
```php
<td style="padding: 1rem;">
    <strong><?php echo htmlspecialchars($org->getFullName()); ?></strong>
    <br>
    <small class="text-muted">
        <a href="<?php echo $org->getUrl(); ?>" target="_blank" class="link">
            <?php echo $org->getSubdomain(); ?>.v4l.app
        </a>
    </small>
    <?php if ($org->getDescription()): ?>
        <br><small class="text-muted"><?php echo htmlspecialchars(substr($org->getDescription(), 0, 50)); ?></small>
    <?php endif; ?>
</td>
```

---

## 📋 Migration Steps

### For Existing Databases

#### SQLite:
1. Delete existing `database/app.db` file
2. Restart server - new schema will be created automatically

#### Supabase:
1. Run ALTER TABLE commands in SQL Editor
2. Update existing records if any:
   ```sql
   UPDATE organizations
   SET subdomain = LOWER(REGEXP_REPLACE(name, '[^a-z0-9]', '-', 'g'))
   WHERE subdomain IS NULL;
   ```

---

## 🎯 Testing Checklist

### Organizations:
- [ ] Create organization with short name + legal structure
- [ ] Verify full name displays correctly (short + legal)
- [ ] Create organization with unique subdomain
- [ ] Try to create with duplicate subdomain (should fail)
- [ ] Verify subdomain URL shows correctly
- [ ] Test subdomain validation (special characters should fail)

### Users:
- [ ] Register with username
- [ ] Login with username
- [ ] Login with email
- [ ] Login with phone
- [ ] Try duplicate username (should fail)
- [ ] Test username validation

---

## 🚀 Quick Implementation Guide

1. **Update database schemas** (SQLite + Supabase)
2. **Update OrganizationRepository** (add subdomain uniqueness check)
3. **Update User entity** (add username field)
4. **Update Auth class** (support username/email/phone login)
5. **Update registration form** (add username field)
6. **Update login form** (accept username/email/phone)
7. **Update organization form** (split name into short_name + legal_structure + subdomain)
8. **Update organizations list** (display full name and subdomain URL)
9. **Test thoroughly**
10. **Deploy!**

---

## 📝 Example Data

### Organization:
- **Short Name**: "Tech Innovators"
- **Legal Structure**: "Private Limited"
- **Subdomain**: "techinnovators"
- **Full Name**: "Tech Innovators Private Limited"
- **URL**: https://techinnovators.v4l.app

### User:
- **Username**: "johndoe"
- **Email**: "john@example.com"
- **Phone**: "+1-555-1234"
- **Can login with**: johndoe OR john@example.com OR +1-555-1234

---

## ⚠️ Important Notes

1. **Subdomain Uniqueness**: Must be enforced at database level (UNIQUE constraint)
2. **Username Uniqueness**: Must be enforced at database level
3. **Password Recovery**: Use email or phone
4. **Subdomain Format**: Always lowercase, validated on both client and server
5. **Organization Display**: Always show full name (short + legal) in UI

---

## 🎨 Branding Colors (Optional)

Update in `public/css/style.css`:

```css
:root {
    --primary-color: #FF6B35;  /* V4L Orange */
    --secondary-color: #004E89; /* V4L Blue */
    --accent-color: #1AA B40;   /* V4L Green */
}
```

---

**Status**: Organization entity updated ✅
**Next**: Complete remaining database and form updates
