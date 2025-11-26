<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

echo "Seeding Hiring Workflow...\n\n";

try {
    $pdo->beginTransaction();

    $workflowId = 'hiring_workflow_v1';
    $createdAt = date('Y-m-d H:i:s');

    // 1. Create workflow definition
    echo "Creating workflow definition...\n";
    $stmt = $pdo->prepare("
        INSERT OR REPLACE INTO workflows (id, name, description, workflow_type, version, config, is_active, created_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $config = json_encode([
        'description' => 'End-to-end employee recruitment process',
        'total_nodes' => 9,
        'average_duration' => '30-45 days'
    ]);

    $stmt->execute([
        $workflowId,
        'Employee Hiring Workflow',
        'End-to-end recruitment process from vacancy posting to employee onboarding',
        'hiring',
        '1.0',
        $config,
        1,
        'system',
        $createdAt
    ]);

    // 2. Create workflow nodes
    echo "Creating workflow nodes...\n";
    $nodes = [
        ['post_vacancy', 'Post Vacancy', 'OrganizationVacancy', ['HR Recruiter', 'HR Manager'], ['create', 'read', 'update'], '1 day', '2 days', 1],
        ['review_applications', 'Review Applications', 'Application', ['HR Recruiter', 'Hiring Manager'], ['read', 'update', 'approve', 'reject'], '3-5 days', '7 days', 2],
        ['screen_candidates', 'Screen Candidates', 'Screening', ['HR Recruiter'], ['create', 'read', 'update'], '3-5 days', '7 days', 3],
        ['technical_interview', 'Technical Interview', 'Interview', ['Technical Lead', 'Senior Engineer', 'Department Manager'], ['create', 'read', 'update', 'approve', 'reject'], '5-7 days', '10 days', 4],
        ['hr_interview', 'HR Interview', 'Interview', ['HR Manager', 'Department Head'], ['create', 'read', 'update', 'approve', 'reject'], '3-5 days', '7 days', 5],
        ['final_interview', 'Final Interview', 'Interview', ['Department Head', 'CEO', 'CTO'], ['create', 'read', 'update', 'approve', 'reject'], '5-7 days', '10 days', 6],
        ['make_offer', 'Make Offer', 'Offer', ['HR Manager'], ['create', 'read', 'update', 'publish'], '2-3 days', '5 days', 7],
        ['await_response', 'Await Candidate Response', 'Offer', ['HR Recruiter', 'HR Manager'], ['read', 'update'], '5-7 days', '14 days', 8],
        ['onboarding', 'Onboarding', 'User', ['HR Manager', 'IT Admin', 'Department Manager'], ['create', 'read', 'update'], '5-10 days', '15 days', 9]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO workflow_nodes (id, workflow_id, node_id, label, entity_type, required_positions, allowed_actions, estimated_duration, sla, sort_order, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($nodes as $index => $node) {
        $nodeDbId = $workflowId . '_node_' . ($index + 1);
        $stmt->execute([
            $nodeDbId,
            $workflowId,
            $node[0], // node_id
            $node[1], // label
            $node[2], // entity_type
            json_encode($node[3]), // required_positions
            json_encode($node[4]), // allowed_actions
            $node[5], // estimated_duration
            $node[6], // sla
            $node[7], // sort_order
            $createdAt
        ]);
    }

    // 3. Create workflow edges
    echo "Creating workflow edges...\n";
    $edges = [
        ['post_vacancy', 'review_applications', 'vacancy_posted', 'Vacancy Posted', 1, 'solid'],
        ['review_applications', 'screen_candidates', 'candidates_shortlisted', 'Candidates Shortlisted', 1, 'solid'],
        ['review_applications', 'post_vacancy', 'no_suitable_candidates', 'Re-post Vacancy', 2, 'dashed'],
        ['screen_candidates', 'technical_interview', 'screening_passed', 'Screening Passed', 1, 'solid'],
        ['screen_candidates', 'review_applications', 'screening_failed', 'Failed - Review More', 2, 'dashed'],
        ['technical_interview', 'hr_interview', 'technical_approved', 'Technical Approved', 1, 'solid'],
        ['technical_interview', 'review_applications', 'technical_rejected', 'Rejected', 2, 'dashed'],
        ['hr_interview', 'make_offer', 'hr_approved', 'HR Approved', 1, 'solid'],
        ['hr_interview', 'final_interview', 'final_round_needed', 'Final Round Required', 2, 'dotted'],
        ['hr_interview', 'review_applications', 'hr_rejected', 'Rejected', 3, 'dashed'],
        ['final_interview', 'make_offer', 'final_approved', 'Final Approved', 1, 'solid'],
        ['final_interview', 'review_applications', 'final_rejected', 'Rejected', 2, 'dashed'],
        ['make_offer', 'await_response', 'offer_sent', 'Offer Sent', 1, 'solid'],
        ['await_response', 'onboarding', 'offer_accepted', 'Offer Accepted', 1, 'solid'],
        ['await_response', 'review_applications', 'offer_rejected', 'Offer Rejected', 2, 'dashed'],
        ['await_response', 'make_offer', 'candidate_negotiating', 'Negotiation', 3, 'dotted']
    ];

    $stmt = $pdo->prepare("
        INSERT INTO workflow_edges (id, workflow_id, source_node_id, target_node_id, condition, label, priority, style, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($edges as $index => $edge) {
        $edgeId = $workflowId . '_edge_' . ($index + 1);
        $stmt->execute([
            $edgeId,
            $workflowId,
            $edge[0], // source_node_id
            $edge[1], // target_node_id
            $edge[2], // condition
            $edge[3], // label
            $edge[4], // priority
            $edge[5], // style
            $createdAt
        ]);
    }

    $pdo->commit();

    echo "\n✓ Hiring workflow seeded successfully!\n";
    echo "\nWorkflow: Employee Hiring Workflow (v1.0)\n";
    echo "  - 9 nodes created\n";
    echo "  - 16 edges created\n";
    echo "  - Ready to create workflow instances\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "\n✗ Error during seeding: " . $e->getMessage() . "\n";
    exit(1);
}
