<?php
require_once __DIR__ . '/../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$orgRepo = new OrganizationRepository();

$errors = [];
$success = false;

// Handle organization selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['organization_id'])) {
    try {
        $auth->setCurrentOrganization($_POST['organization_id']);

        // Redirect to the intended page or dashboard
        $redirect = $_GET['redirect'] ?? '/organizations/';
        header('Location: ' . $redirect);
        exit;
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// Get user's organizations
$organizations = $orgRepo->findAllByUser($user->getId());

// If user has no organizations, redirect to create one
if (empty($organizations)) {
    header('Location: /organizations/form/?message=' . urlencode('Please create an organization first'));
    exit;
}

// If user has only one organization, auto-select it
if (count($organizations) === 1) {
    $auth->setCurrentOrganization($organizations[0]->getId());
    $redirect = $_GET['redirect'] ?? '/organizations/';
    header('Location: ' . $redirect);
    exit;
}

$pageTitle = 'Select Organization';
include __DIR__ . '/../../views/header.php';
?>

<div class="py-4">
    <div style="max-width: 800px; margin: 0 auto;">
        <h1 style="margin-bottom: 1rem;">Select Organization</h1>
        <p style="color: var(--text-light); margin-bottom: 2rem;">
            Choose which organization you want to work with in this session.
        </p>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div style="background-color: rgba(244, 67, 54, 0.1); border-left: 4px solid #f44336; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
                <strong style="color: #f44336;">&#x2715; Error!</strong>
                <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2 style="margin-bottom: 1.5rem;">Your Organizations</h2>

            <div style="display: grid; gap: 1rem;">
                <?php foreach ($organizations as $org): ?>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="organization_id" value="<?php echo $org->getId(); ?>">
                        <button type="submit" class="organization-card">
                            <div class="organization-header">
                                <h3 style="margin: 0; color: var(--primary-color);">
                                    <?php echo htmlspecialchars($org->getName()); ?>
                                </h3>
                                <?php if ($org->getLegalStructure()): ?>
                                    <span class="badge" style="background-color: var(--bg-light); color: var(--text-light); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">
                                        <?php echo htmlspecialchars($org->getLegalStructure()); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if ($org->getDescription()): ?>
                                <p style="color: var(--text-light); margin: 0.5rem 0 0 0;">
                                    <?php echo htmlspecialchars($org->getDescription()); ?>
                                </p>
                            <?php endif; ?>

                            <div style="margin-top: 1rem; font-size: 0.875rem; color: var(--text-light);">
                                <?php if ($org->getSubdomain()): ?>
                                    <span>Subdomain: <?php echo htmlspecialchars($org->getSubdomain()); ?>.v4l.app</span>
                                <?php endif; ?>
                                <?php if ($org->getWebsite()): ?>
                                    <span style="margin-left: 1rem;">
                                        Website: <?php echo htmlspecialchars($org->getWebsite()); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color); text-align: right;">
                                <span style="color: var(--primary-color); font-weight: 500;">
                                    Select this organization &rarr;
                                </span>
                            </div>
                        </button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="margin-top: 2rem; text-align: center;">
            <a href="/organizations/" class="btn btn-secondary" style="padding: 0.75rem 2rem; text-decoration: none;">
                Manage All Organizations
            </a>
        </div>
    </div>
</div>

<style>
.organization-card {
    width: 100%;
    text-align: left;
    padding: 1.5rem;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    background-color: var(--bg-color);
    cursor: pointer;
    transition: all 0.2s ease;
}

.organization-card:hover {
    border-color: var(--primary-color);
    background-color: rgba(33, 150, 243, 0.02);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.organization-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

@media (max-width: 768px) {
    .organization-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php include __DIR__ . '/../../views/footer.php'; ?>
