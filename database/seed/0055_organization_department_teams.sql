-- ===========================
-- Organization Department Teams Table Schema
-- ===========================
-- Teams within organization departments (e.g., teams within Facilities department)
-- This is for organization-specific teams (different from the global department_teams template table)
CREATE TABLE IF NOT EXISTS organization_department_teams (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    code TEXT NOT NULL,
    description TEXT,
    parent_team_id TEXT,
    organization_department_id TEXT NOT NULL,
    organization_id TEXT NOT NULL,

    -- Status and metadata
    is_active INTEGER DEFAULT 1,
    sort_order INTEGER DEFAULT 0,

    -- Default audit fields
    created_by TEXT NOT NULL,
    created_at TEXT NOT NULL,
    updated_by TEXT,
    updated_at TEXT,
    deleted_by TEXT,
    deleted_at TEXT,

    FOREIGN KEY (parent_team_id) REFERENCES organization_department_teams(id),
    FOREIGN KEY (organization_department_id) REFERENCES organization_departments(id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    FOREIGN KEY (deleted_by) REFERENCES users(id)
);

-- Create indexes for organization_department_teams table
CREATE INDEX IF NOT EXISTS idx_dept_teams_department ON organization_department_teams(organization_department_id);
CREATE INDEX IF NOT EXISTS idx_dept_teams_organization ON organization_department_teams(organization_id);
CREATE INDEX IF NOT EXISTS idx_dept_teams_parent ON organization_department_teams(parent_team_id);
CREATE INDEX IF NOT EXISTS idx_dept_teams_deleted ON organization_department_teams(deleted_at);
CREATE INDEX IF NOT EXISTS idx_dept_teams_active ON organization_department_teams(is_active);
CREATE INDEX IF NOT EXISTS idx_dept_teams_code ON organization_department_teams(code);
CREATE UNIQUE INDEX IF NOT EXISTS idx_dept_teams_code_dept ON organization_department_teams(code, organization_department_id) WHERE deleted_at IS NULL;

-- ===========================
-- Organization Department Teams Seed Data
-- ===========================
-- Note: Organization department teams are specific to organizations and should be created per organization as needed
-- This table starts empty - organizations will create their own teams within their departments
-- Global team templates are available in the department_teams table (0050_department_teams.sql)
-- Example:
-- INSERT INTO organization_department_teams (id, name, code, description, organization_department_id, organization_id, created_by, created_at) VALUES
-- ('org-team-001', 'IT Support Team', 'IT_SUPPORT', 'IT support and helpdesk team', 'dept-016', 'org-001', 'user-001', datetime('now'));
