<?php
require_once __DIR__ . '/../../../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationWorkstationRepository;
use App\Classes\OrganizationBuildingRepository;
use App\Classes\OrganizationRepository;

// Get workstation ID from query parameter
if (!isset($_GET['id'])) {
    header('Location: /organizations/departments/facilities/branches/buildings/workstations/?error=Workstation ID is required');
    exit;
}

$workstationId = $_GET['id'];
$workstationRepo = new OrganizationWorkstationRepository();
$buildingRepo = new OrganizationBuildingRepository();
$orgRepo = new OrganizationRepository();

// Try to get current user (may be null for guests)
$auth = new Auth();
$currentUser = $auth->isLoggedIn() ? $auth->getCurrentUser() : null;

// Get workstation - public view or user's workstation
if ($currentUser) {
    $workstation = $workstationRepo->findById($workstationId, $currentUser->getId());
    if (!$workstation) {
        // Try public view as fallback
        $workstationData = $workstationRepo->findByIdPublic($workstationId);
        if (!$workstationData) {
            header('Location: /organizations/departments/facilities/branches/buildings/workstations/?error=Workstation not found');
            exit;
        }
    }
} else {
    // Guest user - only public view
    $workstationData = $workstationRepo->findByIdPublic($workstationId);
    if (!$workstationData) {
        header('Location: /?error=Workstation not found');
        exit;
    }
    // Convert array to object for template consistency
    $workstation = new \App\Classes\OrganizationWorkstation($workstationData);
}

// Get building information
$building = $buildingRepo->findById($workstation->getBuildingId());

// Check if user can edit (owner or Super Admin)
$canEdit = false;
if ($currentUser) {
    $canEdit = $workstationRepo->canEdit($workstationId, $currentUser->getId(), $currentUser->getEmail());
}

