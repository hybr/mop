<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationBranch;
use App\Classes\OrganizationBranchRepository;
use App\Classes\OrganizationRepository;
use App\Components\PhoneNumberField;
use App\Components\OrganizationField;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$branchRepo = new OrganizationBranchRepository();
$orgRepo = new OrganizationRepository();

$errors = [];
$success = false;
$isEdit = false;
$branch = new OrganizationBranch();

// Branch type options (ENUM values)
$branchTypes = [
    'headquarter' => 'Headquarter',
    'regional_office' => 'Regional Office',
    'branch_office' => 'Branch Office',
    'warehouse' => 'Warehouse',
    'retail' => 'Retail Location',
    'manufacturing' => 'Manufacturing',
    'other' => 'Other'
];

// Size category options (ENUM values)
$sizeCategories = [
    'small' => 'Small (1-10 employees)',
    'medium' => 'Medium (11-50 employees)',
    'large' => 'Large (51-200 employees)',
    'enterprise' => 'Enterprise (200+ employees)'
];

// Check if editing existing branch
if (isset($_GET['id'])) {
    $isEdit = true;
    $branch = $branchRepo->findById($_GET['id']);

    if (!$branch) {
        header('Location: /organizations/departments/facilities/branches/?error=Branch not found');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Populate branch from form data
        $branch->setName($_POST['name'] ?? '');
        $branch->setCode($_POST['code'] ?? '');
        $branch->setDescription($_POST['description'] ?? '');
        $branch->setOrganizationId($_POST['organization_id'] ?? null);

        // Contact fields - combine country code and phone number
        $phone = PhoneNumberField::combine(
            $_POST['country_code'] ?? '',
            $_POST['phone_number'] ?? ''
        );
        $branch->setPhone($phone);

        $branch->setEmail($_POST['email'] ?? '');
        $branch->setWebsite($_POST['website'] ?? '');

        // Branch contact person - combine country code and phone number
        $branch->setContactPersonName($_POST['contact_person_name'] ?? '');

        $contactPhone = PhoneNumberField::combine(
            $_POST['contact_country_code'] ?? '',
            $_POST['contact_phone_number'] ?? ''
        );
        $branch->setContactPersonPhone($contactPhone);

        $branch->setContactPersonEmail($_POST['contact_person_email'] ?? '');

        // Status and metadata
        $branch->setBranchType($_POST['branch_type'] ?? '');
        $branch->setSizeCategory($_POST['size_category'] ?? '');
        $branch->setOpeningDate($_POST['opening_date'] ?? '');
        $branch->setIsActive(isset($_POST['is_active']) ? 1 : 0);
        $branch->setSortOrder($_POST['sort_order'] ?? 0);

        // Validate
        $errors = $branch->validate();

        if (empty($errors)) {
            if ($isEdit) {
                // Update existing
                $branchRepo->update($branch, $user->getId(), $user->getEmail());
                $successMsg = 'Branch "' . $branch->getName() . '" updated successfully!';
                header('Location: /organizations/departments/facilities/branches/?success=' . urlencode($successMsg));
                exit;
            } else {
                // Create new
                $branch = $branchRepo->create($branch, $user->getId(), $user->getEmail());
                $successMsg = 'Branch "' . $branch->getName() . '" created successfully!';
                header('Location: /organizations/departments/facilities/branches/?success=' . urlencode($successMsg));
                exit;
            }
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// Get current organization
$currentOrg = $auth->getCurrentOrganization();

// If creating new branch, require organization to be selected
if (!$isEdit && !$currentOrg) {
    header('Location: /organization/?message=' . urlencode('Please select an organization first'));
    exit;
}

// If creating new branch, set organization_id to current organization
if (!$isEdit && $currentOrg) {
    $branch->setOrganizationId($currentOrg->getId());
}

// Get the organization for display
$organization = null;
if ($branch->getOrganizationId()) {
    $organization = $orgRepo->findById($branch->getOrganizationId(), $user->getId());
}

$pageTitle = $isEdit ? 'Edit Branch' : 'New Branch';
include __DIR__ . '/../../../../../views/header.php';
?>

<div class="py-4">
    <!-- Breadcrumb Navigation -->
    <div style="margin-bottom: 1rem;">
        <a href="/organizations/" class="link">Organizations</a>
        <span style="color: var(--text-light);"> / </span>
        <a href="/organizations/departments/facilities/branches/" class="link">Branches</a>
        <span style="color: var(--text-light);"> / </span>
        <span><?php echo $isEdit ? 'Edit' : 'New'; ?></span>
    </div>

    <h1 style="margin-bottom: 2rem;"><?php echo $pageTitle; ?></h1>

    <!-- Success Message -->
    <?php if ($success): ?>
        <div style="background-color: rgba(76, 175, 80, 0.1); border-left: 4px solid var(--secondary-color); padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
            <strong style="color: var(--secondary-color);">&#x2713; Success!</strong>
            <p style="margin: 0.5rem 0 0 0;"><?php echo htmlspecialchars($success); ?></p>
        </div>
    <?php endif; ?>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div style="background-color: rgba(244, 67, 54, 0.1); border-left: 4px solid #f44336; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
            <strong style="color: #f44336;">&#x2715; Error!</strong>
            <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="" style="max-width: 800px;">
            <!-- Basic Details Section -->
            <div style="margin-bottom: 2rem;">
                <h2 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-color);">
                    Basic Details
                </h2>

                <!-- Name Field -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Branch Name <span style="color: #f44336;">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?php echo htmlspecialchars($branch->getName() ?? ''); ?>"
                        required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        placeholder="e.g., Downtown Office, North Campus, West Branch"
                    >
                    <small class="text-muted">The name of this branch location</small>
                </div>

                <!-- Code Field -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="code" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Branch Code
                    </label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        value="<?php echo htmlspecialchars($branch->getCode() ?? ''); ?>"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem; text-transform: uppercase;"
                        placeholder="e.g., DTN, NC, WB"
                        maxlength="20"
                        oninput="this.value = this.value.toUpperCase()"
                    >
                    <small class="text-muted">Optional short code for this branch</small>
                </div>

                <!-- Organization Field (Readonly) -->
                <?php if ($organization): ?>
                    <?php echo OrganizationField::render([
                        'label' => 'Organization',
                        'name' => 'organization_id',
                        'organization_id' => $organization->getId(),
                        'organization_name' => $organization->getFullName(),
                        'required' => true,
                        'help_text' => 'Branch belongs to the currently selected organization'
                    ]); ?>
                <?php else: ?>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Organization <span style="color: #f44336;">*</span>
                        </label>
                        <input
                            type="text"
                            value="No organization selected"
                            readonly
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem; background-color: var(--bg-light); color: var(--text-color);"
                        >
                        <small class="text-muted" style="color: #f44336;">Please select an organization first</small>
                    </div>
                <?php endif; ?>

                <!-- Description Field -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Description
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem; font-family: inherit;"
                        placeholder="Brief description of this branch location..."
                    ><?php echo htmlspecialchars($branch->getDescription() ?? ''); ?></textarea>
                    <small class="text-muted">Optional description of the branch</small>
                </div>

                <!-- Branch Type -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="branch_type" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Branch Type
                    </label>
                    <select
                        id="branch_type"
                        name="branch_type"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                    >
                        <option value="">Select Type</option>
                        <?php foreach ($branchTypes as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>"
                                <?php echo ($branch->getBranchType() === $value) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Type of branch location</small>
                </div>

                <!-- Size Category -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="size_category" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Size Category
                    </label>
                    <select
                        id="size_category"
                        name="size_category"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                    >
                        <option value="">Select Size</option>
                        <?php foreach ($sizeCategories as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>"
                                <?php echo ($branch->getSizeCategory() === $value) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Approximate size of this branch</small>
                </div>

                <!-- Opening Date -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="opening_date" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Opening Date
                    </label>
                    <input
                        type="date"
                        id="opening_date"
                        name="opening_date"
                        value="<?php echo htmlspecialchars($branch->getOpeningDate() ?? ''); ?>"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                    >
                    <small class="text-muted">Date this branch opened</small>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div style="margin-bottom: 2rem;">
                <h2 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-color);">
                    Contact Information
                </h2>

                <!-- Phone -->
                <?php echo PhoneNumberField::render([
                    'label' => 'Phone',
                    'value' => $branch->getPhone(),
                    'help_text' => 'Branch contact phone number'
                ]); ?>

                <!-- Email -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($branch->getEmail() ?? ''); ?>"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        placeholder="branch@example.com"
                    >
                </div>

                <!-- Website -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="website" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Website
                    </label>
                    <input
                        type="url"
                        id="website"
                        name="website"
                        value="<?php echo htmlspecialchars($branch->getWebsite() ?? ''); ?>"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        placeholder="https://example.com"
                    >
                </div>
            </div>

            <!-- Contact Person Section -->
            <div style="margin-bottom: 2rem;">
                <h2 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-color);">
                    Contact Person
                </h2>

                <!-- Contact Person Name -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="contact_person_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Name
                    </label>
                    <input
                        type="text"
                        id="contact_person_name"
                        name="contact_person_name"
                        value="<?php echo htmlspecialchars($branch->getContactPersonName() ?? ''); ?>"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        placeholder="Primary contact person name"
                    >
                </div>

                <!-- Contact Person Phone -->
                <?php echo PhoneNumberField::render([
                    'label' => 'Phone',
                    'country_code_name' => 'contact_country_code',
                    'phone_number_name' => 'contact_phone_number',
                    'value' => $branch->getContactPersonPhone(),
                    'id_prefix' => 'contact_',
                    'help_text' => 'Contact person phone number'
                ]); ?>

                <!-- Contact Person Email -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="contact_person_email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Email
                    </label>
                    <input
                        type="email"
                        id="contact_person_email"
                        name="contact_person_email"
                        value="<?php echo htmlspecialchars($branch->getContactPersonEmail() ?? ''); ?>"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        placeholder="contact@example.com"
                    >
                </div>
            </div>

            <!-- Settings Section -->
            <div style="margin-bottom: 2rem;">
                <h2 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-color);">
                    Settings
                </h2>

                <!-- Sort Order Field -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="sort_order" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Sort Order
                    </label>
                    <input
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        value="<?php echo $branch->getSortOrder() ?? 0; ?>"
                        min="0"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        placeholder="0"
                    >
                    <small class="text-muted">Lower numbers appear first in lists (default: 0)</small>
                </div>

                <!-- Active Status Checkbox -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input
                            type="checkbox"
                            id="is_active"
                            name="is_active"
                            <?php echo ($branch->getIsActive() || !$isEdit) ? 'checked' : ''; ?>
                            style="margin-right: 0.5rem; width: 18px; height: 18px; cursor: pointer;"
                        >
                        <span style="font-weight: 500;">Active Branch</span>
                    </label>
                    <small class="text-muted" style="margin-left: 1.5rem; display: block; margin-top: 0.25rem;">
                        Inactive branches are hidden from dropdown selections
                    </small>
                </div>
            </div>

            <!-- Form Actions -->
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                    <?php echo $isEdit ? 'Update Branch' : 'Create Branch'; ?>
                </button>
                <a href="/organizations/departments/facilities/branches/" class="btn btn-secondary" style="padding: 0.75rem 2rem; text-decoration: none;">
                    Cancel
                </a>
                <?php if ($isEdit): ?>
                    <a href="/organizations/departments/facilities/branches/form/" class="btn btn-secondary" style="padding: 0.75rem 2rem; text-decoration: none; margin-left: auto;">
                        + Create New Instead
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Help Section -->
    <div class="card" style="margin-top: 2rem; background-color: var(--bg-light);">
        <h3 style="margin-bottom: 1rem;">&#128161; Tips</h3>
        <ul style="margin: 0; padding-left: 1.5rem; line-height: 1.8;">
            <li><strong>Branch Name:</strong> Use descriptive names like "Downtown Office" or "North Campus"</li>
            <li><strong>Branch Code:</strong> Optional short identifier for quick reference</li>
            <li><strong>Contact Person:</strong> Primary point of contact for this branch location</li>
            <li><strong>Branch Type:</strong> Categorize branches for better organization and reporting</li>
            <li><strong>Buildings:</strong> Address information will be managed at the building level within each branch</li>
        </ul>
    </div>
</div>

<!-- Auto-focus first input on page load -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    if (nameInput && !nameInput.value) {
        nameInput.focus();
    }
});
</script>

<style>
/* Form styling improvements */
input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
}

input[type="checkbox"]:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .card form {
        max-width: 100%;
    }

    h1 {
        font-size: 1.5rem;
    }

    h2 {
        font-size: 1.2rem;
    }

    /* Stack address fields on mobile */
    div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include __DIR__ . '/../../../../../views/footer.php'; ?>
