-- ===========================
-- Facility Teams Table Schema
-- ===========================
-- Facility = Department (from organization_departments table)
-- This table manages teams within facilities
CREATE TABLE IF NOT EXISTS facility_teams (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    code TEXT UNIQUE NOT NULL,
    description TEXT,
    parent_team_id TEXT,
    facility_id TEXT,
    organization_id TEXT,
    is_active INTEGER DEFAULT 1,
    sort_order INTEGER DEFAULT 0,
    created_by TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_by TEXT,
    updated_at TEXT,
    deleted_by TEXT,
    deleted_at TEXT,
    FOREIGN KEY (facility_id) REFERENCES organization_departments(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_team_id) REFERENCES facility_teams(id) ON DELETE SET NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create indexes for facility_teams table
CREATE UNIQUE INDEX IF NOT EXISTS idx_facility_teams_code ON facility_teams(code);
CREATE INDEX IF NOT EXISTS idx_facility_teams_facility_id ON facility_teams(facility_id);
CREATE INDEX IF NOT EXISTS idx_facility_teams_parent_id ON facility_teams(parent_team_id);
CREATE INDEX IF NOT EXISTS idx_facility_teams_organization_id ON facility_teams(organization_id);
CREATE INDEX IF NOT EXISTS idx_facility_teams_deleted_at ON facility_teams(deleted_at);
CREATE INDEX IF NOT EXISTS idx_facility_teams_name ON facility_teams(name);
CREATE INDEX IF NOT EXISTS idx_facility_teams_created_by ON facility_teams(created_by);

-- ===========================
-- Facility Teams Seed Data
-- ===========================
-- Standard facility teams that can be used across organizations
-- These are generic templates - facility_id and organization_id should be set when creating actual teams

INSERT OR IGNORE INTO facility_teams (id, name, code, description, facility_id, organization_id, is_active, sort_order) VALUES

-- ===========================
-- Core Operational Teams
-- ===========================
('fteam-001', 'Facilities Operations', 'FAC_OPS', 'Day-to-day building upkeep and operations', NULL, NULL, 1, 10),
('fteam-002', 'Building Operations', 'BLDG_OPS', 'Building operations and management', NULL, NULL, 1, 15),
('fteam-003', 'Maintenance Team', 'MAINT', 'Preventive, corrective, and predictive maintenance', NULL, NULL, 1, 20),
('fteam-004', 'Engineering Team', 'ENG_MEP', 'Mechanical, Electrical, and Plumbing (MEP) engineering', NULL, NULL, 1, 30),
('fteam-005', 'Janitorial Team', 'JANITORIAL', 'Custodial and housekeeping services', NULL, NULL, 1, 40),
('fteam-006', 'Housekeeping Team', 'HOUSEKEEP', 'Building cleanliness and hygiene maintenance', NULL, NULL, 1, 45),
('fteam-007', 'Security & Access Control', 'SECURITY', 'Physical security and access control management', NULL, NULL, 1, 50),
('fteam-008', 'Mailroom & Reprographics', 'MAILROOM', 'Mail handling and document reproduction services', NULL, NULL, 1, 60),
('fteam-009', 'MAC Team', 'MAC', 'Moves, Adds, and Changes coordination', NULL, NULL, 1, 70),
('fteam-010', 'Space Management', 'SPACE_MGMT', 'Space planning and allocation management', NULL, NULL, 1, 80),
('fteam-011', 'Space Planning Team', 'SPACE_PLAN', 'Strategic space utilization and planning', NULL, NULL, 1, 85),
('fteam-012', 'Facilities Help Desk', 'FAC_HELPDESK', 'Service center for facility requests and issues', NULL, NULL, 1, 90),
('fteam-013', 'Service Center', 'SERVICE_CTR', 'Central facilities service coordination', NULL, NULL, 1, 95),
('fteam-014', 'Energy Management', 'ENERGY', 'Energy efficiency and sustainability initiatives', NULL, NULL, 1, 100),
('fteam-015', 'Sustainability Team', 'SUSTAIN', 'Environmental sustainability and green initiatives', NULL, NULL, 1, 105),

-- ===========================
-- Strategic & Planning Teams
-- ===========================
('fteam-101', 'Real Estate Portfolio Management', 'RE_PORTFOLIO', 'Strategic real estate portfolio oversight', NULL, NULL, 1, 200),
('fteam-102', 'Workplace Strategy & Design', 'WP_STRATEGY', 'Workplace design and strategic planning', NULL, NULL, 1, 210),
('fteam-103', 'Transactions & Lease Admin', 'LEASE_ADMIN', 'Lease administration and real estate transactions', NULL, NULL, 1, 220),
('fteam-104', 'Project Management Office', 'PMO', 'Capital projects and facilities project management', NULL, NULL, 1, 230),
('fteam-105', 'Capital Projects Team', 'CAP_PROJ', 'Major capital improvement projects', NULL, NULL, 1, 235),
('fteam-106', 'CAD & BIM Team', 'CAD_BIM', 'Computer-aided design and building information modeling', NULL, NULL, 1, 240),
('fteam-107', 'Interior Design', 'INTERIOR_DES', 'Interior design and workspace aesthetics', NULL, NULL, 1, 250),
('fteam-108', 'Furniture Standards', 'FURN_STD', 'Furniture standards and procurement', NULL, NULL, 1, 255),

-- ===========================
-- Data, Systems & Analytics Teams
-- ===========================
('fteam-201', 'Facilities Data Management', 'FAC_DATA', 'Facilities data management and FMIS ownership (branch, building, workstation data)', NULL, NULL, 1, 300),
('fteam-202', 'FMIS Team', 'FMIS', 'Facilities Management Information System administration', NULL, NULL, 1, 305),
('fteam-203', 'IWMS Administration', 'IWMS_ADMIN', 'Integrated Workplace Management System (Archibus, Tririga, Manhattan, Planon, etc.)', NULL, NULL, 1, 310),
('fteam-204', 'CAFM Administration', 'CAFM_ADMIN', 'Computer-Aided Facility Management system administration', NULL, NULL, 1, 315),
('fteam-205', 'Occupancy Analytics', 'OCC_ANALYTICS', 'Occupancy and sensor analytics for space optimization', NULL, NULL, 1, 320),
('fteam-206', 'Sensor Analytics Team', 'SENSOR', 'IoT sensor data analysis and insights', NULL, NULL, 1, 325),
('fteam-207', 'GIS & Site Selection', 'GIS', 'Geographic information systems and site selection for branch network planning', NULL, NULL, 1, 330),

-- ===========================
-- Support & Specialized Teams
-- ===========================
('fteam-301', 'Health, Safety & Environment', 'HSE', 'Health, safety, and environmental compliance (HSE/EHS)', NULL, NULL, 1, 400),
('fteam-302', 'EHS Team', 'EHS', 'Environmental, health, and safety management', NULL, NULL, 1, 405),
('fteam-303', 'Business Continuity', 'BCP', 'Business continuity and emergency preparedness planning', NULL, NULL, 1, 410),
('fteam-304', 'Emergency Preparedness', 'EMERG_PREP', 'Emergency response and crisis management', NULL, NULL, 1, 415),
('fteam-305', 'Food Services', 'FOOD_SVC', 'Cafeteria and catering management', NULL, NULL, 1, 420),
('fteam-306', 'Cafeteria Management', 'CAFETERIA', 'On-site cafeteria operations', NULL, NULL, 1, 425),
('fteam-307', 'Catering Management', 'CATERING', 'Event and meeting catering services', NULL, NULL, 1, 430),
('fteam-308', 'Fleet & Transportation', 'FLEET', 'Company vehicle and shuttle management', NULL, NULL, 1, 440),
('fteam-309', 'Vendor Management', 'VENDOR_MGMT', 'Vendor and contract management', NULL, NULL, 1, 450),
('fteam-310', 'Contract Management', 'CONTRACT', 'Facilities contracts and vendor relationships', NULL, NULL, 1, 455),
('fteam-311', 'Procurement & Supply Chain', 'PROCURE_FM', 'Facilities-specific procurement and supply chain', NULL, NULL, 1, 460),
('fteam-312', 'Cost & Budgeting', 'COST_BUDGET', 'Financial analysis and budget management', NULL, NULL, 1, 470),
('fteam-313', 'Financial Analysis', 'FIN_ANALYSIS', 'Facilities financial planning and analysis', NULL, NULL, 1, 475),
('fteam-314', 'Reception & Guest Services', 'RECEPTION', 'Front desk reception and visitor management', NULL, NULL, 1, 480),
('fteam-315', 'Guest Services', 'GUEST_SVC', 'Guest and visitor experience services', NULL, NULL, 1, 485),
('fteam-316', 'Signage & Wayfinding', 'SIGNAGE', 'Building signage and wayfinding systems', NULL, NULL, 1, 490),
('fteam-317', 'Art & Aesthetics', 'ART_BRAND', 'Art curation and corporate branding (common in HQ buildings)', NULL, NULL, 1, 495),
('fteam-318', 'Branding Team', 'BRANDING', 'Corporate identity and environmental branding', NULL, NULL, 1, 500),

-- ===========================
-- Regional / Decentralized Teams
-- ===========================
('fteam-401', 'Regional Facilities Managers', 'REGIONAL_FM', 'Regional facilities management oversight', NULL, NULL, 1, 600),
('fteam-402', 'Country Facilities Lead', 'COUNTRY_LEAD', 'Country-level facilities leadership', NULL, NULL, 1, 610),
('fteam-403', 'City Facilities Lead', 'CITY_LEAD', 'City-level facilities coordination', NULL, NULL, 1, 620),
('fteam-404', 'Site-Specific Facilities', 'SITE_TEAM', 'Site-specific facilities team (one per major location)', NULL, NULL, 1, 630),
('fteam-405', 'Campus Facilities Team', 'CAMPUS_TEAM', 'Campus or multi-building site management', NULL, NULL, 1, 640),
('fteam-406', 'Remote Site Management', 'REMOTE_SITE', 'Remote or satellite location facilities management', NULL, NULL, 1, 650);
