<?php
require_once __DIR__ . '/../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationPositionRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$positionRepo = new OrganizationPositionRepository();

// Get all positions with related data
$positionsData = $positionRepo->findAllWithRelations();
$totalCount = $positionRepo->count(false);

// Check if user is Super Admin to show deleted items and edit/delete buttons
$isSuperAdmin = $positionRepo->isSuperAdmin($user->getEmail());
$deletedPositions = $isSuperAdmin ? $positionRepo->findDeleted($user->getEmail()) : [];

$pageTitle = 'Organization Positions';
include __DIR__ . '/../../../../../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <a href="/organizations/departments/" class="text-muted" style="text-decoration: none;">&larr; Back to Departments</a>
            <h1 style="margin-top: 0.5rem;">Organization Positions</h1>
        </div>
        <?php if ($isSuperAdmin): ?>
            <a href="/organizations/departments/human_resource/positions/form/" class="btn btn-primary">+ New Position</a>
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
            <strong>Positions</strong> combine Department + Team + Designation with education, experience, and skill requirements.
            They define the roles available across all organizations.
        </p>
    </div>

    <!-- Active Positions -->
    <div class="card">
        <h2 class="card-title">Active Positions (<?php echo count($positionsData); ?>)</h2>

        <?php if (empty($positionsData)): ?>
            <div style="text-align: center; padding: 3rem 1rem;">
                <p class="text-muted" style="font-size: 1.2rem; margin-bottom: 1rem;">No Positions Yet</p>
                <p class="text-muted" style="margin-bottom: 2rem;">Create position templates that combine department, team, designation with requirements.</p>
                <?php if ($isSuperAdmin): ?>
                    <a href="/organizations/departments/human_resource/positions/form/" class="btn btn-primary">Create Your First Position</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Position</th>
                            <th style="padding: 1rem;">Department</th>
                            <th style="padding: 1rem;">Designation</th>
                            <th style="padding: 1rem;">Experience</th>
                            <th style="padding: 1rem;">Education</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($positionsData as $pos): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($pos['name']); ?></strong>
                                    <br>
                                    <code style="background: var(--bg-light); padding: 0.125rem 0.375rem; border-radius: 4px; font-size: 0.75rem;">
                                        <?php echo htmlspecialchars($pos['code']); ?>
                                    </code>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($pos['department_name'] ?? '-'); ?>
                                    <?php if (!empty($pos['team_name'])): ?>
                                        <br><span class="text-muted" style="font-size: 0.875rem;"><?php echo htmlspecialchars($pos['team_name']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($pos['designation_name'] ?? '-'); ?>
                                    <?php if (!empty($pos['designation_level'])): ?>
                                        <br><span class="text-muted" style="font-size: 0.75rem;">Level <?php echo $pos['designation_level']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($pos['min_experience_years']): ?>
                                        <?php echo $pos['min_experience_years']; ?>+ years
                                    <?php else: ?>
                                        <span class="text-muted">Fresher</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php
                                    $educationLevels = \App\Classes\OrganizationPosition::getEducationLevels();
                                    echo htmlspecialchars($educationLevels[$pos['min_education']] ?? $pos['min_education'] ?? '-');
                                    ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($pos['is_active']): ?>
                                        <span style="color: var(--secondary-color);">Active</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/human_resource/positions/view/?id=<?php echo $pos['id']; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">View</a>
                                    <?php if ($isSuperAdmin): ?>
                                        <a href="/organizations/departments/human_resource/positions/form/?id=<?php echo $pos['id']; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Edit</a>
                                        <a href="/organizations/departments/human_resource/positions/delete/?id=<?php echo $pos['id']; ?>" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Are you sure you want to delete this position?');">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Deleted Positions (Trash) - Only for Super Admin -->
    <?php if ($isSuperAdmin && !empty($deletedPositions)): ?>
        <div class="card" style="margin-top: 2rem;">
            <h2 class="card-title">Trash (<?php echo count($deletedPositions); ?>)</h2>
            <p class="text-muted text-small">Deleted positions can be restored.</p>

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
                        <?php foreach ($deletedPositions as $position): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); opacity: 0.6;">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($position->getName()); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <code style="background: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        <?php echo htmlspecialchars($position->getCode()); ?>
                                    </code>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <?php echo date('M j, Y', strtotime($position->getDeletedAt())); ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/human_resource/positions/restore/?id=<?php echo $position->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Restore</a>
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
