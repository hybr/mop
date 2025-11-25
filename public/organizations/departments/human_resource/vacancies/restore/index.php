<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationVacancyRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$vacancyRepo = new OrganizationVacancyRepository();

// Get vacancy ID from query string
$vacancyId = $_GET['id'] ?? null;

if (!$vacancyId) {
    header('Location: /organizations/departments/human_resource/vacancies/?error=' . urlencode('Vacancy ID is required'));
    exit;
}

try {
    // Restore the vacancy
    $result = $vacancyRepo->restore($vacancyId, $user->getId());

    if ($result) {
        header('Location: /organizations/departments/human_resource/vacancies/?success=' . urlencode('Vacancy restored successfully'));
    } else {
        header('Location: /organizations/departments/human_resource/vacancies/?error=' . urlencode('Failed to restore vacancy'));
    }

} catch (Exception $e) {
    header('Location: /organizations/departments/human_resource/vacancies/?error=' . urlencode($e->getMessage()));
}

exit;
