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

try {
    $result = $orgRepo->restore($id, $user->getId());
    if ($result) {
        header('Location: /organizations/?success=' . urlencode('Organization restored successfully!'));
    } else {
        header('Location: /organizations/?error=' . urlencode('Failed to restore organization'));
    }
} catch (Exception $e) {
    header('Location: /organizations/?error=' . urlencode($e->getMessage()));
}
exit;
