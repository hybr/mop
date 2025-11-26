<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationEntityPermissionRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$permissionRepo = new OrganizationEntityPermissionRepository();

// Only Super Admin can delete
if (!$permissionRepo->isSuperAdmin($user->getEmail())) {
    header('Location: /organizations/departments/information_technology/entity_permissions/?error=' . urlencode('Only Super Admin can delete permissions'));
    exit;
}

// Get permission ID
if (!isset($_GET['id'])) {
    header('Location: /organizations/departments/information_technology/entity_permissions/?error=' . urlencode('Permission ID required'));
    exit;
}

$permission = $permissionRepo->findById($_GET['id']);
if (!$permission) {
    header('Location: /organizations/departments/information_technology/entity_permissions/?error=' . urlencode('Permission not found'));
    exit;
}

try {
    $permissionRepo->softDelete($permission->getId(), $user->getId());
    header('Location: /organizations/departments/information_technology/entity_permissions/?success=' . urlencode('Permission has been deleted'));
} catch (Exception $e) {
    header('Location: /organizations/departments/information_technology/entity_permissions/?error=' . urlencode($e->getMessage()));
}
exit;
