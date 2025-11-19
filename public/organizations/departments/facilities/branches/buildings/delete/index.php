<?php
require_once __DIR__ . "/../../../../../../../src/includes/autoload.php";
use App\Classes\Auth;
use App\Classes\OrganizationBuildingRepository;
$auth = new Auth(); $auth->requireAuth();
$user = $auth->getCurrentUser();
$buildingRepo = new OrganizationBuildingRepository();
if (!isset($_GET["id"])) { header("Location: /organizations/departments/facilities/branches/buildings/?error=No building specified"); exit; }
$id = $_GET["id"];
try {
    if (!$buildingRepo->isSuperAdmin($user->getEmail())) { throw new \Exception("Only Super Admin can delete buildings"); }
    $buildingRepo->softDelete($id, $user->getId(), $user->getEmail());
    header("Location: /organizations/departments/facilities/branches/buildings/?success=Building deleted");
} catch (Exception $e) {
    header("Location: /organizations/departments/facilities/branches/buildings/?error=" . urlencode($e->getMessage()));
}
exit;
