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
    $pageTitle = 'Market - ' . htmlspecialchars($currentOrganization['name']);
} else {
    $pageTitle = 'Market - All Organizations';
}

include __DIR__ . '/../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1>Market</h1>
            <?php if ($filterByOrg && $currentOrganization): ?>
                <p class="text-muted">Goods, Services, Needs and Wants from <?php echo htmlspecialchars($currentOrganization['name']); ?></p>
            <?php else: ?>
                <p class="text-muted">Goods, Services, Needs and Wants for Purchase and Rent</p>
            <?php endif; ?>
        </div>
        <?php if ($auth->isLoggedIn()): ?>
            <a href="/market-form.php" class="btn btn-primary">+ Post Listing</a>
        <?php endif; ?>
    </div>

    <!-- Categories -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2 class="card-title">Categories</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
            <button class="btn btn-secondary" style="padding: 1rem;">Goods for Sale</button>
            <button class="btn btn-secondary" style="padding: 1rem;">Services Offered</button>
            <button class="btn btn-secondary" style="padding: 1rem;">Items for Rent</button>
            <button class="btn btn-secondary" style="padding: 1rem;">Needs & Wants</button>
        </div>
    </div>

    <!-- Market Listings -->
    <div class="card">
        <h2 class="card-title">Market Listings</h2>

        <div class="alert alert-info" style="background-color: #e7f3ff; border: 1px solid #b3d9ff; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <strong>Coming Soon!</strong> The market feature is currently under development.
            <?php if ($filterByOrg && $currentOrganization): ?>
                Soon you'll be able to browse goods, services, and listings from <?php echo htmlspecialchars($currentOrganization['name']); ?>.
            <?php else: ?>
                Soon you'll be able to browse goods, services, and listings from all organizations on V4L.
            <?php endif; ?>
        </div>

        <div style="text-align: center; padding: 3rem 1rem; color: var(--text-light);">
            <p>No market listings yet.</p>
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
        <p class="text-muted">Visit an organization's subdomain (e.g., nbs.v4l.app) to see their specific market listings.</p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
