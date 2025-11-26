<?php
require_once __DIR__ . '/../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\WorkflowEngine;
use App\Classes\OrganizationVacancyRepository;
use App\Classes\OrganizationPositionRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$workflowEngine = new WorkflowEngine();

// Get parameters
$entityType = $_GET['entity_type'] ?? '';
$entityId = $_GET['entity_id'] ?? '';
$workflowId = $_GET['workflow_id'] ?? 'hiring_workflow_v1';

// Validate
if (empty($entityType) || empty($entityId)) {
    header('Location: /organizations/departments/?error=' . urlencode('Missing entity information'));
    exit;
}

// For hiring workflow, entity should be OrganizationVacancy
if ($entityType === 'OrganizationVacancy') {
    $vacancyRepo = new OrganizationVacancyRepository();
    $positionRepo = new OrganizationPositionRepository();

    $vacancy = $vacancyRepo->findById($entityId, $user->getId());
    if (!$vacancy) {
        header('Location: /organizations/departments/human_resource/vacancies/?error=' . urlencode('Vacancy not found'));
        exit;
    }

    // Get position details
    $position = $vacancy->getOrganizationPositionId() ?
                $positionRepo->findById($vacancy->getOrganizationPositionId()) : null;

    $positionName = $position ? $position->getName() : 'Unknown Position';

    // Check if workflow already exists for this vacancy
    $instanceRepo = new \App\Classes\WorkflowInstanceRepository();
    $existingInstances = $instanceRepo->findByEntity($entityType, $entityId);

    $activeInstances = array_filter($existingInstances, function($inst) {
        return $inst->getStatus() === 'active';
    });

    if (!empty($activeInstances)) {
        $existingInstance = $activeInstances[0];
        header('Location: /organizations/departments/human_resource/hiring/instances/view/?id=' .
               $existingInstance->getId() . '&error=' . urlencode('Workflow already active for this vacancy'));
        exit;
    }

    try {
        // Start the workflow
        $instanceName = "Hiring: {$positionName} - Vacancy #{$vacancy->getId()}";

        $instance = $workflowEngine->startWorkflow(
            $workflowId,
            $instanceName,
            $entityId,
            $entityType,
            $user->getId()
        );

        // Redirect to workflow instance view
        header('Location: /organizations/departments/human_resource/hiring/instances/?success=' .
               urlencode('Hiring workflow started successfully! Tasks have been assigned.'));
        exit;

    } catch (Exception $e) {
        header('Location: /organizations/departments/human_resource/vacancies/view/?id=' . $entityId .
               '&error=' . urlencode('Failed to start workflow: ' . $e->getMessage()));
        exit;
    }
}

// For other entity types (future workflows)
header('Location: /organizations/departments/?error=' . urlencode('Unsupported entity type for workflow'));
exit;
