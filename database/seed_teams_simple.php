<?php
/**
 * Simple Department Teams Seed Script
 * Directly inserts into department_teams table using PDO
 */

require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

echo "=== Department Teams Seeding Script ===" . PHP_EOL . PHP_EOL;

$db = Database::getInstance();
$pdo = $db->getPdo();

// Define standard teams for each department code
$departmentTeams = [
    // dept-001: Executive Management
    'dept-001' => [
        ['CEO_OFFICE', 'CEO Office', 'Chief Executive Officer and direct reports', 10],
        ['ELT', 'Executive Leadership Team', 'C-suite executives and senior leadership', 20],
        ['EXEC_OPS', 'Executive Operations', 'Executive administrative and operational support', 30],
    ],

    // dept-002: Board of Directors
    'dept-002' => [
        ['BOARD_COMM', 'Board Committees', 'Board committee coordination', 10],
        ['CORP_GOV', 'Corporate Governance', 'Corporate governance and compliance', 20],
    ],

    // dept-003: Human Resources
    'dept-003' => [
        ['HR_RECRUIT', 'Talent Acquisition', 'Recruitment and hiring team', 10],
        ['HR_EMP_REL', 'Employee Relations', 'Employee relations and workplace culture', 20],
        ['HR_COMP_BEN', 'Compensation & Benefits', 'Compensation, benefits, and payroll', 30],
        ['HR_L_D', 'Learning & Development', 'Training and professional development', 40],
        ['HR_OPS', 'HR Operations', 'HR systems and operations', 50],
        ['HRIS', 'HRIS Team', 'HR Information Systems management', 60],
    ],

    // dept-004: Finance
    'dept-004' => [
        ['FP_A', 'Financial Planning & Analysis', 'Financial planning, budgeting, and forecasting', 10],
        ['TREASURY', 'Treasury', 'Cash management and treasury operations', 20],
        ['FIN_REPORT', 'Financial Reporting', 'Financial statements and regulatory reporting', 30],
        ['CORP_FIN', 'Corporate Finance', 'Corporate financial strategy and M&A', 40],
    ],

    // dept-005: Accounting
    'dept-005' => [
        ['AP', 'Accounts Payable', 'Vendor payments and payables', 10],
        ['AR', 'Accounts Receivable', 'Customer billing and receivables', 20],
        ['GL', 'General Ledger', 'General ledger and bookkeeping', 30],
        ['TAX', 'Tax Team', 'Tax compliance and planning', 40],
        ['AUDIT', 'Audit Team', 'Internal audit and controls', 50],
    ],

    // dept-006: Legal
    'dept-006' => [
        ['CORP_LEGAL', 'Corporate Legal', 'Corporate law and governance', 10],
        ['CONTRACTS', 'Contracts Team', 'Contract drafting and review', 20],
        ['LITIGATION', 'Litigation', 'Legal disputes and litigation management', 30],
        ['IP', 'Intellectual Property', 'Patents, trademarks, and IP protection', 40],
    ],

    // dept-007: Administration
    'dept-007' => [
        ['OFFICE_MGMT', 'Office Management', 'General office management and support', 10],
        ['EXEC_ASST', 'Executive Assistants', 'Executive administrative support', 20],
        ['ADMIN_SUP', 'Administrative Support', 'General administrative support staff', 30],
    ],

    // dept-008: Operations
    'dept-008' => [
        ['OPS_MGMT', 'Operations Management', 'Daily operations oversight', 10],
        ['PROCESS_IMP', 'Process Improvement', 'Operational efficiency and process optimization', 20],
        ['BIZ_OPS', 'Business Operations', 'Business operations and support', 30],
    ],

    // dept-009: Supply Chain
    'dept-009' => [
        ['SC_PROCURE', 'Procurement', 'Purchasing and procurement', 10],
        ['LOGISTICS', 'Logistics', 'Transportation and logistics', 20],
        ['INVENTORY', 'Inventory Management', 'Stock and inventory control', 30],
        ['WAREHOUSE', 'Warehouse Operations', 'Warehouse management and fulfillment', 40],
    ],

    // dept-010: Manufacturing
    'dept-010' => [
        ['PRODUCTION', 'Production Team', 'Manufacturing and production operations', 10],
        ['ASSEMBLY', 'Assembly Line', 'Product assembly and manufacturing', 20],
        ['QC', 'Quality Control', 'Production quality control', 30],
        ['MFG_ENG', 'Manufacturing Engineering', 'Manufacturing process engineering', 40],
    ],

    // dept-011: Quality Assurance
    'dept-011' => [
        ['QA_TEST', 'QA Testing', 'Product testing and validation', 10],
        ['QA_STD', 'Quality Standards', 'Quality standards and compliance', 20],
        ['QA_PROCESS', 'Process Quality', 'Process quality and improvement', 30],
    ],

    // dept-012: Sales
    'dept-012' => [
        ['INSIDE_SALES', 'Inside Sales', 'Internal sales and phone sales', 10],
        ['FIELD_SALES', 'Field Sales', 'External field sales representatives', 20],
        ['ENT_SALES', 'Enterprise Sales', 'Large enterprise and strategic accounts', 30],
        ['SALES_OPS', 'Sales Operations', 'Sales operations and enablement', 40],
        ['SALES_ENG', 'Sales Engineering', 'Technical sales support', 50],
    ],

    // dept-013: Marketing
    'dept-013' => [
        ['DIGITAL_MKTG', 'Digital Marketing', 'Online marketing and digital campaigns', 10],
        ['CONTENT_MKTG', 'Content Marketing', 'Content creation and marketing', 20],
        ['BRAND', 'Brand Management', 'Brand strategy and management', 30],
        ['PROD_MKTG', 'Product Marketing', 'Product marketing and positioning', 40],
        ['MKTG_OPS', 'Marketing Operations', 'Marketing automation and operations', 50],
    ],

    // dept-014: Customer Service
    'dept-014' => [
        ['CUST_SUP', 'Customer Support', 'Customer support and helpdesk', 10],
        ['TECH_SUP', 'Technical Support', 'Technical customer support', 20],
        ['CALL_CTR', 'Call Center', 'Inbound call center operations', 30],
    ],

    // dept-015: Customer Success
    'dept-015' => [
        ['CSM_MGMT', 'Customer Success Management', 'Customer success and retention', 10],
        ['ONBOARD', 'Onboarding Team', 'Customer onboarding and implementation', 20],
        ['ACCT_MGMT', 'Account Management', 'Strategic account management', 30],
    ],

    // dept-016: Information Technology
    'dept-016' => [
        ['IT_SUPPORT', 'IT Support', 'IT helpdesk and end-user support', 10],
        ['IT_INFRA', 'Infrastructure', 'IT infrastructure and systems', 20],
        ['IT_NETWORK', 'Network Operations', 'Network administration and operations', 30],
        ['IT_SYSADMIN', 'Systems Administration', 'Server and systems administration', 40],
        ['IT_SEC', 'IT Security', 'Information security and cybersecurity', 50],
    ],

    // dept-017: Engineering
    'dept-017' => [
        ['PROD_ENG', 'Product Engineering', 'Product design and engineering', 10],
        ['SYS_ENG', 'Systems Engineering', 'Systems engineering and architecture', 20],
        ['TEST_ENG', 'Test Engineering', 'Engineering testing and validation', 30],
    ],

    // dept-018: Software Development
    'dept-018' => [
        ['FRONTEND', 'Frontend Development', 'UI/UX and frontend development', 10],
        ['BACKEND', 'Backend Development', 'Backend and API development', 20],
        ['MOBILE_DEV', 'Mobile Development', 'Mobile app development', 30],
        ['DEVOPS', 'DevOps', 'Development operations and CI/CD', 40],
        ['QA_ENG', 'QA Engineering', 'Software quality assurance and testing', 50],
    ],

    // dept-019: Research & Development
    'dept-019' => [
        ['APPLIED_R', 'Applied Research', 'Applied research and development', 10],
        ['INNOVATION', 'Innovation Lab', 'Innovation and experimental projects', 20],
        ['PROD_R', 'Product Research', 'Product research and prototyping', 30],
    ],

    // dept-020: Data & Analytics
    'dept-020' => [
        ['DATA_ENG', 'Data Engineering', 'Data pipeline and engineering', 10],
        ['DATA_SCI', 'Data Science', 'Data science and machine learning', 20],
        ['BI', 'Business Intelligence', 'Business intelligence and reporting', 30],
        ['ANALYTICS', 'Analytics', 'Data analytics and insights', 40],
    ],

    // dept-021: Facilities (skip - already has 54 teams)

    // dept-022: Security
    'dept-022' => [
        ['PHYS_SEC', 'Physical Security', 'Physical security operations', 10],
        ['CYBER_SEC', 'Cybersecurity', 'Information and cybersecurity', 20],
        ['SOC', 'Security Operations Center', 'Security monitoring and response', 30],
    ],

    // dept-023: Procurement
    'dept-023' => [
        ['SOURCING', 'Strategic Sourcing', 'Strategic sourcing and vendor selection', 10],
        ['VENDOR', 'Vendor Management', 'Vendor relations and contract management', 20],
        ['PURCH_OPS', 'Purchase Operations', 'Purchase order processing', 30],
    ],

    // dept-024: Strategy & Planning
    'dept-024' => [
        ['CORP_STRAT', 'Corporate Strategy', 'Corporate strategy development', 10],
        ['BIZ_PLAN', 'Business Planning', 'Business planning and analysis', 20],
        ['STRAT_INIT', 'Strategic Initiatives', 'Strategic project execution', 30],
    ],

    // dept-025: Business Development
    'dept-025' => [
        ['PARTNER', 'Partnership Development', 'Strategic partnerships', 10],
        ['MKT_EXP', 'Market Expansion', 'Market and geographic expansion', 20],
        ['ALLIANCE', 'Alliance Management', 'Alliance and ecosystem management', 30],
    ],

    // dept-026: Project Management
    'dept-026' => [
        ['PMO_TEAM', 'Project Management Office', 'Enterprise project management', 10],
        ['PROGRAM', 'Program Management', 'Program management and coordination', 20],
        ['PORTFOLIO', 'Portfolio Management', 'Project portfolio management', 30],
    ],

    // dept-027: Communications
    'dept-027' => [
        ['INT_COMMS', 'Internal Communications', 'Internal employee communications', 10],
        ['EXT_COMMS', 'External Communications', 'External stakeholder communications', 20],
        ['CORP_COMMS', 'Corporate Communications', 'Corporate messaging and communications', 30],
    ],

    // dept-028: Public Relations
    'dept-028' => [
        ['MEDIA_REL', 'Media Relations', 'Press and media relations', 10],
        ['CRISIS_PR', 'Crisis Communications', 'Crisis management and communications', 20],
        ['REPUTATION', 'Reputation Management', 'Brand reputation management', 30],
    ],

    // dept-029: Training & Development
    'dept-029' => [
        ['EMP_TRAIN', 'Employee Training', 'Employee training programs', 10],
        ['LEAD_DEV', 'Leadership Development', 'Leadership development programs', 20],
        ['LMS', 'Learning Management', 'Learning management systems', 30],
    ],

    // dept-030: Compliance
    'dept-030' => [
        ['REG_COMP', 'Regulatory Compliance', 'Regulatory compliance management', 10],
        ['INT_AUDIT', 'Internal Audit', 'Internal audit and controls', 20],
        ['RISK_MGMT', 'Risk Management', 'Enterprise risk management', 30],
    ],

    // dept-031: Environmental Health & Safety
    'dept-031' => [
        ['WORK_SAFETY', 'Workplace Safety', 'Workplace safety programs', 10],
        ['ENV_COMP', 'Environmental Compliance', 'Environmental compliance', 20],
        ['OCC_HEALTH', 'Occupational Health', 'Occupational health programs', 30],
    ],
];

