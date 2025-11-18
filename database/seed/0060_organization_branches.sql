-- ===========================
-- Organization Branches Table Schema
-- ===========================
-- Each organization can create multiple branch locations
CREATE TABLE IF NOT EXISTS organization_branches (
    id TEXT PRIMARY KEY,
    organization_id TEXT NOT NULL,
    name TEXT NOT NULL,
    code TEXT,
    description TEXT,

    -- Address fields
    address_line1 TEXT,
    address_line2 TEXT,
    city TEXT,
    state TEXT,
    country TEXT,
    postal_code TEXT,

    -- Contact fields
    phone TEXT,
    email TEXT,
    website TEXT,

    -- Branch contact person
    contact_person_name TEXT,
    contact_person_phone TEXT,
    contact_person_email TEXT,

    -- Status and metadata
    is_active INTEGER DEFAULT 1,
    branch_type TEXT,
    size_category TEXT,
    opening_date TEXT,
    sort_order INTEGER DEFAULT 0,

    -- Audit fields
    created_by TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_by TEXT,
    updated_at TEXT,
    deleted_by TEXT,
    deleted_at TEXT,

    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create indexes for organization_branches table
CREATE INDEX IF NOT EXISTS idx_org_branches_organization_id ON organization_branches(organization_id);
CREATE INDEX IF NOT EXISTS idx_org_branches_code ON organization_branches(code);
CREATE INDEX IF NOT EXISTS idx_org_branches_city ON organization_branches(city);
CREATE INDEX IF NOT EXISTS idx_org_branches_state ON organization_branches(state);
CREATE INDEX IF NOT EXISTS idx_org_branches_country ON organization_branches(country);
CREATE INDEX IF NOT EXISTS idx_org_branches_deleted_at ON organization_branches(deleted_at);
CREATE INDEX IF NOT EXISTS idx_org_branches_is_active ON organization_branches(is_active);
CREATE INDEX IF NOT EXISTS idx_org_branches_created_by ON organization_branches(created_by);
CREATE INDEX IF NOT EXISTS idx_org_branches_branch_type ON organization_branches(branch_type);

-- ===========================
-- Organization Branches Seed Data
-- ===========================
-- Note: Branches are specific to organizations and should be created per organization as needed
-- This table starts empty - organizations will create their own branches
-- Example:
-- INSERT INTO organization_branches (id, organization_id, name, code, city, state, country, is_active) VALUES
-- ('branch-001', 'org-001', 'New York HQ', 'NY-HQ', 'New York', 'NY', 'USA', 1);
