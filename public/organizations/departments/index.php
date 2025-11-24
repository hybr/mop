<?php
require_once __DIR__ . '/../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationDepartmentRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$deptRepo = new OrganizationDepartmentRepository();

// Get all departments (all users can view)
$departments = $deptRepo->findAll();
$totalCount = $deptRepo->count(false);

// Check if user is Super Admin to show deleted items
$isSuperAdmin = $deptRepo->isSuperAdmin($user->getEmail());
$deletedDepts = $isSuperAdmin ? $deptRepo->findDeleted($user->getEmail()) : [];

$pageTitle = 'Organization Departments';
include __DIR__ . '/../../../views/header.php';
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Organization Departments</h1>
        <a href="/organizations/departments/form/" class="btn btn-primary">+ New Department</a>
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

    <!-- Facilities Management Quick Access -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2 class="card-title">Facilities Management</h2>
        <p class="text-muted" style="margin-bottom: 1rem;">Quick access to facility management sections</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="/organizations/departments/facilities/teams/" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                Teams
            </a>
            <a href="/organizations/departments/facilities/branches/" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                Branches
            </a>
            <a href="/organizations/departments/facilities/branches/buildings" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                Buildings
            </a>
            <a href="/organizations/departments/facilities/branches/buildings/workstations" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                Workstations
            </a>
        </div>
    </div>

    <!-- Human Resource Quick Access -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2 class="card-title">Human Resource</h2>
        <p class="text-muted" style="margin-bottom: 1rem;">Quick access to human resource management sections</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="/organizations/departments/human_resource/designations/" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                Designations
            </a>
            <a href="/organizations/departments/human_resource/positions/" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                Positions
            </a>
            <button class="btn btn-secondary" style="padding: 1rem; opacity: 0.6;" disabled title="Coming soon">
                Vacancy
            </button>
            <button class="btn btn-secondary" style="padding: 1rem; opacity: 0.6;" disabled title="Coming soon">
                Hiring
            </button>
            <button class="btn btn-secondary" style="padding: 1rem; opacity: 0.6;" disabled title="Coming soon">
                Employees
            </button>
            <button class="btn btn-secondary" style="padding: 1rem; opacity: 0.6;" disabled title="Coming soon">
                Payroll
            </button>
            <button class="btn btn-secondary" style="padding: 1rem; opacity: 0.6;" disabled title="Coming soon">
                Leave Management
            </button>
        </div>
    </div>

    <!-- Active Departments -->
    <div class="card">
        <h2 class="card-title">Active Departments (<?php echo count($departments); ?>)</h2>

        <?php if (empty($departments)): ?>
            <div style="text-align: center; padding: 3rem 1rem;">
                <p class="text-muted" style="font-size: 1.2rem; margin-bottom: 1rem;">No Departments Yet</p>
                <p class="text-muted" style="margin-bottom: 2rem;">Create standard department categories for your organizations.</p>
                <a href="/organizations/departments/form/" class="btn btn-primary">Create Your First Department</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Code</th>
                            <th style="padding: 1rem;">Description</th>
                            <th style="padding: 1rem;">Sort Order</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $dept): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($dept->getName()); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <code style="background: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        <?php echo htmlspecialchars($dept->getCode()); ?>
                                    </code>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($dept->getDescription()): ?>
                                        <span class="text-muted"><?php echo htmlspecialchars(substr($dept->getDescription(), 0, 60)); ?><?php echo strlen($dept->getDescription()) > 60 ? '...' : ''; ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $dept->getSortOrder(); ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($dept->getIsActive()): ?>
                                        <span style="color: var(--secondary-color);">Active</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/view/?id=<?php echo $dept->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">View</a>
                                    <?php if ($isSuperAdmin): ?>
                                        <a href="/organizations/departments/form/?id=<?php echo $dept->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Edit</a>
                                        <a href="/organizations/departments/delete/?id=<?php echo $dept->getId(); ?>" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Are you sure you want to delete this department?');">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Deleted Departments (Trash) - Only for Super Admin -->
    <?php if ($isSuperAdmin && !empty($deletedDepts)): ?>
        <div class="card">
            <h2 class="card-title">Trash (<?php echo count($deletedDepts); ?>)</h2>
            <p class="text-muted text-small">Deleted departments can be restored or permanently deleted.</p>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Code</th>
                            <th style="padding: 1rem;">Deleted</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deletedDepts as $dept): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); opacity: 0.6;">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($dept->getName()); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <code style="background: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        <?php echo htmlspecialchars($dept->getCode()); ?>
                                    </code>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <?php echo date('M j, Y', strtotime($dept->getDeletedAt())); ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/restore/?id=<?php echo $dept->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Restore</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../views/footer.php'; ?>