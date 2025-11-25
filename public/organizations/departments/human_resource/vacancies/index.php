<?php
require_once __DIR__ . '/../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationVacancyRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$vacancyRepo = new OrganizationVacancyRepository();

// Get all vacancies with related data for this user
$vacanciesData = $vacancyRepo->findAllWithRelations($user->getId());
$totalCount = $vacancyRepo->countByUser($user->getId(), false);

// Get deleted vacancies for trash
$deletedVacancies = $vacancyRepo->findDeletedByUser($user->getId());

$pageTitle = 'Job Vacancies';
include __DIR__ . '/../../../../../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <a href="/organizations/departments/" class="text-muted" style="text-decoration: none;">&larr; Back to Departments</a>
            <h1 style="margin-top: 0.5rem;">Job Vacancies</h1>
        </div>
        <a href="/organizations/departments/human_resource/vacancies/form/" class="btn btn-primary">+ New Vacancy</a>
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
            <strong>Vacancies</strong> are job openings that combine Position + Workstation with salary, benefits, and application details.
            Published vacancies are visible to the public at <a href="/vacancies" target="_blank">/vacancies</a>.
        </p>
    </div>

    <!-- Active Vacancies -->
    <div class="card">
        <h2 class="card-title">Active Vacancies (<?php echo count($vacanciesData); ?>)</h2>

        <?php if (empty($vacanciesData)): ?>
            <div style="text-align: center; padding: 3rem 1rem;">
                <p class="text-muted" style="font-size: 1.2rem; margin-bottom: 1rem;">No Vacancies Yet</p>
                <p class="text-muted" style="margin-bottom: 2rem;">Create job vacancies to attract talent for your organization.</p>
                <a href="/organizations/departments/human_resource/vacancies/form/" class="btn btn-primary">Create Your First Vacancy</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Vacancy</th>
                            <th style="padding: 1rem;">Position</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Priority</th>
                            <th style="padding: 1rem;">Openings</th>
                            <th style="padding: 1rem;">Deadline</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vacanciesData as $vac): ?>
                            <?php
                            $vacancy = new \App\Classes\OrganizationVacancy($vac);
                            $daysUntil = $vacancy->getDaysUntilDeadline();
                            ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($vac['title']); ?></strong>
                                    <?php if (!empty($vac['code'])): ?>
                                        <br>
                                        <code style="background: var(--bg-light); padding: 0.125rem 0.375rem; border-radius: 4px; font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($vac['code']); ?>
                                        </code>
                                    <?php endif; ?>
                                    <?php if ($vac['is_published']): ?>
                                        <span style="display: inline-block; margin-left: 0.5rem; padding: 0.125rem 0.5rem; background: #10b981; color: white; border-radius: 4px; font-size: 0.75rem;">PUBLIC</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($vac['position_name'] ?? '-'); ?>
                                    <br>
                                    <span class="text-muted" style="font-size: 0.875rem;"><?php echo htmlspecialchars($vac['organization_name'] ?? ''); ?></span>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.875rem; <?php echo $vacancy->getStatusBadgeClass(); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $vac['status'])); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.875rem; <?php echo $vacancy->getPriorityBadgeClass(); ?>">
                                        <?php echo ucfirst($vac['priority']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $vac['openings_count']; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($vac['application_deadline']): ?>
                                        <?php echo date('M d, Y', strtotime($vac['application_deadline'])); ?>
                                        <?php if ($daysUntil !== null): ?>
                                            <br>
                                            <span class="text-muted" style="font-size: 0.75rem;">
                                                <?php if ($daysUntil > 0): ?>
                                                    (<?php echo $daysUntil; ?> days left)
                                                <?php elseif ($daysUntil == 0): ?>
                                                    (Today!)
                                                <?php else: ?>
                                                    (Expired)
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No deadline</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/human_resource/vacancies/view/?id=<?php echo $vac['id']; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">View</a>
                                    <a href="/organizations/departments/human_resource/vacancies/form/?id=<?php echo $vac['id']; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Edit</a>
                                    <a href="/organizations/departments/human_resource/vacancies/delete/?id=<?php echo $vac['id']; ?>" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Are you sure you want to delete this vacancy?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Deleted Vacancies (Trash) -->
    <?php if (!empty($deletedVacancies)): ?>
        <div class="card" style="margin-top: 2rem;">
            <h2 class="card-title">Trash (<?php echo count($deletedVacancies); ?>)</h2>
            <p class="text-muted text-small">Deleted vacancies can be restored.</p>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Title</th>
                            <th style="padding: 1rem;">Code</th>
                            <th style="padding: 1rem;">Deleted At</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deletedVacancies as $vacancy): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); opacity: 0.6;">
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($vacancy->getTitle()); ?></td>
                                <td style="padding: 1rem;">
                                    <code style="background: var(--bg-light); padding: 0.125rem 0.375rem; border-radius: 4px; font-size: 0.875rem;">
                                        <?php echo htmlspecialchars($vacancy->getCode()); ?>
                                    </code>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($vacancy->getDeletedAt()): ?>
                                        <?php echo date('M d, Y H:i', strtotime($vacancy->getDeletedAt())); ?>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <a href="/organizations/departments/human_resource/vacancies/restore/?id=<?php echo $vacancy->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Restore</a>
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
