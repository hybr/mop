<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationPosition;
use App\Classes\OrganizationPositionRepository;
use App\Classes\OrganizationDepartmentRepository;
use App\Classes\OrganizationDepartmentTeamRepository;
use App\Classes\OrganizationDesignationRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$positionRepo = new OrganizationPositionRepository();
$deptRepo = new OrganizationDepartmentRepository();
$teamRepo = new OrganizationDepartmentTeamRepository();
$designationRepo = new OrganizationDesignationRepository();

$isSuperAdmin = $positionRepo->isSuperAdmin($user->getEmail());

// Get position ID from URL
if (!isset($_GET['id'])) {
    header('Location: /organizations/departments/human_resource/positions/?error=' . urlencode('Position ID required'));
    exit;
}

$position = $positionRepo->findById($_GET['id']);
if (!$position) {
    header('Location: /organizations/departments/human_resource/positions/?error=' . urlencode('Position not found'));
    exit;
}

// Get related data
$department = $position->getOrganizationDepartmentId() ? $deptRepo->findById($position->getOrganizationDepartmentId()) : null;
$team = $position->getOrganizationDepartmentTeamId() ? $teamRepo->findById($position->getOrganizationDepartmentTeamId()) : null;
$designation = $position->getOrganizationDesignationId() ? $designationRepo->findById($position->getOrganizationDesignationId()) : null;
$reportsTo = $position->getReportsToPositionId() ? $positionRepo->findById($position->getReportsToPositionId()) : null;

$pageTitle = $position->getName();
include __DIR__ . '/../../../../../../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
        <div>
            <a href="/organizations/departments/human_resource/positions/" class="text-muted" style="text-decoration: none;">&larr; Back to Positions</a>
            <h1 style="margin-top: 0.5rem;"><?php echo htmlspecialchars($position->getName()); ?></h1>
            <code style="background: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 4px;">
                <?php echo htmlspecialchars($position->getCode()); ?>
            </code>
            <?php if ($position->getIsActive()): ?>
                <span style="margin-left: 1rem; color: var(--secondary-color);">Active</span>
            <?php else: ?>
                <span style="margin-left: 1rem; color: var(--text-light);">Inactive</span>
            <?php endif; ?>
        </div>
        <?php if ($isSuperAdmin): ?>
            <div style="display: flex; gap: 0.5rem;">
                <a href="/organizations/departments/human_resource/positions/form/?id=<?php echo $position->getId(); ?>" class="btn btn-secondary">Edit</a>
                <a href="/organizations/departments/human_resource/positions/delete/?id=<?php echo $position->getId(); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this position?');">Delete</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($position->getDescription()): ?>
        <div class="card" style="margin-bottom: 2rem;">
            <p style="margin: 0;"><?php echo nl2br(htmlspecialchars($position->getDescription())); ?></p>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
        <!-- Organization Structure -->
        <div class="card">
            <h2 class="card-title">Organization Structure</h2>
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Department</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <strong><?php echo $department ? htmlspecialchars($department->getName()) : '-'; ?></strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Team</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php echo $team ? htmlspecialchars($team->getName()) : '<span class="text-muted">-</span>'; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Designation</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <strong><?php echo $designation ? htmlspecialchars($designation->getName()) : '-'; ?></strong>
                        <?php if ($designation && $designation->getLevel()): ?>
                            <br><span class="text-muted" style="font-size: 0.875rem;"><?php echo $designation->getLevelName(); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Reports To</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php if ($reportsTo): ?>
                            <a href="/organizations/departments/human_resource/positions/view/?id=<?php echo $reportsTo->getId(); ?>">
                                <?php echo htmlspecialchars($reportsTo->getName()); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Position Details -->
        <div class="card">
            <h2 class="card-title">Position Details</h2>
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Employment Type</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php echo htmlspecialchars($position->getEmploymentTypeName() ?? 'Full Time'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Headcount</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php echo $position->getHeadcount() ?? 1; ?> position(s)
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Salary Range</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php echo htmlspecialchars($position->getSalaryRangeFormatted()); ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Requirements -->
        <div class="card">
            <h2 class="card-title">Requirements</h2>
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Education</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php echo htmlspecialchars($position->getEducationLevelName() ?? 'Not specified'); ?>
                        <?php if ($position->getMinEducationField()): ?>
                            <br><span class="text-muted" style="font-size: 0.875rem;">in <?php echo htmlspecialchars($position->getMinEducationField()); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; color: var(--text-light);">Experience</td>
                    <td style="padding: 0.5rem 0; text-align: right;">
                        <?php if ($position->getMinExperienceYears()): ?>
                            <?php echo $position->getMinExperienceYears(); ?>+ years
                        <?php else: ?>
                            Fresher / Entry Level
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Certifications -->
        <?php if ($position->getCertificationsRequired() || $position->getCertificationsPreferred()): ?>
        <div class="card">
            <h2 class="card-title">Certifications</h2>
            <?php if ($position->getCertificationsRequired()): ?>
                <p style="margin-bottom: 0.5rem;"><strong>Required:</strong> <?php echo htmlspecialchars($position->getCertificationsRequired()); ?></p>
            <?php endif; ?>
            <?php if ($position->getCertificationsPreferred()): ?>
                <p style="margin: 0;"><strong>Preferred:</strong> <?php echo htmlspecialchars($position->getCertificationsPreferred()); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Skills -->
    <div class="card" style="margin-top: 2rem;">
        <h2 class="card-title">Skills</h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>
                <h3 style="font-size: 1rem; margin-bottom: 1rem;">Required Skills</h3>
                <?php
                $requiredSkills = $position->getSkillsRequiredArray();
                if (!empty($requiredSkills)):
                ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <?php foreach ($requiredSkills as $skill): ?>
                            <span style="background: var(--primary-color); color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">
                                <?php echo htmlspecialchars($skill); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <span class="text-muted">No required skills specified</span>
                <?php endif; ?>
            </div>

            <div>
                <h3 style="font-size: 1rem; margin-bottom: 1rem;">Preferred Skills</h3>
                <?php
                $preferredSkills = $position->getSkillsPreferredArray();
                if (!empty($preferredSkills)):
                ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <?php foreach ($preferredSkills as $skill): ?>
                            <span style="background: var(--bg-light); color: var(--text-color); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; border: 1px solid var(--border-color);">
                                <?php echo htmlspecialchars($skill); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <span class="text-muted">No preferred skills specified</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Metadata -->
    <div class="card" style="margin-top: 2rem;">
        <h2 class="card-title">Metadata</h2>
        <table style="width: 100%;">
            <tr>
                <td style="padding: 0.5rem 0; color: var(--text-light);">Created</td>
                <td style="padding: 0.5rem 0;">
                    <?php echo $position->getCreatedAt() ? date('M j, Y g:i A', strtotime($position->getCreatedAt())) : '-'; ?>
                </td>
            </tr>
            <?php if ($position->getUpdatedAt()): ?>
            <tr>
                <td style="padding: 0.5rem 0; color: var(--text-light);">Last Updated</td>
                <td style="padding: 0.5rem 0;">
                    <?php echo date('M j, Y g:i A', strtotime($position->getUpdatedAt())); ?>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <td style="padding: 0.5rem 0; color: var(--text-light);">Sort Order</td>
                <td style="padding: 0.5rem 0;"><?php echo $position->getSortOrder() ?? 0; ?></td>
            </tr>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../../../../../views/footer.php'; ?>
