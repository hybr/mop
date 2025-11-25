<?php
require_once __DIR__ . '/../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationVacancyRepository;
use App\Classes\OrganizationVacancy;
use App\Classes\OrganizationPositionRepository;
use App\Classes\OrganizationRepository;

$auth = new Auth();
$user = $auth->getCurrentUser(false); // Optional login

// Get vacancy ID from query string
$vacancyId = $_GET['id'] ?? null;

if (!$vacancyId) {
    header('Location: /vacancies?error=' . urlencode('Vacancy not found'));
    exit;
}

$vacancyRepo = new OrganizationVacancyRepository();

// Get published vacancy (PUBLIC access)
$vacancyData = $vacancyRepo->findByIdPublic($vacancyId);

if (!$vacancyData) {
    header('Location: /vacancies?error=' . urlencode('Vacancy not found or not available'));
    exit;
}

$vacancy = new OrganizationVacancy($vacancyData);

// Get related data
$positionRepo = new OrganizationPositionRepository();
$orgRepo = new OrganizationRepository();

$position = $positionRepo->findById($vacancy->getOrganizationPositionId());
$organization = $orgRepo->findByIdPublic($vacancy->getOrganizationId());

$daysUntil = $vacancy->getDaysUntilDeadline();

$pageTitle = $vacancy->getTitle() . ' - Job Vacancy';
include __DIR__ . '/../../../views/header.php';
?>

<div class="py-4">
    <!-- Back Button -->
    <div style="margin-bottom: 2rem;">
        <a href="/vacancies" class="text-muted" style="text-decoration: none;">&larr; Back to All Vacancies</a>
    </div>

    <!-- Header -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
            <div style="flex: 1;">
                <h1 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($vacancy->getTitle()); ?></h1>
                <?php if ($vacancy->getCode()): ?>
                    <code style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.875rem;">
                        <?php echo htmlspecialchars($vacancy->getCode()); ?>
                    </code>
                <?php endif; ?>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <span style="padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.875rem; <?php echo $vacancy->getPriorityBadgeClass(); ?>">
                    <?php echo ucfirst($vacancy->getPriority()); ?> Priority
                </span>
                <span style="padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.875rem; <?php echo $vacancy->getStatusBadgeClass(); ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $vacancy->getStatus())); ?>
                </span>
            </div>
        </div>

        <?php if ($organization): ?>
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <p class="text-muted" style="margin: 0;">
                    Posted by: <strong><?php echo htmlspecialchars($organization['short_name'] ?? $organization['full_legal_name']); ?></strong>
                </p>
            </div>
        <?php endif; ?>
    </div>

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

            <!-- Position Requirements (if available) -->
            <?php if ($position): ?>
                <div class="card" style="margin-bottom: 2rem;">
                    <h2 class="card-title">Requirements</h2>

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
                        <div style="margin-bottom: 1rem;">
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

                    <?php if ($position->getEmploymentType()): ?>
                        <div>
                            <strong>Employment Type:</strong>
                            <p class="text-muted" style="margin: 0.25rem 0 0 0;">
                                <?php echo htmlspecialchars($position->getEmploymentTypeName()); ?>
                            </p>
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
        </div>

        <!-- Right Column: Quick Info & Apply -->
        <div>
            <!-- Quick Info Card -->
            <div class="card" style="margin-bottom: 2rem; position: sticky; top: 1rem;">
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

                <!-- Apply Button -->
                <?php if ($vacancy->isAcceptingApplications()): ?>
                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                        <?php if ($vacancy->getApplicationUrl()): ?>
                            <a href="<?php echo htmlspecialchars($vacancy->getApplicationUrl()); ?>" target="_blank" class="btn btn-primary" style="width: 100%; text-align: center; display: block; padding: 1rem; font-size: 1.125rem;">
                                Apply Now →
                            </a>
                        <?php elseif ($vacancy->getContactEmail()): ?>
                            <a href="mailto:<?php echo htmlspecialchars($vacancy->getContactEmail()); ?>?subject=Application for <?php echo urlencode($vacancy->getTitle()); ?>" class="btn btn-primary" style="width: 100%; text-align: center; display: block; padding: 1rem; font-size: 1.125rem;">
                                Apply via Email
                            </a>
                        <?php endif; ?>

                        <!-- Contact Info -->
                        <?php if ($vacancy->getContactPerson() || $vacancy->getContactEmail() || $vacancy->getContactPhone()): ?>
                            <div style="margin-top: 1rem; padding: 1rem; background: var(--bg-light); border-radius: 4px; font-size: 0.875rem;">
                                <div class="text-muted" style="margin-bottom: 0.5rem;">Contact Information</div>
                                <?php if ($vacancy->getContactPerson()): ?>
                                    <div><strong><?php echo htmlspecialchars($vacancy->getContactPerson()); ?></strong></div>
                                <?php endif; ?>
                                <?php if ($vacancy->getContactEmail()): ?>
                                    <div><?php echo htmlspecialchars($vacancy->getContactEmail()); ?></div>
                                <?php endif; ?>
                                <?php if ($vacancy->getContactPhone()): ?>
                                    <div><?php echo htmlspecialchars($vacancy->getContactPhone()); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 2rem; padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 4px; text-align: center;">
                        <strong>Applications Closed</strong>
                        <div style="font-size: 0.875rem; margin-top: 0.5rem;">This vacancy is no longer accepting applications.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../views/footer.php'; ?>
