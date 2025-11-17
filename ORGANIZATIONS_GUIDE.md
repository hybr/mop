# Organizations Management Guide

## Overview

The Organizations feature allows users to create and manage multiple organizations. Each user can only access their own organizations (CRUD operations are user-scoped).

---

## Features

✅ **Create** organizations with detailed information
✅ **Read** all your organizations
✅ **Update** organization details
✅ **Delete** organizations (soft delete with trash)
✅ **Restore** deleted organizations from trash
✅ **Permanently delete** organizations

### Audit Trail

Every organization tracks:
- **Created by** - User who created it
- **Created at** - Creation timestamp
- **Updated by** - User who last updated it
- **Updated at** - Last update timestamp
- **Deleted by** - User who deleted it
- **Deleted at** - Deletion timestamp (soft delete)

---

## Database Schema

### Organizations Table

```sql
CREATE TABLE organizations (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    email TEXT,
    phone TEXT,
    address TEXT,
    website TEXT,
    logo_url TEXT,
    is_active INTEGER DEFAULT 1,

    -- Audit fields
    created_by TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_by TEXT,
    updated_at TEXT,
    deleted_by TEXT,
    deleted_at TEXT,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```

**Indexes:**
- `idx_organizations_created_by` - Fast user lookups
- `idx_organizations_deleted_at` - Soft delete filtering
- `idx_organizations_name` - Name searches

---

## Security & Access Control

### User Isolation

**SQLite:**
- Repository methods verify `created_by` matches current user
- Queries filter by `created_by = $userId`
- Users cannot access other users' organizations

**Supabase:**
- Row Level Security (RLS) policies enforce access
- `created_by = auth.uid()` in all policies
- Database-level security

### RLS Policies (Supabase)

```sql
-- Users can only see their own organizations
CREATE POLICY "Users can view their own organizations"
ON organizations FOR SELECT
USING (created_by = auth.uid());

-- Users can create organizations
CREATE POLICY "Users can create organizations"
ON organizations FOR INSERT
WITH CHECK (created_by = auth.uid());

-- Users can update their own non-deleted organizations
CREATE POLICY "Users can update their own organizations"
ON organizations FOR UPDATE
USING (created_by = auth.uid() AND deleted_at IS NULL);
```

---

## Usage

### Available Pages

| Page | URL | Purpose |
|------|-----|---------|
| **Organizations List** | `/organizations.php` | View all organizations and trash |
| **Create Organization** | `/organization-form.php` | Create new organization |
| **Edit Organization** | `/organization-form.php?id={id}` | Edit existing organization |
| **Delete Organization** | `/organization-delete.php?id={id}` | Soft delete (move to trash) |
| **Permanent Delete** | `/organization-delete.php?id={id}&permanent=1` | Hard delete forever |
| **Restore Organization** | `/organization-restore.php?id={id}` | Restore from trash |

---

## Code Examples

### Create Organization

```php
use App\Classes\Organization;
use App\Classes\OrganizationRepository;
use App\Classes\Auth;

$auth = new Auth();
$user = $auth->getCurrentUser();
$orgRepo = new OrganizationRepository();

// Create new organization
$org = new Organization();
$org->setName('My Company');
$org->setDescription('We build amazing products');
$org->setEmail('contact@mycompany.com');
$org->setPhone('+1 555-123-4567');
$org->setWebsite('https://mycompany.com');
$org->setIsActive(true);

// Save (automatically adds audit fields)
$orgRepo->create($org, $user->getId());
```

### Get User's Organizations

```php
// Get all active organizations
$organizations = $orgRepo->findAllByUser($user->getId());

// Get deleted organizations (trash)
$deletedOrgs = $orgRepo->findDeletedByUser($user->getId());

// Count organizations
$count = $orgRepo->countByUser($user->getId());
```

### Update Organization

```php
// Find organization
$org = $orgRepo->findById($orgId, $user->getId());

if ($org) {
    // Update fields
    $org->setName('New Name');
    $org->setDescription('Updated description');

    // Save (automatically updates updated_by and updated_at)
    $orgRepo->update($org, $user->getId());
}
```

