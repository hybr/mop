<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationEntityPermissionRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$permissionRepo = new OrganizationEntityPermissionRepository();

// Only Super Admin can restore
if (!$permissionRepo->isSuperAdmin($user->getEmail())) {
    header('Location: /organizations/departments/information_technology/entity_permissions/?error=' . urlencode('Only Super Admin can restore permissions'));
    exit;
}

// Get permission ID
if (!isset($_GET['id'])) {
    header('Location: /organizations/departments/information_technology/entity_permissions/?error=' . urlencode('Permission ID required'));
    exit;
}

try {
    $result = $permissionRepo->restore($_GET['id'], $user->getId());
    if ($result) {
        header('Location: /organizations/departments/information_technology/entity_permissions/?success=' . urlencode('Permission has been restored'));
    } else {
        header('Location: /organizations/departments/information_technology/entity_permissions/?error=' . urlencode('Failed to restore permission'));
    }
} catch (Exception $e) {
    header('Location: /organizations/departments/information_technology/entity_permissions/?error=' . urlencode($e->getMessage()));
}
exit;