$created = 0;
$skipped = 0;
$errors = 0;

// Prepare insert statement
$insertStmt = $pdo->prepare("
    INSERT OR IGNORE INTO department_teams
    (id, name, code, description, organization_department_id, organization_id, is_active, sort_order, created_at)
    VALUES (?, ?, ?, ?, ?, NULL, 1, ?, datetime('now'))
");

foreach ($departmentTeams as $deptId => $teams) {
    echo "Processing department: $deptId" . PHP_EOL;

    foreach ($teams as $teamData) {
        list($code, $name, $desc, $order) = $teamData;
        $id = 'team-' . strtolower(str_replace('_', '-', $code));

        try {
            $result = $insertStmt->execute([$id, $name, $code, $desc, $deptId, $order]);
            if ($result && $insertStmt->rowCount() > 0) {
                echo "  + Created: $name ($code)" . PHP_EOL;
                $created++;
            } else {
                echo "  - Skipped: $name (already exists)" . PHP_EOL;
                $skipped++;
            }
        } catch (Exception $e) {
            echo "  ! Error creating $name: " . $e->getMessage() . PHP_EOL;
            $errors++;
        }
    }
    echo PHP_EOL;
}

echo "=== Seeding Complete ===" . PHP_EOL;
echo "Created: $created teams" . PHP_EOL;
echo "Skipped: $skipped teams (already exist)" . PHP_EOL;
echo "Errors: $errors" . PHP_EOL;
echo PHP_EOL;

// Display summary
$countStmt = $pdo->query("SELECT COUNT(*) as count FROM department_teams WHERE deleted_at IS NULL");
$total = $countStmt->fetch()['count'];
echo "Total teams in database: $total" . PHP_EOL;
