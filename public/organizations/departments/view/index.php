<?php
require_once __DIR__ . '/../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationDepartmentRepository;

$auth = new Auth();
$user = $auth->getCurrentUser(); // May be null if not logged in

$deptRepo = new OrganizationDepartmentRepository();

// Get department ID from URL
if (!isset($_GET['id'])) {
    header('Location: /organizations/departments/?error=' . urlencode('Department ID is required'));
    exit;
}

$department = $deptRepo->findById($_GET['id']);

if (!$department) {
    header('Location: /organizations/departments/?error=' . urlencode('Department not found'));
    exit;
}

// Get parent department if exists
$parentDepartment = null;
if ($department->getParentDepartmentId()) {
    $parentDepartment = $deptRepo->findById($department->getParentDepartmentId());
}

// Check if user can edit (Super Admin only)
$canEdit = $user && $deptRepo->canEdit($user->getEmail());

$pageTitle = htmlspecialchars($department->getName()) . ' - Department';
include __DIR__ . '/../../../../views/header.php';
?>

<div class="py-4">
    <div style="max-width: 1000px; margin: 0 auto;">
        <div style="margin-bottom: 2rem;">
            <a href="/organizations/departments/" class="link">← Back to Departments</a>
        </div>

        <!-- Header Section -->
        <div class="card" style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($department->getName()); ?></h1>
                    <div style="display: flex; gap: 1rem; align-items: center; margin-top: 0.5rem;">
                        <code style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($department->getCode()); ?>
                        </code>
                        <?php if ($department->getIsActive()): ?>
                            <span style="color: var(--secondary-color); font-weight: 500;">● Active</span>
                        <?php else: ?>
                            <span style="color: var(--text-light); font-weight: 500;">● Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($canEdit): ?>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="/organizations/departments/form/?id=<?php echo $department->getId(); ?>" class="btn btn-primary">
                            Edit Department
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($department->getDescription()): ?>
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                    <p style="color: var(--text-muted); line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($department->getDescription())); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Details Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <!-- Basic Information -->
            <div class="card">
                <h2 class="card-title">Basic Information</h2>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Department Name</div>
                        <div style="font-weight: 500;"><?php echo htmlspecialchars($department->getName()); ?></div>
                    </div>

                    <div>
                        <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Department Code</div>
                        <div>
                            <code style="background: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 4px;">
                                <?php echo htmlspecialchars($department->getCode()); ?>
                            </code>
                        </div>
                    </div>

                    <div>
                        <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Status</div>
                        <div>
                            <?php if ($department->getIsActive()): ?>
                                <span style="color: var(--secondary-color); font-weight: 500;">Active</span>
                            <?php else: ?>
                                <span style="color: var(--text-light); font-weight: 500;">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Sort Order</div>
                        <div style="font-weight: 500;"><?php echo $department->getSortOrder(); ?></div>
                    </div>
                </div>
            </div>

            <!-- Hierarchy Information -->
            <div class="card">
                <h2 class="card-title">Hierarchy</h2>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Parent Department</div>
                        <div style="font-weight: 500;">
                            <?php if ($parentDepartment): ?>
                                <a href="/organizations/departments/view/?id=<?php echo $parentDepartment->getId(); ?>" class="link">
                                    <?php echo htmlspecialchars($parentDepartment->getLabel()); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Top Level (No Parent)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Organization Scope</div>
                        <div style="font-weight: 500;">
                            <?php if ($department->getOrganizationId()): ?>
                                <span>Specific Organization</span>
                                <br>
                                <small class="text-muted">ID: <?php echo htmlspecialchars($department->getOrganizationId()); ?></small>
                            <?php else: ?>
                                <span>All Organizations</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit Information -->
        <?php if ($user): ?>
            <div class="card" style="margin-top: 1.5rem;">
                <h2 class="card-title">Audit Information</h2>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <?php if ($department->getCreatedAt()): ?>
                        <div>
                            <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Created</div>
                            <div style="font-weight: 500;">
                                <?php echo date('F j, Y \a\t g:i A', strtotime($department->getCreatedAt())); ?>
                            </div>
                            <?php if ($department->getCreatedBy()): ?>
                                <small class="text-muted">By: <?php echo htmlspecialchars($department->getCreatedBy()); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($department->getUpdatedAt()): ?>
                        <div>
                            <div class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.25rem;">Last Updated</div>
                            <div style="font-weight: 500;">
                                <?php echo date('F j, Y \a\t g:i A', strtotime($department->getUpdatedAt())); ?>
                            </div>
                            <?php if ($department->getUpdatedBy()): ?>
                                <small class="text-muted">By: <?php echo htmlspecialchars($department->getUpdatedBy()); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../../../views/footer.php'; ?>