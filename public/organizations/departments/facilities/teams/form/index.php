<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\DepartmentTeam;
use App\Classes\FacilityTeamRepository;
use App\Classes\OrganizationRepository;
use App\Components\OrganizationField;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$facilityTeamRepo = new FacilityTeamRepository();
$orgRepo = new OrganizationRepository();

$errors = [];
$success = false;
$isEdit = false;
$team = new DepartmentTeam();

// Check if editing existing team
if (isset($_GET['id'])) {
    $isEdit = true;
    $team = $facilityTeamRepo->findById($_GET['id']);

    if (!$team) {
        header('Location: /organizations/departments/facilities/teams?error=Team not found');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Populate team from form data
        $team->setName($_POST['name'] ?? '');
        $team->setCode($_POST['code'] ?? '');
        $team->setDescription($_POST['description'] ?? '');
        $team->setOrganizationDepartmentId($_POST['organization_department_id'] ?? null);
        $team->setOrganizationId($_POST['organization_id'] ?? null);
        $team->setParentTeamId($_POST['parent_team_id'] ?? null);
        $team->setIsActive(isset($_POST['is_active']) ? 1 : 0);
        $team->setSortOrder($_POST['sort_order'] ?? 0);

        // Validate
        $errors = $team->validate();

        if (empty($errors)) {
            if ($isEdit) {
                // Update existing
                $facilityTeamRepo->update($team, $user->getId(), $user->getEmail());
                $success = "Facility team updated successfully!";
            } else {
                // Create new
                $team = $facilityTeamRepo->create($team, $user->getId(), $user->getEmail());
                $success = "Facility team created successfully!";
                $isEdit = true; // Switch to edit mode after creation
            }
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// Get organizations for dropdown
$organizations = $orgRepo->findAllByUser($user->getId());

// Get selected organization details if organization_id is set
$selectedOrganization = null;
if ($team->getOrganizationId()) {
    $selectedOrganization = $orgRepo->findById($team->getOrganizationId(), $user->getId());
}

// Get facility teams for parent dropdown (exclude current if editing)
$allTeams = $facilityTeamRepo->findAll(200);
if ($isEdit) {
    $allTeams = array_filter($allTeams, function($dept) use ($team) {
        return $dept->getId() !== $team->getId();
    });
}

$pageTitle = $isEdit ? 'Edit Facility Team' : 'New Facility Team';
include __DIR__ . '/../../../../../views/header.php';
?>

<div class="py-4">
    <!-- Breadcrumb Navigation -->
    <div style="margin-bottom: 1rem;">
        <a href="/organizations/" class="link">Organizations</a>
        <span style="color: var(--text-light);"> / </span>
        <a href="/organizations/departments/facilities/teams" class="link">Facility Teams</a>
        <span style="color: var(--text-light);"> / </span>
        <span><?php echo $isEdit ? 'Edit' : 'New'; ?></span>
    </div>

    <h1 style="margin-bottom: 2rem;"><?php echo $pageTitle; ?></h1>

    <!-- Success Message -->
    <?php if ($success): ?>
        <div style="background-color: rgba(76, 175, 80, 0.1); border-left: 4px solid var(--secondary-color); padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
            <strong style="color: var(--secondary-color);">&#x2713; Success!</strong>
            <p style="margin: 0.5rem 0 0 0;"><?php echo htmlspecialchars($success); ?></p>
        </div>
    <?php endif; ?>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div style="background-color: rgba(244, 67, 54, 0.1); border-left: 4px solid #f44336; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
            <strong style="color: #f44336;">&#x2715; Error!</strong>
            <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="" style="max-width: 800px;">
            <!-- Basic Details Section -->
            <div style="margin-bottom: 2rem;">
                <h2 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-color);">
                    Basic Details
                </h2>

                <!-- Name Field -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Team Name <span style="color: #f44336;">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?php echo htmlspecialchars($team->getName() ?? ''); ?>"
                        required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        placeholder="e.g., Operations, Maintenance, Security"
                    >
                    <small class="text-muted">The name of this facility team</small>
                </div>

                <!-- Code Field -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="code" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Team Code <span style="color: #f44336;">*</span>
                    </label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        value="<?php echo htmlspecialchars($team->getCode() ?? ''); ?>"
                        required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem; text-transform: uppercase;"
                        placeholder="e.g., OPS, MAINT, SEC"
                        pattern="[A-Z0-9_]{2,20}"
                        maxlength="20"
                        oninput="this.value = this.value.toUpperCase()"
                    >
                    <small class="text-muted">2-20 characters, uppercase letters, numbers, and underscores only</small>
                </div>

                <!-- Description Field -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Description
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem; font-family: inherit;"
                        placeholder="Brief description of this team's responsibilities..."
                    ><?php echo htmlspecialchars($team->getDescription() ?? ''); ?></textarea>
                    <small class="text-muted">Optional description of the team</small>
                </div>
            </div>

            <!-- Organization & Hierarchy Section -->
            <div style="margin-bottom: 2rem;">
                <h2 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-color);">
                    Organization & Hierarchy
                </h2>

                <!-- Organization Field -->
                <?php if ($selectedOrganization): ?>
                    <?php echo OrganizationField::render([
                        'label' => 'Organization',
                        'name' => 'organization_id',
                        'organization_id' => $selectedOrganization->getId(),
                        'organization_name' => $selectedOrganization->getFullName(),
                        'required' => false,
                        'help_text' => 'This team is assigned to this organization'
                    ]); ?>
                <?php else: ?>
                    <div style="margin-bottom: 1.5rem;">
                        <label for="organization_id" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Organization
                        </label>
                        <select
                            id="organization_id"
                            name="organization_id"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        >
                            <option value="">Global (All Organizations)</option>
                            <?php foreach ($organizations as $org): ?>
                                <option value="<?php echo $org->getId(); ?>">
                                    <?php echo htmlspecialchars($org->getName()); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Leave as Global if this team applies to all organizations</small>
                    </div>
                <?php endif; ?>

                <!-- Parent Team Field -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="parent_team_id" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Parent Team
                    </label>
                    <select
                        id="parent_team_id"
                        name="parent_team_id"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                    >
                        <option value="">None (Top Level)</option>
                        <?php foreach ($allTeams as $dept): ?>
                            <option value="<?php echo $dept->getId(); ?>"
                                <?php echo ($team->getParentTeamId() === $dept->getId()) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept->getLabel()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Optional: Select a parent team for hierarchical organization</small>
                </div>

                <!-- Sort Order Field -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="sort_order" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Sort Order
                    </label>
                    <input
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        value="<?php echo $team->getSortOrder() ?? 0; ?>"
                        min="0"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        placeholder="0"
                    >
                    <small class="text-muted">Lower numbers appear first in lists (default: 0)</small>
                </div>
            </div>

            <!-- Status Section -->
            <div style="margin-bottom: 2rem;">
                <h2 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-color);">
                    Status
                </h2>

                <!-- Active Status Checkbox -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input
                            type="checkbox"
                            id="is_active"
                            name="is_active"
                            <?php echo ($team->getIsActive() || !$isEdit) ? 'checked' : ''; ?>
                            style="margin-right: 0.5rem; width: 18px; height: 18px; cursor: pointer;"
                        >
                        <span style="font-weight: 500;">Active Team</span>
                    </label>
                    <small class="text-muted" style="margin-left: 1.5rem; display: block; margin-top: 0.25rem;">
                        Inactive teams are hidden from dropdown selections
                    </small>
                </div>
            </div>

            <!-- Form Actions -->
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                    <?php echo $isEdit ? 'Update Team' : 'Create Team'; ?>
                </button>
                <a href="/organizations/departments/facilities/teams" class="btn btn-secondary" style="padding: 0.75rem 2rem; text-decoration: none;">
                    Cancel
                </a>
                <?php if ($isEdit): ?>
                    <a href="/organizations/departments/facilities/teams/form/" class="btn btn-secondary" style="padding: 0.75rem 2rem; text-decoration: none; margin-left: auto;">
                        + Create New Instead
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Help Section -->
    <div class="card" style="margin-top: 2rem; background-color: var(--bg-light);">
        <h3 style="margin-bottom: 1rem;">&#128161; Tips</h3>
        <ul style="margin: 0; padding-left: 1.5rem; line-height: 1.8;">
            <li><strong>Team Code:</strong> Use short, memorable codes like "OPS" for Operations or "MAINT" for Maintenance</li>
            <li><strong>Organization:</strong> Leave as "Global" if this team type applies to all organizations</li>
            <li><strong>Parent Team:</strong> Create hierarchical structures like "Facilities > Maintenance > HVAC"</li>
            <li><strong>Sort Order:</strong> Use this to control the order teams appear in lists and dropdowns</li>
        </ul>
    </div>
</div>

<!-- Auto-focus first input on page load -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    if (nameInput && !nameInput.value) {
        nameInput.focus();
    }
});

// Show character count for code field
const codeInput = document.getElementById('code');
if (codeInput) {
    codeInput.addEventListener('input', function() {
        const length = this.value.length;
        const maxLength = 20;
        const hint = this.nextElementSibling;
        if (length > 0) {
            hint.textContent = `${length}/${maxLength} characters - Uppercase letters, numbers, and underscores only`;
        } else {
            hint.textContent = '2-20 characters, uppercase letters, numbers, and underscores only';
        }
    });
}
</script>

<style>
/* Form styling improvements */
input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
}

input[type="checkbox"]:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .card form {
        max-width: 100%;
    }

    h1 {
        font-size: 1.5rem;
    }

    h2 {
        font-size: 1.2rem;
    }
}
</style>

<?php include __DIR__ . '/../../../../../views/footer.php'; ?>
