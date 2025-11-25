<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationVacancy;
use App\Classes\OrganizationVacancyRepository;
use App\Classes\OrganizationRepository;
use App\Classes\OrganizationPositionRepository;
use App\Classes\OrganizationWorkstationRepository;
use App\Components\OrganizationField;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$vacancyRepo = new OrganizationVacancyRepository();
$orgRepo = new OrganizationRepository();
$positionRepo = new OrganizationPositionRepository();
$workstationRepo = new OrganizationWorkstationRepository();

$error = '';
$vacancy = new OrganizationVacancy();
$isEdit = false;

// Check if editing existing vacancy
if (isset($_GET['id'])) {
    $vacancy = $vacancyRepo->findById($_GET['id'], $user->getId());
    if (!$vacancy) {
        header('Location: /organizations/departments/human_resource/vacancies/?error=' . urlencode('Vacancy not found or access denied'));
        exit;
    }
    $isEdit = true;
}

// Get dropdown options
$organizations = $orgRepo->findAllByUser($user->getId());
$positions = $positionRepo->findActive();
$workstations = $workstationRepo->findAllByUser($user->getId());
$vacancyTypes = OrganizationVacancy::getVacancyTypes();
$priorityLevels = OrganizationVacancy::getPriorityLevels();
$statusOptions = OrganizationVacancy::getStatusOptions();
$applicationMethods = OrganizationVacancy::getApplicationMethods();

// Get organization details for OrganizationField component
$selectedOrganization = null;
$organizationId = null;
$organizationName = null;

