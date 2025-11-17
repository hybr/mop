<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;

$auth = new Auth();
$auth->requireAuth();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    try {
        if ($newPassword !== $confirmPassword) {
            throw new Exception("Passwords do not match");
        }

        $result = $auth->updatePassword($newPassword);
        $success = $result['message'];

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Change Password';
include __DIR__ . '/../views/header.php';
?>

<div class="form-container">
    <h1 class="form-title">Change Password</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="/change-password.php">
        <div class="form-group">
            <label for="new_password" class="form-label">New Password</label>
            <input
                type="password"
                id="new_password"
                name="new_password"
                class="form-input"
                required
                minlength="6"
            >
            <small class="text-muted text-small">Minimum 6 characters</small>
        </div>

        <div class="form-group">
            <label for="confirm_password" class="form-label">Confirm New Password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                class="form-input"
                required
                minlength="6"
            >
        </div>

        <button type="submit" class="btn btn-primary btn-block">Update Password</button>
    </form>

    <p class="text-center mt-3">
        <a href="/dashboard.php" class="link">Back to Dashboard</a>
    </p>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
