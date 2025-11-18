<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\FacilityTeamRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$facilityTeamRepo = new FacilityTeamRepository();

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Location: /organizations/facilities?error=No team specified');
    exit;
}

$teamId = $_GET['id'];

try {
    // Check permissions - only Super Admin can restore
    if (!$facilityTeamRepo->isSuperAdmin($user->getEmail())) {
        throw new Exception("Only Super Admin can restore facility teams");
    }

    $success = $facilityTeamRepo->restore($teamId, $user->getEmail());

    if ($success) {
        header('Location: /organizations/facilities?success=Facility team restored successfully');
    } else {
        throw new Exception("Failed to restore facility team");
    }
} catch (Exception $e) {
    header('Location: /organizations/facilities?error=' . urlencode($e->getMessage()));
}

exit;
