<?php
/**
 * Organization Positions Seed Script
 * Populates organization_positions table with sample positions
 * combining departments, teams, and designations with requirements
 */

require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

echo "=== Organization Positions Seeding Script ===" . PHP_EOL . PHP_EOL;

$db = Database::getInstance();
$pdo = $db->getPdo();

// Sample positions combining department, team, and designation
// Each position includes: code, name, description, department_id, team_id (optional), designation_id,
// education, education_field, experience_years, skills, employment_type, headcount, salary_range
$positions = [
    // Executive Management Positions
    [
        'code' => 'CEO_EXEC',
        'name' => 'Chief Executive Officer',
        'description' => 'Lead overall company strategy and operations',
        'dept' => 'dept-001',
        'team' => null,
        'designation' => 'CEO',
        'education' => 'masters',
        'education_field' => 'Business Administration',
        'experience' => 15,
        'skills' => json_encode(['Leadership', 'Strategic Planning', 'Business Development', 'Decision Making']),
        'employment_type' => 'full_time',
        'headcount' => 1,
        'salary_min' => 5000000,
        'salary_max' => 10000000
    ],
    [
        'code' => 'COO_EXEC',
        'name' => 'Chief Operating Officer',
        'description' => 'Oversee daily operations and operational excellence',
        'dept' => 'dept-001',
        'team' => null,
        'designation' => 'COO',
        'education' => 'masters',
        'education_field' => 'Business Administration',
        'experience' => 12,
        'skills' => json_encode(['Operations Management', 'Process Optimization', 'Team Leadership']),
        'employment_type' => 'full_time',
        'headcount' => 1,
        'salary_min' => 4000000,
        'salary_max' => 8000000
    ],

    // Human Resources Positions
    [
        'code' => 'HR_MGR_RECRUIT',
        'name' => 'Recruitment Manager',
        'description' => 'Lead talent acquisition and recruitment strategies',
        'dept' => 'dept-003',
        'team' => 'team-hr-recruit',
        'designation' => 'MGR',
        'education' => 'bachelors',
        'education_field' => 'Human Resources',
        'experience' => 5,
        'skills' => json_encode(['Recruitment', 'Interviewing', 'ATS', 'LinkedIn Recruiting', 'Employer Branding']),
        'employment_type' => 'full_time',
        'headcount' => 2,
        'salary_min' => 800000,
        'salary_max' => 1200000
    ],
    [
        'code' => 'HR_SPEC_RECRUIT',
        'name' => 'Recruitment Specialist',
        'description' => 'Execute recruitment campaigns and candidate screening',
        'dept' => 'dept-003',
        'team' => 'team-hr-recruit',
        'designation' => 'SPEC',
        'education' => 'bachelors',
        'education_field' => 'Any',
        'experience' => 2,
        'skills' => json_encode(['Recruitment', 'Screening', 'Job Posting', 'Interview Coordination']),
        'employment_type' => 'full_time',
        'headcount' => 5,
        'salary_min' => 400000,
        'salary_max' => 600000
    ],
    [
        'code' => 'HR_MGR_COMP_BEN',
        'name' => 'Compensation & Benefits Manager',
        'description' => 'Manage employee compensation, benefits, and payroll',
        'dept' => 'dept-003',
        'team' => 'team-hr-comp-ben',
        'designation' => 'MGR',
        'education' => 'bachelors',
        'education_field' => 'Human Resources',
        'experience' => 6,
        'skills' => json_encode(['Compensation Planning', 'Benefits Administration', 'Payroll', 'HRIS']),
        'employment_type' => 'full_time',
        'headcount' => 1,
        'salary_min' => 900000,
        'salary_max' => 1400000
    ],

    // Finance Positions
    [
        'code' => 'CFO_FIN',
        'name' => 'Chief Financial Officer',
        'description' => 'Lead financial strategy and financial management',
        'dept' => 'dept-004',
        'team' => null,
        'designation' => 'CFO',
        'education' => 'masters',
        'education_field' => 'Finance',
        'experience' => 12,
        'skills' => json_encode(['Financial Planning', 'Risk Management', 'Investment Strategy', 'Financial Reporting']),
        'employment_type' => 'full_time',
        'headcount' => 1,
        'salary_min' => 4000000,
        'salary_max' => 7000000
    ],
    [
        'code' => 'FIN_MGR_FPA',
        'name' => 'Financial Planning & Analysis Manager',
        'description' => 'Lead FP&A, budgeting, and financial forecasting',
        'dept' => 'dept-004',
        'team' => 'team-fp-a',
        'designation' => 'MGR',
        'education' => 'bachelors',
        'education_field' => 'Finance',
        'experience' => 5,
        'skills' => json_encode(['Financial Modeling', 'Budgeting', 'Forecasting', 'Excel', 'Power BI']),
        'employment_type' => 'full_time',
        'headcount' => 2,
        'salary_min' => 1000000,
        'salary_max' => 1500000
    ],
    [
        'code' => 'FIN_ANALYST',
        'name' => 'Financial Analyst',
        'description' => 'Perform financial analysis and reporting',
        'dept' => 'dept-004',
        'team' => 'team-fp-a',
        'designation' => 'ANALYST',
        'education' => 'bachelors',
        'education_field' => 'Finance',
        'experience' => 2,
        'skills' => json_encode(['Financial Analysis', 'Excel', 'Financial Reporting', 'Data Analysis']),
        'employment_type' => 'full_time',
        'headcount' => 4,
        'salary_min' => 500000,
        'salary_max' => 800000
    ],

    // Accounting Positions
    [
        'code' => 'ACCT_MGR_AP',
        'name' => 'Accounts Payable Manager',
        'description' => 'Manage accounts payable operations and vendor payments',
        'dept' => 'dept-005',
        'team' => 'team-ap',
        'designation' => 'MGR',
        'education' => 'bachelors',
        'education_field' => 'Accounting',
        'experience' => 5,
        'skills' => json_encode(['AP Management', 'Vendor Management', 'Payment Processing', 'Tally', 'SAP']),
        'employment_type' => 'full_time',
        'headcount' => 1,
        'salary_min' => 700000,
        'salary_max' => 1000000
    ],
    [
        'code' => 'ACCT_EXEC_AR',
        'name' => 'Accounts Receivable Executive',
        'description' => 'Handle customer billing and receivables collection',
        'dept' => 'dept-005',
        'team' => 'team-ar',
        'designation' => 'EXEC',
        'education' => 'bachelors',
        'education_field' => 'Commerce',
        'experience' => 1,
        'skills' => json_encode(['AR Management', 'Invoicing', 'Collections', 'Tally', 'Excel']),
        'employment_type' => 'full_time',
        'headcount' => 3,
        'salary_min' => 300000,
        'salary_max' => 500000
    ],
    [
        'code' => 'TAX_MGR',
        'name' => 'Tax Manager',
        'description' => 'Manage tax compliance and tax planning',
        'dept' => 'dept-005',
        'team' => 'team-tax',
        'designation' => 'MGR',
        'education' => 'bachelors',
        'education_field' => 'Accounting',
        'experience' => 6,
        'skills' => json_encode(['Tax Compliance', 'GST', 'Income Tax', 'TDS', 'Tax Planning']),
        'employment_type' => 'full_time',
        'headcount' => 1,
        'salary_min' => 900000,
        'salary_max' => 1300000
    ],

    // IT Positions
    [
        'code' => 'CTO_IT',
        'name' => 'Chief Technology Officer',
        'description' => 'Lead technology strategy and innovation',
        'dept' => 'dept-016',
        'team' => null,
        'designation' => 'CTO',
        'education' => 'masters',
        'education_field' => 'Computer Science',
        'experience' => 12,
        'skills' => json_encode(['Technology Strategy', 'Architecture', 'Team Leadership', 'Innovation']),
        'employment_type' => 'full_time',
        'headcount' => 1,
        'salary_min' => 4000000,
        'salary_max' => 7000000
    ],
    [
        'code' => 'IT_MGR_SUPPORT',
        'name' => 'IT Support Manager',
        'description' => 'Manage IT support operations and helpdesk',
        'dept' => 'dept-016',
        'team' => 'team-it-support',
        'designation' => 'MGR',
        'education' => 'bachelors',
        'education_field' => 'Information Technology',
        'experience' => 5,
        'skills' => json_encode(['IT Support', 'Helpdesk Management', 'Windows', 'Active Directory', 'ITIL']),
        'employment_type' => 'full_time',
        'headcount' => 2,
        'salary_min' => 800000,
        'salary_max' => 1200000
    ],
    [
        'code' => 'IT_SUPPORT_EXEC',
        'name' => 'IT Support Executive',
        'description' => 'Provide IT helpdesk and technical support',
        'dept' => 'dept-016',
        'team' => 'team-it-support',
        'designation' => 'EXEC',
        'education' => 'bachelors',
        'education_field' => 'Computer Science',
        'experience' => 1,
        'skills' => json_encode(['Technical Support', 'Windows', 'Office 365', 'Troubleshooting']),
        'employment_type' => 'full_time',
        'headcount' => 5,
        'salary_min' => 300000,
        'salary_max' => 500000
    ],
    [
        'code' => 'IT_SYSADMIN_SR',
        'name' => 'Senior Systems Administrator',
        'description' => 'Manage servers and IT infrastructure',
        'dept' => 'dept-016',
        'team' => 'team-it-sysadmin',
        'designation' => 'SR_OFFICER',
        'education' => 'bachelors',
        'education_field' => 'Computer Science',
        'experience' => 5,
        'skills' => json_encode(['Linux', 'Windows Server', 'VMware', 'AWS', 'Docker', 'Kubernetes']),
        'employment_type' => 'full_time',
        'headcount' => 3,
        'salary_min' => 900000,
        'salary_max' => 1400000
    ],

    // Software Development Positions
    [
        'code' => 'DEV_DIR',
        'name' => 'Director of Engineering',
        'description' => 'Lead software development organization',
        'dept' => 'dept-018',
        'team' => null,
        'designation' => 'DIR',
        'education' => 'masters',
        'education_field' => 'Computer Science',
        'experience' => 10,
        'skills' => json_encode(['Engineering Leadership', 'Architecture', 'Agile', 'Product Development']),
        'employment_type' => 'full_time',
        'headcount' => 1,
        'salary_min' => 3000000,
        'salary_max' => 5000000
    ],
    [
        'code' => 'FRONTEND_SR_DEV',
        'name' => 'Senior Frontend Developer',
        'description' => 'Develop and maintain frontend applications',
        'dept' => 'dept-018',
        'team' => 'team-frontend',
        'designation' => 'SR_DEV',
        'education' => 'bachelors',
        'education_field' => 'Computer Science',
        'experience' => 5,
        'skills' => json_encode(['React', 'JavaScript', 'TypeScript', 'HTML', 'CSS', 'Redux', 'Webpack']),
        'employment_type' => 'full_time',
        'headcount' => 4,
        'salary_min' => 1200000,
        'salary_max' => 1800000
    ],
    [
        'code' => 'FRONTEND_DEV',
        'name' => 'Frontend Developer',
        'description' => 'Build user interfaces and web applications',
        'dept' => 'dept-018',
        'team' => 'team-frontend',
        'designation' => 'DEV',
        'education' => 'bachelors',
        'education_field' => 'Computer Science',
        'experience' => 2,
        'skills' => json_encode(['React', 'JavaScript', 'HTML', 'CSS', 'Git']),
        'employment_type' => 'full_time',
        'headcount' => 8,
        'salary_min' => 600000,
        'salary_max' => 1000000
    ],
    [
        'code' => 'BACKEND_SR_DEV',
        'name' => 'Senior Backend Developer',
        'description' => 'Design and develop backend services and APIs',
        'dept' => 'dept-018',
        'team' => 'team-backend',
        'designation' => 'SR_DEV',
        'education' => 'bachelors',
        'education_field' => 'Computer Science',
        'experience' => 5,
        'skills' => json_encode(['Node.js', 'Python', 'PHP', 'SQL', 'MongoDB', 'Redis', 'REST API', 'Microservices']),
        'employment_type' => 'full_time',
        'headcount' => 4,
        'salary_min' => 1200000,
        'salary_max' => 1800000
    ],
    [
        'code' => 'BACKEND_DEV',
        'name' => 'Backend Developer',
        'description' => 'Develop server-side applications and APIs',
        'dept' => 'dept-018',
        'team' => 'team-backend',
        'designation' => 'DEV',
        'education' => 'bachelors',
        'education_field' => 'Computer Science',
        'experience' => 2,
        'skills' => json_encode(['Node.js', 'Python', 'PHP', 'SQL', 'REST API', 'Git']),
        'employment_type' => 'full_time',
        'headcount' => 8,
        'salary_min' => 600000,
        'salary_max' => 1000000
    ],
    [
        'code' => 'MOBILE_SR_DEV',
        'name' => 'Senior Mobile Developer',
        'description' => 'Lead mobile application development',
        'dept' => 'dept-018',
        'team' => 'team-mobile-dev',
        'designation' => 'SR_DEV',
        'education' => 'bachelors',
        'education_field' => 'Computer Science',
        'experience' => 5,
        'skills' => json_encode(['React Native', 'Flutter', 'iOS', 'Android', 'Mobile UI/UX', 'REST API']),
        'employment_type' => 'full_time',
        'headcount' => 2,
        'salary_min' => 1200000,
        'salary_max' => 1800000
    ],
    [
        'code' => 'DEVOPS_SR_ENG',
        'name' => 'Senior DevOps Engineer',
        'description' => 'Lead DevOps practices and CI/CD infrastructure',
        'dept' => 'dept-018',
        'team' => 'team-devops',
        'designation' => 'SR_ENG',
        'education' => 'bachelors',
        'education_field' => 'Computer Science',
        'experience' => 5,
        'skills' => json_encode(['Docker', 'Kubernetes', 'AWS', 'Jenkins', 'GitLab CI', 'Terraform', 'Ansible']),
        'employment_type' => 'full_time',
        'headcount' => 2,
        'salary_min' => 1300000,
        'salary_max' => 1900000
    ],
    [
        'code' => 'QA_LEAD_ENG',
        'name' => 'QA Lead Engineer',
        'description' => 'Lead quality assurance and test automation',
        'dept' => 'dept-018',
        'team' => 'team-qa-eng',
        'designation' => 'LEAD_ENG',
        'education' => 'bachelors',
        'education_field' => 'Computer Science',
        'experience' => 6,
        'skills' => json_encode(['Test Automation', 'Selenium', 'Cypress', 'API Testing', 'Performance Testing']),
        'employment_type' => 'full_time',
        'headcount' => 2,
        'salary_min' => 1100000,
        'salary_max' => 1600000
    ],
    [
        'code' => 'QA_ENG',
        'name' => 'QA Engineer',
        'description' => 'Perform software testing and quality assurance',
        'dept' => 'dept-018',
        'team' => 'team-qa-eng',
        'designation' => 'ENG',
        'education' => 'bachelors',
        'education_field' => 'Computer Science',
        'experience' => 2,
        'skills' => json_encode(['Manual Testing', 'Test Cases', 'Bug Tracking', 'Selenium', 'API Testing']),
        'employment_type' => 'full_time',
        'headcount' => 6,
        'salary_min' => 500000,
        'salary_max' => 800000
    ],

    // Data & Analytics Positions
    [
        'code' => 'DATA_DIR',
        'name' => 'Director of Data & Analytics',
        'description' => 'Lead data strategy and analytics initiatives',
        'dept' => 'dept-020',
        'team' => null,
        'designation' => 'DIR',
        'education' => 'masters',
        'education_field' => 'Data Science',
        'experience' => 10,
        'skills' => json_encode(['Data Strategy', 'Analytics', 'Machine Learning', 'Team Leadership']),
        'employment_type' => 'full_time',
        'headcount' => 1,
        'salary_min' => 3000000,
        'salary_max' => 5000000
    ],
    [
        'code' => 'DATA_SCI_SR',
        'name' => 'Senior Data Scientist',
        'description' => 'Build machine learning models and analytics solutions',
        'dept' => 'dept-020',
        'team' => 'team-data-sci',
        'designation' => 'SR_SPEC',
        'education' => 'masters',
        'education_field' => 'Data Science',
        'experience' => 5,
        'skills' => json_encode(['Python', 'Machine Learning', 'Deep Learning', 'TensorFlow', 'PyTorch', 'SQL']),
        'employment_type' => 'full_time',
        'headcount' => 3,
        'salary_min' => 1500000,
        'salary_max' => 2500000
    ],
    [
        'code' => 'DATA_ENG_SR',
        'name' => 'Senior Data Engineer',
        'description' => 'Design and build data pipelines and infrastructure',
        'dept' => 'dept-020',
        'team' => 'team-data-eng',
        'designation' => 'SR_ENG',
        'education' => 'bachelors',
        'education_field' => 'Computer Science',
        'experience' => 5,
        'skills' => json_encode(['Python', 'SQL', 'Spark', 'Airflow', 'AWS', 'ETL', 'Data Modeling']),
        'employment_type' => 'full_time',
        'headcount' => 3,
        'salary_min' => 1400000,
        'salary_max' => 2000000
    ],
    [
        'code' => 'DATA_ANALYST',
        'name' => 'Data Analyst',
        'description' => 'Analyze data and create insights and reports',
        'dept' => 'dept-020',
        'team' => 'team-analytics',
        'designation' => 'ANALYST',
        'education' => 'bachelors',
        'education_field' => 'Statistics',
        'experience' => 2,
        'skills' => json_encode(['SQL', 'Excel', 'Power BI', 'Tableau', 'Data Visualization', 'Python']),
        'employment_type' => 'full_time',
        'headcount' => 5,
        'salary_min' => 500000,
        'salary_max' => 900000
    ],

    // Sales Positions
    [
        'code' => 'SALES_VP',
        'name' => 'Vice President of Sales',
        'description' => 'Lead sales organization and revenue growth',
        'dept' => 'dept-012',
        'team' => null,
        'designation' => 'VP',
        'education' => 'bachelors',
        'education_field' => 'Business',
        'experience' => 10,
        'skills' => json_encode(['Sales Leadership', 'Revenue Growth', 'Team Building', 'Strategic Planning']),
        'employment_type' => 'full_time',
        'headcount' => 1,
        'salary_min' => 3000000,
        'salary_max' => 6000000
    ],
    [
        'code' => 'SALES_MGR_ENT',
        'name' => 'Enterprise Sales Manager',
        'description' => 'Manage enterprise sales and strategic accounts',
        'dept' => 'dept-012',
        'team' => 'team-ent-sales',
        'designation' => 'MGR',
        'education' => 'bachelors',
        'education_field' => 'Business',
        'experience' => 6,
        'skills' => json_encode(['Enterprise Sales', 'Account Management', 'Negotiation', 'CRM', 'Salesforce']),
        'employment_type' => 'full_time',
        'headcount' => 3,
        'salary_min' => 1200000,
        'salary_max' => 2000000
    ],
    [
        'code' => 'SALES_EXEC',
        'name' => 'Sales Executive',
        'description' => 'Drive sales and business development',
        'dept' => 'dept-012',
        'team' => 'team-inside-sales',
        'designation' => 'EXEC',
        'education' => 'bachelors',
        'education_field' => 'Any',
        'experience' => 2,
        'skills' => json_encode(['Sales', 'Lead Generation', 'Negotiation', 'CRM', 'Communication']),
        'employment_type' => 'full_time',
        'headcount' => 10,
        'salary_min' => 400000,
        'salary_max' => 700000
    ],

    // Marketing Positions
    [
        'code' => 'MARKETING_VP',
        'name' => 'Vice President of Marketing',
        'description' => 'Lead marketing strategy and brand management',
        'dept' => 'dept-013',
        'team' => null,
        'designation' => 'VP',
        'education' => 'masters',
        'education_field' => 'Marketing',
        'experience' => 10,
        'skills' => json_encode(['Marketing Strategy', 'Brand Management', 'Digital Marketing', 'Team Leadership']),
        'employment_type' => 'full_time',
        'headcount' => 1,
        'salary_min' => 2500000,
        'salary_max' => 4500000
    ],
    [
        'code' => 'DIGITAL_MKTG_MGR',
        'name' => 'Digital Marketing Manager',
        'description' => 'Manage digital marketing campaigns and channels',
        'dept' => 'dept-013',
        'team' => 'team-digital-mktg',
        'designation' => 'MGR',
        'education' => 'bachelors',
        'education_field' => 'Marketing',
        'experience' => 5,
        'skills' => json_encode(['SEO', 'SEM', 'Social Media', 'Google Ads', 'Analytics', 'Content Marketing']),
        'employment_type' => 'full_time',
        'headcount' => 2,
        'salary_min' => 900000,
        'salary_max' => 1400000
    ],
    [
        'code' => 'CONTENT_SPEC',
        'name' => 'Content Marketing Specialist',
        'description' => 'Create marketing content and manage content strategy',
        'dept' => 'dept-013',
        'team' => 'team-content-mktg',
        'designation' => 'SPEC',
        'education' => 'bachelors',
        'education_field' => 'Communications',
        'experience' => 3,
        'skills' => json_encode(['Content Writing', 'Copywriting', 'SEO', 'Social Media', 'WordPress']),
        'employment_type' => 'full_time',
        'headcount' => 3,
        'salary_min' => 500000,
        'salary_max' => 800000
    ],

    // Customer Service Positions
    [
        'code' => 'CS_MGR',
        'name' => 'Customer Service Manager',
        'description' => 'Manage customer service operations and team',
        'dept' => 'dept-014',
        'team' => 'team-cust-sup',
        'designation' => 'MGR',
        'education' => 'bachelors',
        'education_field' => 'Any',
        'experience' => 5,
        'skills' => json_encode(['Customer Service', 'Team Management', 'CRM', 'Conflict Resolution']),
        'employment_type' => 'full_time',
        'headcount' => 2,
        'salary_min' => 700000,
        'salary_max' => 1000000
    ],
    [
        'code' => 'CS_EXEC',
        'name' => 'Customer Service Executive',
        'description' => 'Provide customer support and resolve issues',
        'dept' => 'dept-014',
        'team' => 'team-cust-sup',
        'designation' => 'EXEC',
        'education' => 'bachelors',
        'education_field' => 'Any',
        'experience' => 1,
        'skills' => json_encode(['Customer Service', 'Communication', 'Problem Solving', 'CRM']),
        'employment_type' => 'full_time',
        'headcount' => 15,
        'salary_min' => 250000,
        'salary_max' => 400000
    ],

    // Operations Positions
    [
        'code' => 'OPS_DIR',
        'name' => 'Director of Operations',
        'description' => 'Lead operational strategy and execution',
        'dept' => 'dept-008',
        'team' => null,
        'designation' => 'DIR',
        'education' => 'masters',
        'education_field' => 'Business Administration',
        'experience' => 10,
        'skills' => json_encode(['Operations Management', 'Process Optimization', 'Team Leadership', 'Strategy']),
        'employment_type' => 'full_time',
        'headcount' => 1,
        'salary_min' => 2500000,
        'salary_max' => 4000000
    ],
    [
        'code' => 'OPS_MGR',
        'name' => 'Operations Manager',
        'description' => 'Manage daily operations and process improvement',
        'dept' => 'dept-008',
        'team' => 'team-ops-mgmt',
        'designation' => 'MGR',
        'education' => 'bachelors',
        'education_field' => 'Business',
        'experience' => 5,
        'skills' => json_encode(['Operations Management', 'Process Improvement', 'Project Management', 'Analytics']),
        'employment_type' => 'full_time',
        'headcount' => 3,
        'salary_min' => 900000,
        'salary_max' => 1400000
    ],
];