### Soft Delete (Move to Trash)

```php
// Soft delete - can be restored
$orgRepo->softDelete($orgId, $user->getId());
```

### Restore from Trash

```php
// Restore deleted organization
$orgRepo->restore($orgId, $user->getId());
```

### Permanent Delete

```php
// Hard delete - cannot be undone
$orgRepo->hardDelete($orgId, $user->getId());
```

### Search Organizations

```php
// Search by name or description
$results = $orgRepo->searchByUser('tech', $user->getId());
```

---

## Organization Entity

### Properties

```php
$org->getId()           // UUID
$org->getName()         // Organization name
$org->getDescription()  // Description
$org->getEmail()        // Contact email
$org->getPhone()        // Phone number
$org->getAddress()      // Physical address
$org->getWebsite()      // Website URL
$org->getLogoUrl()      // Logo image URL
$org->getIsActive()     // Active status (boolean)

// Audit fields
$org->getCreatedBy()    // User ID who created
$org->getCreatedAt()    // Creation timestamp
$org->getUpdatedBy()    // User ID who last updated
$org->getUpdatedAt()    // Last update timestamp
$org->getDeletedBy()    // User ID who deleted
$org->getDeletedAt()    // Deletion timestamp
$org->isDeleted()       // Check if deleted (boolean)
```

### Validation

```php
// Validate organization data
$errors = $org->validate();

if (!empty($errors)) {
    // Handle validation errors
    foreach ($errors as $error) {
        echo $error;
    }
}
```

**Validation Rules:**
- Name is required
- Email must be valid format (if provided)
- Website must be valid URL (if provided)

---

## Repository Methods

### OrganizationRepository

```php
// Create
create(Organization $org, $userId): Organization

// Read
findById($id, $userId): ?Organization
findAllByUser($userId, $limit = 100, $offset = 0): array
findDeletedByUser($userId, $limit = 100): array

// Update
update(Organization $org, $userId): Organization

// Delete
softDelete($id, $userId): bool
hardDelete($id, $userId): bool

// Restore
restore($id, $userId): bool

// Search
searchByUser($query, $userId, $limit = 20): array

// Count
countByUser($userId, $includeDeleted = false): int
```

---

## Soft Delete Implementation

### How It Works

1. **Soft Delete**: Sets `deleted_at` and `deleted_by` fields
2. **Queries**: Filter `WHERE deleted_at IS NULL` for active records
3. **Trash View**: Shows records `WHERE deleted_at IS NOT NULL`
4. **Restore**: Sets `deleted_at = NULL` and `deleted_by = NULL`
5. **Permanent Delete**: Removes record from database

### Benefits

- ✅ Accidentally deleted data can be recovered
- ✅ Audit trail of deletions
- ✅ "Trash" functionality
- ✅ Option to permanently delete later

---

## Database Differences

### SQLite (Development)

```php
// Queries use prepared statements
$stmt = $pdo->prepare("SELECT * FROM organizations
                       WHERE created_by = ? AND deleted_at IS NULL");
$stmt->execute([$userId]);
```

### Supabase (Production)

```php
// API requests with RLS policies
$response = $this->db->request('GET',
    'organizations?created_by=eq.' . $userId . '&deleted_at=is.null'
);
```

**The code automatically handles both!**

---

## UI Components

### Organizations List

- Table view of all organizations
- Shows: Name, Email, Phone, Status, Created date
- Actions: Edit, Delete buttons
- Separate "Trash" section for deleted items

### Organization Form

- Create/Edit with same form
- Fields: Name, Description, Email, Phone, Address, Website
- Active/Inactive toggle
- Validation before save
- Audit information display (edit mode)

### Trash Section

- Shows soft-deleted organizations
- Deletion date displayed
- Actions: Restore, Delete Forever
- Confirmation for permanent deletion

---

## Best Practices

### 1. Always Use Repository Methods

```php
// ✅ Good
$org = $orgRepo->findById($id, $user->getId());

// ❌ Bad - bypasses security
$org = $orgRepo->findById($id, $someOtherUserId);
```

