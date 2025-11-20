<?php
require_once __DIR__ . '/../../../src/includes/autoload.php';

use App\Classes\Auth;

$auth = new Auth();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: /market');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? '';  // Can be username, email, or phone
    $password = $_POST['password'] ?? '';

    try {
        $result = $auth->login($identifier, $password);

        // Try to auto-select organization
        $auth->autoSelectOrganization();

        // Redirect to dashboard on successful login
        header('Location: /market');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Login';
include __DIR__ . '/../../../views/header.php';
?>

<div class="form-container">
    <h1 class="form-title">Welcome Back</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="/auth/login">
        <div class="form-group">
            <label for="identifier" class="form-label">Username, Email, or Phone</label>
            <input
                type="text"
                id="identifier"
                name="identifier"
                class="form-input"
                required
                autofocus
                value="<?php echo htmlspecialchars($_POST['identifier'] ?? ''); ?>"
                placeholder="username or email@example.com or +1-555-1234"
                autocomplete="username"
            >
            <small class="text-muted text-small">
                You can login with your username, email, or phone number
            </small>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-input"
                required
                autocomplete="current-password"
            >
        </div>

        <div class="form-group">
            <a href="/forgot-password.php" class="link text-small">Forgot your password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>

    <p class="text-center mt-3">
        Don't have an account? <a href="/register.php" class="link">Sign up here</a>
    </p>
</div>

<?php include __DIR__ . '/../../../views/footer.php'; ?>
