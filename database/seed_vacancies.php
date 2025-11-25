<?php
/**
 * Organization Vacancies Seed Script
 * Populates organization_vacancies table with sample job vacancies
 * combining positions and workstations
 */

require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

echo "=== Organization Vacancies Seeding Script ===" . PHP_EOL . PHP_EOL;

$db = Database::getInstance();
$pdo = $db->getPdo();

// Get sample user, organization, positions, and workstation
$userStmt = $pdo->query("SELECT id FROM users LIMIT 1");
$userId = $userStmt->fetchColumn();

if (!$userId) {
    echo "ERROR: No users found in database. Please create a user first." . PHP_EOL;
    exit(1);
}

$orgStmt = $pdo->query("SELECT id FROM organizations LIMIT 1");
$orgId = $orgStmt->fetchColumn();

if (!$orgId) {
    echo "ERROR: No organizations found. Please create an organization first." . PHP_EOL;
    exit(1);
}

$workstationStmt = $pdo->query("SELECT id FROM organization_workstations LIMIT 1");
$workstationId = $workstationStmt->fetchColumn();

// Sample vacancies
$vacancies = [
    [
        'code' => 'VAC-2024-001',
        'title' => 'Senior Frontend Developer - React/TypeScript',
        'description' => 'We are looking for an experienced Frontend Developer to join our engineering team. You will be responsible for building responsive web applications using React and TypeScript.',
        'position_code' => 'FRONTEND_SR_DEV',
        'vacancy_type' => 'new',
        'priority' => 'high',
        'openings_count' => 2,
        'application_deadline' => date('Y-m-d', strtotime('+30 days')),
        'target_start_date' => date('Y-m-d', strtotime('+45 days')),
        'salary_min' => 1200000,
        'salary_max' => 1800000,
        'benefits' => 'Health insurance, flexible hours, remote work option, professional development budget',
        'application_method' => 'both',
        'status' => 'open',
        'is_published' => 1
    ],
    [
        'code' => 'VAC-2024-002',
        'title' => 'Backend Developer - Node.js/Python',
        'description' => 'Join our backend team to build scalable APIs and microservices. Experience with Node.js, Python, and cloud technologies required.',
        'position_code' => 'BACKEND_SR_DEV',
        'vacancy_type' => 'replacement',
        'priority' => 'urgent',
        'openings_count' => 1,
        'application_deadline' => date('Y-m-d', strtotime('+20 days')),
        'target_start_date' => date('Y-m-d', strtotime('+30 days')),
        'salary_min' => 1200000,
        'salary_max' => 1800000,
        'benefits' => 'Health insurance, stock options, gym membership, learning budget',
        'application_method' => 'both',
        'status' => 'open',
        'is_published' => 1
    ],
    [
        'code' => 'VAC-2024-003',
        'title' => 'DevOps Engineer - AWS/Kubernetes',
        'description' => 'We need a DevOps engineer to manage our cloud infrastructure and CI/CD pipelines. Strong experience with AWS, Docker, and Kubernetes is required.',
        'position_code' => 'DEVOPS_SR_ENG',
        'vacancy_type' => 'expansion',
        'priority' => 'high',
        'openings_count' => 1,
        'application_deadline' => date('Y-m-d', strtotime('+25 days')),
        'target_start_date' => date('Y-m-d', strtotime('+40 days')),
        'salary_min' => 1300000,
        'salary_max' => 1900000,
        'benefits' => 'Health insurance, flexible schedule, remote work, certification reimbursement',
        'application_method' => 'external',
        'status' => 'open',
        'is_published' => 1
    ],
    [
        'code' => 'VAC-2024-004',
        'title' => 'Data Scientist - Machine Learning',
        'description' => 'Looking for a data scientist to build ML models and data pipelines. PhD preferred but not required. Strong Python, TensorFlow/PyTorch experience needed.',
        'position_code' => 'DATA_SCI_SR',
        'vacancy_type' => 'new',
        'priority' => 'medium',
        'openings_count' => 2,
        'application_deadline' => date('Y-m-d', strtotime('+40 days')),
        'target_start_date' => date('Y-m-d', strtotime('+60 days')),
        'salary_min' => 1500000,
        'salary_max' => 2500000,
        'benefits' => 'Health insurance, research budget, conference attendance, flexible hours',
        'application_method' => 'both',
        'status' => 'open',
        'is_published' => 1
    ],
    [
        'code' => 'VAC-2024-005',
        'title' => 'QA Engineer - Automation Testing',
        'description' => 'Join our QA team to build and maintain automated test suites. Experience with Selenium, Cypress, and API testing required.',
        'position_code' => 'QA_LEAD_ENG',
        'vacancy_type' => 'new',
        'priority' => 'medium',
        'openings_count' => 2,
        'application_deadline' => date('Y-m-d', strtotime('+35 days')),
        'target_start_date' => date('Y-m-d', strtotime('+50 days')),
        'salary_min' => 1100000,
        'salary_max' => 1600000,
        'benefits' => 'Health insurance, work from home option, training budget',
        'application_method' => 'both',
        'status' => 'open',
        'is_published' => 1
    ],
    [
        'code' => 'VAC-2024-006',
        'title' => 'Product Manager - SaaS Products',
        'description' => 'Seeking an experienced product manager to lead our SaaS product roadmap. 5+ years experience in product management required.',
        'position_code' => 'PROD_MGR',
        'vacancy_type' => 'replacement',
        'priority' => 'high',
        'openings_count' => 1,
        'application_deadline' => date('Y-m-d', strtotime('+15 days')),
        'target_start_date' => date('Y-m-d', strtotime('+30 days')),
        'salary_min' => null,
        'salary_max' => null,
        'benefits' => 'Health insurance, equity, flexible hours, unlimited PTO',
        'application_method' => 'both',
        'status' => 'open',
        'is_published' => 1
    ],
    [
        'code' => 'VAC-2024-007',
        'title' => 'Digital Marketing Manager',
        'description' => 'Lead our digital marketing efforts including SEO, SEM, social media, and content marketing. 4+ years experience required.',
        'position_code' => 'DIGITAL_MKTG_MGR',
        'vacancy_type' => 'new',
        'priority' => 'medium',
        'openings_count' => 1,
        'application_deadline' => date('Y-m-d', strtotime('+30 days')),
        'target_start_date' => date('Y-m-d', strtotime('+45 days')),
        'salary_min' => 900000,
        'salary_max' => 1400000,
        'benefits' => 'Health insurance, performance bonus, flexible schedule',
        'application_method' => 'both',
        'status' => 'open',
        'is_published' => 1
    ],
    [
        'code' => 'VAC-2024-008',
        'title' => 'HR Manager - Talent Acquisition',
        'description' => 'Manage recruitment and talent acquisition strategies. Experience with HRIS and recruitment tools required.',
        'position_code' => 'HR_MGR_RECRUIT',
        'vacancy_type' => 'replacement',
        'priority' => 'medium',
        'openings_count' => 1,
        'application_deadline' => date('Y-m-d', strtotime('+25 days')),
        'target_start_date' => date('Y-m-d', strtotime('+40 days')),
        'salary_min' => 800000,
        'salary_max' => 1200000,
        'benefits' => 'Health insurance, professional development, flexible hours',
        'application_method' => 'internal',
        'status' => 'open',
        'is_published' => 0
    ],
    [
        'code' => 'VAC-2024-009',
        'title' => 'Sales Executive - Enterprise Sales',
        'description' => 'Drive enterprise sales and build client relationships. Experience in B2B SaaS sales preferred.',
        'position_code' => 'SALES_MGR_ENT',
        'vacancy_type' => 'expansion',
        'priority' => 'high',
        'openings_count' => 3,
        'application_deadline' => date('Y-m-d', strtotime('+35 days')),
        'target_start_date' => date('Y-m-d', strtotime('+50 days')),
        'salary_min' => 1200000,
        'salary_max' => 2000000,
        'benefits' => 'Health insurance, commission structure, travel allowance, phone reimbursement',
        'application_method' => 'both',
        'status' => 'open',
        'is_published' => 1
    ],
    [
        'code' => 'VAC-2024-010',
        'title' => 'Customer Success Manager',
        'description' => 'Manage customer relationships and ensure customer satisfaction. Experience with SaaS products required.',
        'position_code' => 'CS_MGR',
        'vacancy_type' => 'new',
        'priority' => 'medium',
        'openings_count' => 2,
        'application_deadline' => date('Y-m-d', strtotime('+30 days')),
        'target_start_date' => date('Y-m-d', strtotime('+45 days')),
        'salary_min' => 700000,
        'salary_max' => 1000000,
        'benefits' => 'Health insurance, performance bonus, work from home option',
        'application_method' => 'both',
        'status' => 'draft',
        'is_published' => 0
    ],
];

