<?php
require_once __DIR__ . '/../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\WorkflowTaskRepository;

$auth = new Auth();
$auth->requireAuth();

// Auto-select organization on dashboard load
$auth->autoSelectOrganization();

$user = $auth->getCurrentUser();
$currentOrg = $auth->getCurrentOrganization();

// Get user's task counts
$taskRepo = new WorkflowTaskRepository();
$pendingTasksCount = $taskRepo->countByUser($user->getId(), 'pending');
$inProgressTasksCount = $taskRepo->countByUser($user->getId(), 'in_progress');
$totalActiveTasks = $pendingTasksCount + $inProgressTasksCount;

$pageTitle = 'Dashboard';
include __DIR__ . '/../../views/header.php';
?>

<div class="py-4">
    <h1 class="mb-4">Dashboard</h1>

    <div class="card">
        <div class="profile-header">
            <?php if ($user->getAvatarUrl()): ?>
                <img src="<?php echo htmlspecialchars($user->getAvatarUrl()); ?>" alt="Avatar" class="avatar">
            <?php else: ?>
                <div class="avatar-placeholder">
                    <?php echo strtoupper(substr($user->getFullName(), 0, 1)); ?>
                </div>
            <?php endif; ?>

            <div>
                <h2 class="profile-name"><?php echo htmlspecialchars($user->getFullName()); ?></h2>
                <p class="profile-email"><?php echo htmlspecialchars($user->getEmail()); ?></p>
                <?php if ($user->getRole()): ?>
                    <p class="text-muted text-small">
                        Role: <strong><?php echo ucfirst(htmlspecialchars($user->getRole())); ?></strong>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="card-title">Account Information</h3>

        <div class="mb-3">
            <p class="text-muted text-small mb-1">Email</p>
            <p><?php echo htmlspecialchars($user->getEmail()); ?></p>
        </div>

        <div class="mb-3">
            <p class="text-muted text-small mb-1">Full Name</p>
            <p><?php echo htmlspecialchars($user->getFullName()); ?></p>
        </div>

        <?php if ($user->getPhone()): ?>
            <div class="mb-3">
                <p class="text-muted text-small mb-1">Phone</p>
                <p><?php echo htmlspecialchars($user->getPhone()); ?></p>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <p class="text-muted text-small mb-1">Account Status</p>
            <p><?php echo $user->getIsActive() ? '<span style="color: var(--secondary-color);">Active</span>' : '<span style="color: var(--danger-color);">Inactive</span>'; ?></p>
        </div>

        <?php if ($user->getCreatedAt()): ?>
            <div class="mb-3">
                <p class="text-muted text-small mb-1">Member Since</p>
                <p><?php echo date('F j, Y', strtotime($user->getCreatedAt())); ?></p>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="/profile.php" class="btn btn-primary">Edit Profile</a>
            <a href="/change-password.php" class="btn btn-secondary">Change Password</a>
        </div>
    </div>

    <!-- Workflow Tasks Summary -->
    <?php if ($totalActiveTasks > 0): ?>
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h3 style="margin: 0 0 0.5rem 0; color: white;">Your Workflow Tasks</h3>
            <p style="margin: 0 0 1rem 0; opacity: 0.9;">
                You have <strong><?php echo $totalActiveTasks; ?></strong> active workflow task<?php echo $totalActiveTasks > 1 ? 's' : ''; ?>
                (<?php echo $pendingTasksCount; ?> pending, <?php echo $inProgressTasksCount; ?> in progress)
            </p>
            <a href="/tasks/"
               class="btn"
               style="background: white; color: #667eea; border: none; font-weight: bold;">
                View My Tasks →
            </a>
        </div>
    <?php endif; ?>

    <div class="card">
        <h3 class="card-title">Quick Actions</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
            <a href="/tasks/" class="btn btn-primary" style="position: relative;">
                View Tasks
                <?php if ($totalActiveTasks > 0): ?>
                    <span style="position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">
                        <?php echo $totalActiveTasks; ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="/organizations/departments/" class="btn btn-secondary">Departments</a>
            <a href="/organizations/departments/human_resource/vacancies/" class="btn btn-secondary">Vacancies</a>
            <a href="/organizations/departments/human_resource/hiring/instances/" class="btn btn-secondary">Hiring Workflows</a>
            <a href="/profile.php" class="btn btn-secondary">Update Profile</a>
            <?php if ($auth->hasRole('admin')): ?>
                <a href="/admin/users.php" class="btn btn-secondary">Manage Users</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/footer.php'; ?>
