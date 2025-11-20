# URL Structure - SEO-Friendly URLs

## Overview
The application now uses folder-based URLs with `index.php` files for better SEO and cleaner URLs.

## URL Mapping

### Authentication
| Old URL | New URL | Description |
|---------|---------|-------------|
| `/auth/login` | `/auth/login/` | User login |
| `/logout.php` | `/auth/logout/` | User logout |
| `/register.php` | `/auth/register/` | User registration |
| `/forgot-password.php` | `/auth/forgot-password/` | Password recovery |
| `/change-password.php` | `/auth/change-password/` | Change password |

### Dashboard & Profile
| Old URL | New URL | Description |
|---------|---------|-------------|
| `/dashboard.php` | `/dashboard/` | User dashboard |
| `/profile.php` | `/profile/` | User profile |
| `/market` | `/market/` | Market page |

### Organizations
| Old URL | New URL | Description |
|---------|---------|-------------|
| `/organizations.php` | `/organizations/` | Organizations list |
| `/organization.php` | `/organization/` | Select organization |
| `/organization-form.php` | `/organizations/form/` | Create/Edit organization |
| `/organization-view.php` | `/organizations/view/` | View organization details |
| `/organization-delete.php` | `/organizations/delete/` | Delete organization |
| `/organization-restore.php` | `/organizations/restore/` | Restore organization |
| `/organizations-directory.php` | `/organizations/directory/` | Public directory |

### Departments
| Old URL | New URL | Description |
|---------|---------|-------------|
| `/organization-departments.php` | `/organizations/departments/` | Departments list |
| `/organization-department-form.php` | `/organizations/departments/form/` | Create/Edit department |
| `/organization-department-view.php` | `/organizations/departments/view/` | View department |
| `/organization-department-delete.php` | `/organizations/departments/delete/` | Delete department |
| `/organization-department-restore.php` | `/organizations/departments/restore/` | Restore department |

### Branches
| Old URL | New URL | Description |
|---------|---------|-------------|
| `/organizations-facilities-branches.php` | `/organizations/departments/facilities/branches/` | Branches list |
| `/branch-form.php` | `/organizations/departments/facilities/branches/form/` | Create/Edit branch |
| `/branch-delete.php` | `/organizations/departments/facilities/branches/delete/` | Delete branch |
| `/branch-restore.php` | `/organizations/departments/facilities/branches/restore/` | Restore branch |

### Teams
| Old URL | New URL | Description |
|---------|---------|-------------|
| `/organizations-facilities.php` | `/organizations/departments/facilities/teams/` | Teams list |
| `/facility-team-form.php` | `/organizations/departments/facilities/teams/form/` | Create/Edit team |
| `/facility-team-delete.php` | `/organizations/departments/facilities/teams/delete/` | Delete team |
| `/facility-team-restore.php` | `/organizations/departments/facilities/teams/restore/` | Restore team |

## Benefits

### SEO Benefits
1. **Cleaner URLs** - `/organizations/departments/facilities/branches/` instead of `/organizations-facilities-branches.php`
2. **Better Hierarchy** - Clear content structure visible in URL
3. **No File Extensions** - More professional and flexible
4. **Keyword-Rich** - Better for search engine indexing

### Development Benefits
1. **Logical Organization** - Files grouped by feature
2. **Easier Navigation** - Clear folder structure
3. **Scalability** - Easy to add new pages within sections
4. **Maintainability** - Related files are together

## File Structure

```
public/
├── index.php
├── auth/
│   ├── login/index.php
│   ├── logout/index.php
│   ├── register/index.php
│   ├── forgot-password/index.php
│   └── change-password/index.php
├── dashboard/index.php
├── profile/index.php
├── market/index.php
├── organization/index.php
└── organizations/
    ├── index.php
    ├── form/index.php
    ├── view/index.php
    ├── delete/index.php
    ├── restore/index.php
    ├── directory/index.php
    └── departments/
        ├── index.php
        ├── form/index.php
        ├── view/index.php
        ├── delete/index.php
        ├── restore/index.php
        └── facilities/
            ├── branches/
            │   ├── index.php
            │   ├── form/index.php
            │   ├── delete/index.php
            │   └── restore/index.php
            └── teams/
                ├── index.php
                ├── form/index.php
                ├── delete/index.php
                └── restore/index.php
```

## Example URLs in Action

### Before (Old)
```
https://example.com/organizations-facilities-branches.php
https://example.com/branch-form.php?id=123
https://example.com/auth/login
```

### After (New)
```
https://example.com/organizations/departments/facilities/branches/
https://example.com/organizations/departments/facilities/branches/form/?id=123
https://example.com/auth/login/
```

## Notes

1. **Trailing Slashes** - All new URLs end with `/` to indicate directory structure
2. **Backward Compatibility** - Old `.php` files are still present but should redirect to new structure
3. **Query Parameters** - Query parameters (like `?id=123`) work the same way
4. **Forms** - POST requests work identically with the new structure

## Migration Completed

✅ All 28 PHP files moved to new structure
✅ All internal links updated
✅ Header navigation updated
✅ Auth class redirects updated
✅ Form submissions updated

---

Last Updated: <?php echo date('Y-m-d H:i:s'); ?>
