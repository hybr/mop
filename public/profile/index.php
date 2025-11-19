<?php
require_once __DIR__ . '/../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\UserRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$userRepo = new UserRepository();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';

    try {
        $user->setFullName($fullName);
        $user->setPhone($phone);

        $errors = $user->validate();
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        $userRepo->update($user);

        // Update session data
        $_SESSION['user_data'] = $user->toArray();

        $success = 'Profile updated successfully!';

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Edit Profile';
include __DIR__ . '/../../views/header.php';
?>

<div class="py-4">
    <h1 class="mb-4">Edit Profile</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="card">
        <h3 class="card-title">Profile Information</h3>

        <form method="POST" action="/profile.php">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input
                    type="email"
                    id="email"
                    class="form-input"
                    value="<?php echo htmlspecialchars($user->getEmail()); ?>"
                    disabled
                >
                <small class="text-muted text-small">Email cannot be changed</small>
            </div>

            <div class="form-group">
                <label for="full_name" class="form-label">Full Name</label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    class="form-input"
                    required
                    value="<?php echo htmlspecialchars($user->getFullName()); ?>"
                >
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    class="form-input"
                    value="<?php echo htmlspecialchars($user->getPhone() ?? ''); ?>"
                >
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">Account Settings</h3>
        <p class="mb-3">Manage your account security and preferences</p>
        <a href="/change-password.php" class="btn btn-secondary">Change Password</a>
    </div>
</div>

<?php include __DIR__ . '/../../views/footer.php'; ?>
