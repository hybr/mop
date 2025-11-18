<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationRepository;

$auth = new Auth();
$orgRepo = new OrganizationRepository();

// Subdomain detection logic
$host = $_SERVER['HTTP_HOST'] ?? '';
$subdomain = null;
$currentOrganization = null;
$filterByOrg = false;

// Extract subdomain if present (e.g., nbs.v4l.app -> nbs)
if (preg_match('/^([^.]+)\.v4l\.app$/i', $host, $matches)) {
    $subdomain = $matches[1];
    $currentOrganization = $orgRepo->findBySubdomainPublic($subdomain);
    $filterByOrg = true;
}

// Set page title based on context
if ($filterByOrg && $currentOrganization) {
    $pageTitle = 'Vacancies - ' . htmlspecialchars($currentOrganization['name']);
} else {
    $pageTitle = 'Vacancies - All Organizations';
}

include __DIR__ . '/../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1>Vacancies</h1>
            <?php if ($filterByOrg && $currentOrganization): ?>
                <p class="text-muted">Showing open positions from <?php echo htmlspecialchars($currentOrganization['name']); ?></p>
            <?php else: ?>
                <p class="text-muted">Browse open positions from all organizations</p>
            <?php endif; ?>
        </div>
        <?php if ($auth->isLoggedIn()): ?>
            <a href="/vacancy-form.php" class="btn btn-primary">+ Post Vacancy</a>
        <?php endif; ?>
    </div>

    <!-- Vacancies List -->
    <div class="card">
        <h2 class="card-title">Open Positions</h2>

        <div class="alert alert-info" style="background-color: #e7f3ff; border: 1px solid #b3d9ff; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <strong>Coming Soon!</strong> The vacancies feature is currently under development.
            <?php if ($filterByOrg && $currentOrganization): ?>
                Soon you'll be able to browse job openings from <?php echo htmlspecialchars($currentOrganization['name']); ?>.
            <?php else: ?>
                Soon you'll be able to browse job openings from all organizations on V4L.
            <?php endif; ?>
        </div>

        <div style="text-align: center; padding: 3rem 1rem; color: var(--text-light);">
            <p>No vacancies posted yet.</p>
            <?php if ($auth->isLoggedIn()): ?>
                <p style="margin-top: 1rem;">
                    <a href="/organizations.php" class="btn btn-secondary">Go to My Organizations</a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Options (Placeholder) -->
    <?php if (!$filterByOrg): ?>
    <div class="card" style="margin-top: 2rem;">
        <h3 class="card-title">Filter by Organization</h3>
        <p class="text-muted">Visit an organization's subdomain (e.g., nbs.v4l.app) to see their specific vacancies.</p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
