-- ===========================
-- Organization Positions Table Schema
-- ===========================
-- Positions combine Department + Team + Designation with requirements
-- Positions are global (common to all organizations)

CREATE TABLE IF NOT EXISTS organization_positions (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,                              -- Position name (e.g., "Senior Software Engineer")
    code TEXT NOT NULL UNIQUE,                       -- Unique code (e.g., "SR_SWE")
    description TEXT,                                -- Position description

    -- Foreign keys: Department + Team + Designation
    organization_department_id TEXT NOT NULL,        -- Required: Department
    organization_department_team_id TEXT,            -- Optional: Team within department
    organization_designation_id TEXT NOT NULL,       -- Required: Designation level

    -- Education Requirements
    min_education TEXT CHECK(min_education IN ('none', 'high_school', 'higher_secondary', 'diploma', 'bachelors', 'masters', 'doctorate', 'professional')),
    min_education_field TEXT,                        -- Field of study (e.g., "Computer Science")

    -- Experience Requirements
    min_experience_years INTEGER DEFAULT 0,          -- Minimum years of experience

    -- Skills Requirements (stored as JSON or comma-separated)
    skills_required TEXT,                            -- Required skills
    skills_preferred TEXT,                           -- Preferred skills
    certifications_required TEXT,                    -- Required certifications
    certifications_preferred TEXT,                   -- Preferred certifications

    -- Position Details
    employment_type TEXT DEFAULT 'full_time' CHECK(employment_type IN ('full_time', 'part_time', 'contract', 'internship', 'freelance', 'temporary')),
    reports_to_position_id TEXT,                     -- Reporting position (hierarchy)
    headcount INTEGER DEFAULT 1,                     -- Number of positions available
    salary_range_min REAL,                           -- Minimum salary
    salary_range_max REAL,                           -- Maximum salary
    salary_currency TEXT DEFAULT 'INR',              -- Currency code

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

    FOREIGN KEY (organization_department_id) REFERENCES organization_departments(id),
    FOREIGN KEY (organization_department_team_id) REFERENCES organization_department_teams(id),
    FOREIGN KEY (organization_designation_id) REFERENCES organization_designations(id),
    FOREIGN KEY (reports_to_position_id) REFERENCES organization_positions(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    FOREIGN KEY (deleted_by) REFERENCES users(id)
);

-- Create indexes for organization_positions table
CREATE INDEX IF NOT EXISTS idx_positions_department ON organization_positions(organization_department_id);
CREATE INDEX IF NOT EXISTS idx_positions_team ON organization_positions(organization_department_team_id);
CREATE INDEX IF NOT EXISTS idx_positions_designation ON organization_positions(organization_designation_id);
CREATE INDEX IF NOT EXISTS idx_positions_reports_to ON organization_positions(reports_to_position_id);
CREATE INDEX IF NOT EXISTS idx_positions_code ON organization_positions(code);
CREATE INDEX IF NOT EXISTS idx_positions_deleted ON organization_positions(deleted_at);
CREATE INDEX IF NOT EXISTS idx_positions_active ON organization_positions(is_active);
CREATE INDEX IF NOT EXISTS idx_positions_employment_type ON organization_positions(employment_type);

-- ===========================
-- Organization Positions Seed Data
-- ===========================
-- Sample positions combining department, team, and designation with requirements
-- Note: These use placeholder IDs that should match your existing department/designation data

-- Example positions (commented out - create through UI or uncomment with valid IDs):
-- INSERT INTO organization_positions (id, name, code, description, organization_department_id, organization_designation_id, min_education, min_education_field, min_experience_years, skills_required, employment_type, headcount, created_by, created_at) VALUES
-- ('pos-001', 'Senior Software Engineer', 'SR_SWE', 'Lead development of software solutions', 'dept-it', 'desig-senior', 'bachelors', 'Computer Science', 3, '["JavaScript","Python","SQL","Git"]', 'full_time', 5, 'user-001', datetime('now')),
-- ('pos-002', 'HR Manager', 'HR_MGR', 'Manage human resources operations', 'dept-hr', 'desig-manager', 'bachelors', 'Human Resources', 5, '["Recruitment","Employee Relations","HRIS"]', 'full_time', 1, 'user-001', datetime('now')),
-- ('pos-003', 'Junior Accountant', 'JR_ACCT', 'Handle day-to-day accounting tasks', 'dept-finance', 'desig-junior', 'bachelors', 'Accounting', 0, '["Tally","Excel","GST"]', 'full_time', 3, 'user-001', datetime('now'));
