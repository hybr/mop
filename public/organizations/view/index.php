<?php
require_once __DIR__ . '/../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationRepository;

$auth = new Auth();
$orgRepo = new OrganizationRepository();

$error = '';
$organization = null;
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $isLoggedIn ? $auth->getCurrentUser() : null;

// Get organization by ID or subdomain
if (isset($_GET['id'])) {
    $organization = $orgRepo->findByIdPublic($_GET['id']);
} elseif (isset($_GET['subdomain'])) {
    $organization = $orgRepo->findBySubdomainPublic($_GET['subdomain']);
}

if (!$organization) {
    $error = 'Organization not found or is not active';
}

$pageTitle = $organization ? ($organization['short_name'] . ($organization['legal_structure'] ? ' ' . $organization['legal_structure'] : '')) : 'Organization Not Found';
include __DIR__ . '/../../../views/header.php';
?>

<div class="py-4">
    <?php if ($error): ?>
        <div class="card" style="max-width: 800px; margin: 0 auto; text-align: center; padding: 3rem;">
            <h1>😕 Organization Not Found</h1>
            <p class="text-muted" style="margin-top: 1rem;"><?php echo htmlspecialchars($error); ?></p>
            <div style="margin-top: 2rem;">
                <a href="/" class="btn btn-primary">Go to Home</a>
                <?php if (!$isLoggedIn): ?>
                    <a href="/auth/login/" class="btn btn-secondary" style="margin-left: 1rem;">Login</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Organization Header -->
        <div class="card" style="max-width: 1200px; margin: 0 auto;">
            <div style="display: flex; align-items: start; gap: 2rem; flex-wrap: wrap;">
                <?php if ($organization['logo_url']): ?>
                    <div style="flex-shrink: 0;">
                        <img src="<?php echo htmlspecialchars($organization['logo_url']); ?>" alt="Logo" style="width: 120px; height: 120px; object-fit: cover; border-radius: 12px; border: 2px solid var(--border-color);">
                    </div>
                <?php endif; ?>

                <div style="flex: 1; min-width: 250px;">
                    <h1 style="margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($organization['short_name']); ?>
                        <?php if ($organization['legal_structure']): ?>
                            <span style="color: var(--text-light); font-weight: 400; font-size: 0.9em;">
                                <?php echo htmlspecialchars($organization['legal_structure']); ?>
                            </span>
                        <?php endif; ?>
                    </h1>

                    <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
                        <a href="https://<?php echo htmlspecialchars($organization['subdomain']); ?>.v4l.app" target="_blank" class="link" style="display: flex; align-items: center; gap: 0.5rem;">
                            🔗 <?php echo htmlspecialchars($organization['subdomain']); ?>.v4l.app
                        </a>

                        <?php if ($organization['website']): ?>
                            <a href="<?php echo htmlspecialchars($organization['website']); ?>" target="_blank" class="link" style="display: flex; align-items: center; gap: 0.5rem;">
                                🌐 Website
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($organization['description']): ?>
                        <p style="margin-top: 1.5rem; color: var(--text-light); line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($organization['description'])); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Organization Details -->
        <div class="card" style="max-width: 1200px; margin: 2rem auto 0;">
            <h2 class="card-title">About This Organization</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 1.5rem;">
                <!-- Basic Information -->
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--text-light);">Basic Information</h3>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div>
                            <p class="text-muted text-small" style="margin-bottom: 0.25rem;">Organization Name</p>
                            <p style="font-weight: 500;">
                                <?php echo htmlspecialchars($organization['short_name']); ?>
                                <?php if ($organization['legal_structure']): ?>
                                    <?php echo htmlspecialchars($organization['legal_structure']); ?>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div>
                            <p class="text-muted text-small" style="margin-bottom: 0.25rem;">Subdomain</p>
                            <p style="font-weight: 500;"><?php echo htmlspecialchars($organization['subdomain']); ?>.v4l.app</p>
                        </div>

                        <div>
                            <p class="text-muted text-small" style="margin-bottom: 0.25rem;">Status</p>
                            <span style="display: inline-block; padding: 0.25rem 0.75rem; background: var(--secondary-color); color: white; border-radius: 12px; font-size: 0.875rem; font-weight: 500;">
                                Active
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--text-light);">Contact Information</h3>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php if ($organization['website']): ?>
                            <div>
                                <p class="text-muted text-small" style="margin-bottom: 0.25rem;">Website</p>
                                <a href="<?php echo htmlspecialchars($organization['website']); ?>" target="_blank" class="link">
                                    <?php echo htmlspecialchars($organization['website']); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (!$organization['website']): ?>
                            <p class="text-muted">No contact information available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions (if logged in and authorized) -->
        <?php if ($isLoggedIn): ?>
            <?php
            $canEdit = $orgRepo->canEdit($organization['id'], $currentUser->getId(), $currentUser->getEmail());
            ?>
            <?php if ($canEdit): ?>
                <div class="card" style="max-width: 1200px; margin: 2rem auto 0; background: var(--bg-light);">
                    <p class="text-muted text-small" style="margin-bottom: 1rem;">
                        <?php if ($orgRepo->isSuperAdmin($currentUser->getEmail())): ?>
                            🔑 You are the Super Admin and can edit this organization
                        <?php else: ?>
                            ✏️ You created this organization and can edit it
                        <?php endif; ?>
                    </p>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="/organizations/form/?id=<?php echo $organization['id']; ?>" class="btn btn-primary">
                            Edit Organization
                        </a>
                        <a href="/organizations/" class="btn btn-secondary">
                            My Organizations
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Guest CTA -->
            <div class="card" style="max-width: 1200px; margin: 2rem auto 0; background: var(--bg-light); text-align: center;">
                <h3 style="margin-bottom: 1rem;">Want to create your own organization?</h3>
                <p class="text-muted" style="margin-bottom: 1.5rem;">
                    Join V4L and get your own subdomain at subdomain.v4l.app
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="/auth/register/" class="btn btn-primary">Sign Up Free</a>
                    <a href="/auth/login/" class="btn btn-secondary">Login</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Back Link -->
        <div style="max-width: 1200px; margin: 2rem auto 0; text-align: center;">
            <a href="/" class="link">← Back to Home</a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../views/footer.php'; ?>
