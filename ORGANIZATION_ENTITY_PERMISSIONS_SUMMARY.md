# OrganizationEntityPermission - Implementation Summary

## Overview

The **OrganizationEntityPermission** entity manages role-based access control (RBAC) for your organization. It defines which actions specific positions can perform on different entities within the system.

### Core Concept

Permissions read as: **`<OrganizationPosition> can <Action> the entity <Entity> with <Scope>`**

**Example**: "Senior Software Engineer can Create OrganizationVacancy with scope 'department'"

### Integration with Hiring

When a user is hired through an **OrganizationVacancy**, they are assigned an **OrganizationPosition**. This position determines their permissions throughout the system via the OrganizationEntityPermission rules.

---

## Entity Structure

### Core Attributes

| Attribute | Type | Required | Description |
|-----------|------|----------|-------------|
| `organization_position_id` | TEXT | Yes | The position this permission applies to |
| `entity_name` | TEXT | Yes | Entity class name (e.g., "Organization", "OrganizationVacancy") |
| `action` | TEXT | Yes | Action allowed (create, read, update, delete, approve, reject, publish, archive) |
| `scope` | TEXT | No | Permission scope (own, team, department, organization, all) |
| `conditions` | TEXT | No | JSON field for additional conditions |
| `description` | TEXT | No | Human-readable description |
| `is_active` | INTEGER | No | Active status (default: 1) |
| `priority` | INTEGER | No | Priority for conflicting permissions (default: 0, higher = takes precedence) |

### Available Actions

```php
const ACTION_CREATE = 'create';     // Create new records
const ACTION_READ = 'read';         // View records
const ACTION_UPDATE = 'update';     // Modify existing records
const ACTION_DELETE = 'delete';     // Delete records
const ACTION_APPROVE = 'approve';   // Approve records (workflow)
const ACTION_REJECT = 'reject';     // Reject records (workflow)
const ACTION_PUBLISH = 'publish';   // Publish records
const ACTION_ARCHIVE = 'archive';   // Archive records
```

### Available Scopes

```php
const SCOPE_OWN = 'own';                    // Only records created by the user
const SCOPE_TEAM = 'team';                  // Records within the user's team
const SCOPE_DEPARTMENT = 'department';       // Records within the user's department
const SCOPE_ORGANIZATION = 'organization';   // Records within the user's organization
const SCOPE_ALL = 'all';                    // All records (super admin level)
```

---

## Files Created

### 1. Entity Class
**File**: `src/classes/OrganizationEntityPermission.php`

**Key Methods**:
- `getLabel()` - Returns human-readable permission string
- `getReadablePermission()` - Returns formatted permission description
- `getPublicFields()` - Returns public-safe fields
- `getAvailableActions()` - Returns all available actions
- `getAvailableScopes()` - Returns all available scopes
- `getCommonEntities()` - Returns list of common entity names
- `getConditionsArray()` - Parse JSON conditions
- `setConditionsArray()` - Set conditions from array
- `getActionBadgeClass()` - CSS styling for action badges
- `getScopeBadgeClass()` - CSS styling for scope badges

### 2. Repository Class
**File**: `src/classes/OrganizationEntityPermissionRepository.php`

**Key Methods**:

#### CRUD Operations
```php
create(OrganizationEntityPermission $permission, $userId)
findById($id, $userId = null)
update(OrganizationEntityPermission $permission, $userId)
softDelete($id, $userId)
restore($id, $userId)
```

#### Query Methods
```php
findAll($limit = 100, $offset = 0)
findAllWithRelations($limit = 100, $offset = 0)
findByPosition($positionId, $limit = 100, $offset = 0)
findByEntity($entityName, $limit = 100, $offset = 0)
findDeleted($limit = 100, $offset = 0)
count($filters = [])
```

#### Permission Checking
```php
hasPermission($positionId, $entityName, $action, $scope = null)
// Returns: OrganizationEntityPermission object if permission exists, null otherwise

getPermissionMatrix($positionId)
// Returns: Array of all permissions grouped by entity
```

#### Bulk Operations
```php
bulkCreate($positionId, $permissions, $userId)
// Create multiple permissions at once

copyPermissions($fromPositionId, $toPositionId, $userId)
// Copy all permissions from one position to another
```

### 3. Database Migration
**File**: `database/seed/0120_organization_entity_permissions.sql`

**Table Schema**:
```sql
CREATE TABLE IF NOT EXISTS organization_entity_permissions (
    id TEXT PRIMARY KEY,
    organization_position_id TEXT NOT NULL,
    entity_name TEXT NOT NULL,
    action TEXT NOT NULL,
    scope TEXT DEFAULT 'own',
    conditions TEXT,
    description TEXT,
    is_active INTEGER DEFAULT 1,
    priority INTEGER DEFAULT 0,
    created_by TEXT NOT NULL,
    created_at TEXT NOT NULL,
    updated_by TEXT,
    updated_at TEXT,
    deleted_by TEXT,
    deleted_at TEXT,
    UNIQUE (organization_position_id, entity_name, action)
);
```