### 2. Validate Before Saving

```php
$org->setName($_POST['name']);
$errors = $org->validate();

if (empty($errors)) {
    $orgRepo->create($org, $user->getId());
}
```

### 3. Use Soft Delete by Default

```php
// ✅ Soft delete - can be recovered
$orgRepo->softDelete($id, $user->getId());

// Only use hard delete when certain
$orgRepo->hardDelete($id, $user->getId());
```

### 4. Check Ownership

```php
$org = $orgRepo->findById($id, $user->getId());

if (!$org) {
    // Organization not found or user doesn't own it
    throw new Exception('Access denied');
}
```

---

## Testing

### Manual Testing Checklist

- [ ] Create organization
- [ ] View organizations list
- [ ] Edit organization
- [ ] Mark organization as inactive
- [ ] Soft delete organization
- [ ] View trash section
- [ ] Restore organization from trash
- [ ] Permanently delete organization
- [ ] Try to access another user's organization (should fail)
- [ ] Search organizations

### Test Data

```php
// Create test organizations
$testOrgs = [
    ['name' => 'Tech Corp', 'email' => 'tech@example.com'],
    ['name' => 'Design Studio', 'email' => 'design@example.com'],
    ['name' => 'Marketing Agency', 'email' => 'marketing@example.com'],
];

foreach ($testOrgs as $data) {
    $org = new Organization($data);
    $orgRepo->create($org, $user->getId());
}
```

---

## Troubleshooting

### Organization Not Found

**Problem**: Can't find organization
**Solution**: Check that you're using the correct user ID

```php
// Make sure you're passing the current user's ID
$org = $orgRepo->findById($id, $auth->getCurrentUser()->getId());
```

### Access Denied Errors

**Problem**: Getting access denied when editing
**Solution**: Verify user owns the organization

```php
$org = $orgRepo->findById($id, $user->getId());
if (!$org) {
    // User doesn't own this organization
}
```

### Validation Errors

**Problem**: Can't save organization
**Solution**: Check validation errors

```php
$errors = $org->validate();
print_r($errors); // See what's wrong
```

---

## Extending Organizations

### Add Custom Fields

1. Update database schema (add column)
2. Add property to `Organization` class
3. Add getter/setter methods
4. Update form view

### Example: Add Industry Field

```php
// 1. Database (in createTables() method)
// industry TEXT

// 2. Organization.php
private $industry;

public function getIndustry() {
    return $this->industry;
}

public function setIndustry($industry) {
    $this->industry = $industry;
}

// 3. Update hydrate() and toArray()
```

---

## Performance Optimization

### Indexes

Already created for common queries:
- `created_by` - User's organizations
- `deleted_at` - Active/deleted filtering
- `name` - Search queries

### Pagination

```php
// Get organizations with pagination
$page = 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$organizations = $orgRepo->findAllByUser($user->getId(), $perPage, $offset);
$total = $orgRepo->countByUser($user->getId());
$totalPages = ceil($total / $perPage);
```

---

## Security Considerations

✅ **User Isolation** - Users can only access their own data
✅ **SQL Injection** - Prepared statements used throughout
✅ **XSS Protection** - All output is HTML-escaped
✅ **Access Control** - Ownership verified on all operations
✅ **Audit Trail** - All changes are tracked

---

## Migration Notes

### From SQLite to Supabase

The organizations table and RLS policies are already included in `database_setup.sql`.

Just run the SQL in Supabase and update `.env`:

```env
DB_DRIVER=supabase
```

No code changes needed!

---

## Summary

- ✅ Full CRUD operations for organizations
- ✅ User-scoped access (users only see their own)
- ✅ Complete audit trail (created/updated/deleted by/at)
- ✅ Soft delete with trash and restore
- ✅ Works with both SQLite and Supabase
- ✅ Secure by design (RLS policies)
- ✅ Mobile-responsive UI
- ✅ Ready to use!

---

**Next Steps:**

1. Login to your application
2. Click "Organizations" in navigation
3. Create your first organization
4. Explore the features!
