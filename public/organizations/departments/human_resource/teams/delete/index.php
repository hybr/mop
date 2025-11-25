<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\Authorization;
use App\Classes\OrganizationDepartmentTeamRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$teamRepo = new OrganizationDepartmentTeamRepository();

// Only Super Admin can delete
Authorization::requireSuperAdmin($user->getEmail(), 'delete teams');

// Get team ID
if (!isset($_GET['id'])) {
    header('Location: /organizations/departments/human_resource/teams/?error=' . urlencode('Team ID required'));
    exit;
}

$team = $teamRepo->findById($_GET['id']);
if (!$team) {
    header('Location: /organizations/departments/human_resource/teams/?error=' . urlencode('Team not found'));
    exit;
}

try {
    $teamRepo->softDelete($team->getId(), $user->getId(), $user->getEmail());
    header('Location: /organizations/departments/human_resource/teams/?success=' . urlencode('Team "' . $team->getName() . '" has been moved to trash'));
} catch (Exception $e) {
    header('Location: /organizations/departments/human_resource/teams/?error=' . urlencode($e->getMessage()));
}
exit;