**Indexes Created**:
- `idx_entity_permissions_position` - On organization_position_id
- `idx_entity_permissions_entity` - On entity_name
- `idx_entity_permissions_action` - On action
- `idx_entity_permissions_scope` - On scope
- `idx_entity_permissions_active` - On is_active
- `idx_entity_permissions_priority` - On priority
- `idx_entity_permissions_deleted` - On deleted_at
- `idx_entity_permissions_check` - Compound index for permission checking

### 4. Seed Script
**File**: `database/seed_entity_permissions.php`

**What it does**:
- Seeds sample permissions for various positions
- Maps positions to role templates (executive, manager, hr, senior_technical, employee)
- Creates 22 sample permissions across 6 positions
- Handles duplicate detection

**Sample Permission Templates**:

#### Executive Level
- Full CRUD on Organization (organization scope)
- Full CRUD on OrganizationVacancy (organization scope)
- Approve OrganizationVacancy (organization scope)
- Full CRUD on OrganizationEntityPermission (organization scope)

#### Manager Level
- Read Organization (organization scope)
- Create/Update OrganizationVacancy (department scope)
- Approve OrganizationVacancy (department scope)
- Read OrganizationEntityPermission (department scope)

#### HR Level
- Full CRUD on OrganizationVacancy (organization scope)
- Publish OrganizationVacancy (organization scope)
- Create/Update OrganizationPosition (organization scope)
- Read OrganizationEntityPermission (organization scope)

#### Senior Technical Level
- Read Organization (organization scope)
- Read OrganizationVacancy (organization scope)
- Read OrganizationPosition (all scope)

#### Employee Level
- Read Organization (organization scope)
- Read OrganizationVacancy (organization scope)
- Read OrganizationPosition (all scope)

### 5. Helper Scripts
**Files**:
- `database/create_permissions_table.php` - Creates the table
- `database/verify_permissions.php` - Verifies data after seeding

---

## Usage Examples

### 1. Check if a position has permission

```php
use App\Classes\OrganizationEntityPermissionRepository;

$permRepo = new OrganizationEntityPermissionRepository();

// Check if position can create vacancies
$permission = $permRepo->hasPermission(
    $positionId,
    'OrganizationVacancy',
    'create'
);

if ($permission) {
    echo "Permission granted with scope: " . $permission->getScope();
    // Allow the action
} else {
    echo "Permission denied";
    // Deny the action
}
```

### 2. Get all permissions for a position

```php
$permissions = $permRepo->findByPosition($positionId);

foreach ($permissions as $perm) {
    echo $perm->getReadablePermission() . "\n";
    // Output: "Operations Manager can create OrganizationVacancy (department)"
}
```

### 3. Get permission matrix for a position

```php
$matrix = $permRepo->getPermissionMatrix($positionId);

// Returns array grouped by entity:
// [
//     'Organization' => [
//         ['action' => 'read', 'scope' => 'organization', 'priority' => 50],
//         ['action' => 'update', 'scope' => 'organization', 'priority' => 50]
//     ],
//     'OrganizationVacancy' => [
//         ['action' => 'create', 'scope' => 'department', 'priority' => 60],
//         ['action' => 'read', 'scope' => 'organization', 'priority' => 60]
//     ]
// ]
```

### 4. Create a new permission

```php
use App\Classes\OrganizationEntityPermission;

$permission = new OrganizationEntityPermission();
$permission->setOrganizationPositionId($positionId);
$permission->setEntityName('OrganizationVacancy');
$permission->setAction('create');
$permission->setScope('department');
$permission->setDescription('Allow department managers to create vacancies');
$permission->setPriority(60);

$permRepo->create($permission, $userId);
```

### 5. Bulk create permissions for a new position

```php
$permissions = [
    [
        'entity_name' => 'Organization',
        'action' => 'read',
        'scope' => 'organization',
        'priority' => 50
    ],
    [
        'entity_name' => 'OrganizationVacancy',
        'action' => 'read',
        'scope' => 'organization',
        'priority' => 50
    ]
];

$permRepo->bulkCreate($newPositionId, $permissions, $userId);
```

### 6. Copy permissions from one position to another

```php
// Copy all permissions from Senior Developer to New Senior Developer
$permRepo->copyPermissions(
    $seniorDevPositionId,
    $newSeniorDevPositionId,
    $userId
);
```

---

## Database Summary

### Tables
- ✅ `organization_entity_permissions` - Created successfully

### Current Data (After Seeding)
- **Total Permissions**: 22
- **Positions with Permissions**: 6
  - Operations Manager (OPS_MGR) - 7 permissions
  - Senior Backend Developer (BACKEND_SR_DEV) - 3 permissions
  - Senior Frontend Developer (FRONTEND_SR_DEV) - 3 permissions
  - Senior DevOps Engineer (DEVOPS_SR_ENG) - 3 permissions
  - Senior Data Scientist (DATA_SCI_SR) - 3 permissions
  - QA Engineer (QA_ENG) - 3 permissions

### Sample Permissions in Database

