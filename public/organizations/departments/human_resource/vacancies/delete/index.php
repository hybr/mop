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
    // Verify user owns this vacancy
    $vacancy = $vacancyRepo->findById($vacancyId, $user->getId());
    if (!$vacancy) {
        header('Location: /organizations/departments/human_resource/vacancies/?error=' . urlencode('Vacancy not found or access denied'));
        exit;
    }

    // Soft delete the vacancy
    $result = $vacancyRepo->softDelete($vacancyId, $user->getId());

    if ($result) {
        header('Location: /organizations/departments/human_resource/vacancies/?success=' . urlencode('Vacancy moved to trash. You can restore it anytime.'));
    } else {
        header('Location: /organizations/departments/human_resource/vacancies/?error=' . urlencode('Failed to delete vacancy'));
    }

} catch (Exception $e) {
    header('Location: /organizations/departments/human_resource/vacancies/?error=' . urlencode($e->getMessage()));
}

exit;
