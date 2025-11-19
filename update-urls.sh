#!/bin/bash

# Script to update all URLs from old structure to new structure

FILES=$(find public/organizations public/auth public/dashboard public/profile public/market public/organization views -name "*.php" -type f 2>/dev/null)

for file in $FILES; do
    echo "Updating: $file"

    # Organizations
    sed -i 's|/organizations\.php|/organizations/|g' "$file"
    sed -i 's|/organization\.php|/organization/|g' "$file"
    sed -i 's|/organization-form\.php|/organizations/form/|g' "$file"
    sed -i 's|/organization-delete\.php|/organizations/delete/|g' "$file"
    sed -i 's|/organization-restore\.php|/organizations/restore/|g' "$file"
    sed -i 's|/organization-view\.php|/organizations/view/|g' "$file"
    sed -i 's|/organizations-directory\.php|/organizations/directory/|g' "$file"

    # Departments
    sed -i 's|/organization-departments\.php|/organizations/departments/|g' "$file"
    sed -i 's|/organization-department-form\.php|/organizations/departments/form/|g' "$file"
    sed -i 's|/organization-department-view\.php|/organizations/departments/view/|g' "$file"
    sed -i 's|/organization-department-delete\.php|/organizations/departments/delete/|g' "$file"
    sed -i 's|/organization-department-restore\.php|/organizations/departments/restore/|g' "$file"

    # Branches
    sed -i 's|/organizations-facilities-branches\.php|/organizations/facilities/branches/|g' "$file"
    sed -i 's|/branch-form\.php|/organizations/facilities/branches/form/|g' "$file"
    sed -i 's|/branch-delete\.php|/organizations/facilities/branches/delete/|g' "$file"
    sed -i 's|/branch-restore\.php|/organizations/facilities/branches/restore/|g' "$file"

    # Teams
    sed -i 's|/organizations-facilities\.php|/organizations/facilities/teams/|g' "$file"
    sed -i 's|/organizations/facilities|/organizations/facilities/teams|g' "$file"
    sed -i 's|/facility-team-form\.php|/organizations/facilities/teams/form/|g' "$file"
    sed -i 's|/facility-team-delete\.php|/organizations/facilities/teams/delete/|g' "$file"
    sed -i 's|/facility-team-restore\.php|/organizations/facilities/teams/restore/|g' "$file"

    # Auth
    sed -i 's|/login\.php|/auth/login/|g' "$file"
    sed -i 's|/logout\.php|/auth/logout/|g' "$file"
    sed -i 's|/register\.php|/auth/register/|g' "$file"
    sed -i 's|/forgot-password\.php|/auth/forgot-password/|g' "$file"
    sed -i 's|/change-password\.php|/auth/change-password/|g' "$file"

    # Root level
    sed -i 's|/dashboard\.php|/dashboard/|g' "$file"
    sed -i 's|/profile\.php|/profile/|g' "$file"
    sed -i 's|/market\.php|/market/|g' "$file"
done

echo "URL updates completed!"
