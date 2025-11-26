<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationEntityPermission;
use App\Classes\OrganizationEntityPermissionRepository;
use App\Classes\OrganizationPositionRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$permissionRepo = new OrganizationEntityPermissionRepository();
$positionRepo = new OrganizationPositionRepository();

$isSuperAdmin = $permissionRepo->isSuperAdmin($user->getEmail());

// Get permission ID from URL
if (!isset($_GET['id'])) {
    header('Location: /organizations/departments/information_technology/entity_permissions/?error=' . urlencode('Permission ID required'));
    exit;
}

$permission = $permissionRepo->findById($_GET['id']);
if (!$permission) {
    header('Location: /organizations/departments/information_technology/entity_permissions/?error=' . urlencode('Permission not found'));
    exit;
}

// Get related data
$position = $permission->getOrganizationPositionId() ? $positionRepo->findById($permission->getOrganizationPositionId()) : null;

$pageTitle = 'Permission Details';
include __DIR__ . '/../../../../../../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
        <div>
            <a href="/organizations/departments/information_technology/entity_permissions/" class="text-muted" style="text-decoration: none;">&larr; Back to Permissions</a>
            <h1 style="margin-top: 0.5rem;">Permission Details</h1>
            <p style="margin-top: 0.5rem; font-size: 1.1rem;">
                <strong><?php echo htmlspecialchars($position ? $position->getName() : 'Unknown Position'); ?></strong>
                can
                <span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; <?php echo $permission->getActionBadgeClass(); ?>">
                    <?php echo strtoupper($permission->getAction()); ?>
                </span>
                <strong><?php echo htmlspecialchars($permission->getEntityName()); ?></strong>
            </p>
        </div>
        <?php if ($isSuperAdmin): ?>
            <div style="display: flex; gap: 0.5rem;">
                <a href="/organizations/departments/information_technology/entity_permissions/form/?id=<?php echo $permission->getId(); ?>" class="btn btn-secondary">Edit</a>
                <a href="/organizations/departments/information_technology/entity_permissions/delete/?id=<?php echo $permission->getId(); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this permission?');">Delete</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($permission->getDescription()): ?>
        <div class="card" style="margin-bottom: 2rem; background: var(--bg-light);">
            <p style="margin: 0;"><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($permission->getDescription())); ?></p>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
        <!-- Permission Details -->
        <div class="card">
            <h2 class="card-title">Permission Details</h2>
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Position</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <strong><?php echo htmlspecialchars($position ? $position->getName() : 'Unknown'); ?></strong>
                        <?php if ($position): ?>
                            <br><code style="background: var(--bg-light); padding: 0.125rem 0.375rem; border-radius: 4px; font-size: 0.75rem;">
                                <?php echo htmlspecialchars($position->getCode()); ?>
                            </code>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Entity</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <strong><?php echo htmlspecialchars($permission->getEntityName()); ?></strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Action</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; <?php echo $permission->getActionBadgeClass(); ?>">
                            <?php echo strtoupper($permission->getAction()); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Scope</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; <?php echo $permission->getScopeBadgeClass(); ?>">
                            <?php echo strtoupper($permission->getScope()); ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Additional Info -->
        <div class="card">
            <h2 class="card-title">Additional Information</h2>
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Priority</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <strong><?php echo $permission->getPriority(); ?></strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Status</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php if ($permission->getIsActive()): ?>
                            <span style="color: var(--secondary-color);">Active</span>
                        <?php else: ?>
                            <span style="color: var(--text-light);">Inactive</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Created</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php if ($permission->getCreatedAt()): ?>
                            <?php echo date('M j, Y', strtotime($permission->getCreatedAt())); ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Last Updated</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php if ($permission->getUpdatedAt()): ?>
                            <?php echo date('M j, Y', strtotime($permission->getUpdatedAt())); ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Conditions (if any) -->
    <?php if ($permission->getConditions()): ?>
        <div class="card" style="margin-top: 2rem;">
            <h2 class="card-title">Conditions</h2>
            <pre style="background: var(--bg-light); padding: 1rem; border-radius: 4px; overflow-x: auto; margin: 0;"><?php echo htmlspecialchars($permission->getConditions()); ?></pre>
        </div>
    <?php endif; ?>

    <!-- Scope Explanation -->
    <div class="card" style="margin-top: 2rem; background: var(--bg-light);">
        <h3 style="margin-top: 0; margin-bottom: 0.5rem;">Scope Explanation</h3>
        <?php
        $scopeDescriptions = [
            'own' => 'This position can only perform the action on records they created themselves.',
            'team' => 'This position can perform the action on records within their team.',
            'department' => 'This position can perform the action on records within their department.',
            'organization' => 'This position can perform the action on records within their organization.',
            'all' => 'This position can perform the action on all records (super admin level).'
        ];
        ?>
        <p style="margin: 0;">
            <?php echo $scopeDescriptions[$permission->getScope()] ?? 'Unknown scope level.'; ?>
        </p>
    </div>

    <!-- What this means -->
    <div class="card" style="margin-top: 2rem;">
        <h2 class="card-title">Permission Summary</h2>
        <p style="margin: 0; font-size: 1.1rem;">
            Users with the <strong><?php echo htmlspecialchars($position ? $position->getName() : 'Unknown Position'); ?></strong> position
            can <strong><?php echo strtolower($permission->getAction()); ?></strong>
            <strong><?php echo htmlspecialchars($permission->getEntityName()); ?></strong> records
            within the <strong><?php echo strtolower($permission->getScope()); ?></strong> scope.
        </p>
    </div>
</div>

<?php include __DIR__ . '/../../../../../../views/footer.php'; ?>
