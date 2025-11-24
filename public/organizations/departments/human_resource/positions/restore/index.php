<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationPositionRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$positionRepo = new OrganizationPositionRepository();

// Only Super Admin can restore
if (!$positionRepo->isSuperAdmin($user->getEmail())) {
    header('Location: /organizations/departments/human_resource/positions/?error=' . urlencode('Only Super Admin can restore positions'));
    exit;
}

// Get position ID
if (!isset($_GET['id'])) {
    header('Location: /organizations/departments/human_resource/positions/?error=' . urlencode('Position ID required'));
    exit;
}

try {
    $result = $positionRepo->restore($_GET['id'], $user->getEmail());
    if ($result) {
        header('Location: /organizations/departments/human_resource/positions/?success=' . urlencode('Position has been restored'));
    } else {
        header('Location: /organizations/departments/human_resource/positions/?error=' . urlencode('Failed to restore position'));
    }
} catch (Exception $e) {
    header('Location: /organizations/departments/human_resource/positions/?error=' . urlencode($e->getMessage()));
}
exit;
