-- ===========================
-- Organization Departments Table Schema
-- ===========================
CREATE TABLE IF NOT EXISTS organization_departments (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    code TEXT UNIQUE NOT NULL,
    description TEXT,
    parent_department_id TEXT,
    organization_id TEXT,
    is_active INTEGER DEFAULT 1,
    sort_order INTEGER DEFAULT 0,
    created_by TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_by TEXT,
    updated_at TEXT,
    deleted_by TEXT,
    deleted_at TEXT,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_department_id) REFERENCES organization_departments(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create indexes for organization_departments table
CREATE UNIQUE INDEX IF NOT EXISTS idx_org_departments_code ON organization_departments(code);
CREATE INDEX IF NOT EXISTS idx_org_departments_organization_id ON organization_departments(organization_id);
CREATE INDEX IF NOT EXISTS idx_org_departments_parent_id ON organization_departments(parent_department_id);
CREATE INDEX IF NOT EXISTS idx_org_departments_deleted_at ON organization_departments(deleted_at);
CREATE INDEX IF NOT EXISTS idx_org_departments_name ON organization_departments(name);
CREATE INDEX IF NOT EXISTS idx_org_departments_created_by ON organization_departments(created_by);

-- ===========================
-- Organization Departments Seed Data
-- ===========================
-- Standard department categories
INSERT OR IGNORE INTO organization_departments (id, name, code, description, sort_order, is_active, organization_id, parent_department_id) VALUES
-- Executive & Leadership
('dept-001', 'Executive Management', 'EXEC', 'Top-level executive leadership including CEO, President, and C-suite officers', 10, 1, NULL, NULL),
('dept-002', 'Board of Directors', 'BOARD', 'Corporate governance and strategic oversight', 5, 1, NULL, NULL),

-- Core Business Functions
('dept-003', 'Human Resources', 'HR', 'Employee recruitment, development, benefits, and workplace relations', 20, 1, NULL, NULL),
('dept-004', 'Finance', 'FINANCE', 'Financial planning, accounting, budgeting, and reporting', 30, 1, NULL, NULL),
('dept-005', 'Accounting', 'ACCOUNTING', 'Financial record-keeping, bookkeeping, and tax compliance', 35, 1, NULL, NULL),
('dept-006', 'Legal', 'LEGAL', 'Legal counsel, compliance, contracts, and risk management', 40, 1, NULL, NULL),
('dept-007', 'Administration', 'ADMIN', 'General administrative support and office management', 50, 1, NULL, NULL),

-- Operations
('dept-008', 'Operations', 'OPS', 'Day-to-day business operations and process management', 60, 1, NULL, NULL),
('dept-009', 'Supply Chain', 'SUPPLY_CHAIN', 'Procurement, logistics, and inventory management', 70, 1, NULL, NULL),
('dept-010', 'Manufacturing', 'MFG', 'Production, assembly, and quality control', 80, 1, NULL, NULL),
('dept-011', 'Quality Assurance', 'QA', 'Product quality testing, standards compliance, and process improvement', 90, 1, NULL, NULL),

-- Customer-Facing
('dept-012', 'Sales', 'SALES', 'Revenue generation, business development, and client acquisition', 100, 1, NULL, NULL),
('dept-013', 'Marketing', 'MARKETING', 'Brand management, market research, advertising, and promotions', 110, 1, NULL, NULL),
('dept-014', 'Customer Service', 'CS', 'Customer support, issue resolution, and client satisfaction', 120, 1, NULL, NULL),
('dept-015', 'Customer Success', 'CSM', 'Client onboarding, retention, and relationship management', 125, 1, NULL, NULL),

-- Technology & Innovation
('dept-016', 'Information Technology', 'IT', 'Technology infrastructure, systems administration, and technical support', 130, 1, NULL, NULL),
('dept-017', 'Engineering', 'ENG', 'Product development, technical design, and engineering services', 140, 1, NULL, NULL),
('dept-018', 'Software Development', 'DEV', 'Application development, programming, and software engineering', 145, 1, NULL, NULL),
('dept-019', 'Research & Development', 'RND', 'Innovation, new product research, and technology advancement', 150, 1, NULL, NULL),
('dept-020', 'Data & Analytics', 'DATA', 'Data analysis, business intelligence, and insights', 160, 1, NULL, NULL),

-- Support Functions
('dept-021', 'Facilities', 'FACILITIES', 'Building maintenance, workspace management, and physical security', 170, 1, NULL, NULL),
('dept-022', 'Security', 'SECURITY', 'Physical and information security, risk mitigation', 180, 1, NULL, NULL),
('dept-023', 'Procurement', 'PROCUREMENT', 'Vendor management, purchasing, and contract negotiation', 190, 1, NULL, NULL),

-- Strategic & Planning
('dept-024', 'Strategy & Planning', 'STRATEGY', 'Corporate strategy, business planning, and strategic initiatives', 200, 1, NULL, NULL),
('dept-025', 'Business Development', 'BIZ_DEV', 'Partnership development, market expansion, and growth opportunities', 210, 1, NULL, NULL),
('dept-026', 'Project Management', 'PMO', 'Project oversight, resource allocation, and delivery management', 220, 1, NULL, NULL),

-- Communications
('dept-027', 'Communications', 'COMMS', 'Internal and external communications, public relations', 230, 1, NULL, NULL),
('dept-028', 'Public Relations', 'PR', 'Media relations, press releases, and reputation management', 240, 1, NULL, NULL),

-- Specialized
('dept-029', 'Training & Development', 'TRAINING', 'Employee training, professional development, and learning programs', 250, 1, NULL, NULL),
('dept-030', 'Compliance', 'COMPLIANCE', 'Regulatory compliance, policy enforcement, and auditing', 260, 1, NULL, NULL),
('dept-031', 'Environmental Health & Safety', 'EHS', 'Workplace safety, environmental compliance, and health programs', 270, 1, NULL, NULL);