<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationBranch;
use App\Classes\OrganizationBranchRepository;
use App\Classes\OrganizationRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$branchRepo = new OrganizationBranchRepository();
$orgRepo = new OrganizationRepository();

$errors = [];
$success = false;
$isEdit = false;
$branch = new OrganizationBranch();

// Check if editing existing branch
if (isset($_GET['id'])) {
    $isEdit = true;
    $branch = $branchRepo->findById($_GET['id']);

    if (!$branch) {
        header('Location: /organizations-facilities-branches.php?error=Branch not found');
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

        // Address fields
        $branch->setAddressLine1($_POST['address_line1'] ?? '');
        $branch->setAddressLine2($_POST['address_line2'] ?? '');
        $branch->setCity($_POST['city'] ?? '');
        $branch->setState($_POST['state'] ?? '');
        $branch->setCountry($_POST['country'] ?? '');
        $branch->setPostalCode($_POST['postal_code'] ?? '');

        // Contact fields
        $branch->setPhone($_POST['phone'] ?? '');
        $branch->setEmail($_POST['email'] ?? '');
        $branch->setWebsite($_POST['website'] ?? '');

        // Branch contact person
        $branch->setContactPersonName($_POST['contact_person_name'] ?? '');
        $branch->setContactPersonPhone($_POST['contact_person_phone'] ?? '');
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
                $success = "Branch updated successfully!";
            } else {
                // Create new
                $branch = $branchRepo->create($branch, $user->getId(), $user->getEmail());
                $success = "Branch created successfully!";
                $isEdit = true; // Switch to edit mode after creation
            }
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// Get organizations for dropdown
$organizations = $orgRepo->findAllByUser($user->getId());

$pageTitle = $isEdit ? 'Edit Branch' : 'New Branch';
include __DIR__ . '/../views/header.php';
?>

<div class="py-4">
    <!-- Breadcrumb Navigation -->
    <div style="margin-bottom: 1rem;">
        <a href="/organizations.php" class="link">Organizations</a>
        <span style="color: var(--text-light);"> / </span>
        <a href="/organizations-facilities-branches.php" class="link">Branches</a>
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

                <!-- Organization Field -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="organization_id" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Organization <span style="color: #f44336;">*</span>
                    </label>
                    <select
                        id="organization_id"
                        name="organization_id"
                        required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                    >
                        <option value="">Select Organization</option>
                        <?php foreach ($organizations as $org): ?>
                            <option value="<?php echo $org->getId(); ?>"
                                <?php echo ($branch->getOrganizationId() === $org->getId()) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($org->getName()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">The organization this branch belongs to</small>
                </div>

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
                        <option value="headquarters" <?php echo ($branch->getBranchType() === 'headquarters') ? 'selected' : ''; ?>>Headquarters</option>
                        <option value="regional_office" <?php echo ($branch->getBranchType() === 'regional_office') ? 'selected' : ''; ?>>Regional Office</option>
                        <option value="branch_office" <?php echo ($branch->getBranchType() === 'branch_office') ? 'selected' : ''; ?>>Branch Office</option>
                        <option value="warehouse" <?php echo ($branch->getBranchType() === 'warehouse') ? 'selected' : ''; ?>>Warehouse</option>
                        <option value="retail" <?php echo ($branch->getBranchType() === 'retail') ? 'selected' : ''; ?>>Retail Location</option>
                        <option value="manufacturing" <?php echo ($branch->getBranchType() === 'manufacturing') ? 'selected' : ''; ?>>Manufacturing</option>
                        <option value="other" <?php echo ($branch->getBranchType() === 'other') ? 'selected' : ''; ?>>Other</option>
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
                        <option value="small" <?php echo ($branch->getSizeCategory() === 'small') ? 'selected' : ''; ?>>Small (1-10 employees)</option>
                        <option value="medium" <?php echo ($branch->getSizeCategory() === 'medium') ? 'selected' : ''; ?>>Medium (11-50 employees)</option>
                        <option value="large" <?php echo ($branch->getSizeCategory() === 'large') ? 'selected' : ''; ?>>Large (51-200 employees)</option>
                        <option value="enterprise" <?php echo ($branch->getSizeCategory() === 'enterprise') ? 'selected' : ''; ?>>Enterprise (200+ employees)</option>
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

            <!-- Address Section -->
            <div style="margin-bottom: 2rem;">
                <h2 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-color);">
                    Address
                </h2>

                <!-- Address Line 1 -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="address_line1" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Address Line 1
                    </label>
                    <input
                        type="text"
                        id="address_line1"
                        name="address_line1"
                        value="<?php echo htmlspecialchars($branch->getAddressLine1() ?? ''); ?>"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        placeholder="Street address"
                    >
                </div>

                <!-- Address Line 2 -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="address_line2" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Address Line 2
                    </label>
                    <input
                        type="text"
                        id="address_line2"
                        name="address_line2"
                        value="<?php echo htmlspecialchars($branch->getAddressLine2() ?? ''); ?>"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        placeholder="Suite, unit, building, floor, etc."
                    >
                </div>

                <!-- City, State, Postal Code Row -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label for="city" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            City
                        </label>
                        <input
                            type="text"
                            id="city"
                            name="city"
                            value="<?php echo htmlspecialchars($branch->getCity() ?? ''); ?>"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                            placeholder="City"
                        >
                    </div>
                    <div>
                        <label for="state" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            State/Province
                        </label>
                        <input
                            type="text"
                            id="state"
                            name="state"
                            value="<?php echo htmlspecialchars($branch->getState() ?? ''); ?>"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                            placeholder="State"
                        >
                    </div>
                    <div>
                        <label for="postal_code" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Postal Code
                        </label>
                        <input
                            type="text"
                            id="postal_code"
                            name="postal_code"
                            value="<?php echo htmlspecialchars($branch->getPostalCode() ?? ''); ?>"
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                            placeholder="ZIP/Postal"
                        >
                    </div>
                </div>

                <!-- Country -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="country" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Country
                    </label>
                    <input
                        type="text"
                        id="country"
                        name="country"
                        value="<?php echo htmlspecialchars($branch->getCountry() ?? ''); ?>"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        placeholder="Country"
                    >
                </div>
            </div>

            <!-- Contact Information Section -->
            <div style="margin-bottom: 2rem;">
                <h2 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-color);">
                    Contact Information
                </h2>

                <!-- Phone -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="phone" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Phone
                    </label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="<?php echo htmlspecialchars($branch->getPhone() ?? ''); ?>"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        placeholder="Branch phone number"
                    >
                </div>

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
                <div style="margin-bottom: 1.5rem;">
                    <label for="contact_person_phone" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Phone
                    </label>
                    <input
                        type="tel"
                        id="contact_person_phone"
                        name="contact_person_phone"
                        value="<?php echo htmlspecialchars($branch->getContactPersonPhone() ?? ''); ?>"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem;"
                        placeholder="Contact person phone"
                    >
                </div>

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
                <a href="/organizations-facilities-branches.php" class="btn btn-secondary" style="padding: 0.75rem 2rem; text-decoration: none;">
                    Cancel
                </a>
                <?php if ($isEdit): ?>
                    <a href="/branch-form.php" class="btn btn-secondary" style="padding: 0.75rem 2rem; text-decoration: none; margin-left: auto;">
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
            <li><strong>Address:</strong> Complete address information helps with location services and reporting</li>
            <li><strong>Contact Person:</strong> Primary point of contact for this branch location</li>
            <li><strong>Branch Type:</strong> Categorize branches for better organization and reporting</li>
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

<?php include __DIR__ . '/../views/footer.php'; ?>
