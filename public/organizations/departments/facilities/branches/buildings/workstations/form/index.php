<?php
require_once __DIR__ . '/../../../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationWorkstation;
use App\Classes\OrganizationWorkstationRepository;
use App\Classes\OrganizationRepository;
use App\Classes\OrganizationBuildingRepository;
use App\Components\OrganizationField;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$workstationRepo = new OrganizationWorkstationRepository();
$orgRepo = new OrganizationRepository();
$buildingRepo = new OrganizationBuildingRepository();

$isEdit = false;
$errors = [];
$success = false;

// Workstation type options
$workstationTypes = [
    'desk' => 'Desk',
    'cubicle' => 'Cubicle',
    'private_office' => 'Private Office',
    'hot_desk' => 'Hot Desk',
    'meeting_room' => 'Meeting Room',
    'lab' => 'Lab',
    'workshop' => 'Workshop',
    'other' => 'Other'
];

// Initialize workstation
$workstation = new OrganizationWorkstation();

// Check if editing existing workstation
if (isset($_GET['id'])) {
    $isEdit = true;
    $workstation = $workstationRepo->findById($_GET['id'], $user->getId());

    if (!$workstation) {
        header('Location: /organizations/departments/facilities/branches/buildings/workstations/?error=Workstation not found');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Populate workstation from form data
        $workstation->setName($_POST['name'] ?? '');
        $workstation->setCode($_POST['code'] ?? '');
        $workstation->setDescription($_POST['description'] ?? '');
        $workstation->setBuildingId($_POST['building_id'] ?? '');
        $workstation->setOrganizationId($_POST['organization_id'] ?? '');

        // Location fields
        $workstation->setFloor($_POST['floor'] ?? '');
        $workstation->setRoom($_POST['room'] ?? '');
        $workstation->setSeatNumber($_POST['seat_number'] ?? '');

        // Workstation details
        $workstation->setWorkstationType($_POST['workstation_type'] ?? 'desk');
        $workstation->setCapacity($_POST['capacity'] ?? 1);
        $workstation->setAreaSqft($_POST['area_sqft'] ?? null);

        // Equipment
        $workstation->setHasComputer(isset($_POST['has_computer']) ? 1 : 0);
        $workstation->setHasPhone(isset($_POST['has_phone']) ? 1 : 0);
        $workstation->setHasPrinter(isset($_POST['has_printer']) ? 1 : 0);
        $workstation->setAmenities($_POST['amenities'] ?? '');

        // Assignment
        $workstation->setIsOccupied(isset($_POST['is_occupied']) ? 1 : 0);

        // Status
        $workstation->setIsActive(isset($_POST['is_active']) ? 1 : 0);
        $workstation->setSortOrder($_POST['sort_order'] ?? 0);

        // Validate
        $errors = $workstation->validate();

        if (empty($errors)) {
            if ($isEdit) {
                // Update existing
                $workstationRepo->update($workstation, $user->getId());
                $successMsg = 'Workstation "' . $workstation->getName() . '" updated successfully!';
                header('Location: /organizations/departments/facilities/branches/buildings/workstations/?success=' . urlencode($successMsg));
                exit;
            } else {
                // Create new
                $workstation = $workstationRepo->create($workstation, $user->getId());
                $successMsg = 'Workstation "' . $workstation->getName() . '" created successfully!';
                header('Location: /organizations/departments/facilities/branches/buildings/workstations/?success=' . urlencode($successMsg));
                exit;
            }
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// Get organizations for dropdown
$organizations = $orgRepo->findAllByUser($user->getId());

// Get buildings for dropdown
$buildings = $buildingRepo->findByUser($user->getId());

// Pre-select organization and building if provided
$selectedOrgId = $_GET['organization_id'] ?? $workstation->getOrganizationId() ?? '';
$selectedBuildingId = $_GET['building_id'] ?? $workstation->getBuildingId() ?? '';

// Get organization object if selected
$selectedOrganization = null;
if ($selectedOrgId) {
    $selectedOrganization = $orgRepo->findById($selectedOrgId, $user->getId());
}

$pageTitle = $isEdit ? 'Edit Workstation' : 'New Workstation';
include __DIR__ . '/../../../../../../../../views/header.php';
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
        <a href="/organizations/departments/facilities/branches/buildings/workstations/" class="link">Workstations</a>
        <span style="color: var(--text-light);"> / </span>
        <span><?php echo $isEdit ? 'Edit' : 'New'; ?></span>
    </div>

    <h1 style="margin-bottom: 2rem;"><?php echo $pageTitle; ?></h1>

    <!-- Success Message -->
    <?php if ($success): ?>
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            <p style="margin: 0;">Workstation saved successfully!</p>
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

                <!-- Organization Field (readonly, populated from selected building) -->
                <?php if ($selectedOrganization): ?>
                    <?php echo OrganizationField::render([
                        'label' => 'Organization',
                        'name' => 'organization_id',
                        'id' => 'organization_id',
                        'organization_id' => $selectedOrganization->getId(),
                        'organization_name' => $selectedOrganization->getFullName(),
                        'required' => true,
                        'help_text' => 'Organization is determined by the selected building'
                    ]); ?>
                <?php else: ?>
                    <!-- Hidden field for when no org selected yet - JS will populate this -->
                    <input type="hidden" name="organization_id" id="organization_id" value="">
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <!-- Building -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Building <span style="color: var(--danger-color);">*</span>
                        </label>
                        <select name="building_id" id="building_id" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                            <option value="">Select Building</option>
                            <?php foreach ($buildings as $building): ?>
                                <option value="<?php echo htmlspecialchars($building->getId()); ?>"
                                    data-org="<?php echo htmlspecialchars($building->getOrganizationId()); ?>"
                                    <?php echo $building->getId() === $selectedBuildingId ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($building->getLabel()); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Select the building this workstation is in</small>
                    </div>

                    <!-- Workstation Name -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Workstation Name <span style="color: var(--danger-color);">*</span>
                        </label>
                        <input type="text" name="name" required
                               value="<?php echo htmlspecialchars($workstation->getName() ?? ''); ?>"
                               placeholder="e.g., Developer Desk 1"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <small class="text-muted">Name or identifier for this workstation</small>
                    </div>

                    <!-- Workstation Code -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Workstation Code
                        </label>
                        <input type="text" name="code"
                               value="<?php echo htmlspecialchars($workstation->getCode() ?? ''); ?>"
                               placeholder="e.g., WS-001"
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
                              placeholder="Brief description of the workstation..."
                              style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;"><?php echo htmlspecialchars($workstation->getDescription() ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Location Information -->
            <div style="margin-bottom: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <h2 class="card-title">Location in Building</h2>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <!-- Floor -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Floor <span style="color: var(--danger-color);">*</span>
                        </label>
                        <input type="text" name="floor" required
                               value="<?php echo htmlspecialchars($workstation->getFloor() ?? ''); ?>"
                               placeholder="e.g., 3, G, B1"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <small class="text-muted">Floor number or level (required)</small>
                    </div>

                    <!-- Room -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Room
                        </label>
                        <input type="text" name="room"
                               value="<?php echo htmlspecialchars($workstation->getRoom() ?? ''); ?>"
                               placeholder="e.g., 301, Meeting Room A"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <small class="text-muted">Room number or name</small>
                    </div>

                    <!-- Seat Number -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Seat Number
                        </label>
                        <input type="text" name="seat_number"
                               value="<?php echo htmlspecialchars($workstation->getSeatNumber() ?? ''); ?>"
                               placeholder="e.g., A12, Desk 5"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <small class="text-muted">Specific seat or desk identifier</small>
                    </div>
                </div>
            </div>

            <!-- Workstation Details -->
            <div style="margin-bottom: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <h2 class="card-title">Workstation Details</h2>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <!-- Workstation Type -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Workstation Type
                        </label>
                        <select name="workstation_type" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                            <?php foreach ($workstationTypes as $value => $label): ?>
                                <option value="<?php echo $value; ?>"
                                    <?php echo ($workstation->getWorkstationType() ?? 'desk') === $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Capacity -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Capacity
                        </label>
                        <input type="number" name="capacity" min="1"
                               value="<?php echo htmlspecialchars($workstation->getCapacity() ?? 1); ?>"
                               placeholder="1"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <small class="text-muted">Number of people this workstation can accommodate</small>
                    </div>

                    <!-- Area -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Area (sq ft)
                        </label>
                        <input type="number" step="0.01" name="area_sqft" min="0"
                               value="<?php echo htmlspecialchars($workstation->getAreaSqft() ?? ''); ?>"
                               placeholder="e.g., 80"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <small class="text-muted">Workstation floor area</small>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            Sort Order
                        </label>
                        <input type="number" name="sort_order" min="0"
                               value="<?php echo htmlspecialchars($workstation->getSortOrder() ?? 0); ?>"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        <small class="text-muted">Display order (0 = first)</small>
                    </div>
                </div>
            </div>

            <!-- Equipment & Amenities -->
            <div style="margin-bottom: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <h2 class="card-title">Equipment & Amenities</h2>

                <!-- Equipment Checkboxes -->
                <div style="display: flex; gap: 2rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="has_computer" value="1"
                               <?php echo $workstation->getHasComputer() ? 'checked' : ''; ?>
                               style="margin-right: 0.5rem; width: 1.2rem; height: 1.2rem; cursor: pointer;">
                        <span>Has Computer</span>
                    </label>

                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="has_phone" value="1"
                               <?php echo $workstation->getHasPhone() ? 'checked' : ''; ?>
                               style="margin-right: 0.5rem; width: 1.2rem; height: 1.2rem; cursor: pointer;">
                        <span>Has Phone</span>
                    </label>

                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="has_printer" value="1"
                               <?php echo $workstation->getHasPrinter() ? 'checked' : ''; ?>
                               style="margin-right: 0.5rem; width: 1.2rem; height: 1.2rem; cursor: pointer;">
                        <span>Has Printer Access</span>
                    </label>
                </div>

                <!-- Amenities -->
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Additional Amenities
                    </label>
                    <textarea name="amenities" rows="3"
                              placeholder="e.g., Standing desk, dual monitors, ergonomic chair..."
                              style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;"><?php echo htmlspecialchars($workstation->getAmenities() ?? ''); ?></textarea>
                    <small class="text-muted">List any additional features or amenities</small>
                </div>
            </div>

            <!-- Status -->
            <div style="margin-bottom: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <h2 class="card-title">Status</h2>

                <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="is_occupied" value="1"
                               <?php echo $workstation->getIsOccupied() ? 'checked' : ''; ?>
                               style="margin-right: 0.5rem; width: 1.2rem; height: 1.2rem; cursor: pointer;">
                        <span style="font-weight: 500;">Currently Occupied</span>
                    </label>

                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1"
                               <?php echo ($workstation->getIsActive() ?? true) ? 'checked' : ''; ?>
                               style="margin-right: 0.5rem; width: 1.2rem; height: 1.2rem; cursor: pointer;">
                        <span style="font-weight: 500;">Active Workstation</span>
                    </label>
                </div>
                <small class="text-muted" style="display: block; margin-top: 0.5rem;">
                    Inactive workstations won't appear in public listings
                </small>
            </div>

            <!-- Form Actions -->
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                    <?php echo $isEdit ? 'Update Workstation' : 'Create Workstation'; ?>
                </button>
                <a href="/organizations/departments/facilities/branches/buildings/workstations/" class="btn btn-secondary" style="padding: 0.75rem 2rem; text-decoration: none;">
                    Cancel
                </a>
                <?php if ($isEdit): ?>
                    <a href="/organizations/departments/facilities/branches/buildings/workstations/form/" class="btn btn-secondary" style="padding: 0.75rem 2rem; text-decoration: none; margin-left: auto;">
                        + Create New Instead
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-populate organization_id from selected building
    const orgIdInput = document.getElementById('organization_id');
    const buildingSelect = document.getElementById('building_id');

    function updateOrganizationFromBuilding() {
        const selectedBuildingOption = buildingSelect.options[buildingSelect.selectedIndex];
        if (selectedBuildingOption && selectedBuildingOption.value) {
            const orgId = selectedBuildingOption.getAttribute('data-org');
            orgIdInput.value = orgId || '';
        } else {
            orgIdInput.value = '';
        }
    }

    buildingSelect.addEventListener('change', updateOrganizationFromBuilding);
    updateOrganizationFromBuilding(); // Run on page load to set initial value
});
</script>

<?php include __DIR__ . '/../../../../../../../../views/footer.php'; ?>
