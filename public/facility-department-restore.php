<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\FacilityDepartmentRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$facilityDeptRepo = new FacilityDepartmentRepository();

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Location: /organizations/facilities?error=No department specified');
    exit;
}

$departmentId = $_GET['id'];

try {
    // Check permissions - only Super Admin can restore
    if (!$facilityDeptRepo->isSuperAdmin($user->getEmail())) {
        throw new Exception("Only Super Admin can restore facility departments");
    }

    $success = $facilityDeptRepo->restore($departmentId, $user->getEmail());

    if ($success) {
        header('Location: /organizations/facilities?success=Facility department restored successfully');
    } else {
        throw new Exception("Failed to restore facility department");
    }
} catch (Exception $e) {
    header('Location: /organizations/facilities?error=' . urlencode($e->getMessage()));
}

exit;
