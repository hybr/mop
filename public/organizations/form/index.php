<?php
require_once __DIR__ . '/../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\Organization;
use App\Classes\OrganizationRepository;
use App\Components\PhoneNumberField;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$orgRepo = new OrganizationRepository();

$error = '';
$success = '';
$organization = null;
$isEdit = false;

// Legal structure options (ENUM values)
$legalStructures = [
    // India-specific types
    'Private Limited' => 'Private Limited (India)',
    'Public Limited Company (India)' => 'Public Limited Company (India)',
    'Limited Liability Partnership (India)' => 'Limited Liability Partnership (LLP) (India)',
    'One Person Company (OPC) (India)' => 'One Person Company (OPC) (India)',
    'Section 8 Company (India)' => 'Section 8 Company (Non‑profit) (India)',
    'Society (India)' => 'Society (India)',
    'Cooperative Society (India)' => 'Cooperative Society (India)',
    'Proprietorship (India)' => 'Proprietorship (India)',
    'Sansthan (India)' => 'Sansthan (India)',
    'Trust (India)' => 'Trust (India)',

    // USA-specific types
    'Limited Liability Company (LLc) (USA)' => 'Limited Liability Company (LLc) (USA)',
    'C Corporation (USA)' => 'C Corporation (Inc.) (USA)',
    'S Corporation (USA)' => 'S Corporation (USA)',
    'Limited Partnership (LP) (USA)' => 'Limited Partnership (LP) (USA)',
    'Limited Liability Partnership (USA)' => 'Limited Liability Partnership (LLP) (USA)',
    'Professional Corporation (PC) (USA)' => 'Professional Corporation (PC) (USA)',
    'Nonprofit Corporation (USA)' => 'Nonprofit Corporation (USA)',
    'Benefit Corporation (B Corp) (USA)' => 'Benefit Corporation (B Corp) (USA)',
    'Inc.' => 'Inc. (USA)',

    'Ltd.' => 'Ltd. (UK)',
    'GmbH' => 'GmbH (Germany)'
];

