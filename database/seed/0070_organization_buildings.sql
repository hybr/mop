-- ===========================
-- Organization Buildings Table Schema
-- ===========================
-- Buildings under branch locations
-- Per navigation.md: One branch may have more than one building. One building is a must.
CREATE TABLE IF NOT EXISTS organization_buildings (
    id TEXT PRIMARY KEY,
    branch_id TEXT NOT NULL,
    organization_id TEXT NOT NULL,
    name TEXT NOT NULL,
    code TEXT,
    description TEXT,

    -- Address fields (required per navigation.md)
    street_address TEXT,
    city TEXT,
    state TEXT,
    postal_code TEXT,
    country TEXT DEFAULT 'India',

    -- Geo coordinates (required per navigation.md)
    latitude REAL NOT NULL,
    longitude REAL NOT NULL,

    -- Contact fields
    phone TEXT,
    email TEXT,

    -- Building details
    building_type TEXT CHECK(building_type IN ('office', 'warehouse', 'retail', 'factory', 'mixed_use', 'residential', 'other')),
    total_floors INTEGER,
    total_area_sqft REAL,
    year_built INTEGER,
    ownership_type TEXT CHECK(ownership_type IN ('owned', 'leased', 'rented')),

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

    FOREIGN KEY (branch_id) REFERENCES organization_branches(id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    FOREIGN KEY (deleted_by) REFERENCES users(id)
);

-- Create indexes for organization_buildings table
CREATE INDEX IF NOT EXISTS idx_buildings_branch ON organization_buildings(branch_id);
CREATE INDEX IF NOT EXISTS idx_buildings_organization ON organization_buildings(organization_id);
CREATE INDEX IF NOT EXISTS idx_buildings_deleted ON organization_buildings(deleted_at);
CREATE INDEX IF NOT EXISTS idx_buildings_active ON organization_buildings(is_active);
CREATE INDEX IF NOT EXISTS idx_buildings_location ON organization_buildings(latitude, longitude);
CREATE INDEX IF NOT EXISTS idx_buildings_code ON organization_buildings(code);

-- ===========================
-- Organization Buildings Seed Data
-- ===========================
-- Note: Buildings are specific to branches and should be created per branch as needed
-- This table starts empty - organizations will create their own buildings under branches
-- Example:
-- INSERT INTO organization_buildings (id, branch_id, organization_id, name, code, street_address, city, state, latitude, longitude, building_type, created_by, created_at) VALUES
-- ('bldg-001', 'branch-001', 'org-001', 'Main Office Tower', 'MAIN-TWR', '123 Business Park', 'Mumbai', 'Maharashtra', 19.0760, 72.8777, 'office', 'user-001', datetime('now'));
