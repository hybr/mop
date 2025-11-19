# Path Resolution Guide

## Directory Depth and Path Resolution

When files are moved to subdirectories, the relative paths to `autoload.php` and view files need to be adjusted based on the directory depth.

### Path Patterns by Depth

#### Root Level (public/)
```php
// Example: public/index.php
require_once __DIR__ . '/../src/includes/autoload.php';
include __DIR__ . '/../views/header.php';
```

#### 1 Level Deep (public/folder/)
```php
// Example: public/dashboard/index.php, public/market/index.php
require_once __DIR__ . '/../../src/includes/autoload.php';
include __DIR__ . '/../../views/header.php';
```

#### 2 Levels Deep (public/folder/subfolder/)
```php
// Example: public/auth/login/index.php, public/organizations/form/index.php
require_once __DIR__ . '/../../../src/includes/autoload.php';
include __DIR__ . '/../../../views/header.php';
```

#### 3 Levels Deep (public/folder/subfolder/subsubfolder/)
```php
// Example: public/organizations/departments/form/index.php
require_once __DIR__ . '/../../../../src/includes/autoload.php';
include __DIR__ . '/../../../../views/header.php';
```

#### 4 Levels Deep (public/folder/subfolder/subsubfolder/page/)
```php
// Example: public/organizations/facilities/branches/form/index.php
require_once __DIR__ . '/../../../../../src/includes/autoload.php';
include __DIR__ . '/../../../../../views/header.php';
```

## Quick Reference Table

| Depth | Levels Up | Path Pattern | Example Location |
|-------|-----------|--------------|------------------|
| Root | `../` | `__DIR__ . '/../src/'` | `public/index.php` |
| 1 | `../../` | `__DIR__ . '/../../src/'` | `public/market/` |
| 2 | `../../../` | `__DIR__ . '/../../../src/'` | `public/auth/login/` |
| 3 | `../../../../` | `__DIR__ . '/../../../../src/'` | `public/organizations/departments/form/` |
| 4 | `../../../../../` | `__DIR__ . '/../../../../../src/'` | `public/organizations/facilities/branches/form/` |

## Files Affected

### Depth 1 (2 levels up)
- `/dashboard/index.php`
- `/profile/index.php`
- `/market/index.php`
- `/organization/index.php`

### Depth 2 (3 levels up)
- `/auth/login/index.php`
- `/auth/logout/index.php`
- `/auth/register/index.php`
- `/auth/forgot-password/index.php`
- `/auth/change-password/index.php`
- `/organizations/index.php`
- `/organizations/form/index.php`
- `/organizations/view/index.php`
- `/organizations/delete/index.php`
- `/organizations/restore/index.php`
- `/organizations/directory/index.php`

### Depth 3 (4 levels up)
- `/organizations/departments/index.php`
- `/organizations/departments/form/index.php`
- `/organizations/departments/view/index.php`
- `/organizations/departments/delete/index.php`
- `/organizations/departments/restore/index.php`
- `/organizations/facilities/branches/index.php`
- `/organizations/facilities/teams/index.php`

### Depth 4 (5 levels up)
- `/organizations/facilities/branches/form/index.php`
- `/organizations/facilities/branches/delete/index.php`
- `/organizations/facilities/branches/restore/index.php`
- `/organizations/facilities/teams/form/index.php`
- `/organizations/facilities/teams/delete/index.php`
- `/organizations/facilities/teams/restore/index.php`

## Verification Commands

```bash
# Check for any files still using old single-level path
find public/auth public/organizations public/dashboard public/profile public/market public/organization -name "index.php" -exec grep -l "__DIR__ . '/../src/includes/autoload.php'" {} \;

# Verify corrected paths at each depth
grep "require_once __DIR__" public/market/index.php
grep "require_once __DIR__" public/auth/login/index.php
grep "require_once __DIR__" public/organizations/departments/form/index.php
grep "require_once __DIR__" public/organizations/facilities/branches/form/index.php
```

## Fixed Issues

✅ All autoload.php paths corrected based on directory depth
✅ All header.php paths corrected
✅ All footer.php paths corrected
✅ 0 files remaining with incorrect paths

---

Last Updated: 2025-11-19