// Check if editing existing organization
if (isset($_GET['id'])) {
    $isEdit = true;
    $organization = $orgRepo->findById($_GET['id'], $user->getId());

    if (!$organization) {
        header('Location: /organizations/?error=' . urlencode('Organization not found or access denied'));
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$organization) {
            $organization = new Organization();
        }

        $organization->setShortName($_POST['short_name'] ?? '');
        $organization->setLegalStructure($_POST['legal_structure'] ?? '');
        $organization->setSubdomain($_POST['subdomain'] ?? '');
        $organization->setDescription($_POST['description'] ?? '');
        $organization->setEmail($_POST['email'] ?? '');

        // Combine country code and phone number
        $phone = PhoneNumberField::combine(
            $_POST['country_code'] ?? '',
            $_POST['phone_number'] ?? ''
        );
        $organization->setPhone($phone);

        $organization->setWebsite($_POST['website'] ?? '');
        $organization->setIsActive(isset($_POST['is_active']) ? 1 : 0);

        // Validate
        $errors = $organization->validate();
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        if ($isEdit) {
            $orgRepo->update($organization, $user->getId());
            $successMsg = 'Organization "' . $organization->getShortName() . '" updated successfully!';
        } else {
            $orgRepo->create($organization, $user->getId());
            $successMsg = 'Organization "' . $organization->getShortName() . '" created successfully!';
        }

        header('Location: /organizations/?success=' . urlencode($successMsg));
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = $isEdit ? 'Edit Organization' : 'New Organization';
include __DIR__ . '/../../../views/header.php';
?>

<div class="page-content">
    <div class="back-link">
        <a href="/organizations/" class="link">← Back to Organizations</a>
    </div>

        <div class="card">
            <h1 class="card-title"><?php echo $isEdit ? 'Edit Organization' : 'Create New Organization'; ?></h1>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="short_name" class="form-label">Organization Short Name *</label>
                    <input
                        type="text"
                        id="short_name"
                        name="short_name"
                        class="form-input"
                        required
                        value="<?php echo $organization ? htmlspecialchars($organization->getShortName()) : ''; ?>"
                        placeholder="Acme Corp"
                    >
                </div>

                <div class="form-group">
                    <label for="legal_structure" class="form-label">Legal Structure</label>
                    <select id="legal_structure" name="legal_structure" class="form-input">
                        <option value="">Select...</option>
                        <?php foreach ($legalStructures as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>"
                                <?php echo ($organization && $organization->getLegalStructure() === $value) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted text-small">
                        Full name will be: Short Name + Legal Structure
                    </small>
                </div>

                <div class="form-group">
                    <label for="subdomain" class="form-label">Subdomain *</label>
                    <div class="form-row">
                        <input
                            type="text"
                            id="subdomain"
                            name="subdomain"
                            class="form-input"
                            required
                            pattern="[a-z0-9-]+"
                            minlength="3"
                            maxlength="63"
                            value="<?php echo $organization ? htmlspecialchars($organization->getSubdomain()) : ''; ?>"
                            placeholder="acmecorp"
                        >
                        <span class="form-suffix">.v4l.app</span>
                    </div>
                    <small class="text-muted text-small">
                        Your organization will be accessible at https://subdomain.v4l.app
                    </small>
                    <small class="text-muted text-small">
                        3-63 characters. Lowercase letters, numbers, and hyphens only.
                    </small>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        class="form-input"
                        rows="4"
                        placeholder="Tell us about your organization..."
                    ><?php echo $organization ? htmlspecialchars($organization->getDescription()) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        value="<?php echo $organization ? htmlspecialchars($organization->getEmail() ?? '') : ''; ?>"
                        placeholder="contact@organization.com"
                    >
                </div>

                <?php echo PhoneNumberField::render([
                    'label' => 'Phone',
                    'value' => $organization ? $organization->getPhone() : '',
                    'help_text' => 'Organization contact phone number'
                ]); ?>

                <div class="form-group">
                    <label for="website" class="form-label">Website</label>
                    <input
                        type="url"
                        id="website"
                        name="website"
                        class="form-input"
                        value="<?php echo $organization ? htmlspecialchars($organization->getWebsite() ?? '') : ''; ?>"
                        placeholder="https://www.organization.com"
                    >
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input
                            type="checkbox"
                            name="is_active"
                            <?php echo (!$organization || $organization->getIsActive()) ? 'checked' : ''; ?>
                        >
                        <span>Active</span>
                    </label>
                    <small class="text-muted text-small">Inactive organizations are hidden but not deleted</small>
                </div>

                <div class="form-group btn-group">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $isEdit ? 'Update Organization' : 'Create Organization'; ?>
                    </button>
                    <a href="/organizations/" class="btn btn-secondary">Cancel</a>
                </div>
            </form>

            <?php if (!$isEdit): ?>
            <script>
                // Auto-generate subdomain from short_name (only for new organizations)
                const shortNameInput = document.getElementById('short_name');
                const subdomainInput = document.getElementById('subdomain');
                let subdomainManuallyEdited = false;

                subdomainInput.addEventListener('input', function() {
                    subdomainManuallyEdited = true;
                });

                shortNameInput.addEventListener('input', function() {
                    if (!subdomainManuallyEdited) {
                        const subdomain = this.value
                            .toLowerCase()
                            .replace(/[^a-z0-9-]/g, '-')
                            .replace(/-+/g, '-')
                            .replace(/^-|-$/g, '')
                            .substring(0, 63);
                        subdomainInput.value = subdomain;
                    }
                });
            </script>
            <?php endif; ?>
        </div>

    <?php if ($isEdit && $organization): ?>
        <div class="card mt-3">
            <h3 class="card-title">Audit Information</h3>
            <div class="info-grid">
                <div>
                    <p class="text-muted text-small mb-1">Created</p>
                    <p><?php echo date('F j, Y g:i A', strtotime($organization->getCreatedAt())); ?></p>
                </div>
                <?php if ($organization->getUpdatedAt() && $organization->getUpdatedAt() != $organization->getCreatedAt()): ?>
                    <div>
                        <p class="text-muted text-small mb-1">Last Updated</p>
                        <p><?php echo date('F j, Y g:i A', strtotime($organization->getUpdatedAt())); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../views/footer.php'; ?>
