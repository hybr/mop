<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\Authorization;
use App\Classes\OrganizationDepartmentTeamRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$teamRepo = new OrganizationDepartmentTeamRepository();

// Only Super Admin can restore
Authorization::requireSuperAdmin($user->getEmail(), 'restore teams');

// Get team ID
if (!isset($_GET['id'])) {
    header('Location: /organizations/departments/human_resource/teams/?error=' . urlencode('Team ID required'));
    exit;
}

try {
    $result = $teamRepo->restore($_GET['id'], $user->getEmail());
    if ($result) {
        header('Location: /organizations/departments/human_resource/teams/?success=' . urlencode('Team has been restored'));
    } else {
        header('Location: /organizations/departments/human_resource/teams/?error=' . urlencode('Failed to restore team'));
    }
} catch (Exception $e) {
    header('Location: /organizations/departments/human_resource/teams/?error=' . urlencode($e->getMessage()));
}
exit;
