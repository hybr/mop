<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;

$auth = new Auth();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    try {
        $result = $auth->resetPasswordRequest($email);
        $success = $result['message'];
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Forgot Password';
include __DIR__ . '/../views/header.php';
?>

<div class="form-container">
    <h1 class="form-title">Reset Password</h1>

    <p class="text-center text-muted mb-3">
        Enter your email address and we'll send you a link to reset your password.
    </p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <p class="text-center mt-3">
            <a href="/login.php" class="link">Back to Login</a>
        </p>
    <?php else: ?>
        <form method="POST" action="/forgot-password.php">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input"
                    required
                    autofocus
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
        </form>

        <p class="text-center mt-3">
            <a href="/login.php" class="link">Back to Login</a>
        </p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
