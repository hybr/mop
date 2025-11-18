<?php
require_once __DIR__ . "/../src/includes/autoload.php";
use App\Classes\Auth;
use App\Classes\OrganizationBranchRepository;
$auth = new Auth(); $auth->requireAuth();
$user = $auth->getCurrentUser();
$branchRepo = new OrganizationBranchRepository();
if (!isset($_GET["id"])) { header("Location: /organizations-facilities-branches.php?error=No branch specified"); exit; }
$id = $_GET["id"];
try {
    if (!$branchRepo->isSuperAdmin($user->getEmail())) { throw new \Exception("Only Super Admin can delete branches"); }
    $branchRepo->softDelete($id, $user->getId(), $user->getEmail());
    header("Location: /organizations-facilities-branches.php?success=Branch deleted");
} catch (Exception $e) {
    header("Location: /organizations-facilities-branches.php?error=" . urlencode($e->getMessage()));
}
exit;
