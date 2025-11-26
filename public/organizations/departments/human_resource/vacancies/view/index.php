<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationVacancyRepository;
use App\Classes\OrganizationVacancy;
use App\Classes\OrganizationPositionRepository;
use App\Classes\OrganizationRepository;
use App\Classes\WorkflowInstanceRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$vacancyRepo = new OrganizationVacancyRepository();

// Get vacancy ID from query string
$vacancyId = $_GET['id'] ?? null;

if (!$vacancyId) {
    header('Location: /organizations/departments/human_resource/vacancies/?error=' . urlencode('Vacancy not found'));
    exit;
}

// Get vacancy (user must own organization)
$vacancyData = $vacancyRepo->findAllWithRelations($user->getId(), 1, 0);
$vacancyFound = null;

foreach ($vacancyData as $vac) {
    if ($vac['id'] === $vacancyId) {
        $vacancyFound = $vac;
        break;
    }
}

if (!$vacancyFound) {
    // Try to find by ID directly
    $vacancyObj = $vacancyRepo->findById($vacancyId, $user->getId());
    if (!$vacancyObj) {
        header('Location: /organizations/departments/human_resource/vacancies/?error=' . urlencode('Vacancy not found or access denied'));
        exit;
    }
    $vacancyFound = $vacancyObj->toArray();
}

$vacancy = new OrganizationVacancy($vacancyFound);

// Get related data
$positionRepo = new OrganizationPositionRepository();
$orgRepo = new OrganizationRepository();

$position = $vacancy->getOrganizationPositionId() ? $positionRepo->findById($vacancy->getOrganizationPositionId()) : null;
$organization = $vacancy->getOrganizationId() ? $orgRepo->findById($vacancy->getOrganizationId(), $user->getId()) : null;

$daysUntil = $vacancy->getDaysUntilDeadline();

// Check if hiring workflow already exists
$instanceRepo = new WorkflowInstanceRepository();
$existingInstances = $instanceRepo->findByEntity('OrganizationVacancy', $vacancy->getId());
$activeWorkflow = null;
foreach ($existingInstances as $inst) {
    if ($inst->getStatus() === 'active') {
        $activeWorkflow = $inst;
        break;
    }
}

$pageTitle = $vacancy->getTitle() . ' - Vacancy Details';
include __DIR__ . '/../../../../../../views/header.php';
?>

