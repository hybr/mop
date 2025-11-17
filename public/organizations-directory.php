<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationRepository;

$auth = new Auth();
$orgRepo = new OrganizationRepository();

$isLoggedIn = $auth->isLoggedIn();

// Get all active organizations (public access)
$organizations = $orgRepo->findAllPublic(100, 0);

$pageTitle = 'Organizations Directory';
include __DIR__ . '/../views/header.php';
?>

<div class="py-4">
    <div style="max-width: 1200px; margin: 0 auto;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 3rem;">
            <h1>Organizations Directory</h1>
            <p class="text-muted" style="margin-top: 1rem;">
                Discover organizations on V4L - Vocal 4 Local
            </p>
        </div>

        <?php if (empty($organizations)): ?>
            <!-- Empty State -->
            <div class="card" style="text-align: center; padding: 4rem 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🏢</div>
                <h2 style="margin-bottom: 1rem;">No Organizations Yet</h2>
                <p class="text-muted" style="margin-bottom: 2rem;">
                    Be the first to create an organization on V4L!
                </p>
                <?php if (!$isLoggedIn): ?>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="/register.php" class="btn btn-primary">Sign Up Free</a>
                        <a href="/login.php" class="btn btn-secondary">Login</a>
                    </div>
                <?php else: ?>
                    <a href="/organization-form.php" class="btn btn-primary">Create First Organization</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Organizations Grid (Mobile-First Card Layout) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                <?php foreach ($organizations as $org): ?>
                    <div class="card" style="display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" onclick="window.location.href='/organization-view.php?id=<?php echo $org['id']; ?>'">
                        <!-- Logo -->
                        <?php if ($org['logo_url']): ?>
                            <div style="width: 100%; height: 150px; overflow: hidden; border-radius: 8px 8px 0 0; margin-bottom: 1rem;">
                                <img src="<?php echo htmlspecialchars($org['logo_url']); ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <div style="width: 100%; height: 150px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border-radius: 8px 8px 0 0; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                                🏢
                            </div>
                        <?php endif; ?>

                        <!-- Content -->
                        <div style="flex: 1; display: flex; flex-direction: column;">
                            <h3 style="margin-bottom: 0.5rem; font-size: 1.25rem;">
                                <?php echo htmlspecialchars($org['short_name']); ?>
                            </h3>

                            <?php if ($org['legal_structure']): ?>
                                <p class="text-muted text-small" style="margin-bottom: 0.75rem;">
                                    <?php echo htmlspecialchars($org['legal_structure']); ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($org['description']): ?>
                                <p class="text-muted" style="margin-bottom: 1rem; flex: 1; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?php echo htmlspecialchars($org['description']); ?>
                                </p>
                            <?php endif; ?>

                            <!-- Footer -->
                            <div style="padding-top: 1rem; border-top: 1px solid var(--border-color);">
                                <a href="https://<?php echo htmlspecialchars($org['subdomain']); ?>.v4l.app" target="_blank" class="link text-small" onclick="event.stopPropagation();" style="display: flex; align-items: center; gap: 0.5rem;">
                                    🔗 <?php echo htmlspecialchars($org['subdomain']); ?>.v4l.app
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- CTA for non-logged-in users -->
            <?php if (!$isLoggedIn): ?>
                <div class="card" style="margin-top: 3rem; text-align: center; background: var(--bg-light);">
                    <h3 style="margin-bottom: 1rem;">Want to create your own organization?</h3>
                    <p class="text-muted" style="margin-bottom: 1.5rem;">
                        Join V4L and get your own subdomain at subdomain.v4l.app
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="/register.php" class="btn btn-primary">Sign Up Free</a>
                        <a href="/login.php" class="btn btn-secondary">Login</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
/* Card hover effect */
.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .card {
        margin-bottom: 0;
    }
}
</style>

<?php include __DIR__ . '/../views/footer.php'; ?>
