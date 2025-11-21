<?php
require_once __DIR__ . '/../../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationBuilding;
use App\Classes\OrganizationBuildingRepository;
use App\Classes\OrganizationRepository;
use App\Classes\OrganizationBranchRepository;
use App\Components\PhoneNumberField;
use App\Components\PostalAddressField;
use App\Components\OrganizationField;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$buildingRepo = new OrganizationBuildingRepository();
$orgRepo = new OrganizationRepository();
$branchRepo = new OrganizationBranchRepository();

$isEdit = false;
$errors = [];
$success = false;

// Building type options
$buildingTypes = [
    'office' => 'Office',
    'warehouse' => 'Warehouse',
    'retail' => 'Retail',
    'factory' => 'Factory',
    'mixed_use' => 'Mixed Use',
    'residential' => 'Residential',
    'other' => 'Other'
];

// Ownership type options
$ownershipTypes = [
    'owned' => 'Owned',
    'leased' => 'Leased',
    'rented' => 'Rented'
];

// Initialize building
$building = new OrganizationBuilding();

// Check if editing existing building
if (isset($_GET['id'])) {
    $isEdit = true;
    $building = $buildingRepo->findById($_GET['id']);

    if (!$building) {
        header('Location: /organizations/departments/facilities/branches/buildings/?error=Building not found');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Populate building from form data
        $building->setName($_POST['name'] ?? '');
        $building->setCode($_POST['code'] ?? '');
        $building->setDescription($_POST['description'] ?? '');
        $building->setBranchId($_POST['branch_id'] ?? '');
        $building->setOrganizationId($_POST['organization_id'] ?? '');

        // Address fields
        $building->setStreetAddress($_POST['street_address'] ?? '');
        $building->setCity($_POST['city'] ?? '');
        $building->setState($_POST['state'] ?? '');
        $building->setPostalCode($_POST['postal_code'] ?? '');
        $building->setCountry($_POST['country'] ?? '');

        // Geo coordinates (required)
        $building->setLatitude($_POST['latitude'] ?? '');
        $building->setLongitude($_POST['longitude'] ?? '');

        // Contact fields
        $phone = PhoneNumberField::combine(
            $_POST['country_code'] ?? '',
            $_POST['phone_number'] ?? ''
        );
        $building->setPhone($phone);
        $building->setEmail($_POST['email'] ?? '');

        // Building details
        $building->setBuildingType($_POST['building_type'] ?? '');
        $building->setTotalFloors($_POST['total_floors'] ?? null);
        $building->setTotalAreaSqft($_POST['total_area_sqft'] ?? null);
        $building->setYearBuilt($_POST['year_built'] ?? null);
        $building->setOwnershipType($_POST['ownership_type'] ?? '');

        // Status
        $building->setIsActive(isset($_POST['is_active']) ? 1 : 0);
        $building->setSortOrder($_POST['sort_order'] ?? 0);

        // Validate
        $errors = $building->validate();

        if (empty($errors)) {
            if ($isEdit) {
                // Update existing
                $buildingRepo->update($building, $user->getId(), $user->getEmail());
                $successMsg = 'Building "' . $building->getName() . '" updated successfully!';
                header('Location: /organizations/departments/facilities/branches/buildings/?success=' . urlencode($successMsg));
                exit;
            } else {
                // Create new
                $building = $buildingRepo->create($building, $user->getId(), $user->getEmail());
                $successMsg = 'Building "' . $building->getName() . '" created successfully!';
                header('Location: /organizations/departments/facilities/branches/buildings/?success=' . urlencode($successMsg));
                exit;
            }
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// Get organizations for dropdown
$organizations = $orgRepo->findAllByUser($user->getId());

// Get branches for dropdown
$branches = $branchRepo->findByUser($user->getId());

// Pre-select organization and branch if provided
$selectedOrgId = $_GET['organization_id'] ?? $building->getOrganizationId() ?? '';
$selectedBranchId = $_GET['branch_id'] ?? $building->getBranchId() ?? '';

// Get organization object if selected
$selectedOrganization = null;
if ($selectedOrgId) {
    $selectedOrganization = $orgRepo->findById($selectedOrgId, $user->getId());
}

$pageTitle = $isEdit ? 'Edit Building' : 'New Building';
include __DIR__ . '/../../../../../../../views/header.php';
?>

<div class="py-4">
    <!-- Breadcrumb Navigation -->
    <div style="margin-bottom: 1rem;">
        <a href="/organizations/" class="link">Organizations</a>
        <span style="color: var(--text-light);"> / </span>
        <a href="/organizations/departments/facilities/teams/" class="link">Facilities</a>
        <span style="color: var(--text-light);"> / </span>
        <a href="/organizations/departments/facilities/branches/" class="link">Branches</a>
        <span style="color: var(--text-light);"> / </span>
        <a href="/organizations/departments/facilities/branches/buildings/" class="link">Buildings</a>
        <span style="color: var(--text-light);"> / </span>
        <span><?php echo $isEdit ? 'Edit' : 'New'; ?></span>
    </div>

    <h1 style="margin-bottom: 2rem;"><?php echo $pageTitle; ?></h1>

    <!-- Success Message -->
    <?php if ($success): ?>
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            <p style="margin: 0;">Building saved successfully!</p>
        </div>
    <?php endif; ?>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
            <p style="margin: 0 0 0.5rem 0;"><strong>Please fix the following errors:</strong></p>
            <ul style="margin: 0; padding-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="card">
        <form method="POST" action="">
            <!-- Basic Information -->
            <div style="margin-bottom: 2rem;">
                <h2 class="card-title">Basic Information</h2>

                <!-- Organization Field (readonly, populated from selected branch) -->
                <?php if ($selectedOrganization): ?>
                    <?php echo OrganizationField::render([
                        'label' => 'Organization',
                        'name' => 'organization_id',
                        'id' => 'organization_id',
                        'organization_id' => $selectedOrganization->getId(),
                        'organization_name' => $selectedOrganization->getFullName(),
                        'required' => true,
                        'help_text' => 'Organization is determined by the selected branch'
                    ]); ?>
                <?php else: ?>
                    <!-- Hidden field for when no org selected yet - JS will populate this -->
                    <input type="hidden" name="organization_id" id="organization_id" value="">
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <!-- Branch -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Branch <span style="color: var(--danger-color);">*</span>
                        </label>
                        <select name="branch_id" id="branch_id" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                            <option value="">Select Branch</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?php echo htmlspecialchars($branch->getId()); ?>"
                                    data-org="<?php echo htmlspecialchars($branch->getOrganizationId()); ?>"
                                    <?php echo $branch->getId() === $selectedBranchId ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($branch->getLabel()); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Select the branch this building belongs to</small>
                    </div>

                    <!-- Building Name -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Building Name <span style="color: var(--danger-color);">*</span>
                        </label>
                        <input type="text" name="name" required
                               value="<?php echo htmlspecialchars($building->getName() ?? ''); ?>"
                               placeholder="e.g., Main Office Building"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <small class="text-muted">Full name of the building</small>
                    </div>

                    <!-- Building Code -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Building Code
                        </label>
                        <input type="text" name="code"
                               value="<?php echo htmlspecialchars($building->getCode() ?? ''); ?>"
                               placeholder="e.g., BLD-001"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <small class="text-muted">Optional unique code</small>
                    </div>
                </div>

                <!-- Description -->
                <div style="margin-top: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Description
                    </label>
                    <textarea name="description" rows="3"
                              placeholder="Brief description of the building..."
                              style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;"><?php echo htmlspecialchars($building->getDescription() ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Address Information -->
            <div style="margin-bottom: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <h2 class="card-title">Address & Location</h2>
                <p class="text-muted" style="margin-bottom: 1rem;">All address fields and geo-coordinates are required</p>

                <?php echo PostalAddressField::render([
                    'label' => 'Building Address',
                    'id_prefix' => 'building_',
                    'street_address_value' => $building->getStreetAddress() ?? '',
                    'city_value' => $building->getCity() ?? '',
                    'state_value' => $building->getState() ?? '',
                    'postal_code_value' => $building->getPostalCode() ?? '',
                    'country_value' => $building->getCountry() ?? '',
                    'latitude_value' => $building->getLatitude() ?? '',
                    'longitude_value' => $building->getLongitude() ?? '',
                    'required' => true,
                    'help_text' => 'Enter the complete building address with geographic coordinates'
                ]); ?>
            </div>

            <!-- Contact Information -->
            <div style="margin-bottom: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <h2 class="card-title">Contact Information</h2>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <!-- Phone -->
                    <div>
                        <?php echo PhoneNumberField::render([
                            'label' => 'Phone',
                            'value' => $building->getPhone(),
                            'help_text' => 'Building contact phone number'
                        ]); ?>
                    </div>

                    <!-- Email -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Email
                        </label>
                        <input type="email" name="email"
                               value="<?php echo htmlspecialchars($building->getEmail() ?? ''); ?>"
                               placeholder="building@example.com"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <small class="text-muted">Building contact email</small>
                    </div>
                </div>
            </div>

            <!-- Building Details -->
            <div style="margin-bottom: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <h2 class="card-title">Building Details</h2>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <!-- Building Type -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Building Type
                        </label>
                        <select name="building_type" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                            <option value="">Select Type</option>
                            <?php foreach ($buildingTypes as $value => $label): ?>
                                <option value="<?php echo $value; ?>"
                                    <?php echo $building->getBuildingType() === $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Ownership Type -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Ownership Type
                        </label>
                        <select name="ownership_type" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                            <option value="">Select Ownership</option>
                            <?php foreach ($ownershipTypes as $value => $label): ?>
                                <option value="<?php echo $value; ?>"
                                    <?php echo $building->getOwnershipType() === $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Total Floors -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Total Floors
                        </label>
                        <input type="number" name="total_floors" min="1"
                               value="<?php echo htmlspecialchars($building->getTotalFloors() ?? ''); ?>"
                               placeholder="e.g., 10"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                    </div>

                    <!-- Total Area -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Total Area (sq ft)
                        </label>
                        <input type="number" step="0.01" name="total_area_sqft" min="0"
                               value="<?php echo htmlspecialchars($building->getTotalAreaSqft() ?? ''); ?>"
                               placeholder="e.g., 50000"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                    </div>

                    <!-- Year Built -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Year Built
                        </label>
                        <input type="number" name="year_built" min="1800" max="<?php echo date('Y') + 5; ?>"
                               value="<?php echo htmlspecialchars($building->getYearBuilt() ?? ''); ?>"
                               placeholder="e.g., 2020"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Sort Order
                        </label>
                        <input type="number" name="sort_order" min="0"
                               value="<?php echo htmlspecialchars($building->getSortOrder() ?? 0); ?>"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <small class="text-muted">Display order (0 = first)</small>
                    </div>
                </div>

                <!-- Active Status -->
                <div style="margin-top: 1.5rem;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1"
                               <?php echo ($building->getIsActive() ?? true) ? 'checked' : ''; ?>
                               style="margin-right: 0.5rem; width: 1.2rem; height: 1.2rem; cursor: pointer;">
                        <span style="font-weight: 500;">Active Building</span>
                    </label>
                    <small class="text-muted" style="display: block; margin-left: 1.7rem;">
                        Inactive buildings won't appear in public listings
                    </small>
                </div>
            </div>

            <!-- Form Actions -->
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                    <?php echo $isEdit ? 'Update Building' : 'Create Building'; ?>
                </button>
                <a href="/organizations/departments/facilities/branches/buildings/" class="btn btn-secondary" style="padding: 0.75rem 2rem; text-decoration: none;">
                    Cancel
                </a>
                <?php if ($isEdit): ?>
                    <a href="/organizations/departments/facilities/branches/buildings/form/" class="btn btn-secondary" style="padding: 0.75rem 2rem; text-decoration: none; margin-left: auto;">
                        + Create New Instead
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-populate organization_id from selected branch
    const orgIdInput = document.getElementById('organization_id');
    const branchSelect = document.getElementById('branch_id');

    function updateOrganizationFromBranch() {
        const selectedBranchOption = branchSelect.options[branchSelect.selectedIndex];
        if (selectedBranchOption && selectedBranchOption.value) {
            const orgId = selectedBranchOption.getAttribute('data-org');
            orgIdInput.value = orgId || '';
        } else {
            orgIdInput.value = '';
        }
    }

    branchSelect.addEventListener('change', updateOrganizationFromBranch);
    updateOrganizationFromBranch(); // Run on page load to set initial value
});
</script>

<?php include __DIR__ . '/../../../../../../../views/footer.php'; ?>