<div class="py-4">
    <!-- Back Button -->
    <div style="margin-bottom: 2rem;">
        <a href="/organizations/departments/human_resource/vacancies/" class="text-muted" style="text-decoration: none;">&larr; Back to Vacancies</a>
    </div>

    <!-- Header -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
            <div style="flex: 1;">
                <h1 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($vacancy->getTitle()); ?></h1>
                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                    <?php if ($vacancy->getCode()): ?>
                        <code style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.875rem;">
                            <?php echo htmlspecialchars($vacancy->getCode()); ?>
                        </code>
                    <?php endif; ?>
                    <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.875rem; <?php echo $vacancy->getPriorityBadgeClass(); ?>">
                        <?php echo ucfirst($vacancy->getPriority()); ?> Priority
                    </span>
                    <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.875rem; <?php echo $vacancy->getStatusBadgeClass(); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $vacancy->getStatus())); ?>
                    </span>
                    <?php if ($vacancy->getIsPublished()): ?>
                        <span style="padding: 0.25rem 0.75rem; background: #10b981; color: white; border-radius: 4px; font-size: 0.875rem;">
                            PUBLISHED
                        </span>
                    <?php else: ?>
                        <span style="padding: 0.25rem 0.75rem; background: #6b7280; color: white; border-radius: 4px; font-size: 0.875rem;">
                            DRAFT
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="/organizations/departments/human_resource/vacancies/form/?id=<?php echo $vacancy->getId(); ?>" class="btn btn-primary">
                    Edit Vacancy
                </a>
                <?php if ($vacancy->getIsPublished()): ?>
                    <a href="/vacancies/view?id=<?php echo $vacancy->getId(); ?>" class="btn btn-secondary" target="_blank">
                        View Public Page
                    </a>
                <?php endif; ?>
                <a href="/organizations/departments/human_resource/vacancies/delete/?id=<?php echo $vacancy->getId(); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this vacancy?');">
                    Delete
                </a>
            </div>
        </div>

        <?php if ($organization): ?>
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <p class="text-muted" style="margin: 0;">
                    Organization: <strong><?php echo htmlspecialchars($organization->getLabel()); ?></strong>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Hiring Workflow Section -->
    <?php if ($vacancy->getIsPublished()): ?>
        <div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h2 style="margin: 0 0 1rem 0; color: white;">Hiring Workflow</h2>

            <?php if ($activeWorkflow): ?>
                <!-- Active Workflow -->
                <p style="margin: 0 0 1rem 0; opacity: 0.9;">
                    A hiring workflow is currently active for this vacancy.
                </p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="/organizations/departments/human_resource/hiring/instances/view/?id=<?php echo $activeWorkflow->getId(); ?>"
                       class="btn"
                       style="background: white; color: #667eea; border: none;">
                        View Workflow Progress
                    </a>
                    <a href="/tasks/"
                       class="btn"
                       style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.5);">
                        View My Tasks
                    </a>
                </div>
            <?php else: ?>
                <!-- Start Workflow -->
                <p style="margin: 0 0 1rem 0; opacity: 0.9;">
                    Start the automated hiring process to manage candidates from application to onboarding.
                </p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                    <a href="/workflows/start/?entity_type=OrganizationVacancy&entity_id=<?php echo $vacancy->getId(); ?>"
                       class="btn"
                       style="background: white; color: #667eea; border: none; font-weight: bold;"
                       onclick="return confirm('Start hiring workflow for this vacancy?\n\nThis will:\n- Create workflow instance\n- Assign tasks to HR team\n- Begin automated hiring process');">
                        🚀 Start Hiring Workflow
                    </a>
                    <a href="/organizations/departments/human_resource/hiring/"
                       class="btn"
                       style="background: transparent; color: white; border: 1px solid rgba(255,255,255,0.5);">
                        Learn More
                    </a>
                </div>
                <p style="margin: 1rem 0 0 0; opacity: 0.8; font-size: 0.875rem;">
                    💡 The workflow includes: Post Vacancy → Review Applications → Screen → Interview → Offer → Onboard
                </p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card" style="margin-bottom: 2rem; background: #f59e0b; color: white;">
            <p style="margin: 0;">
                ⚠️ <strong>Vacancy must be published</strong> before you can start the hiring workflow.
                <a href="/organizations/departments/human_resource/vacancies/form/?id=<?php echo $vacancy->getId(); ?>"
                   style="color: white; text-decoration: underline; margin-left: 0.5rem;">Publish now</a>
            </p>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Left Column: Main Content -->
        <div>
            <!-- Job Description -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">Job Description</h2>
                <div style="line-height: 1.8; white-space: pre-wrap;">
                    <?php echo nl2br(htmlspecialchars($vacancy->getDescription())); ?>
                </div>
            </div>

            <!-- Position Requirements -->
            <?php if ($position): ?>
                <div class="card" style="margin-bottom: 2rem;">
                    <h2 class="card-title">Position Requirements</h2>
                    <p class="text-muted" style="margin-bottom: 1rem;">
                        <strong>Position:</strong> <?php echo htmlspecialchars($position->getName()); ?>
                    </p>

                    <?php if ($position->getMinEducation()): ?>
                        <div style="margin-bottom: 1rem;">
                            <strong>Education:</strong>
                            <p class="text-muted" style="margin: 0.25rem 0 0 0;">
                                <?php echo htmlspecialchars($position->getEducationLevelName()); ?>
                                <?php if ($position->getMinEducationField()): ?>
                                    in <?php echo htmlspecialchars($position->getMinEducationField()); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($position->getMinExperienceYears()): ?>
                        <div style="margin-bottom: 1rem;">
                            <strong>Experience:</strong>
                            <p class="text-muted" style="margin: 0.25rem 0 0 0;">
                                <?php echo $position->getMinExperienceYears(); ?>+ years
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php
                    $skillsRequired = $position->getSkillsRequiredArray();
                    if (!empty($skillsRequired)):
                    ?>
                        <div style="margin-bottom: 1rem;">
                            <strong>Required Skills:</strong>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                                <?php foreach ($skillsRequired as $skill): ?>
                                    <span style="padding: 0.25rem 0.75rem; background: var(--primary-color); color: white; border-radius: 4px; font-size: 0.875rem;">
                                        <?php echo htmlspecialchars($skill); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    $skillsPreferred = $position->getSkillsPreferredArray();
                    if (!empty($skillsPreferred)):
                    ?>
                        <div>
                            <strong>Preferred Skills:</strong>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                                <?php foreach ($skillsPreferred as $skill): ?>
                                    <span style="padding: 0.25rem 0.75rem; background: var(--bg-light); color: var(--text-color); border-radius: 4px; font-size: 0.875rem;">
                                        <?php echo htmlspecialchars($skill); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Benefits -->
            <?php if ($vacancy->getBenefits()): ?>
                <div class="card" style="margin-bottom: 2rem;">
                    <h2 class="card-title">Benefits</h2>
                    <div style="line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($vacancy->getBenefits())); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Application Details -->
            <div class="card">
                <h2 class="card-title">Application Details</h2>

                <div style="display: grid; gap: 1rem;">
                    <div>
                        <strong>Application Method:</strong>
                        <p class="text-muted" style="margin: 0.25rem 0 0 0;">
                            <?php
                            $methods = OrganizationVacancy::getApplicationMethods();
                            echo htmlspecialchars($methods[$vacancy->getApplicationMethod()] ?? $vacancy->getApplicationMethod());
                            ?>
                        </p>
                    </div>

                    <?php if ($vacancy->getApplicationUrl()): ?>
                        <div>
                            <strong>Application URL:</strong>
                            <p class="text-muted" style="margin: 0.25rem 0 0 0;">
                                <a href="<?php echo htmlspecialchars($vacancy->getApplicationUrl()); ?>" target="_blank">
                                    <?php echo htmlspecialchars($vacancy->getApplicationUrl()); ?>
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($vacancy->getContactPerson()): ?>
                        <div>
                            <strong>Contact Person:</strong>
                            <p class="text-muted" style="margin: 0.25rem 0 0 0;">
                                <?php echo htmlspecialchars($vacancy->getContactPerson()); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($vacancy->getContactEmail()): ?>
                        <div>
                            <strong>Contact Email:</strong>
                            <p class="text-muted" style="margin: 0.25rem 0 0 0;">
                                <?php echo htmlspecialchars($vacancy->getContactEmail()); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($vacancy->getContactPhone()): ?>
                        <div>
                            <strong>Contact Phone:</strong>
                            <p class="text-muted" style="margin: 0.25rem 0 0 0;">
                                <?php echo htmlspecialchars($vacancy->getContactPhone()); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Quick Info & Stats -->
        <div>
            <!-- Quick Info Card -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">Quick Info</h2>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <!-- Openings -->
                    <div>
                        <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Openings</div>
                        <strong style="font-size: 1.25rem;"><?php echo $vacancy->getOpeningsCount(); ?> <?php echo $vacancy->getOpeningsCount() === 1 ? 'position' : 'positions'; ?></strong>
                    </div>

                    <!-- Salary -->
                    <div>
                        <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Salary Range</div>
                        <strong><?php echo $vacancy->getSalaryRangeFormatted(); ?></strong>
                    </div>

                    <!-- Posted Date -->
                    <?php if ($vacancy->getPostedDate()): ?>
                        <div>
                            <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Posted On</div>
                            <strong><?php echo date('M d, Y', strtotime($vacancy->getPostedDate())); ?></strong>
                        </div>
                    <?php endif; ?>

                    <!-- Deadline -->
                    <?php if ($vacancy->getApplicationDeadline()): ?>
                        <div>
                            <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Application Deadline</div>
                            <strong style="<?php echo ($daysUntil !== null && $daysUntil < 7) ? 'color: #dc2626;' : ''; ?>">
                                <?php echo date('M d, Y', strtotime($vacancy->getApplicationDeadline())); ?>
                            </strong>
                            <?php if ($daysUntil !== null && $daysUntil >= 0): ?>
                                <div class="text-muted" style="font-size: 0.75rem; margin-top: 0.25rem;">
                                    <?php if ($daysUntil === 0): ?>
                                        Last day to apply!
                                    <?php elseif ($daysUntil === 1): ?>
                                        1 day left
                                    <?php else: ?>
                                        <?php echo $daysUntil; ?> days left
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Target Start Date -->
                    <?php if ($vacancy->getTargetStartDate()): ?>
                        <div>
                            <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Expected Start Date</div>
                            <strong><?php echo date('M d, Y', strtotime($vacancy->getTargetStartDate())); ?></strong>
                        </div>
                    <?php endif; ?>

                    <!-- Vacancy Type -->
                    <div>
                        <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Vacancy Type</div>
                        <strong><?php echo ucfirst(str_replace('_', ' ', $vacancy->getVacancyType())); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card">
                <h2 class="card-title">Statistics</h2>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Views</div>
                        <strong style="font-size: 1.25rem;"><?php echo $vacancy->getViewsCount() ?? 0; ?></strong>
                    </div>

                    <div>
                        <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Applications</div>
                        <strong style="font-size: 1.25rem;"><?php echo $vacancy->getApplicationsCount() ?? 0; ?></strong>
                    </div>

                    <?php if ($vacancy->getPublishedAt()): ?>
                        <div>
                            <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Published At</div>
                            <strong><?php echo date('M d, Y H:i', strtotime($vacancy->getPublishedAt())); ?></strong>
                        </div>
                    <?php endif; ?>

                    <?php if ($vacancy->getFilledAt()): ?>
                        <div>
                            <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Filled At</div>
                            <strong><?php echo date('M d, Y H:i', strtotime($vacancy->getFilledAt())); ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../../../../views/footer.php'; ?>
