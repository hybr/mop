-- ===========================
-- Organization Vacancies Table Schema
-- ===========================
-- Job vacancies combining Position and Workstation
-- Vacancies can be viewed publicly when published

CREATE TABLE IF NOT EXISTS organization_vacancies (
    id TEXT PRIMARY KEY,
    title TEXT NOT NULL,                              -- Vacancy title
    code TEXT UNIQUE,                                 -- Unique code (e.g., "VAC-2024-001")
    description TEXT,                                 -- Job description

    -- Foreign keys
    organization_id TEXT NOT NULL,                    -- Required: Organization posting vacancy
    organization_position_id TEXT NOT NULL,           -- Required: Position to fill
    organization_workstation_id TEXT,                 -- Optional: Specific workstation
    reports_to_user_id TEXT,                          -- Optional: Reporting manager

    -- Vacancy details
    vacancy_type TEXT DEFAULT 'new' CHECK(vacancy_type IN ('new', 'replacement', 'expansion')),
    priority TEXT DEFAULT 'medium' CHECK(priority IN ('low', 'medium', 'high', 'urgent')),
    openings_count INTEGER DEFAULT 1,                 -- Number of positions to fill

    -- Timeline
    posted_date TEXT,                                 -- When vacancy was posted
    application_deadline TEXT,                        -- Last date to apply
    target_start_date TEXT,                           -- Expected joining date
    target_end_date TEXT,                             -- For contract positions

    -- Salary and benefits
    salary_offered_min REAL,                          -- Minimum salary offered
    salary_offered_max REAL,                          -- Maximum salary offered
    salary_currency TEXT DEFAULT 'INR',               -- Currency code
    benefits TEXT,                                    -- Benefits description

    -- Application details
    application_method TEXT DEFAULT 'both' CHECK(application_method IN ('internal', 'external', 'both')),
    application_url TEXT,                             -- External application URL
    contact_person TEXT,                              -- Contact person name
    contact_email TEXT,                               -- Contact email
    contact_phone TEXT,                               -- Contact phone

    -- Status tracking
    status TEXT DEFAULT 'draft' CHECK(status IN ('draft', 'open', 'on_hold', 'filled', 'cancelled')),
    is_published INTEGER DEFAULT 0,                   -- Whether publicly visible
    published_at TEXT,                                -- When published
    filled_at TEXT,                                   -- When filled
    filled_by_user_id TEXT,                           -- User who filled the position

    -- Metadata
    views_count INTEGER DEFAULT 0,                    -- Number of views
    applications_count INTEGER DEFAULT 0,             -- Number of applications
    is_active INTEGER DEFAULT 1,
    sort_order INTEGER DEFAULT 0,

    -- Default audit fields
    created_by TEXT NOT NULL,
    created_at TEXT NOT NULL,
    updated_by TEXT,
    updated_at TEXT,
    deleted_by TEXT,
    deleted_at TEXT,

    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (organization_position_id) REFERENCES organization_positions(id),
    FOREIGN KEY (organization_workstation_id) REFERENCES organization_workstations(id),
    FOREIGN KEY (reports_to_user_id) REFERENCES users(id),
    FOREIGN KEY (filled_by_user_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    FOREIGN KEY (deleted_by) REFERENCES users(id)
);

-- Create indexes for organization_vacancies table
CREATE INDEX IF NOT EXISTS idx_vacancies_organization ON organization_vacancies(organization_id);
CREATE INDEX IF NOT EXISTS idx_vacancies_position ON organization_vacancies(organization_position_id);
CREATE INDEX IF NOT EXISTS idx_vacancies_workstation ON organization_vacancies(organization_workstation_id);
CREATE INDEX IF NOT EXISTS idx_vacancies_status ON organization_vacancies(status);
CREATE INDEX IF NOT EXISTS idx_vacancies_is_published ON organization_vacancies(is_published);
CREATE INDEX IF NOT EXISTS idx_vacancies_posted_date ON organization_vacancies(posted_date);
CREATE INDEX IF NOT EXISTS idx_vacancies_deadline ON organization_vacancies(application_deadline);
CREATE INDEX IF NOT EXISTS idx_vacancies_priority ON organization_vacancies(priority);
CREATE INDEX IF NOT EXISTS idx_vacancies_code ON organization_vacancies(code);
CREATE INDEX IF NOT EXISTS idx_vacancies_deleted ON organization_vacancies(deleted_at);
