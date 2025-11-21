<?php
require_once __DIR__ . '/../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationDesignationRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$designationRepo = new OrganizationDesignationRepository();

// Get all designations (all users can view)
$designations = $designationRepo->findAll();
$totalCount = $designationRepo->count(false);

// Check if user is Super Admin to show deleted items and edit/delete buttons
$isSuperAdmin = $designationRepo->isSuperAdmin($user->getEmail());
$deletedDesignations = $isSuperAdmin ? $designationRepo->findDeleted($user->getEmail()) : [];

$pageTitle = 'Organization Designations';
include __DIR__ . '/../../../../../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Organization Designations</h1>
        <?php if ($isSuperAdmin): ?>
            <a href="/organizations/departments/human_resource/designations/form/" class="btn btn-primary">+ New Designation</a>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Active Designations -->
    <div class="card">
        <h2 class="card-title">Active Designations (<?php echo count($designations); ?>)</h2>

        <?php if (empty($designations)): ?>
            <div style="text-align: center; padding: 3rem 1rem;">
                <p class="text-muted" style="font-size: 1.2rem; margin-bottom: 1rem;">No Designations Yet</p>
                <p class="text-muted" style="margin-bottom: 2rem;">Create standard designation categories for your organizations.</p>
                <?php if ($isSuperAdmin): ?>
                    <a href="/organizations/departments/human_resource/designations/form/" class="btn btn-primary">Create Your First Designation</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Code</th>
                            <th style="padding: 1rem;">Level</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($designations as $designation): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($designation->getName()); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <code style="background: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        <?php echo htmlspecialchars($designation->getCode()); ?>
                                    </code>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($designation->getLevel()): ?>
                                        <span style="display: inline-block; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px; font-size: 0.875rem;">
                                            <?php echo htmlspecialchars($designation->getLevelName()); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($designation->getIsActive()): ?>
                                        <span style="color: var(--secondary-color);">Active</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/human_resource/designations/view/?id=<?php echo $designation->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">View</a>
                                    <?php if ($isSuperAdmin): ?>
                                        <a href="/organizations/departments/human_resource/designations/form/?id=<?php echo $designation->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Edit</a>
                                        <a href="/organizations/departments/human_resource/designations/delete.php?id=<?php echo $designation->getId(); ?>" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Are you sure you want to delete this designation?');">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Deleted Designations (Trash) - Only for Super Admin -->
    <?php if ($isSuperAdmin && !empty($deletedDesignations)): ?>
        <div class="card" style="margin-top: 2rem;">
            <h2 class="card-title">Trash (<?php echo count($deletedDesignations); ?>)</h2>
            <p class="text-muted text-small">Deleted designations can be restored.</p>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Code</th>
                            <th style="padding: 1rem;">Deleted</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deletedDesignations as $designation): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); opacity: 0.6;">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($designation->getName()); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <code style="background: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        <?php echo htmlspecialchars($designation->getCode()); ?>
                                    </code>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <?php echo date('M j, Y', strtotime($designation->getDeletedAt())); ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/human_resource/designations/restore.php?id=<?php echo $designation->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Restore</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../../../views/footer.php'; ?>
