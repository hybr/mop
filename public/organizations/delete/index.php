<?php
require_once __DIR__ . '/../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$orgRepo = new OrganizationRepository();

if (!isset($_GET['id'])) {
    header('Location: /organizations/?error=' . urlencode('Organization ID is required'));
    exit;
}

$id = $_GET['id'];
$permanent = isset($_GET['permanent']) && $_GET['permanent'] == '1';

try {
    if ($permanent) {
        // Permanent delete
        $result = $orgRepo->hardDelete($id, $user->getId());
        if ($result) {
            $message = 'Organization permanently deleted';
            header('Location: /organizations/?success=' . urlencode($message));
        } else {
            header('Location: /organizations/?error=' . urlencode('Failed to delete organization'));
        }
    } else {
        // Soft delete
        $result = $orgRepo->softDelete($id, $user->getId());
        if ($result) {
            $message = 'Organization moved to trash. You can restore it anytime.';
            header('Location: /organizations/?success=' . urlencode($message));
        } else {
            header('Location: /organizations/?error=' . urlencode('Failed to delete organization'));
        }
    }
} catch (Exception $e) {
    header('Location: /organizations/?error=' . urlencode($e->getMessage()));
}
exit;
