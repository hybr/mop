# Authorization Implementation

## Overview

The application now uses a centralized `Authorization` class to manage permissions instead of having duplicate `isSuperAdmin()` methods in every repository.

## Changes Made

### 1. Created Centralized Authorization Class

**File**: `src/classes/Authorization.php`

This class provides:
- `isSuperAdmin($email)` - Check if user is Super Admin
- `canEditGlobalEntities($userEmail)` - Check if user can edit global entities
- `canDeleteGlobalEntities($userEmail)` - Check if user can delete global entities
- `canRestoreDeleted($userEmail)` - Check if user can restore soft-deleted items
- `requireSuperAdmin($userEmail, $action)` - Require Super Admin or throw exception

### 2. Updated All Repository Classes

The following repositories now use the centralized `Authorization` class:

- `OrganizationPositionRepository`
- `OrganizationDepartmentRepository`
- `OrganizationDesignationRepository`
- `OrganizationDepartmentTeamRepository`
- `OrganizationBranchRepository`
- `OrganizationBuildingRepository`
- `OrganizationWorkstationRepository`
- `OrganizationRepository`

Each repository's `isSuperAdmin()` method now delegates to `Authorization::isSuperAdmin()` and is marked as `@deprecated`.

### 3. Environment Configuration

Super Admin emails are now configurable via environment variables.

**Configuration**: Add to `.env` file

```env
# Single Super Admin
SUPER_ADMIN_EMAILS=sharma.yogesh.1234@gmail.com

# Multiple Super Admins (comma-separated)
SUPER_ADMIN_EMAILS=admin1@example.com,admin2@example.com,admin3@example.com
```

If not configured, defaults to: `sharma.yogesh.1234@gmail.com`

## Usage

### For New Code

Use the `Authorization` class directly:

```php
use App\Classes\Authorization;

// Check if user is Super Admin
if (Authorization::isSuperAdmin($userEmail)) {
    // Super Admin logic
}

// Require Super Admin or throw exception
Authorization::requireSuperAdmin($userEmail, 'create positions');

// Check specific permissions
if (Authorization::canEditGlobalEntities($userEmail)) {
    // Allow editing
}
```

### For Existing Code

Existing code using `$repo->isSuperAdmin()` will continue to work but should be migrated to use `Authorization::isSuperAdmin()` directly.

```php
// Old way (deprecated but still works)
$positionRepo = new OrganizationPositionRepository();
if ($positionRepo->isSuperAdmin($userEmail)) { ... }

// New way (recommended)
use App\Classes\Authorization;
if (Authorization::isSuperAdmin($userEmail)) { ... }
```

## Benefits

1. **Single Source of Truth**: All authorization logic in one place
2. **Easy to Maintain**: Update authorization logic in one file
3. **Configurable**: Super Admin emails via environment variables
4. **Extensible**: Easy to add new permission methods
5. **Multiple Super Admins**: Support for comma-separated list of admin emails
6. **Consistent**: Same authorization behavior across all repositories

## Future Enhancements

The `Authorization` class is designed to be extended with:
- Role-based access control (RBAC)
- Organization-specific permissions
- Resource-level permissions
- Permission caching
- Audit logging

## Migration Guide

To migrate existing code:

1. Add `use App\Classes\Authorization;` at the top of your file
2. Replace `$repo->isSuperAdmin($email)` with `Authorization::isSuperAdmin($email)`
3. Replace permission checks with appropriate `Authorization` methods
4. Use `Authorization::requireSuperAdmin()` for cleaner error handling

## Testing

All authorization methods have been tested and verified to work correctly. See `test_authorization.php` for test examples.