$pageTitle = $workstation->getName() . ' - Workstation Details';
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
        <span><?php echo htmlspecialchars($workstation->getName()); ?></span>
    </div>

    <!-- Header Section -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($workstation->getName()); ?></h1>
                <?php if ($workstation->getCode()): ?>
                    <p class="text-muted" style="font-size: 1.1rem; margin-bottom: 0.5rem;">
                        Code: <?php echo htmlspecialchars($workstation->getCode()); ?>
                    </p>
                <?php endif; ?>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
                    <?php if ($workstation->getIsOccupied()): ?>
                        <span style="display: inline-block; padding: 0.5rem 1rem; background: var(--warning-color); color: white; border-radius: 4px; font-weight: 500;">
                            &#x1F464; Occupied
                        </span>
                    <?php else: ?>
                        <span style="display: inline-block; padding: 0.5rem 1rem; background: var(--secondary-color); color: white; border-radius: 4px; font-weight: 500;">
                            &#x2713; Available
                        </span>
                    <?php endif; ?>
                    <?php if ($workstation->getIsActive()): ?>
                        <span style="display: inline-block; padding: 0.5rem 1rem; background: var(--secondary-color); color: white; border-radius: 4px;">
                            Active
                        </span>
                    <?php else: ?>
                        <span style="display: inline-block; padding: 0.5rem 1rem; background: var(--text-light); color: white; border-radius: 4px;">
                            Inactive
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($canEdit): ?>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="/organizations/departments/facilities/branches/buildings/workstations/form/?id=<?php echo $workstation->getId(); ?>" class="btn btn-primary">
                        Edit Workstation
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">
        <!-- Left Column: Basic Information -->
        <div>
            <!-- Location Information -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">Location</h2>
                <div style="display: grid; gap: 1rem;">
                    <?php if ($building): ?>
                        <div>
                            <div class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.25rem;">Building</div>
                            <div style="font-weight: 500;"><?php echo htmlspecialchars($building->getName()); ?></div>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.25rem;">Full Location</div>
                        <div style="font-weight: 500;"><?php echo htmlspecialchars($workstation->getLocation()); ?></div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 1rem;">
                        <div>
                            <div class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.25rem;">Floor</div>
                            <div><?php echo htmlspecialchars($workstation->getFloor()); ?></div>
                        </div>
                        <?php if ($workstation->getRoom()): ?>
                            <div>
                                <div class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.25rem;">Room</div>
                                <div><?php echo htmlspecialchars($workstation->getRoom()); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($workstation->getSeatNumber()): ?>
                            <div>
                                <div class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.25rem;">Seat</div>
                                <div><?php echo htmlspecialchars($workstation->getSeatNumber()); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Workstation Details -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">Workstation Details</h2>
                <div style="display: grid; gap: 1rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <div>
                            <div class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.25rem;">Type</div>
                            <div style="text-transform: capitalize;"><?php echo htmlspecialchars(str_replace('_', ' ', $workstation->getWorkstationType() ?? 'desk')); ?></div>
                        </div>
                        <div>
                            <div class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.25rem;">Capacity</div>
                            <div><?php echo htmlspecialchars($workstation->getCapacity() ?? '1'); ?> person(s)</div>
                        </div>
                        <?php if ($workstation->getAreaSqft()): ?>
                            <div>
                                <div class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.25rem;">Area</div>
                                <div><?php echo number_format($workstation->getAreaSqft()); ?> sq ft</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($workstation->getDescription()): ?>
                <!-- Description -->
                <div class="card" style="margin-bottom: 2rem;">
                    <h2 class="card-title">Description</h2>
                    <p style="margin: 0; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($workstation->getDescription())); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Equipment & Amenities -->
        <div>
            <!-- Equipment -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">Equipment</h2>
                <div style="display: grid; gap: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <?php if ($workstation->getHasComputer()): ?>
                            <span style="color: var(--secondary-color); font-size: 1.2rem;">&#x2713;</span>
                            <span>Computer</span>
                        <?php else: ?>
                            <span style="color: var(--text-light); font-size: 1.2rem;">&#x2715;</span>
                            <span style="color: var(--text-light);">Computer</span>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <?php if ($workstation->getHasPhone()): ?>
                            <span style="color: var(--secondary-color); font-size: 1.2rem;">&#x2713;</span>
                            <span>Phone</span>
                        <?php else: ?>
                            <span style="color: var(--text-light); font-size: 1.2rem;">&#x2715;</span>
                            <span style="color: var(--text-light);">Phone</span>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <?php if ($workstation->getHasPrinter()): ?>
                            <span style="color: var(--secondary-color); font-size: 1.2rem;">&#x2713;</span>
                            <span>Printer Access</span>
                        <?php else: ?>
                            <span style="color: var(--text-light); font-size: 1.2rem;">&#x2715;</span>
                            <span style="color: var(--text-light);">Printer Access</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($workstation->getAmenities()): ?>
                <!-- Additional Amenities -->
                <div class="card" style="margin-bottom: 2rem;">
                    <h2 class="card-title">Additional Amenities</h2>
                    <p style="margin: 0; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($workstation->getAmenities())); ?></p>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <?php if (!$currentUser): ?>
                <div class="card" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
                    <h3 style="margin-bottom: 1rem; color: white;">Want to manage workstations?</h3>
                    <p style="margin-bottom: 1.5rem; opacity: 0.9;">Sign up for free to create and manage your own workstations and facilities.</p>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="/auth/register/" class="btn" style="background: white; color: var(--primary-color); padding: 0.75rem 1.5rem;">
                            Sign Up Free
                        </a>
                        <a href="/auth/login/" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid white; padding: 0.75rem 1.5rem;">
                            Log In
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back Button -->
    <div style="margin-top: 2rem;">
        <a href="/organizations/departments/facilities/branches/buildings/workstations/" class="btn btn-secondary">
            &larr; Back to Workstations
        </a>
    </div>
</div>

<?php include __DIR__ . '/../../../../../../../../views/footer.php'; ?>
