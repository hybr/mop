<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\Authorization;
use App\Classes\OrganizationDepartmentTeamRepository;
use App\Classes\OrganizationDepartmentRepository;
use App\Classes\OrganizationRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$teamRepo = new OrganizationDepartmentTeamRepository();
$deptRepo = new OrganizationDepartmentRepository();
$orgRepo = new OrganizationRepository();

$isSuperAdmin = Authorization::isSuperAdmin($user->getEmail());

// Get team ID from URL
if (!isset($_GET['id'])) {
    header('Location: /organizations/departments/human_resource/teams/?error=' . urlencode('Team ID required'));
    exit;
}

$team = $teamRepo->findById($_GET['id']);
if (!$team) {
    header('Location: /organizations/departments/human_resource/teams/?error=' . urlencode('Team not found'));
    exit;
}

// Get related data
$department = $team->getOrganizationDepartmentId() ? $deptRepo->findById($team->getOrganizationDepartmentId()) : null;
$organization = $team->getOrganizationId() ? $orgRepo->findById($team->getOrganizationId(), $user->getId()) : null;
$parentTeam = $team->getParentTeamId() ? $teamRepo->findById($team->getParentTeamId()) : null;

$pageTitle = $team->getName();
include __DIR__ . '/../../../../../../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
        <div>
            <a href="/organizations/departments/human_resource/teams/" class="text-muted" style="text-decoration: none;">&larr; Back to Teams</a>
            <h1 style="margin-top: 0.5rem;"><?php echo htmlspecialchars($team->getName()); ?></h1>
            <code style="background: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 4px;">
                <?php echo htmlspecialchars($team->getCode()); ?>
            </code>
            <?php if ($team->getIsActive()): ?>
                <span style="margin-left: 1rem; color: var(--secondary-color);">Active</span>
            <?php else: ?>
                <span style="margin-left: 1rem; color: var(--text-light);">Inactive</span>
            <?php endif; ?>
        </div>
        <?php if ($isSuperAdmin): ?>
            <div style="display: flex; gap: 0.5rem;">
                <a href="/organizations/departments/human_resource/teams/form/?id=<?php echo $team->getId(); ?>" class="btn btn-secondary">Edit</a>
                <a href="/organizations/departments/human_resource/teams/delete/?id=<?php echo $team->getId(); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this team?');">Delete</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($team->getDescription()): ?>
        <div class="card" style="margin-bottom: 2rem;">
            <p style="margin: 0;"><?php echo nl2br(htmlspecialchars($team->getDescription())); ?></p>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
        <!-- Department & Organization -->
        <div class="card">
            <h2 class="card-title">Assignment</h2>
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Department</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <strong><?php echo $department ? htmlspecialchars($department->getName()) : '-'; ?></strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Organization</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php if ($organization): ?>
                            <?php echo htmlspecialchars($organization->getName()); ?>
                        <?php else: ?>
                            <span class="text-muted">Global</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Parent Team</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php if ($parentTeam): ?>
                            <a href="/organizations/departments/human_resource/teams/view/?id=<?php echo $parentTeam->getId(); ?>">
                                <?php echo htmlspecialchars($parentTeam->getName()); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">None</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Metadata -->
        <div class="card">
            <h2 class="card-title">Metadata</h2>
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Created</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php echo $team->getCreatedAt() ? date('M j, Y g:i A', strtotime($team->getCreatedAt())) : '-'; ?>
                    </td>
                </tr>
                <?php if ($team->getUpdatedAt()): ?>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Last Updated</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php echo date('M j, Y g:i A', strtotime($team->getUpdatedAt())); ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Sort Order</td>
                    <td style="padding: 0.5rem 0; text-align: right;"><?php echo $team->getSortOrder() ?? 0; ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../../../../views/footer.php'; ?>
