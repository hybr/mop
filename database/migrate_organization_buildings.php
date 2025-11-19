<?php
/**
 * Migration: Create organization_buildings table
 * Buildings under branch locations
 * Per navigation.md: One branch may have more than one building. One building is a must.
 */

require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

try {
    echo "Creating organization_buildings table...\n";

    $sql = "CREATE TABLE IF NOT EXISTS organization_buildings (
        id TEXT PRIMARY KEY,
        branch_id TEXT NOT NULL,
        organization_id TEXT NOT NULL,
        name TEXT NOT NULL,
        code TEXT,
        description TEXT,

        -- Address fields (required per navigation.md)
        postal_address TEXT NOT NULL,
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
    )";

    $pdo->exec($sql);

    echo "✓ organization_buildings table created successfully\n";

    // Create indexes
    echo "Creating indexes...\n";

    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_buildings_branch ON organization_buildings(branch_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_buildings_organization ON organization_buildings(organization_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_buildings_deleted ON organization_buildings(deleted_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_buildings_active ON organization_buildings(is_active)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_buildings_location ON organization_buildings(latitude, longitude)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_buildings_code ON organization_buildings(code)");

    echo "✓ Indexes created successfully\n";

    echo "\nMigration completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
