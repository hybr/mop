<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\Authorization;
use App\Classes\OrganizationDepartmentTeam;
use App\Classes\OrganizationDepartmentTeamRepository;
use App\Classes\OrganizationDepartmentRepository;
use App\Classes\OrganizationRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$teamRepo = new OrganizationDepartmentTeamRepository();
$deptRepo = new OrganizationDepartmentRepository();
$orgRepo = new OrganizationRepository();

// Only Super Admin can create/edit teams
if (!Authorization::isSuperAdmin($user->getEmail())) {
    header('Location: /organizations/departments/human_resource/teams/?error=' . urlencode('Only Super Admin can manage teams'));
    exit;
}

$error = '';
$team = new OrganizationDepartmentTeam();
$isEdit = false;

// Check if editing existing team
if (isset($_GET['id'])) {
    $team = $teamRepo->findById($_GET['id']);
    if (!$team) {
        header('Location: /organizations/departments/human_resource/teams/?error=' . urlencode('Team not found'));
        exit;
    }
    $isEdit = true;
}

// Get dropdown options
$departments = $deptRepo->findAll();
$organizations = $orgRepo->findAllByUser($user->getId());
$allTeams = $teamRepo->findAll(200);

// If editing, exclude current team from parent options
if ($isEdit) {
    $allTeams = array_filter($allTeams, function($t) use ($team) {
        return $t->getId() !== $team->getId();
    });
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        if ($isEdit) {
            $teamRepo->update($team, $user->getId(), $user->getEmail());
            header('Location: /organizations/departments/human_resource/teams/?success=' . urlencode('Team updated successfully'));
        } else {
            $teamRepo->create($team, $user->getId(), $user->getEmail());
            header('Location: /organizations/departments/human_resource/teams/?success=' . urlencode('Team created successfully'));
        }
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = $isEdit ? 'Edit Team' : 'New Team';
include __DIR__ . '/../../../../../../views/header.php';
?>

<div class="py-4">
    <div style="margin-bottom: 2rem;">
        <a href="/organizations/departments/human_resource/teams/" class="text-muted" style="text-decoration: none;">&larr; Back to Teams</a>
        <h1 style="margin-top: 0.5rem;"><?php echo $isEdit ? 'Edit Team' : 'Create New Team'; ?></h1>
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
            <label for="name" class="form-label">Team Name *</label>
            <input
                type="text"
                id="name"
                name="name"
                class="form-input"
                required
                value="<?php echo htmlspecialchars($team->getName() ?? ''); ?>"
                placeholder="e.g., Operations Team, Maintenance Team"
            >
        </div>

        <div class="form-group">
            <label for="code" class="form-label">Team Code *</label>
            <input
                type="text"
                id="code"
                name="code"
                class="form-input"
                required
                pattern="[A-Z0-9_]+"
                style="text-transform: uppercase;"
                value="<?php echo htmlspecialchars($team->getCode() ?? ''); ?>"
                placeholder="e.g., OPS_TEAM"
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
                placeholder="Team responsibilities and overview"
            ><?php echo htmlspecialchars($team->getDescription() ?? ''); ?></textarea>
        </div>

        <!-- Department & Organization -->
        <h2 class="card-title" style="margin-top: 2rem;">Assignment</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="organization_department_id" class="form-label">Department *</label>
                <select id="organization_department_id" name="organization_department_id" class="form-input" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept->getId(); ?>" <?php echo $team->getOrganizationDepartmentId() === $dept->getId() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept->getName()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted text-small">Which department this team belongs to</small>
            </div>

            <div class="form-group">
                <label for="organization_id" class="form-label">Organization (Optional)</label>
                <select id="organization_id" name="organization_id" class="form-input">
                    <option value="">Global (All Organizations)</option>
                    <?php foreach ($organizations as $org): ?>
                        <option value="<?php echo $org->getId(); ?>" <?php echo $team->getOrganizationId() === $org->getId() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($org->getName()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted text-small">Leave empty for global teams</small>
            </div>
        </div>

        <!-- Hierarchy -->
        <h2 class="card-title" style="margin-top: 2rem;">Hierarchy (Optional)</h2>

        <div class="form-group">
            <label for="parent_team_id" class="form-label">Parent Team</label>
            <select id="parent_team_id" name="parent_team_id" class="form-input">
                <option value="">No Parent Team</option>
                <?php foreach ($allTeams as $t): ?>
                    <option value="<?php echo $t->getId(); ?>" <?php echo $team->getParentTeamId() === $t->getId() ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($t->getName() . ' (' . $t->getCode() . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted text-small">For creating sub-teams</small>
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
                    value="<?php echo htmlspecialchars($team->getSortOrder() ?? 0); ?>"
                >
            </div>

            <div class="form-group" style="display: flex; align-items: center; padding-top: 2rem;">
                <input
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    <?php echo ($team->getIsActive() !== false && $team->getIsActive() !== 0) || !$isEdit ? 'checked' : ''; ?>
                    style="margin-right: 0.5rem;"
                >
                <label for="is_active">Active</label>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Update Team' : 'Create Team'; ?></button>
            <a href="/organizations/departments/human_resource/teams/" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../../../../../views/footer.php'; ?>
