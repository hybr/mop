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
$isPermanent = isset($_GET['permanent']) && $_GET['permanent'] == '1';

try {
    // Find the department
    $department = $facilityDeptRepo->findById($departmentId);

    if (!$department && !$isPermanent) {
        throw new Exception("Facility department not found");
    }

    // Check permissions
    if ($isPermanent) {
        // Permanent delete - only Super Admin
        if (!$facilityDeptRepo->isSuperAdmin($user->getEmail())) {
            throw new Exception("Only Super Admin can permanently delete facility departments");
        }

        $success = $facilityDeptRepo->hardDelete($departmentId, $user->getEmail());

        if ($success) {
            header('Location: /organizations/facilities?success=Facility department permanently deleted');
        } else {
            throw new Exception("Failed to permanently delete facility department");
        }
    } else {
        // Soft delete - only Super Admin
        if (!$facilityDeptRepo->isSuperAdmin($user->getEmail())) {
            throw new Exception("Only Super Admin can delete facility departments");
        }

        $success = $facilityDeptRepo->softDelete($departmentId, $user->getId(), $user->getEmail());

        if ($success) {
            header('Location: /organizations/facilities?success=Facility department moved to trash');
        } else {
            throw new Exception("Failed to delete facility department");
        }
    }
} catch (Exception $e) {
    header('Location: /organizations/facilities?error=' . urlencode($e->getMessage()));
}

exit;
