<?php
require_once __DIR__ . '/../../../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationWorkstationRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$workstationRepo = new OrganizationWorkstationRepository();

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Location: /organizations/departments/facilities/branches/buildings/workstations/?error=Workstation ID is required');
    exit;
}

$workstationId = $_GET['id'];
$isPermanent = isset($_GET['permanent']) && $_GET['permanent'] == '1';

try {
    // Check if user has permission (only Super Admin can permanently delete)
    if ($isPermanent && !$workstationRepo->isSuperAdmin($user->getEmail())) {
        header('Location: /organizations/departments/facilities/branches/buildings/workstations/?error=Access denied. Only Super Admin can permanently delete workstations.');
        exit;
    }

    if ($isPermanent) {
        // Permanent delete (hard delete)
        $result = $workstationRepo->hardDelete($workstationId, $user->getId());
        if ($result) {
            $successMsg = 'Workstation permanently deleted successfully!';
            header('Location: /organizations/departments/facilities/branches/buildings/workstations/?success=' . urlencode($successMsg));
            exit;
        } else {
            throw new Exception('Failed to permanently delete workstation');
        }
    } else {
        // Soft delete (move to trash)
        $result = $workstationRepo->softDelete($workstationId, $user->getId());
        if ($result) {
            $successMsg = 'Workstation moved to trash. You can restore it anytime.';
            header('Location: /organizations/departments/facilities/branches/buildings/workstations/?success=' . urlencode($successMsg));
            exit;
        } else {
            throw new Exception('Failed to delete workstation');
        }
    }
} catch (Exception $e) {
    header('Location: /organizations/departments/facilities/branches/buildings/workstations/?error=' . urlencode($e->getMessage()));
    exit;
}
