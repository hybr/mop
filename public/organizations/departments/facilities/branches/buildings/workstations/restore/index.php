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

try {
    // Restore workstation
    $result = $workstationRepo->restore($workstationId, $user->getId());

    if ($result) {
        $successMsg = 'Workstation restored successfully!';
        header('Location: /organizations/departments/facilities/branches/buildings/workstations/?success=' . urlencode($successMsg));
        exit;
    } else {
        throw new Exception('Failed to restore workstation');
    }
} catch (Exception $e) {
    header('Location: /organizations/departments/facilities/branches/buildings/workstations/?error=' . urlencode($e->getMessage()));
    exit;
}
