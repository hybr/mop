<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$orgRepo = new OrganizationRepository();

// Get organizations for current user
$organizations = $orgRepo->findAllByUser($user->getId());
$deletedOrgs = $orgRepo->findDeletedByUser($user->getId());
$totalCount = $orgRepo->countByUser($user->getId(), false);

$pageTitle = 'My Organizations';
include __DIR__ . '/../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>My Organizations</h1>
        <a href="/organization-form.php" class="btn btn-primary">+ New Organization</a>
    </div>

    <!-- Active Organizations -->
    <div class="card">
        <h2 class="card-title">Active Organizations (<?php echo count($organizations); ?>)</h2>

        <?php if (empty($organizations)): ?>
            <p class="text-muted">You haven't created any organizations yet.</p>
            <a href="/organization-form.php" class="btn btn-primary">Create Your First Organization</a>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Email</th>
                            <th style="padding: 1rem;">Phone</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Created</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($organizations as $org): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($org->getFullName()); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <a href="<?php echo $org->getUrl(); ?>" target="_blank" class="link">
                                            <?php echo $org->getSubdomain(); ?>.v4l.app
                                        </a>
                                    </small>
                                    <?php if ($org->getDescription()): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($org->getDescription(), 0, 50)); ?><?php echo strlen($org->getDescription()) > 50 ? '...' : ''; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $org->getEmail() ? htmlspecialchars($org->getEmail()) : '-'; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $org->getPhone() ? htmlspecialchars($org->getPhone()) : '-'; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($org->getIsActive()): ?>
                                        <span style="color: var(--secondary-color);">Active</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <?php echo date('M j, Y', strtotime($org->getCreatedAt())); ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organization-form.php?id=<?php echo $org->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Edit</a>
                                    <a href="/organization-delete.php?id=<?php echo $org->getId(); ?>" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Are you sure you want to delete this organization?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Deleted Organizations (Trash) -->
    <?php if (!empty($deletedOrgs)): ?>
        <div class="card">
            <h2 class="card-title">Trash (<?php echo count($deletedOrgs); ?>)</h2>
            <p class="text-muted text-small">Deleted organizations can be restored or permanently deleted.</p>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Deleted</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deletedOrgs as $org): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); opacity: 0.6;">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($org->getFullName()); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo $org->getSubdomain(); ?>.v4l.app</small>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo date('M j, Y', strtotime($org->getDeletedAt())); ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organization-restore.php?id=<?php echo $org->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Restore</a>
                                    <a href="/organization-delete.php?id=<?php echo $org->getId(); ?>&permanent=1" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Permanently delete this organization? This cannot be undone!');">Delete Forever</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
