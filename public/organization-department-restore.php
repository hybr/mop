<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationDepartmentRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$deptRepo = new OrganizationDepartmentRepository();

// Check if user is Super Admin
if (!$deptRepo->isSuperAdmin($user->getEmail())) {
    header('Location: /organization-departments.php?error=' . urlencode('Only Super Admin can restore departments'));
    exit;
}

// Get department ID from URL
if (!isset($_GET['id'])) {
    header('Location: /organization-departments.php?error=' . urlencode('Department ID is required'));
    exit;
}

try {
    // Restore the department
    $success = $deptRepo->restore($_GET['id'], $user->getEmail());

    if ($success) {
        header('Location: /organization-departments.php?success=' . urlencode('Department restored successfully'));
    } else {
        throw new Exception('Failed to restore department');
    }

} catch (Exception $e) {
    header('Location: /organization-departments.php?error=' . urlencode($e->getMessage()));
}
exit;