$created = 0;
$skipped = 0;
$errors = 0;

// Get a sample user ID for created_by field
$userStmt = $pdo->query("SELECT id FROM users LIMIT 1");
$userId = $userStmt->fetchColumn();

if (!$userId) {
    echo "ERROR: No users found in database. Please create a user first." . PHP_EOL;
    exit(1);
}

// Prepare insert statement
$insertStmt = $pdo->prepare("
    INSERT OR IGNORE INTO organization_positions
    (id, code, name, description, organization_department_id, organization_department_team_id,
     organization_designation_id, min_education, min_education_field, min_experience_years,
     skills_required, employment_type, headcount, salary_range_min, salary_range_max,
     salary_currency, is_active, sort_order, created_by, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'INR', 1, ?, ?, datetime('now'))
");

foreach ($positions as $index => $pos) {
    $id = 'pos-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

    // Get designation ID by code
    $desigStmt = $pdo->prepare("SELECT id FROM organization_designations WHERE code = ? LIMIT 1");
    $desigStmt->execute([$pos['designation']]);
    $designationId = $desigStmt->fetchColumn();

    if (!$designationId) {
        echo "  ! Error: Designation '{$pos['designation']}' not found for position '{$pos['name']}'" . PHP_EOL;
        $errors++;
        continue;
    }

    // Get team ID by code if team is specified
    $teamId = null;
    if ($pos['team']) {
        $teamStmt = $pdo->prepare("SELECT id FROM department_teams WHERE id = ? LIMIT 1");
        $teamStmt->execute([$pos['team']]);
        $teamId = $teamStmt->fetchColumn();

        if (!$teamId) {
            echo "  - Warning: Team '{$pos['team']}' not found for position '{$pos['name']}', creating without team" . PHP_EOL;
        }
    }

    try {
        $result = $insertStmt->execute([
            $id,
            $pos['code'],
            $pos['name'],
            $pos['description'],
            $pos['dept'],
            $teamId,
            $designationId,
            $pos['education'],
            $pos['education_field'],
            $pos['experience'],
            $pos['skills'],
            $pos['employment_type'],
            $pos['headcount'],
            $pos['salary_min'],
            $pos['salary_max'],
            ($index + 1) * 10,
            $userId
        ]);

        if ($result && $insertStmt->rowCount() > 0) {
            echo "  + Created: {$pos['name']} ({$pos['code']})" . PHP_EOL;
            $created++;
        } else {
            echo "  - Skipped: {$pos['name']} (already exists)" . PHP_EOL;
            $skipped++;
        }
    } catch (Exception $e) {
        echo "  ! Error creating {$pos['name']}: " . $e->getMessage() . PHP_EOL;
        $errors++;
    }
}

echo PHP_EOL;
echo "=== Seeding Complete ===" . PHP_EOL;
echo "Created: $created positions" . PHP_EOL;
echo "Skipped: $skipped positions (already exist)" . PHP_EOL;
echo "Errors: $errors" . PHP_EOL;
echo PHP_EOL;

// Display summary
$countStmt = $pdo->query("SELECT COUNT(*) as count FROM organization_positions WHERE deleted_at IS NULL");
$total = $countStmt->fetch()['count'];
echo "Total positions in database: $total" . PHP_EOL;
echo PHP_EOL;

// Display sample positions by department
echo "=== Sample Positions by Department ===" . PHP_EOL;
$sampleStmt = $pdo->query("
    SELECT d.name as department, p.name as position, p.code, p.employment_type, p.headcount
    FROM organization_positions p
    LEFT JOIN organization_departments d ON p.organization_department_id = d.id
    WHERE p.deleted_at IS NULL
    ORDER BY d.sort_order, p.sort_order
    LIMIT 20
");
foreach ($sampleStmt->fetchAll() as $row) {
    echo sprintf("- %-30s | %-40s | %s\n", $row['department'], $row['position'], $row['code']);
}
