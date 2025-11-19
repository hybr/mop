<?php
require_once __DIR__ . '/../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationDepartment;
use App\Classes\OrganizationDepartmentRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$deptRepo = new OrganizationDepartmentRepository();

$error = '';
$success = '';
$department = null;
$isEdit = false;

// Check if editing existing department
if (isset($_GET['id'])) {
    $isEdit = true;
    $department = $deptRepo->findById($_GET['id']);

    if (!$department) {
        header('Location: /organizations/departments/?error=' . urlencode('Department not found'));
        exit;
    }

    // Check if user can edit (Super Admin only)
    if (!$deptRepo->canEdit($user->getEmail())) {
        header('Location: /organizations/departments/?error=' . urlencode('Only Super Admin can edit departments'));
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$department) {
            $department = new OrganizationDepartment();
        }

        $department->setName($_POST['name'] ?? '');
        $department->setCode($_POST['code'] ?? '');
        $department->setDescription($_POST['description'] ?? '');
        $department->setParentDepartmentId(!empty($_POST['parent_department_id']) ? $_POST['parent_department_id'] : null);
        $department->setOrganizationId(!empty($_POST['organization_id']) ? $_POST['organization_id'] : null);
        $department->setIsActive(isset($_POST['is_active']) ? 1 : 0);
        $department->setSortOrder(!empty($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0);

        // Validate
        $errors = $department->validate();
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        if ($isEdit) {
            $deptRepo->update($department, $user->getId(), $user->getEmail());
            $successMsg = 'Department "' . $department->getName() . '" updated successfully!';
        } else {
            $deptRepo->create($department, $user->getId(), $user->getEmail());
            $successMsg = 'Department "' . $department->getName() . '" created successfully!';
        }

        header('Location: /organizations/departments/?success=' . urlencode($successMsg));
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get all departments for parent dropdown
$allDepartments = $deptRepo->findAll(1000);

$pageTitle = $isEdit ? 'Edit Department' : 'New Department';
include __DIR__ . '/../../../../views/header.php';
?>

<div class="py-4">
    <div style="max-width: 800px; margin: 0 auto;">
        <div style="margin-bottom: 2rem;">
            <a href="/organizations/departments/" class="link">← Back to Departments</a>
        </div>

        <div class="card">
            <h1 class="card-title"><?php echo $isEdit ? 'Edit Department' : 'Create New Department'; ?></h1>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Basic Details Section -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem; color: var(--text-color);">Basic Details</h3>

                    <div class="form-group">
                        <label for="name" class="form-label">Department Name *</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-input"
                            required
                            value="<?php echo $department ? htmlspecialchars($department->getName()) : ''; ?>"
                            placeholder="Human Resources"
                        >
                        <small class="text-muted">The full name of the department</small>
                    </div>

                    <div class="form-group">
                        <label for="code" class="form-label">Department Code *</label>
                        <input
                            type="text"
                            id="code"
                            name="code"
                            class="form-input"
                            required
                            value="<?php echo $department ? htmlspecialchars($department->getCode()) : ''; ?>"
                            placeholder="HR"
                            style="text-transform: uppercase;"
                            pattern="[A-Z0-9_]+"
                            title="Only uppercase letters, numbers, and underscores"
                        >
                        <small class="text-muted">Unique code (2-20 chars, uppercase letters, numbers, and underscores only)</small>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            class="form-input"
                            rows="4"
                            placeholder="Description of the department's responsibilities..."
                        ><?php echo $department ? htmlspecialchars($department->getDescription()) : ''; ?></textarea>
                    </div>
                </div>

                <!-- Hierarchy & Organization Section -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem; color: var(--text-color);">Hierarchy & Organization</h3>

                    <div class="form-group">
                        <label for="parent_department_id" class="form-label">Parent Department</label>
                        <select id="parent_department_id" name="parent_department_id" class="form-input">
                            <option value="">-- No Parent (Top Level) --</option>
                            <?php foreach ($allDepartments as $dept): ?>
                                <?php if (!$department || $dept->getId() !== $department->getId()): // Can't be parent of itself ?>
                                    <option
                                        value="<?php echo $dept->getId(); ?>"
                                        <?php echo $department && $department->getParentDepartmentId() === $dept->getId() ? 'selected' : ''; ?>
                                    >
                                        <?php echo htmlspecialchars($dept->getLabel()); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Optional: Select a parent department for hierarchical structure</small>
                    </div>

                    <div class="form-group">
                        <label for="organization_id" class="form-label">Specific Organization</label>
                        <input
                            type="text"
                            id="organization_id"
                            name="organization_id"
                            class="form-input"
                            value="<?php echo $department ? htmlspecialchars($department->getOrganizationId() ?? '') : ''; ?>"
                            placeholder="Leave empty for all organizations"
                        >
                        <small class="text-muted">Optional: Leave empty to make this department available to all organizations</small>
                    </div>

                    <div class="form-group">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input
                            type="number"
                            id="sort_order"
                            name="sort_order"
                            class="form-input"
                            value="<?php echo $department ? $department->getSortOrder() : '0'; ?>"
                            min="0"
                        >
                        <small class="text-muted">Departments are sorted by this number (0 = first)</small>
                    </div>
                </div>

                <!-- Status Section -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem; color: var(--text-color);">Status</h3>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input
                                type="checkbox"
                                name="is_active"
                                <?php echo !$department || $department->getIsActive() ? 'checked' : ''; ?>
                            >
                            <span>Active</span>
                        </label>
                        <small class="text-muted">Only active departments can be selected in forms</small>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="/organizations/departments/" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $isEdit ? 'Update Department' : 'Create Department'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-uppercase the code field
document.getElementById('code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>

<?php include __DIR__ . '/../../../../views/footer.php'; ?>