```
• Operations Manager (OPS_MGR) can approve OrganizationVacancy [scope: department, priority: 70]
• Operations Manager (OPS_MGR) can update OrganizationVacancy [scope: department, priority: 60]
• Operations Manager (OPS_MGR) can read OrganizationVacancy [scope: organization, priority: 60]
• Operations Manager (OPS_MGR) can create OrganizationVacancy [scope: department, priority: 60]
• Senior Backend Developer (BACKEND_SR_DEV) can read Organization [scope: organization, priority: 30]
• Senior Backend Developer (BACKEND_SR_DEV) can read OrganizationVacancy [scope: organization, priority: 30]
• Senior Backend Developer (BACKEND_SR_DEV) can read OrganizationPosition [scope: all, priority: 30]
```

---

## Integration Points

### 1. With User Authentication
When a user logs in, retrieve their assigned OrganizationPosition and check permissions before allowing actions.

```php
$user = $auth->getCurrentUser();
$userPositionId = $user->getOrganizationPositionId();

// Check permission before showing "Create Vacancy" button
$canCreate = $permRepo->hasPermission(
    $userPositionId,
    'OrganizationVacancy',
    'create'
);
```

### 2. With OrganizationVacancy
When a candidate is hired through a vacancy, they are assigned the OrganizationPosition from the vacancy, which determines their permissions.

```php
// After hiring
$newEmployee->setOrganizationPositionId($vacancy->getOrganizationPositionId());

// Their permissions are now determined by that position
$permissions = $permRepo->findByPosition($newEmployee->getOrganizationPositionId());
```

### 3. With Middleware/Guards
Create middleware to check permissions before executing controllers:

```php
function requirePermission($entityName, $action) {
    $user = getCurrentUser();
    $permRepo = new OrganizationEntityPermissionRepository();

    $permission = $permRepo->hasPermission(
        $user->getOrganizationPositionId(),
        $entityName,
        $action
    );

    if (!$permission) {
        throw new Exception('Access denied');
    }
}

// Usage in controller
requirePermission('OrganizationVacancy', 'create');
// ... proceed with create logic
```

---

## Future Enhancements

### 1. Condition-Based Permissions
Use the `conditions` JSON field for complex rules:

```php
$permission->setConditionsArray([
    'max_salary' => 1000000,  // Can only create vacancies with salary < 1M
    'departments' => ['IT', 'Engineering'],  // Only for specific departments
    'requires_approval' => true  // Requires manager approval
]);
```

### 2. Time-Based Permissions
Add temporal constraints:

```php
$permission->setConditionsArray([
    'valid_from' => '2024-01-01',
    'valid_until' => '2024-12-31',
    'working_hours_only' => true
]);
```

### 3. Hierarchical Permissions
Implement permission inheritance through position hierarchy:

```php
// Senior Manager inherits all Manager permissions + additional ones
$permRepo->copyPermissions($managerPositionId, $seniorManagerPositionId, $userId);
// Then add senior-specific permissions
```

### 4. Permission Caching
Implement caching for frequently checked permissions:

```php
$cacheKey = "permissions:{$positionId}:{$entityName}:{$action}";
$cached = $cache->get($cacheKey);
if (!$cached) {
    $cached = $permRepo->hasPermission($positionId, $entityName, $action);
    $cache->set($cacheKey, $cached, 3600); // Cache for 1 hour
}
```

---

## Testing Checklist

### Unit Tests
- [ ] Test permission creation
- [ ] Test permission checking logic
- [ ] Test scope validation
- [ ] Test priority handling for conflicts
- [ ] Test bulk operations
- [ ] Test copy permissions

### Integration Tests
- [ ] Test permission checking in controllers
- [ ] Test permission inheritance
- [ ] Test user-position-permission flow
- [ ] Test permission updates and their immediate effect
- [ ] Test soft delete and restore

### Security Tests
- [ ] Verify users cannot bypass permissions
- [ ] Test edge cases (no permissions, multiple conflicting permissions)
- [ ] Test SQL injection attempts in conditions field
- [ ] Verify audit trail (created_by, updated_by)

---

## Maintenance

### Adding New Entities
When you create a new entity that needs permission control:

1. Add entity name to `getCommonEntities()` in OrganizationEntityPermission.php
2. Define default permissions in seed script
3. Create permissions for relevant positions
4. Update any permission checking middleware

### Adding New Actions
To add a new action type:

1. Add constant to OrganizationEntityPermission class
2. Update `getAvailableActions()` method
3. Add to CHECK constraint in SQL (or validate in PHP)
4. Update `getActionBadgeClass()` for UI styling

### Adding New Scopes
To add a new scope:

1. Add constant to OrganizationEntityPermission class
2. Update `getAvailableScopes()` method
3. Add to CHECK constraint in SQL (or validate in PHP)
4. Update `getScopeBadgeClass()` for UI styling

---

## Summary

✅ **Entity Created**: OrganizationEntityPermission with full CRUD support
✅ **Repository Created**: With permission checking, bulk operations, and query methods
✅ **Database Migrated**: Table and indexes created successfully
✅ **Sample Data Seeded**: 22 permissions across 6 positions
✅ **Documentation**: Complete usage guide and examples

**The permission system is now ready to be integrated into your application's access control layer.**

---

*Generated: 2025-11-25*
