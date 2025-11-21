<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

// Super Admin
$superAdminUserId = 'sharma.yogesh.1234@gmail.com';

// Common designations
$designations = [
    // Entry Level (Level 1)
    ['name' => 'Intern', 'code' => 'INTERN', 'level' => 1, 'sort_order' => 10],
    ['name' => 'Trainee', 'code' => 'TRAINEE', 'level' => 1, 'sort_order' => 20],
    ['name' => 'Junior Associate', 'code' => 'JR_ASSOC', 'level' => 1, 'sort_order' => 30],
    ['name' => 'Assistant', 'code' => 'ASST', 'level' => 1, 'sort_order' => 40],

    // Mid Level (Level 2)
    ['name' => 'Associate', 'code' => 'ASSOC', 'level' => 2, 'sort_order' => 110],
    ['name' => 'Executive', 'code' => 'EXEC', 'level' => 2, 'sort_order' => 120],
    ['name' => 'Specialist', 'code' => 'SPEC', 'level' => 2, 'sort_order' => 130],
    ['name' => 'Officer', 'code' => 'OFFICER', 'level' => 2, 'sort_order' => 140],
    ['name' => 'Analyst', 'code' => 'ANALYST', 'level' => 2, 'sort_order' => 150],
    ['name' => 'Developer', 'code' => 'DEV', 'level' => 2, 'sort_order' => 160],
    ['name' => 'Engineer', 'code' => 'ENG', 'level' => 2, 'sort_order' => 170],

    // Senior Level (Level 3)
    ['name' => 'Senior Associate', 'code' => 'SR_ASSOC', 'level' => 3, 'sort_order' => 210],
    ['name' => 'Senior Executive', 'code' => 'SR_EXEC', 'level' => 3, 'sort_order' => 220],
    ['name' => 'Senior Specialist', 'code' => 'SR_SPEC', 'level' => 3, 'sort_order' => 230],
    ['name' => 'Senior Officer', 'code' => 'SR_OFFICER', 'level' => 3, 'sort_order' => 240],
    ['name' => 'Senior Analyst', 'code' => 'SR_ANALYST', 'level' => 3, 'sort_order' => 250],
    ['name' => 'Senior Developer', 'code' => 'SR_DEV', 'level' => 3, 'sort_order' => 260],
    ['name' => 'Senior Engineer', 'code' => 'SR_ENG', 'level' => 3, 'sort_order' => 270],
    ['name' => 'Consultant', 'code' => 'CONSULTANT', 'level' => 3, 'sort_order' => 280],

    // Lead Level (Level 4)
    ['name' => 'Team Lead', 'code' => 'TEAM_LEAD', 'level' => 4, 'sort_order' => 310],
    ['name' => 'Tech Lead', 'code' => 'TECH_LEAD', 'level' => 4, 'sort_order' => 320],
    ['name' => 'Lead Developer', 'code' => 'LEAD_DEV', 'level' => 4, 'sort_order' => 330],
    ['name' => 'Lead Engineer', 'code' => 'LEAD_ENG', 'level' => 4, 'sort_order' => 340],
    ['name' => 'Lead Analyst', 'code' => 'LEAD_ANALYST', 'level' => 4, 'sort_order' => 350],
    ['name' => 'Principal Consultant', 'code' => 'PRIN_CONSULT', 'level' => 4, 'sort_order' => 360],
    ['name' => 'Architect', 'code' => 'ARCHITECT', 'level' => 4, 'sort_order' => 370],

    // Manager Level (Level 5)
    ['name' => 'Assistant Manager', 'code' => 'ASST_MGR', 'level' => 5, 'sort_order' => 410],
    ['name' => 'Manager', 'code' => 'MGR', 'level' => 5, 'sort_order' => 420],
    ['name' => 'Senior Manager', 'code' => 'SR_MGR', 'level' => 5, 'sort_order' => 430],
    ['name' => 'Project Manager', 'code' => 'PROJ_MGR', 'level' => 5, 'sort_order' => 440],
    ['name' => 'Product Manager', 'code' => 'PROD_MGR', 'level' => 5, 'sort_order' => 450],
    ['name' => 'Program Manager', 'code' => 'PROG_MGR', 'level' => 5, 'sort_order' => 460],
    ['name' => 'Department Head', 'code' => 'DEPT_HEAD', 'level' => 5, 'sort_order' => 470],

    // Executive Level (Level 6)
    ['name' => 'Associate Director', 'code' => 'ASSOC_DIR', 'level' => 6, 'sort_order' => 510],
    ['name' => 'Director', 'code' => 'DIR', 'level' => 6, 'sort_order' => 520],
    ['name' => 'Senior Director', 'code' => 'SR_DIR', 'level' => 6, 'sort_order' => 530],
    ['name' => 'Vice President', 'code' => 'VP', 'level' => 6, 'sort_order' => 540],
    ['name' => 'Senior Vice President', 'code' => 'SVP', 'level' => 6, 'sort_order' => 550],
    ['name' => 'Chief Technology Officer', 'code' => 'CTO', 'level' => 6, 'sort_order' => 560],
    ['name' => 'Chief Executive Officer', 'code' => 'CEO', 'level' => 6, 'sort_order' => 570],
    ['name' => 'Chief Operating Officer', 'code' => 'COO', 'level' => 6, 'sort_order' => 580],
    ['name' => 'Chief Financial Officer', 'code' => 'CFO', 'level' => 6, 'sort_order' => 590],
    ['name' => 'Chief Human Resources Officer', 'code' => 'CHRO', 'level' => 6, 'sort_order' => 600],
    ['name' => 'Managing Director', 'code' => 'MD', 'level' => 6, 'sort_order' => 610],
    ['name' => 'President', 'code' => 'PRESIDENT', 'level' => 6, 'sort_order' => 620],
];

$successCount = 0;
$errorCount = 0;

$stmt = $pdo->prepare("INSERT INTO organization_designations
    (name, code, level, is_active, sort_order, created_by, created_at)
    VALUES (?, ?, ?, 1, ?, ?, datetime('now'))");

foreach ($designations as $designation) {
    try {
        // Check if already exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM organization_designations WHERE code = ?");
        $checkStmt->execute([$designation['code']]);
        if ($checkStmt->fetchColumn() > 0) {
            echo "⚠️  Skipping '{$designation['name']}' ({$designation['code']}) - already exists\n";
            continue;
        }

        $stmt->execute([
            $designation['name'],
            $designation['code'],
            $designation['level'],
            $designation['sort_order'],
            $superAdminUserId
        ]);

        echo "✅ Created: {$designation['name']} ({$designation['code']}) - Level {$designation['level']}\n";
        $successCount++;

    } catch (Exception $e) {
        echo "❌ Error creating '{$designation['name']}': " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "\n";
echo "=============================================\n";
echo "✨ Seeding completed!\n";
echo "✅ Successfully created: $successCount designations\n";
if ($errorCount > 0) {
    echo "❌ Errors: $errorCount\n";
}
echo "=============================================\n";
