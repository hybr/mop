-- ===========================
-- Organization Designations Table Schema
-- ===========================
-- Job titles/designations within organizations
CREATE TABLE IF NOT EXISTS organization_designations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    code TEXT NOT NULL UNIQUE,
    description TEXT,
    level INTEGER,
    organization_id INTEGER,
    organization_department_id INTEGER,
    is_active INTEGER DEFAULT 1,
    sort_order INTEGER DEFAULT 0,
    created_by TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by TEXT,
    updated_at DATETIME,
    deleted_by TEXT,
    deleted_at DATETIME,
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (organization_department_id) REFERENCES organization_departments(id)
);

-- Create indexes for organization_designations table
CREATE INDEX IF NOT EXISTS idx_org_designations_code ON organization_designations(code);
CREATE INDEX IF NOT EXISTS idx_org_designations_level ON organization_designations(level);
CREATE INDEX IF NOT EXISTS idx_org_designations_org_id ON organization_designations(organization_id);
CREATE INDEX IF NOT EXISTS idx_org_designations_dept_id ON organization_designations(organization_department_id);
CREATE INDEX IF NOT EXISTS idx_org_designations_deleted ON organization_designations(deleted_at);
CREATE INDEX IF NOT EXISTS idx_org_designations_active ON organization_designations(is_active);

-- ===========================
-- Organization Designations Seed Data
-- ===========================
-- Standard designations across industries (global templates)
-- organization_id is NULL as these are global templates that can be used across organizations

INSERT OR IGNORE INTO organization_designations (name, code, description, level, is_active, sort_order, created_by) VALUES
-- Entry Level (Level 1)
('Intern', 'INTERN', 'Entry-level internship position', 1, 1, 10, 'system'),
('Trainee', 'TRAINEE', 'Training position for new hires', 1, 1, 20, 'system'),
('Junior Associate', 'JR_ASSOC', 'Junior level associate position', 1, 1, 30, 'system'),
('Assistant', 'ASST', 'Assistant level position', 1, 1, 40, 'system'),

-- Mid Level (Level 2)
('Associate', 'ASSOC', 'Associate level position', 2, 1, 110, 'system'),
('Executive', 'EXEC', 'Executive level position', 2, 1, 120, 'system'),
('Specialist', 'SPEC', 'Specialist in a specific domain', 2, 1, 130, 'system'),
('Officer', 'OFFICER', 'Officer level position', 2, 1, 140, 'system'),
('Analyst', 'ANALYST', 'Analyst position', 2, 1, 150, 'system'),
('Developer', 'DEV', 'Software developer position', 2, 1, 160, 'system'),
('Engineer', 'ENG', 'Engineer position', 2, 1, 170, 'system'),

-- Senior Level (Level 3)
('Senior Associate', 'SR_ASSOC', 'Senior associate position', 3, 1, 210, 'system'),
('Senior Executive', 'SR_EXEC', 'Senior executive position', 3, 1, 220, 'system'),
('Senior Specialist', 'SR_SPEC', 'Senior specialist position', 3, 1, 230, 'system'),
('Senior Officer', 'SR_OFFICER', 'Senior officer position', 3, 1, 240, 'system'),
('Senior Analyst', 'SR_ANALYST', 'Senior analyst position', 3, 1, 250, 'system'),
('Senior Developer', 'SR_DEV', 'Senior software developer position', 3, 1, 260, 'system'),
('Senior Engineer', 'SR_ENG', 'Senior engineer position', 3, 1, 270, 'system'),
('Consultant', 'CONSULTANT', 'Consultant position', 3, 1, 280, 'system'),

-- Lead Level (Level 4)
('Team Lead', 'TEAM_LEAD', 'Team leader position', 4, 1, 310, 'system'),
('Tech Lead', 'TECH_LEAD', 'Technical lead position', 4, 1, 320, 'system'),
('Lead Developer', 'LEAD_DEV', 'Lead developer position', 4, 1, 330, 'system'),
('Lead Engineer', 'LEAD_ENG', 'Lead engineer position', 4, 1, 340, 'system'),
('Lead Analyst', 'LEAD_ANALYST', 'Lead analyst position', 4, 1, 350, 'system'),
('Principal Consultant', 'PRIN_CONSULT', 'Principal consultant position', 4, 1, 360, 'system'),
('Architect', 'ARCHITECT', 'Solution/technical architect position', 4, 1, 370, 'system'),

-- Manager Level (Level 5)
('Assistant Manager', 'ASST_MGR', 'Assistant manager position', 5, 1, 410, 'system'),
('Manager', 'MGR', 'Manager position', 5, 1, 420, 'system'),
('Senior Manager', 'SR_MGR', 'Senior manager position', 5, 1, 430, 'system'),
('Project Manager', 'PROJ_MGR', 'Project manager position', 5, 1, 440, 'system'),
('Product Manager', 'PROD_MGR', 'Product manager position', 5, 1, 450, 'system'),
('Program Manager', 'PROG_MGR', 'Program manager position', 5, 1, 460, 'system'),
('Department Head', 'DEPT_HEAD', 'Department head position', 5, 1, 470, 'system'),

-- Executive Level (Level 6)
('Associate Director', 'ASSOC_DIR', 'Associate director position', 6, 1, 510, 'system'),
('Director', 'DIR', 'Director position', 6, 1, 520, 'system'),
('Senior Director', 'SR_DIR', 'Senior director position', 6, 1, 530, 'system'),
('Vice President', 'VP', 'Vice president position', 6, 1, 540, 'system'),
('Senior Vice President', 'SVP', 'Senior vice president position', 6, 1, 550, 'system'),
('Chief Technology Officer', 'CTO', 'Chief technology officer', 6, 1, 560, 'system'),
('Chief Executive Officer', 'CEO', 'Chief executive officer', 6, 1, 570, 'system'),
('Chief Operating Officer', 'COO', 'Chief operating officer', 6, 1, 580, 'system'),
('Chief Financial Officer', 'CFO', 'Chief financial officer', 6, 1, 590, 'system'),
('Chief Human Resources Officer', 'CHRO', 'Chief human resources officer', 6, 1, 600, 'system'),
('Managing Director', 'MD', 'Managing director position', 6, 1, 610, 'system'),
('President', 'PRESIDENT', 'President position', 6, 1, 620, 'system');
