<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationPositionRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$positionRepo = new OrganizationPositionRepository();

// Only Super Admin can delete
if (!$positionRepo->isSuperAdmin($user->getEmail())) {
    header('Location: /organizations/departments/human_resource/positions/?error=' . urlencode('Only Super Admin can delete positions'));
    exit;
}

// Get position ID
if (!isset($_GET['id'])) {
    header('Location: /organizations/departments/human_resource/positions/?error=' . urlencode('Position ID required'));
    exit;
}

$position = $positionRepo->findById($_GET['id']);
if (!$position) {
    header('Location: /organizations/departments/human_resource/positions/?error=' . urlencode('Position not found'));
    exit;
}

try {
    $positionRepo->softDelete($position->getId(), $user->getId(), $user->getEmail());
    header('Location: /organizations/departments/human_resource/positions/?success=' . urlencode('Position "' . $position->getName() . '" has been moved to trash'));
} catch (Exception $e) {
    header('Location: /organizations/departments/human_resource/positions/?error=' . urlencode($e->getMessage()));
}
exit;
