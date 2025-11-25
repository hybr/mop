<?php
require_once __DIR__ . '/../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\Authorization;
use App\Classes\OrganizationRepository;
use App\Classes\OrganizationDepartmentRepository;
use App\Classes\OrganizationDepartmentTeamRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$orgRepo = new OrganizationRepository();
$deptRepo = new OrganizationDepartmentRepository();
$teamRepo = new OrganizationDepartmentTeamRepository();

// Get all teams with department information
$allTeams = $teamRepo->findAllWithDepartments();
$totalCount = $teamRepo->count(false);

// Check if user is Super Admin to show deleted items and edit/delete buttons
$isSuperAdmin = Authorization::isSuperAdmin($user->getEmail());
$deletedTeams = $isSuperAdmin ? $teamRepo->findDeleted($user->getEmail()) : [];

$pageTitle = 'Department Teams';
include __DIR__ . '/../../../../../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <a href="/organizations/departments/" class="text-muted" style="text-decoration: none;">&larr; Back to Departments</a>
            <h1 style="margin-top: 0.5rem;">Department Teams</h1>
        </div>
        <?php if ($isSuperAdmin): ?>
            <a href="/organizations/departments/human_resource/teams/form/" class="btn btn-primary">+ New Team</a>
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
            <strong>Teams</strong> are groups within departments. Each department can have multiple teams for better organization.
            Teams can be assigned to any department (HR, Facilities, Operations, etc.).
        </p>
    </div>

    <!-- Active Teams -->
    <div class="card">
        <h2 class="card-title">All Teams (<?php echo count($allTeams); ?>)</h2>

        <?php if (empty($allTeams)): ?>
            <div style="text-align: center; padding: 3rem 1rem;">
                <p class="text-muted" style="font-size: 1.2rem; margin-bottom: 1rem;">No Teams Yet</p>
                <p class="text-muted" style="margin-bottom: 2rem;">Create teams to organize your departments better.</p>
                <?php if ($isSuperAdmin): ?>
                    <a href="/organizations/departments/human_resource/teams/form/" class="btn btn-primary">Create Your First Team</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Team Name</th>
                            <th style="padding: 1rem;">Code</th>
                            <th style="padding: 1rem;">Department</th>
                            <th style="padding: 1rem;">Description</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allTeams as $team): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($team['name']); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <code style="background: var(--bg-light); padding: 0.125rem 0.375rem; border-radius: 4px; font-size: 0.75rem;">
                                        <?php echo htmlspecialchars($team['code']); ?>
                                    </code>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($team['department_name'] ?? 'N/A'); ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if (!empty($team['description'])): ?>
                                        <span class="text-muted"><?php echo htmlspecialchars(substr($team['description'], 0, 50)); ?><?php echo strlen($team['description']) > 50 ? '...' : ''; ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($team['is_active']): ?>
                                        <span style="color: var(--secondary-color);">Active</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/human_resource/teams/view/?id=<?php echo $team['id']; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">View</a>
                                    <?php if ($isSuperAdmin): ?>
                                        <a href="/organizations/departments/human_resource/teams/form/?id=<?php echo $team['id']; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Edit</a>
                                        <a href="/organizations/departments/human_resource/teams/delete/?id=<?php echo $team['id']; ?>" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Are you sure you want to delete this team?');">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Deleted Teams (Trash) - Only for Super Admin -->
    <?php if ($isSuperAdmin && !empty($deletedTeams)): ?>
        <div class="card" style="margin-top: 2rem;">
            <h2 class="card-title">Trash (<?php echo count($deletedTeams); ?>)</h2>
            <p class="text-muted text-small">Deleted teams can be restored.</p>

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
                        <?php foreach ($deletedTeams as $team): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); opacity: 0.6;">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($team->getName()); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <code style="background: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        <?php echo htmlspecialchars($team->getCode()); ?>
                                    </code>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <?php echo date('M j, Y', strtotime($team->getDeletedAt())); ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/human_resource/teams/restore/?id=<?php echo $team->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Restore</a>
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
