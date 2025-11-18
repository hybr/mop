<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;

$auth = new Auth();
$auth->requireAuth();

// Auto-select organization on dashboard load
$auth->autoSelectOrganization();

$user = $auth->getCurrentUser();
$currentOrg = $auth->getCurrentOrganization();
$pageTitle = 'Dashboard';
include __DIR__ . '/../views/header.php';
?>

<div class="py-4">
    <h1 class="mb-4">Dashboard</h1>

    <!-- Current Organization -->
    <?php if ($currentOrg): ?>
        <div class="card" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(79, 70, 229, 0.05) 100%); border-left: 4px solid var(--primary-color);">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <p class="text-muted text-small mb-1">Current Organization</p>
                    <h2 style="margin: 0; color: var(--primary-color);"><?php echo htmlspecialchars($currentOrg->getName()); ?></h2>
                    <?php if ($currentOrg->getDescription()): ?>
                        <p style="margin-top: 0.5rem; color: var(--text-light);"><?php echo htmlspecialchars($currentOrg->getDescription()); ?></p>
                    <?php endif; ?>
                </div>
                <a href="/select-organization.php" class="btn btn-secondary" style="white-space: nowrap;">
                    Switch Organization
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card" style="background-color: rgba(245, 158, 11, 0.1); border-left: 4px solid var(--warning-color);">
            <p style="margin: 0;">
                <strong>No organization selected.</strong>
                <a href="/select-organization.php" class="link">Click here to select an organization</a> or
                <a href="/organization-form.php" class="link">create a new one</a>.
            </p>
        </div>
    <?php endif; ?>

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

    <div class="card">
        <h3 class="card-title">Quick Actions</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
            <a href="/profile.php" class="btn btn-primary">Update Profile</a>
            <?php if ($auth->hasRole('admin')): ?>
                <a href="/admin/users.php" class="btn btn-secondary">Manage Users</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
