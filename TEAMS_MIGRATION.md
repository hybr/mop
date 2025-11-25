# Teams Migration to Human Resource

## Overview

Teams functionality has been moved from `/organizations/departments/facilities/teams` to `/organizations/departments/human_resource/teams`. This reflects the fact that teams belong to ALL departments, not just Facilities.

## Changes Made

### 1. New Directory Structure

Created new directory structure under Human Resource:
```
public/organizations/departments/human_resource/teams/
├── index.php          # List all teams with department info
├── form/
│   └── index.php      # Create/Edit team form
├── view/
│   └── index.php      # View team details
├── delete/
│   └── index.php      # Soft delete team
└── restore/
    └── index.php      # Restore deleted team
```

### 2. Full CRUD Implementation

All CRUD operations are now working:

#### **Create (C)**
- URL: `/organizations/departments/human_resource/teams/form/`
- Requires Super Admin permission
- Fields:
  - Team Name (required)
  - Team Code (required, uppercase)
  - Description
  - Department (required) - Can be any department
  - Organization (optional) - Can be global or org-specific
  - Parent Team (optional) - For hierarchical structure
  - Status (Active/Inactive)
  - Sort Order

#### **Read (R)**
- List: `/organizations/departments/human_resource/teams/`
- View: `/organizations/departments/human_resource/teams/view/?id={id}`
- Shows all teams with their assigned departments
- Displays department name, organization, parent team, etc.

#### **Update (U)**
- URL: `/organizations/departments/human_resource/teams/form/?id={id}`
- Requires Super Admin permission
- Can edit all team fields

#### **Delete (D)**
- URL: `/organizations/departments/human_resource/teams/delete/?id={id}`
- Soft delete with trash/restore functionality
- Requires Super Admin permission
- Restore: `/organizations/departments/human_resource/teams/restore/?id={id}`

### 3. Repository Enhancements

Added `findAllWithDepartments()` method to `OrganizationDepartmentTeamRepository`:
```php
public function findAllWithDepartments($limit = 1000, $offset = 0)
```

This method joins with the `organization_departments` table to display department names alongside teams.

### 4. Navigation Updates

Updated `/organizations/departments/index.php`:
- Moved "Teams" link from Facilities section to Human Resource section
- Teams now appears as the first item in Human Resource quick access
- Order: Teams → Designations → Positions

### 5. Database Schema

Teams use the `organization_department_teams` table (originally `department_teams`):
- `id` - Unique identifier
- `name` - Team name
- `code` - Unique code (uppercase)
- `description` - Team description
- `organization_department_id` - Department this team belongs to (can be any dept)
- `organization_id` - Optional organization assignment
- `parent_team_id` - For hierarchical teams
- `is_active` - Active status
- `sort_order` - Display order
- Audit fields: created_by, created_at, updated_by, updated_at, deleted_by, deleted_at

### 6. Authorization

Uses centralized `Authorization` class:
- Super Admin can create, edit, delete, restore teams
- All users can view teams
- Permission checks use `Authorization::isSuperAdmin()` instead of repository methods

## Key Features

1. **Cross-Department Teams**: Teams can be assigned to ANY department (HR, Facilities, Operations, etc.)
2. **Global or Organization-Specific**: Teams can be global (available to all orgs) or org-specific
3. **Hierarchical Structure**: Teams can have parent teams for better organization
4. **Soft Delete**: Deleted teams go to trash and can be restored
5. **Full Authorization**: Proper permission checks using centralized Authorization class

## Testing

All CRUD operations have been tested and verified:
- ✅ List teams with department information
- ✅ Create new teams
- ✅ Edit existing teams
- ✅ View team details
- ✅ Soft delete teams
- ✅ Restore deleted teams
- ✅ Authorization checks working
- ✅ Repository methods working correctly

## Migration Notes

### Old Location (Deprecated)
`/organizations/departments/facilities/teams/`

### New Location (Active)
`/organizations/departments/human_resource/teams/`

The old facilities/teams directory still exists but should be considered deprecated. All new development should use the human_resource/teams location.

## URLs

### Primary URLs
- **List**: `http://localhost:8000/organizations/departments/human_resource/teams/`
- **Create**: `http://localhost:8000/organizations/departments/human_resource/teams/form/`
- **View**: `http://localhost:8000/organizations/departments/human_resource/teams/view/?id={id}`
- **Edit**: `http://localhost:8000/organizations/departments/human_resource/teams/form/?id={id}`
- **Delete**: `http://localhost:8000/organizations/departments/human_resource/teams/delete/?id={id}`
- **Restore**: `http://localhost:8000/organizations/departments/human_resource/teams/restore/?id={id}`

## Navigation Path

1. Organizations (`/organizations/`)
2. Departments (`/organizations/departments/`)
3. Human Resource Section
4. Teams (`/organizations/departments/human_resource/teams/`)

## Next Steps

To use the Teams functionality:

1. **Log in as Super Admin** (email must be in SUPER_ADMIN_EMAILS env variable)
2. Navigate to `/organizations/departments/`
3. Click "Teams" in the Human Resource section
4. Click "+ New Team" to create your first team
5. Assign teams to any department (not just Facilities)

## Benefits of This Change

1. **Better Organization**: Teams now logically sit with HR functions
2. **More Flexible**: Teams can be assigned to ANY department, not just Facilities
3. **Consistent UI**: Matches the pattern of Designations and Positions
4. **Clearer Purpose**: Teams are organizational units that cross departments
5. **Full CRUD**: Complete create, read, update, delete functionality with proper permissions
