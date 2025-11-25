-- ===========================
-- Organization Entity Permissions Table Schema
-- ===========================
-- Manages permissions for OrganizationPositions to perform actions on entities
-- Reads as: <OrganizationPosition> can <Action> the entity <Entity>
-- Example: "Senior Software Engineer can Create Projects"
--
-- When a user is hired through OrganizationVacancy, they get assigned
-- an OrganizationPosition which determines their permissions

CREATE TABLE IF NOT EXISTS organization_entity_permissions (
    id TEXT PRIMARY KEY,

    -- Core permission attributes
    organization_position_id TEXT NOT NULL,           -- Required: The position this permission applies to
    entity_name TEXT NOT NULL,                        -- Required: Entity class name (e.g., "Organization", "OrganizationVacancy")
    action TEXT NOT NULL CHECK(action IN (
        'create', 'read', 'update', 'delete',
        'approve', 'reject', 'publish', 'archive'
    )),                                               -- Required: Action allowed

    -- Optional constraints
    scope TEXT DEFAULT 'own' CHECK(scope IN (
        'own',          -- Only records created by the user
        'team',         -- Records within the user's team
        'department',   -- Records within the user's department
        'organization', -- Records within the user's organization
        'all'           -- All records (super admin level)
    )),                                               -- Permission scope
    conditions TEXT,                                  -- JSON field for additional conditions
    description TEXT,                                 -- Human-readable description

    -- Status and metadata
    is_active INTEGER DEFAULT 1,                      -- Active status
    priority INTEGER DEFAULT 0,                       -- Priority for conflicting permissions (higher = takes precedence)

    -- Default audit fields
    created_by TEXT NOT NULL,
    created_at TEXT NOT NULL,
    updated_by TEXT,
    updated_at TEXT,
    deleted_by TEXT,
    deleted_at TEXT,

    FOREIGN KEY (organization_position_id) REFERENCES organization_positions(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    FOREIGN KEY (deleted_by) REFERENCES users(id),

    -- Ensure unique combination of position + entity + action
    UNIQUE (organization_position_id, entity_name, action)
);

-- Create indexes for organization_entity_permissions table
CREATE INDEX IF NOT EXISTS idx_entity_permissions_position ON organization_entity_permissions(organization_position_id);
CREATE INDEX IF NOT EXISTS idx_entity_permissions_entity ON organization_entity_permissions(entity_name);
CREATE INDEX IF NOT EXISTS idx_entity_permissions_action ON organization_entity_permissions(action);
CREATE INDEX IF NOT EXISTS idx_entity_permissions_scope ON organization_entity_permissions(scope);
CREATE INDEX IF NOT EXISTS idx_entity_permissions_active ON organization_entity_permissions(is_active);
CREATE INDEX IF NOT EXISTS idx_entity_permissions_priority ON organization_entity_permissions(priority);
CREATE INDEX IF NOT EXISTS idx_entity_permissions_deleted ON organization_entity_permissions(deleted_at);

-- Compound index for permission checking (most common query)
CREATE INDEX IF NOT EXISTS idx_entity_permissions_check ON organization_entity_permissions(
    organization_position_id,
    entity_name,
    action,
    is_active,
    deleted_at
);