$created = 0;
$skipped = 0;
$errors = 0;

// Prepare insert statement
$insertStmt = $pdo->prepare("
    INSERT OR IGNORE INTO organization_vacancies
    (id, code, title, description, organization_id, organization_position_id, organization_workstation_id,
     vacancy_type, priority, openings_count, posted_date, application_deadline, target_start_date,
     salary_offered_min, salary_offered_max, salary_currency, benefits, application_method, contact_email,
     status, is_published, published_at, is_active, sort_order, created_by, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'INR', ?, ?, ?, ?, ?, ?, 1, ?, ?, datetime('now'))
");

foreach ($vacancies as $index => $vac) {
    $id = 'vac-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

    // Get position ID by code
    $posStmt = $pdo->prepare("SELECT id FROM organization_positions WHERE code = ? LIMIT 1");
    $posStmt->execute([$vac['position_code']]);
    $positionId = $posStmt->fetchColumn();

    if (!$positionId) {
        echo "  ! Error: Position '{$vac['position_code']}' not found for vacancy '{$vac['title']}'" . PHP_EOL;
        $errors++;
        continue;
    }

    $publishedAt = ($vac['is_published'] == 1) ? date('Y-m-d H:i:s') : null;

    try {
        $result = $insertStmt->execute([
            $id,
            $vac['code'],
            $vac['title'],
            $vac['description'],
            $orgId,
            $positionId,
            $workstationId,
            $vac['vacancy_type'],
            $vac['priority'],
            $vac['openings_count'],
            date('Y-m-d'),
            $vac['application_deadline'],
            $vac['target_start_date'],
            $vac['salary_min'],
            $vac['salary_max'],
            $vac['benefits'],
            $vac['application_method'],
            'hr@example.com',
            $vac['status'],
            $vac['is_published'],
            $publishedAt,
            ($index + 1) * 10,
            $userId
        ]);

        if ($result && $insertStmt->rowCount() > 0) {
            echo "  + Created: {$vac['title']} ({$vac['code']})" . PHP_EOL;
            $created++;
        } else {
            echo "  - Skipped: {$vac['title']} (already exists)" . PHP_EOL;
            $skipped++;
        }
    } catch (Exception $e) {
        echo "  ! Error creating {$vac['title']}: " . $e->getMessage() . PHP_EOL;
        $errors++;
    }
}

echo PHP_EOL;
echo "=== Seeding Complete ===" . PHP_EOL;
echo "Created: $created vacancies" . PHP_EOL;
echo "Skipped: $skipped vacancies (already exist)" . PHP_EOL;
echo "Errors: $errors" . PHP_EOL;
echo PHP_EOL;

// Display summary
$countStmt = $pdo->query("SELECT COUNT(*) as count FROM organization_vacancies WHERE deleted_at IS NULL");
$total = $countStmt->fetch()['count'];
echo "Total vacancies in database: $total" . PHP_EOL;
echo PHP_EOL;

// Display sample vacancies
echo "=== Sample Vacancies ===" . PHP_EOL;
$sampleStmt = $pdo->query("
    SELECT v.code, v.title, v.status, v.priority, v.openings_count, v.is_published
    FROM organization_vacancies v
    WHERE v.deleted_at IS NULL
    ORDER BY v.posted_date DESC
    LIMIT 10
");
foreach ($sampleStmt->fetchAll() as $row) {
    $status = $row['is_published'] ? '[PUBLISHED]' : '[DRAFT]';
    echo sprintf("%-15s | %-50s | %s %s (%d openings)\n",
        $row['code'],
        substr($row['title'], 0, 50),
        $status,
        strtoupper($row['priority']),
        $row['openings_count']
    );
}
