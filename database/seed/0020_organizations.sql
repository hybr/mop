-- ===========================
-- Organizations Table Schema
-- ===========================
CREATE TABLE IF NOT EXISTS organizations (
    id TEXT PRIMARY KEY,
    short_name TEXT NOT NULL,
    legal_structure TEXT,
    subdomain TEXT UNIQUE NOT NULL,
    description TEXT,
    email TEXT,
    phone TEXT,
    website TEXT,
    logo_url TEXT,
    is_active INTEGER DEFAULT 1,
    created_by TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_by TEXT,
    updated_at TEXT,
    deleted_by TEXT,
    deleted_at TEXT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes for organizations table
CREATE UNIQUE INDEX IF NOT EXISTS idx_organizations_subdomain ON organizations(subdomain);
CREATE INDEX IF NOT EXISTS idx_organizations_created_by ON organizations(created_by);
CREATE INDEX IF NOT EXISTS idx_organizations_deleted_at ON organizations(deleted_at);
CREATE INDEX IF NOT EXISTS idx_organizations_short_name ON organizations(short_name);

-- ===========================
-- Organizations Table Seed Data
-- ===========================
-- Note: Add your seed organizations here
-- Example:
-- INSERT INTO organizations (id, short_name, legal_structure, subdomain, description, email, phone, is_active, created_by) VALUES
-- ('org-001', 'ACME Corp', 'LLC', 'acme', 'ACME Corporation - Sample Organization', 'info@acme.com', '555-0100', 1, 'user-001');