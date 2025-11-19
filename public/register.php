<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;
use App\Components\PhoneNumberField;

$auth = new Auth();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = $_POST['full_name'] ?? '';

    // Combine country code and phone number
    $phone = PhoneNumberField::combine(
        $_POST['country_code'] ?? '',
        $_POST['phone_number'] ?? ''
    );

    try {
        // Validate
        if ($password !== $confirmPassword) {
            throw new Exception("Passwords do not match");
        }

        // Register
        $result = $auth->register($username, $email, $password, $fullName, $phone);
        $success = $result['message'];

        // Optionally auto-login after registration
        // Uncomment the following lines to auto-login
        // $auth->login($email, $password);
        // header('Location: /dashboard.php');
        // exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Register';
include __DIR__ . '/../views/header.php';
?>

<div class="form-container">
    <h1 class="form-title">Create Account</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <p class="text-center mt-3">
            <a href="/login.php" class="link">Click here to login</a>
        </p>
    <?php else: ?>
        <form method="POST" action="/register.php">
            <div class="form-group">
                <label for="username" class="form-label">Username *</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-input"
                    required
                    pattern="[a-z0-9_-]+"
                    minlength="3"
                    maxlength="30"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    placeholder="johndoe"
                    autocomplete="username"
                >
                <small class="text-muted text-small">
                    3-30 characters. Lowercase letters, numbers, underscores, and hyphens only.
                </small>
            </div>

            <div class="form-group">
                <label for="full_name" class="form-label">Full Name *</label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    class="form-input"
                    required
                    value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                    autocomplete="name"
                >
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address *</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input"
                    required
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    autocomplete="email"
                >
                <small class="text-muted text-small">Used for password recovery</small>
            </div>

            <?php echo PhoneNumberField::render([
                'label' => 'Phone Number (Optional)',
                'selected_country_code' => $_POST['country_code'] ?? '+91',
                'phone_number_value' => $_POST['phone_number'] ?? '',
                'help_text' => 'Can also be used for password recovery'
            ]); ?>

            <div class="form-group">
                <label for="password" class="form-label">Password *</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    required
                    minlength="6"
                    autocomplete="new-password"
                >
                <small class="text-muted text-small">Minimum 6 characters</small>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password *</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    class="form-input"
                    required
                    minlength="6"
                    autocomplete="new-password"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>

        <p class="text-center mt-3">
            Already have an account? <a href="/login.php" class="link">Login here</a>
        </p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
