<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationEntityPermission;
use App\Classes\OrganizationEntityPermissionRepository;
use App\Classes\OrganizationPositionRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$permissionRepo = new OrganizationEntityPermissionRepository();
$positionRepo = new OrganizationPositionRepository();

// Only Super Admin can create/edit permissions
if (!$permissionRepo->isSuperAdmin($user->getEmail())) {
    header('Location: /organizations/departments/information_technology/entity_permissions/?error=' . urlencode('Only Super Admin can manage permissions'));
    exit;
}

$error = '';
$permission = new OrganizationEntityPermission();
$isEdit = false;

// Check if editing existing permission
if (isset($_GET['id'])) {
    $permission = $permissionRepo->findById($_GET['id']);
    if (!$permission) {
        header('Location: /organizations/departments/information_technology/entity_permissions/?error=' . urlencode('Permission not found'));
        exit;
    }
    $isEdit = true;
}

// Get dropdown options
$positions = $positionRepo->findAll();
$availableActions = OrganizationEntityPermission::getAvailableActions();
$availableScopes = OrganizationEntityPermission::getAvailableScopes();
$commonEntities = OrganizationEntityPermission::getCommonEntities();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $permission->setOrganizationPositionId($_POST['organization_position_id'] ?? null);
        $permission->setEntityName($_POST['entity_name'] ?? '');
        $permission->setAction($_POST['action'] ?? '');
        $permission->setScope($_POST['scope'] ?? OrganizationEntityPermission::SCOPE_OWN);
        $permission->setDescription($_POST['description'] ?? '');
        $permission->setPriority($_POST['priority'] ?? 0);
        $permission->setIsActive(isset($_POST['is_active']) ? 1 : 0);

        // Handle conditions (optional JSON field)
        if (!empty($_POST['conditions'])) {
            $permission->setConditions($_POST['conditions']);
        }

        // Basic validation
        if (empty($permission->getOrganizationPositionId())) {
            throw new Exception('Position is required');
        }
        if (empty($permission->getEntityName())) {
            throw new Exception('Entity name is required');
        }
        if (empty($permission->getAction())) {
            throw new Exception('Action is required');
        }

        if ($isEdit) {
            $permissionRepo->update($permission, $user->getId());
            header('Location: /organizations/departments/information_technology/entity_permissions/?success=' . urlencode('Permission updated successfully'));
        } else {
            $permissionRepo->create($permission, $user->getId());
            header('Location: /organizations/departments/information_technology/entity_permissions/?success=' . urlencode('Permission created successfully'));
        }
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = $isEdit ? 'Edit Permission' : 'New Permission';
include __DIR__ . '/../../../../../../views/header.php';
?>

