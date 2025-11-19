<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\FacilityTeamRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$facilityTeamRepo = new FacilityTeamRepository();

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Location: /organizations/departments/facilities/teams?error=No team specified');
    exit;
}

$teamId = $_GET['id'];
$isPermanent = isset($_GET['permanent']) && $_GET['permanent'] == '1';

try {
    // Find the team
    $team = $facilityTeamRepo->findById($teamId);

    if (!$team && !$isPermanent) {
        throw new Exception("Facility team not found");
    }

    // Check permissions
    if ($isPermanent) {
        // Permanent delete - only Super Admin
        if (!$facilityTeamRepo->isSuperAdmin($user->getEmail())) {
            throw new Exception("Only Super Admin can permanently delete facility teams");
        }

        $success = $facilityTeamRepo->hardDelete($teamId, $user->getEmail());

        if ($success) {
            header('Location: /organizations/departments/facilities/teams?success=Facility team permanently deleted');
        } else {
            throw new Exception("Failed to permanently delete facility team");
        }
    } else {
        // Soft delete - only Super Admin
        if (!$facilityTeamRepo->isSuperAdmin($user->getEmail())) {
            throw new Exception("Only Super Admin can delete facility teams");
        }

        $success = $facilityTeamRepo->softDelete($teamId, $user->getId(), $user->getEmail());

        if ($success) {
            header('Location: /organizations/departments/facilities/teams?success=Facility team moved to trash');
        } else {
            throw new Exception("Failed to delete facility team");
        }
    }
} catch (Exception $e) {
    header('Location: /organizations/departments/facilities/teams?error=' . urlencode($e->getMessage()));
}

exit;
