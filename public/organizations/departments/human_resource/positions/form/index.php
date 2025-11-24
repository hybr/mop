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

// Only Super Admin can create/edit positions
if (!$positionRepo->isSuperAdmin($user->getEmail())) {
    header('Location: /organizations/departments/human_resource/positions/?error=' . urlencode('Only Super Admin can manage positions'));
    exit;
}

$error = '';
$position = new OrganizationPosition();
$isEdit = false;

// Check if editing existing position
if (isset($_GET['id'])) {
    $position = $positionRepo->findById($_GET['id']);
    if (!$position) {
        header('Location: /organizations/departments/human_resource/positions/?error=' . urlencode('Position not found'));
        exit;
    }
    $isEdit = true;
}

// Get dropdown options
$departments = $deptRepo->findAll();
$teams = $teamRepo->findAll();
$designations = $designationRepo->findAll();
$educationLevels = OrganizationPosition::getEducationLevels();
$employmentTypes = OrganizationPosition::getEmploymentTypes();

// Get existing positions for "reports to" dropdown
$allPositions = $positionRepo->findActive();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $position->setName($_POST['name'] ?? '');
        $position->setCode($_POST['code'] ?? '');
        $position->setDescription($_POST['description'] ?? '');
        $position->setOrganizationDepartmentId($_POST['organization_department_id'] ?? null);
        $position->setOrganizationDepartmentTeamId($_POST['organization_department_team_id'] ?? null);
        $position->setOrganizationDesignationId($_POST['organization_designation_id'] ?? null);
        $position->setMinEducation($_POST['min_education'] ?? null);
        $position->setMinEducationField($_POST['min_education_field'] ?? '');
        $position->setMinExperienceYears($_POST['min_experience_years'] ?? null);
        $position->setSkillsRequired($_POST['skills_required'] ?? '');
        $position->setSkillsPreferred($_POST['skills_preferred'] ?? '');
        $position->setCertificationsRequired($_POST['certifications_required'] ?? '');
        $position->setCertificationsPreferred($_POST['certifications_preferred'] ?? '');
        $position->setEmploymentType($_POST['employment_type'] ?? 'full_time');
        $position->setReportsToPositionId($_POST['reports_to_position_id'] ?? null);
        $position->setHeadcount($_POST['headcount'] ?? 1);
        $position->setSalaryRangeMin($_POST['salary_range_min'] ?? null);
        $position->setSalaryRangeMax($_POST['salary_range_max'] ?? null);
        $position->setSalaryCurrency($_POST['salary_currency'] ?? 'INR');
        $position->setIsActive(isset($_POST['is_active']) ? 1 : 0);
        $position->setSortOrder($_POST['sort_order'] ?? 0);

        // Validate
        $errors = $position->validate();
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        if ($isEdit) {
            $positionRepo->update($position, $user->getId(), $user->getEmail());
            header('Location: /organizations/departments/human_resource/positions/?success=' . urlencode('Position updated successfully'));
        } else {
            $positionRepo->create($position, $user->getId(), $user->getEmail());
            header('Location: /organizations/departments/human_resource/positions/?success=' . urlencode('Position created successfully'));
        }
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = $isEdit ? 'Edit Position' : 'New Position';
include __DIR__ . '/../../../../../../views/header.php';
?>