if ($isEdit && $vacancy->getOrganizationId()) {
    // Editing: Get the organization from vacancy
    $selectedOrganization = $orgRepo->findById($vacancy->getOrganizationId(), $user->getId());
    if ($selectedOrganization) {
        $organizationId = $selectedOrganization->getId();
        $organizationName = $selectedOrganization->getLabel();
    }
} elseif (count($organizations) === 1) {
    // Creating: Auto-select if user has only one organization
    $selectedOrganization = $organizations[0];
    $organizationId = $selectedOrganization->getId();
    $organizationName = $selectedOrganization->getLabel();
    // Set it on the vacancy object for new records
    if (!$isEdit) {
        $vacancy->setOrganizationId($organizationId);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $vacancy->setTitle($_POST['title'] ?? '');
        $vacancy->setCode($_POST['code'] ?? '');
        $vacancy->setDescription($_POST['description'] ?? '');
        $vacancy->setOrganizationId($_POST['organization_id'] ?? null);
        $vacancy->setOrganizationPositionId($_POST['organization_position_id'] ?? null);
        $vacancy->setOrganizationWorkstationId($_POST['organization_workstation_id'] ?? null);
        $vacancy->setVacancyType($_POST['vacancy_type'] ?? 'new');
        $vacancy->setPriority($_POST['priority'] ?? 'medium');
        $vacancy->setOpeningsCount($_POST['openings_count'] ?? 1);
        $vacancy->setPostedDate($_POST['posted_date'] ?? date('Y-m-d'));
        $vacancy->setApplicationDeadline($_POST['application_deadline'] ?? null);
        $vacancy->setTargetStartDate($_POST['target_start_date'] ?? null);
        $vacancy->setTargetEndDate($_POST['target_end_date'] ?? null);
        $vacancy->setSalaryOfferedMin($_POST['salary_offered_min'] ?? null);
        $vacancy->setSalaryOfferedMax($_POST['salary_offered_max'] ?? null);
        $vacancy->setSalaryCurrency($_POST['salary_currency'] ?? 'INR');
        $vacancy->setBenefits($_POST['benefits'] ?? '');
        $vacancy->setApplicationMethod($_POST['application_method'] ?? 'both');
        $vacancy->setApplicationUrl($_POST['application_url'] ?? '');
        $vacancy->setContactPerson($_POST['contact_person'] ?? '');
        $vacancy->setContactEmail($_POST['contact_email'] ?? '');
        $vacancy->setContactPhone($_POST['contact_phone'] ?? '');
        $vacancy->setStatus($_POST['status'] ?? 'draft');
        $vacancy->setIsPublished(isset($_POST['is_published']) ? 1 : 0);

        // Set published_at if publishing for first time
        if ($vacancy->getIsPublished() && !$vacancy->getPublishedAt()) {
            $vacancy->setPublishedAt(date('Y-m-d H:i:s'));
        }

        $vacancy->setIsActive(isset($_POST['is_active']) ? 1 : 0);
        $vacancy->setSortOrder($_POST['sort_order'] ?? 0);

        // Validate
        $errors = $vacancy->validate();
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        if ($isEdit) {
            $vacancyRepo->update($vacancy, $user->getId());
            header('Location: /organizations/departments/human_resource/vacancies/?success=' . urlencode('Vacancy updated successfully'));
        } else {
            $vacancyRepo->create($vacancy, $user->getId());
            header('Location: /organizations/departments/human_resource/vacancies/?success=' . urlencode('Vacancy created successfully'));
        }
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = $isEdit ? 'Edit Vacancy' : 'New Vacancy';
include __DIR__ . '/../../../../../../views/header.php';
?>

<div class="py-4">
    <div style="margin-bottom: 2rem;">
        <a href="/organizations/departments/human_resource/vacancies/" class="text-muted" style="text-decoration: none;">&larr; Back to Vacancies</a>
        <h1 style="margin-top: 0.5rem;"><?php echo $isEdit ? 'Edit Vacancy' : 'Create New Vacancy'; ?></h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="card">
        <!-- Basic Information -->
        <h2 class="card-title">Basic Information</h2>

        <div class="form-group">
            <label for="title" class="form-label">Vacancy Title *</label>
            <input
                type="text"
                id="title"
                name="title"
                class="form-input"
                required
                value="<?php echo htmlspecialchars($vacancy->getTitle() ?? ''); ?>"
                placeholder="e.g., Senior Frontend Developer - React/TypeScript"
            >
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="code" class="form-label">Vacancy Code</label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    class="form-input"
                    style="text-transform: uppercase;"
                    value="<?php echo htmlspecialchars($vacancy->getCode() ?? ''); ?>"
                    placeholder="e.g., VAC-2024-001"
                >
                <small class="text-muted text-small">Unique identifier (optional)</small>
            </div>

            <!-- Organization Field (Read-Only) -->
            <?php if ($organizationId && $organizationName): ?>
                <?php echo OrganizationField::render([
                    'label' => 'Organization',
                    'name' => 'organization_id',
                    'organization_id' => $organizationId,
                    'organization_name' => $organizationName,
                    'help_text' => 'Vacancy will be created for this organization',
                    'required' => true
                ]); ?>
            <?php else: ?>
                <div class="form-group">
                    <label for="organization_id" class="form-label">Organization *</label>
                    <select id="organization_id" name="organization_id" class="form-input" required>
                        <option value="">Select Organization</option>
                        <?php foreach ($organizations as $org): ?>
                            <option value="<?php echo $org->getId(); ?>" <?php echo $vacancy->getOrganizationId() === $org->getId() ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($org->getLabel()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted text-small">Please create an organization first</small>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Job Description *</label>
            <textarea
                id="description"
                name="description"
                class="form-input"
                rows="6"
                required
                placeholder="Describe the job responsibilities, requirements, and what the candidate will be doing..."
            ><?php echo htmlspecialchars($vacancy->getDescription() ?? ''); ?></textarea>
        </div>

        <!-- Position & Workstation -->
        <h2 class="card-title" style="margin-top: 2rem;">Position Details</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="organization_position_id" class="form-label">Position *</label>
                <select id="organization_position_id" name="organization_position_id" class="form-input" required>
                    <option value="">Select Position</option>
                    <?php foreach ($positions as $pos): ?>
                        <option value="<?php echo $pos->getId(); ?>" <?php echo $vacancy->getOrganizationPositionId() === $pos->getId() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pos->getLabel()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="organization_workstation_id" class="form-label">Workstation (Optional)</label>
                <select id="organization_workstation_id" name="organization_workstation_id" class="form-input">
                    <option value="">No specific workstation</option>
                    <?php foreach ($workstations as $ws): ?>
                        <option value="<?php echo $ws->getId(); ?>" <?php echo $vacancy->getOrganizationWorkstationId() === $ws->getId() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ws->getLabel()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Vacancy Settings -->
        <h2 class="card-title" style="margin-top: 2rem;">Vacancy Settings</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="vacancy_type" class="form-label">Vacancy Type *</label>
                <select id="vacancy_type" name="vacancy_type" class="form-input" required>
                    <?php foreach ($vacancyTypes as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo $vacancy->getVacancyType() === $value ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="priority" class="form-label">Priority *</label>
                <select id="priority" name="priority" class="form-input" required>
                    <?php foreach ($priorityLevels as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo $vacancy->getPriority() === $value ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="openings_count" class="form-label">Number of Openings *</label>
                <input
                    type="number"
                    id="openings_count"
                    name="openings_count"
                    class="form-input"
                    min="1"
                    required
                    value="<?php echo htmlspecialchars($vacancy->getOpeningsCount() ?? 1); ?>"
                >
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status *</label>
                <select id="status" name="status" class="form-input" required>
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo $vacancy->getStatus() === $value ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Timeline -->
        <h2 class="card-title" style="margin-top: 2rem;">Timeline</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="posted_date" class="form-label">Posted Date *</label>
                <input
                    type="date"
                    id="posted_date"
                    name="posted_date"
                    class="form-input"
                    required
                    value="<?php echo htmlspecialchars($vacancy->getPostedDate() ?? date('Y-m-d')); ?>"
                >
            </div>

            <div class="form-group">
                <label for="application_deadline" class="form-label">Application Deadline</label>
                <input
                    type="date"
                    id="application_deadline"
                    name="application_deadline"
                    class="form-input"
                    value="<?php echo htmlspecialchars($vacancy->getApplicationDeadline() ?? ''); ?>"
                >
            </div>

            <div class="form-group">
                <label for="target_start_date" class="form-label">Target Start Date</label>
                <input
                    type="date"
                    id="target_start_date"
                    name="target_start_date"
                    class="form-input"
                    value="<?php echo htmlspecialchars($vacancy->getTargetStartDate() ?? ''); ?>"
                >
            </div>

            <div class="form-group">
                <label for="target_end_date" class="form-label">Target End Date (for contracts)</label>
                <input
                    type="date"
                    id="target_end_date"
                    name="target_end_date"
                    class="form-input"
                    value="<?php echo htmlspecialchars($vacancy->getTargetEndDate() ?? ''); ?>"
                >
            </div>
        </div>

        <!-- Compensation -->
        <h2 class="card-title" style="margin-top: 2rem;">Compensation & Benefits</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="salary_offered_min" class="form-label">Minimum Salary</label>
                <input
                    type="number"
                    id="salary_offered_min"
                    name="salary_offered_min"
                    class="form-input"
                    min="0"
                    step="1000"
                    value="<?php echo htmlspecialchars($vacancy->getSalaryOfferedMin() ?? ''); ?>"
                    placeholder="e.g., 600000"
                >
            </div>

            <div class="form-group">
                <label for="salary_offered_max" class="form-label">Maximum Salary</label>
                <input
                    type="number"
                    id="salary_offered_max"
                    name="salary_offered_max"
                    class="form-input"
                    min="0"
                    step="1000"
                    value="<?php echo htmlspecialchars($vacancy->getSalaryOfferedMax() ?? ''); ?>"
                    placeholder="e.g., 1000000"
                >
            </div>

            <div class="form-group">
                <label for="salary_currency" class="form-label">Currency</label>
                <select id="salary_currency" name="salary_currency" class="form-input">
                    <option value="INR" <?php echo $vacancy->getSalaryCurrency() === 'INR' ? 'selected' : ''; ?>>INR (₹)</option>
                    <option value="USD" <?php echo $vacancy->getSalaryCurrency() === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                    <option value="EUR" <?php echo $vacancy->getSalaryCurrency() === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                    <option value="GBP" <?php echo $vacancy->getSalaryCurrency() === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="benefits" class="form-label">Benefits</label>
            <textarea
                id="benefits"
                name="benefits"
                class="form-input"
                rows="3"
                placeholder="e.g., Health insurance, flexible hours, remote work option, professional development budget"
            ><?php echo htmlspecialchars($vacancy->getBenefits() ?? ''); ?></textarea>
        </div>

        <!-- Application Details -->
        <h2 class="card-title" style="margin-top: 2rem;">Application Details</h2>

        <div class="form-group">
            <label for="application_method" class="form-label">Application Method *</label>
            <select id="application_method" name="application_method" class="form-input" required>
                <?php foreach ($applicationMethods as $value => $label): ?>
                    <option value="<?php echo $value; ?>" <?php echo $vacancy->getApplicationMethod() === $value ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="application_url" class="form-label">Application URL</label>
            <input
                type="url"
                id="application_url"
                name="application_url"
                class="form-input"
                value="<?php echo htmlspecialchars($vacancy->getApplicationUrl() ?? ''); ?>"
                placeholder="https://example.com/apply"
            >
            <small class="text-muted text-small">External application link (if applicable)</small>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="contact_person" class="form-label">Contact Person</label>
                <input
                    type="text"
                    id="contact_person"
                    name="contact_person"
                    class="form-input"
                    value="<?php echo htmlspecialchars($vacancy->getContactPerson() ?? ''); ?>"
                    placeholder="HR Manager Name"
                >
            </div>

            <div class="form-group">
                <label for="contact_email" class="form-label">Contact Email</label>
                <input
                    type="email"
                    id="contact_email"
                    name="contact_email"
                    class="form-input"
                    value="<?php echo htmlspecialchars($vacancy->getContactEmail() ?? ''); ?>"
                    placeholder="hr@company.com"
                >
            </div>

            <div class="form-group">
                <label for="contact_phone" class="form-label">Contact Phone</label>
                <input
                    type="tel"
                    id="contact_phone"
                    name="contact_phone"
                    class="form-input"
                    value="<?php echo htmlspecialchars($vacancy->getContactPhone() ?? ''); ?>"
                    placeholder="+91 9876543210"
                >
            </div>
        </div>

        <!-- Publishing & Status -->
        <h2 class="card-title" style="margin-top: 2rem;">Publishing</h2>

        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input
                        type="checkbox"
                        name="is_published"
                        <?php echo $vacancy->getIsPublished() ? 'checked' : ''; ?>
                        style="width: auto;"
                    >
                    <span>Publish to public job board</span>
                </label>
                <small class="text-muted text-small">Make this vacancy visible at /vacancies</small>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input
                        type="checkbox"
                        name="is_active"
                        <?php echo ($vacancy->getIsActive() !== null ? $vacancy->getIsActive() : true) ? 'checked' : ''; ?>
                        style="width: auto;"
                    >
                    <span>Active</span>
                </label>
            </div>
        </div>

        <div class="form-group" style="display: none;">
            <label for="sort_order" class="form-label">Sort Order</label>
            <input
                type="number"
                id="sort_order"
                name="sort_order"
                class="form-input"
                value="<?php echo htmlspecialchars($vacancy->getSortOrder() ?? 0); ?>"
            >
        </div>

        <!-- Form Actions -->
        <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
            <button type="submit" class="btn btn-primary">
                <?php echo $isEdit ? 'Update Vacancy' : 'Create Vacancy'; ?>
            </button>
            <a href="/organizations/departments/human_resource/vacancies/" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../../../../../views/footer.php'; ?>
