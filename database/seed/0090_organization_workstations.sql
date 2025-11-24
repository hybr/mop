-- ===========================
-- Organization Workstations Table Schema
-- ===========================
-- Workstations are working places in buildings with specific locations (floor, room, seat)
CREATE TABLE IF NOT EXISTS organization_workstations (
    id TEXT PRIMARY KEY,
    building_id TEXT NOT NULL,
    organization_id TEXT NOT NULL,

    -- Workstation identification
    name TEXT NOT NULL,
    code TEXT,
    description TEXT,

    -- Location within building
    floor TEXT NOT NULL,
    room TEXT,
    seat_number TEXT,

    -- Workstation details
    workstation_type TEXT CHECK(workstation_type IN ('desk', 'cubicle', 'private_office', 'hot_desk', 'meeting_room', 'lab', 'workshop', 'other')) DEFAULT 'desk',
    capacity INTEGER DEFAULT 1,
    area_sqft REAL,

    -- Equipment and amenities
    has_computer INTEGER DEFAULT 0,
    has_phone INTEGER DEFAULT 0,
    has_printer INTEGER DEFAULT 0,
    amenities TEXT,

    -- Assignment status
    is_occupied INTEGER DEFAULT 0,
    assigned_to TEXT,

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

    FOREIGN KEY (building_id) REFERENCES organization_buildings(id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    FOREIGN KEY (deleted_by) REFERENCES users(id)
);

-- Create indexes for organization_workstations table
CREATE INDEX IF NOT EXISTS idx_workstations_building ON organization_workstations(building_id);
CREATE INDEX IF NOT EXISTS idx_workstations_organization ON organization_workstations(organization_id);
CREATE INDEX IF NOT EXISTS idx_workstations_deleted ON organization_workstations(deleted_at);
CREATE INDEX IF NOT EXISTS idx_workstations_active ON organization_workstations(is_active);
CREATE INDEX IF NOT EXISTS idx_workstations_code ON organization_workstations(code);
CREATE INDEX IF NOT EXISTS idx_workstations_floor ON organization_workstations(floor);
CREATE INDEX IF NOT EXISTS idx_workstations_assigned ON organization_workstations(assigned_to);
CREATE INDEX IF NOT EXISTS idx_workstations_occupied ON organization_workstations(is_occupied);

-- ===========================
-- Organization Workstations Seed Data
-- ===========================
-- Note: Workstations are specific to buildings and should be created per building as needed
-- This table starts empty - organizations will create their own workstations within buildings
-- Example:
-- INSERT INTO organization_workstations (id, building_id, organization_id, name, code, floor, room, seat_number, workstation_type, has_computer, created_by, created_at) VALUES
-- ('ws-001', 'bldg-001', 'org-001', 'Desk A1', 'DESK-A1', '1', 'Room 101', 'A1', 'desk', 1, 'user-001', datetime('now'));