<div class="py-4">
    <div style="margin-bottom: 2rem;">
        <a href="/organizations/departments/information_technology/entity_permissions/" class="text-muted" style="text-decoration: none;">&larr; Back to Permissions</a>
        <h1 style="margin-top: 0.5rem;"><?php echo $isEdit ? 'Edit Permission' : 'Create New Permission'; ?></h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="card">
        <!-- Basic Information -->
        <h2 class="card-title">Permission Details</h2>

        <div class="form-group">
            <label for="organization_position_id" class="form-label">Position *</label>
            <select id="organization_position_id" name="organization_position_id" class="form-input" required>
                <option value="">Select Position</option>
                <?php foreach ($positions as $pos): ?>
                    <option value="<?php echo $pos->getId(); ?>" <?php echo $permission->getOrganizationPositionId() === $pos->getId() ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($pos->getName()); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted text-small">The position that will have this permission</small>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="entity_name" class="form-label">Entity *</label>
                <select id="entity_name" name="entity_name" class="form-input" required>
                    <option value="">Select Entity</option>
                    <?php foreach ($commonEntities as $entityClass => $entityLabel): ?>
                        <option value="<?php echo $entityClass; ?>" <?php echo $permission->getEntityName() === $entityClass ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($entityLabel); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted text-small">The entity this permission applies to</small>
            </div>

            <div class="form-group">
                <label for="action" class="form-label">Action *</label>
                <select id="action" name="action" class="form-input" required>
                    <option value="">Select Action</option>
                    <?php foreach ($availableActions as $actionKey => $actionLabel): ?>
                        <option value="<?php echo $actionKey; ?>" <?php echo $permission->getAction() === $actionKey ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($actionLabel); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted text-small">The action this permission allows</small>
            </div>
        </div>

        <!-- Scope and Priority -->
        <h2 class="card-title" style="margin-top: 2rem;">Scope & Priority</h2>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="scope" class="form-label">Scope *</label>
                <select id="scope" name="scope" class="form-input" required>
                    <?php foreach ($availableScopes as $scopeKey => $scopeLabel): ?>
                        <option value="<?php echo $scopeKey; ?>" <?php echo ($permission->getScope() ?? OrganizationEntityPermission::SCOPE_OWN) === $scopeKey ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($scopeLabel); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted text-small">
                    <strong>Own:</strong> Only records created by the user<br>
                    <strong>Team:</strong> Records within the user's team<br>
                    <strong>Department:</strong> Records within the user's department<br>
                    <strong>Organization:</strong> Records within the user's organization<br>
                    <strong>All:</strong> All records (super admin level)
                </small>
            </div>

            <div class="form-group">
                <label for="priority" class="form-label">Priority</label>
                <input
                    type="number"
                    id="priority"
                    name="priority"
                    class="form-input"
                    min="0"
                    max="100"
                    value="<?php echo htmlspecialchars($permission->getPriority() ?? 0); ?>"
                >
                <small class="text-muted text-small">Higher priority takes precedence (0-100)</small>
            </div>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea
                id="description"
                name="description"
                class="form-input"
                rows="3"
                placeholder="Describe what this permission allows and why it's needed"
            ><?php echo htmlspecialchars($permission->getDescription() ?? ''); ?></textarea>
        </div>

        <!-- Advanced: Conditions -->
        <h2 class="card-title" style="margin-top: 2rem;">Advanced Options</h2>

        <div class="form-group">
            <label for="conditions" class="form-label">Conditions (JSON, Optional)</label>
            <textarea
                id="conditions"
                name="conditions"
                class="form-input"
                rows="4"
                placeholder='{"status": "active", "created_after": "2024-01-01"}'
                style="font-family: monospace; font-size: 0.875rem;"
            ><?php echo htmlspecialchars($permission->getConditions() ?? ''); ?></textarea>
            <small class="text-muted text-small">Additional conditions as JSON. Leave empty if not needed.</small>
        </div>

        <!-- Status -->
        <div class="form-group" style="display: flex; align-items: center; padding-top: 1rem;">
            <input
                type="checkbox"
                id="is_active"
                name="is_active"
                <?php echo ($permission->getIsActive() !== false && $permission->getIsActive() !== 0) || !$isEdit ? 'checked' : ''; ?>
                style="margin-right: 0.5rem;"
            >
            <label for="is_active">Active</label>
        </div>

        <!-- Permission Preview -->
        <div style="margin-top: 2rem; padding: 1rem; background: var(--bg-light); border-radius: 8px; border-left: 4px solid var(--primary-color);">
            <strong>Permission Preview:</strong>
            <p id="permission-preview" style="margin-top: 0.5rem; font-style: italic; color: var(--text-muted);">
                Select position, entity, and action to see preview
            </p>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Update Permission' : 'Create Permission'; ?></button>
            <a href="/organizations/departments/information_technology/entity_permissions/" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
// Update permission preview dynamically
function updatePreview() {
    const positionSelect = document.getElementById('organization_position_id');
    const entitySelect = document.getElementById('entity_name');
    const actionSelect = document.getElementById('action');
    const scopeSelect = document.getElementById('scope');
    const previewElement = document.getElementById('permission-preview');

    const positionText = positionSelect.options[positionSelect.selectedIndex]?.text || 'Position';
    const entityText = entitySelect.options[entitySelect.selectedIndex]?.text || 'Entity';
    const actionText = actionSelect.options[actionSelect.selectedIndex]?.text || 'Action';
    const scopeText = scopeSelect.options[scopeSelect.selectedIndex]?.text || 'Scope';

    if (positionSelect.value && entitySelect.value && actionSelect.value) {
        previewElement.textContent = `${positionText} can ${actionText} ${entityText} (${scopeText})`;
        previewElement.style.color = 'var(--text-color)';
    } else {
        previewElement.textContent = 'Select position, entity, and action to see preview';
        previewElement.style.color = 'var(--text-muted)';
    }
}

// Add event listeners
document.getElementById('organization_position_id').addEventListener('change', updatePreview);
document.getElementById('entity_name').addEventListener('change', updatePreview);
document.getElementById('action').addEventListener('change', updatePreview);
document.getElementById('scope').addEventListener('change', updatePreview);

// Initial preview update
updatePreview();
</script>

<?php include __DIR__ . '/../../../../../../views/footer.php'; ?>