<div class="py-4">
    <div style="margin-bottom: 2rem;">
        <a href="/organizations/departments/human_resource/positions/" class="text-muted" style="text-decoration: none;">&larr; Back to Positions</a>
        <h1 style="margin-top: 0.5rem;"><?php echo $isEdit ? 'Edit Position' : 'Create New Position'; ?></h1>
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
            <label for="name" class="form-label">Position Name *</label>
            <input
                type="text"
                id="name"
                name="name"
                class="form-input"
                required
                value="<?php echo htmlspecialchars($position->getName() ?? ''); ?>"
                placeholder="e.g., Senior Software Engineer"
            >
        </div>

        <div class="form-group">
            <label for="code" class="form-label">Position Code *</label>
            <input
                type="text"
                id="code"
                name="code"
                class="form-input"
                required
                pattern="[A-Z0-9_]+"
                style="text-transform: uppercase;"
                value="<?php echo htmlspecialchars($position->getCode() ?? ''); ?>"
                placeholder="e.g., SR_SWE"
            >
            <small class="text-muted text-small">Uppercase letters, numbers, and underscores only</small>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea
                id="description"
                name="description"
                class="form-input"
                rows="3"
                placeholder="Position responsibilities and overview"
            ><?php echo htmlspecialchars($position->getDescription() ?? ''); ?></textarea>
        </div>

        <!-- Department, Team, Designation -->
        <h2 class="card-title" style="margin-top: 2rem;">Organization Structure</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="organization_department_id" class="form-label">Department *</label>
                <select id="organization_department_id" name="organization_department_id" class="form-input" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept->getId(); ?>" <?php echo $position->getOrganizationDepartmentId() === $dept->getId() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept->getLabel()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="organization_department_team_id" class="form-label">Team (Optional)</label>
                <select id="organization_department_team_id" name="organization_department_team_id" class="form-input">
                    <option value="">No specific team</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?php echo $team->getId(); ?>" <?php echo $position->getOrganizationDepartmentTeamId() === $team->getId() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($team->getLabel()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="organization_designation_id" class="form-label">Designation *</label>
                <select id="organization_designation_id" name="organization_designation_id" class="form-input" required>
                    <option value="">Select Designation</option>
                    <?php foreach ($designations as $desig): ?>
                        <option value="<?php echo $desig->getId(); ?>" <?php echo $position->getOrganizationDesignationId() === $desig->getId() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($desig->getLabel()); ?>
                            <?php if ($desig->getLevel()): ?> - <?php echo $desig->getLevelName(); ?><?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Requirements -->
        <h2 class="card-title" style="margin-top: 2rem;">Requirements</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="min_education" class="form-label">Minimum Education</label>
                <select id="min_education" name="min_education" class="form-input">
                    <option value="">Not specified</option>
                    <?php foreach ($educationLevels as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo $position->getMinEducation() === $key ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="min_education_field" class="form-label">Field of Study</label>
                <input
                    type="text"
                    id="min_education_field"
                    name="min_education_field"
                    class="form-input"
                    value="<?php echo htmlspecialchars($position->getMinEducationField() ?? ''); ?>"
                    placeholder="e.g., Computer Science, Engineering"
                >
            </div>

            <div class="form-group">
                <label for="min_experience_years" class="form-label">Minimum Experience (Years)</label>
                <input
                    type="number"
                    id="min_experience_years"
                    name="min_experience_years"
                    class="form-input"
                    min="0"
                    max="50"
                    value="<?php echo htmlspecialchars($position->getMinExperienceYears() ?? ''); ?>"
                    placeholder="0 for fresher"
                >
            </div>
        </div>

        <div class="form-group">
            <label for="skills_required" class="form-label">Required Skills</label>
            <textarea
                id="skills_required"
                name="skills_required"
                class="form-input"
                rows="2"
                placeholder="Comma-separated list: JavaScript, Python, SQL, Git"
            ><?php echo htmlspecialchars($position->getSkillsRequired() ?? ''); ?></textarea>
            <small class="text-muted text-small">Enter skills separated by commas</small>
        </div>

        <div class="form-group">
            <label for="skills_preferred" class="form-label">Preferred Skills</label>
            <textarea
                id="skills_preferred"
                name="skills_preferred"
                class="form-input"
                rows="2"
                placeholder="Comma-separated list: React, AWS, Docker"
            ><?php echo htmlspecialchars($position->getSkillsPreferred() ?? ''); ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="certifications_required" class="form-label">Required Certifications</label>
                <input
                    type="text"
                    id="certifications_required"
                    name="certifications_required"
                    class="form-input"
                    value="<?php echo htmlspecialchars($position->getCertificationsRequired() ?? ''); ?>"
                    placeholder="e.g., PMP, AWS Certified"
                >
            </div>

            <div class="form-group">
                <label for="certifications_preferred" class="form-label">Preferred Certifications</label>
                <input
                    type="text"
                    id="certifications_preferred"
                    name="certifications_preferred"
                    class="form-input"
                    value="<?php echo htmlspecialchars($position->getCertificationsPreferred() ?? ''); ?>"
                    placeholder="e.g., Scrum Master"
                >
            </div>
        </div>

        <!-- Position Details -->
        <h2 class="card-title" style="margin-top: 2rem;">Position Details</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="employment_type" class="form-label">Employment Type</label>
                <select id="employment_type" name="employment_type" class="form-input">
                    <?php foreach ($employmentTypes as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($position->getEmploymentType() ?? 'full_time') === $key ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="reports_to_position_id" class="form-label">Reports To</label>
                <select id="reports_to_position_id" name="reports_to_position_id" class="form-input">
                    <option value="">No reporting position</option>
                    <?php foreach ($allPositions as $pos): ?>
                        <?php if (!$isEdit || $pos->getId() !== $position->getId()): ?>
                            <option value="<?php echo $pos->getId(); ?>" <?php echo $position->getReportsToPositionId() === $pos->getId() ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($pos->getLabel()); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="headcount" class="form-label">Headcount</label>
                <input
                    type="number"
                    id="headcount"
                    name="headcount"
                    class="form-input"
                    min="1"
                    value="<?php echo htmlspecialchars($position->getHeadcount() ?? 1); ?>"
                >
                <small class="text-muted text-small">Number of positions available</small>
            </div>
        </div>

        <!-- Salary Range -->
        <h2 class="card-title" style="margin-top: 2rem;">Compensation (Optional)</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="salary_currency" class="form-label">Currency</label>
                <select id="salary_currency" name="salary_currency" class="form-input">
                    <option value="INR" <?php echo ($position->getSalaryCurrency() ?? 'INR') === 'INR' ? 'selected' : ''; ?>>INR</option>
                    <option value="USD" <?php echo $position->getSalaryCurrency() === 'USD' ? 'selected' : ''; ?>>USD</option>
                    <option value="EUR" <?php echo $position->getSalaryCurrency() === 'EUR' ? 'selected' : ''; ?>>EUR</option>
                    <option value="GBP" <?php echo $position->getSalaryCurrency() === 'GBP' ? 'selected' : ''; ?>>GBP</option>
                </select>
            </div>

            <div class="form-group">
                <label for="salary_range_min" class="form-label">Minimum Salary</label>
                <input
                    type="number"
                    id="salary_range_min"
                    name="salary_range_min"
                    class="form-input"
                    min="0"
                    step="1000"
                    value="<?php echo htmlspecialchars($position->getSalaryRangeMin() ?? ''); ?>"
                    placeholder="e.g., 500000"
                >
            </div>

            <div class="form-group">
                <label for="salary_range_max" class="form-label">Maximum Salary</label>
                <input
                    type="number"
                    id="salary_range_max"
                    name="salary_range_max"
                    class="form-input"
                    min="0"
                    step="1000"
                    value="<?php echo htmlspecialchars($position->getSalaryRangeMax() ?? ''); ?>"
                    placeholder="e.g., 800000"
                >
            </div>
        </div>

        <!-- Status -->
        <h2 class="card-title" style="margin-top: 2rem;">Status</h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="sort_order" class="form-label">Sort Order</label>
                <input
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    class="form-input"
                    value="<?php echo htmlspecialchars($position->getSortOrder() ?? 0); ?>"
                >
            </div>

            <div class="form-group" style="display: flex; align-items: center; padding-top: 2rem;">
                <input
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    <?php echo ($position->getIsActive() !== false && $position->getIsActive() !== 0) || !$isEdit ? 'checked' : ''; ?>
                    style="margin-right: 0.5rem;"
                >
                <label for="is_active">Active</label>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Update Position' : 'Create Position'; ?></button>
            <a href="/organizations/departments/human_resource/positions/" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../../../../../views/footer.php'; ?>
