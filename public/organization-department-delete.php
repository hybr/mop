<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationDepartmentRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$deptRepo = new OrganizationDepartmentRepository();

// Check if user is Super Admin
if (!$deptRepo->canDelete($user->getEmail())) {
    header('Location: /organization-departments.php?error=' . urlencode('Only Super Admin can delete departments'));
    exit;
}

// Get department ID from URL
if (!isset($_GET['id'])) {
    header('Location: /organization-departments.php?error=' . urlencode('Department ID is required'));
    exit;
}

try {
    $department = $deptRepo->findById($_GET['id']);

    if (!$department) {
        throw new Exception('Department not found');
    }

    $deptName = $department->getName();

    // Soft delete the department
    $success = $deptRepo->softDelete($_GET['id'], $user->getId(), $user->getEmail());

    if ($success) {
        header('Location: /organization-departments.php?success=' . urlencode('Department "' . $deptName . '" deleted successfully'));
    } else {
        throw new Exception('Failed to delete department');
    }

} catch (Exception $e) {
    header('Location: /organization-departments.php?error=' . urlencode($e->getMessage()));
}
exit;