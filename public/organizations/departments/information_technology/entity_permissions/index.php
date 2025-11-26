<?php
require_once __DIR__ . '/../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationEntityPermissionRepository;
use App\Classes\OrganizationPositionRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$permissionRepo = new OrganizationEntityPermissionRepository();
$positionRepo = new OrganizationPositionRepository();

// Get all permissions with related position data
$permissions = $permissionRepo->findAll();
$totalCount = count($permissions);

// Get positions for the filter/reference
$positions = $positionRepo->findAll();

// Check if user is Super Admin
$isSuperAdmin = $permissionRepo->isSuperAdmin($user->getEmail());

$pageTitle = 'Organization Entity Permissions';
include __DIR__ . '/../../../../../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <a href="/organizations/departments/" class="text-muted" style="text-decoration: none;">&larr; Back to Departments</a>
            <h1 style="margin-top: 0.5rem;">Organization Entity Permissions</h1>
        </div>
        <?php if ($isSuperAdmin): ?>
            <a href="/organizations/departments/information_technology/entity_permissions/form/" class="btn btn-primary">+ New Permission</a>
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

    <!-- Info Card -->
    <div class="card" style="margin-bottom: 2rem; background: var(--bg-light);">
        <p class="text-muted" style="margin: 0;">
            <strong>Entity Permissions</strong> define what actions each position can perform on different entities.
            Control create, read, update, delete, and special actions with configurable scopes.
        </p>
    </div>

    <!-- Active Permissions -->
    <div class="card">
        <h2 class="card-title">Active Permissions (<?php echo $totalCount; ?>)</h2>

        <?php if (empty($permissions)): ?>
            <div style="text-align: center; padding: 3rem 1rem;">
                <p class="text-muted" style="font-size: 1.2rem; margin-bottom: 1rem;">No Permissions Yet</p>
                <p class="text-muted" style="margin-bottom: 2rem;">Create permission rules to control what positions can do with entities.</p>
                <?php if ($isSuperAdmin): ?>
                    <a href="/organizations/departments/information_technology/entity_permissions/form/" class="btn btn-primary">Create Your First Permission</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Position</th>
                            <th style="padding: 1rem;">Entity</th>
                            <th style="padding: 1rem;">Action</th>
                            <th style="padding: 1rem;">Scope</th>
                            <th style="padding: 1rem;">Priority</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($permissions as $perm): ?>
                            <?php
                            // Get position name
                            $position = null;
                            foreach ($positions as $pos) {
                                if ($pos->getId() == $perm->getOrganizationPositionId()) {
                                    $position = $pos;
                                    break;
                                }
                            }
                            $positionName = $position ? $position->getName() : 'Unknown';
                            ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($positionName); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($perm->getEntityName()); ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; <?php echo $perm->getActionBadgeClass(); ?>">
                                        <?php echo strtoupper($perm->getAction()); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; <?php echo $perm->getScopeBadgeClass(); ?>">
                                        <?php echo strtoupper($perm->getScope()); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $perm->getPriority(); ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($perm->getIsActive()): ?>
                                        <span style="color: var(--secondary-color);">Active</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/information_technology/entity_permissions/view/?id=<?php echo $perm->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">View</a>
                                    <?php if ($isSuperAdmin): ?>
                                        <a href="/organizations/departments/information_technology/entity_permissions/form/?id=<?php echo $perm->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Edit</a>
                                        <a href="/organizations/departments/information_technology/entity_permissions/delete/?id=<?php echo $perm->getId(); ?>" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Are you sure you want to delete this permission?');">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Permission Summary by Position -->
    <?php if (!empty($permissions)): ?>
        <div class="card" style="margin-top: 2rem;">
            <h2 class="card-title">Permission Summary by Position</h2>
            <p class="text-muted" style="margin-bottom: 1rem;">Overview of permissions grouped by position</p>

            <?php
            // Group permissions by position
            $permissionsByPosition = [];
            foreach ($permissions as $perm) {
                $posId = $perm->getOrganizationPositionId();
                if (!isset($permissionsByPosition[$posId])) {
                    $permissionsByPosition[$posId] = [];
                }
                $permissionsByPosition[$posId][] = $perm;
            }
            ?>

            <div style="display: grid; gap: 1rem;">
                <?php foreach ($permissionsByPosition as $posId => $perms): ?>
                    <?php
                    // Get position name
                    $position = null;
                    foreach ($positions as $pos) {
                        if ($pos->getId() == $posId) {
                            $position = $pos;
                            break;
                        }
                    }
                    $positionName = $position ? $position->getName() : 'Unknown Position';
                    ?>
                    <div style="border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px;">
                        <h3 style="margin-top: 0; margin-bottom: 0.5rem; font-size: 1.1rem;">
                            <?php echo htmlspecialchars($positionName); ?>
                        </h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <?php foreach ($perms as $perm): ?>
                                <span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; background: var(--bg-light);">
                                    <strong><?php echo htmlspecialchars($perm->getEntityName()); ?>:</strong>
                                    <span style="<?php echo $perm->getActionBadgeClass(); ?> padding: 0.125rem 0.375rem; border-radius: 3px; margin-left: 0.25rem;">
                                        <?php echo strtoupper($perm->getAction()); ?>
                                    </span>
                                    <span style="opacity: 0.7; margin-left: 0.25rem;">(<?php echo $perm->getScope(); ?>)</span>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../../../views/footer.php'; ?>
