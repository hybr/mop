<?php
require_once __DIR__ . '/../../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationRepository;
use App\Classes\OrganizationBuildingRepository;
use App\Classes\OrganizationWorkstationRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$orgRepo = new OrganizationRepository();
$buildingRepo = new OrganizationBuildingRepository();
$workstationRepo = new OrganizationWorkstationRepository();

// Get organizations for current user
$organizations = $orgRepo->findAllByUser($user->getId());

// Get all workstations for user's organizations
$workstations = $workstationRepo->findAllByUser($user->getId());

// Get deleted workstations if Super Admin
$isSuperAdmin = $workstationRepo->isSuperAdmin($user->getEmail());
$deletedWorkstations = $isSuperAdmin ? $workstationRepo->findDeletedByUser($user->getId()) : [];

// Get all buildings for building dropdown
$buildings = $buildingRepo->findByUser($user->getId());

$totalCount = $workstationRepo->countByUser($user->getId(), false);

$pageTitle = 'Workstations Management';
include __DIR__ . '/../../../../../../../views/header.php';
?>

<div class="py-4">
    <!-- Success/Error Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            <p style="margin: 0.5rem 0 0 0;"><?php echo htmlspecialchars($_GET['success']); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
            <p style="margin: 0.5rem 0 0 0;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        </div>
    <?php endif; ?>

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
        <span>Workstations</span>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <h1>Workstations</h1>
        <a href="/organizations/departments/facilities/branches/buildings/workstations/form/" class="btn btn-primary">+ New Workstation</a>
    </div>

    <!-- Quick Access Links -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2 class="card-title">Facility Management</h2>
        <p class="text-muted" style="margin-bottom: 1rem;">Quick access to facility management sections</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="/organizations/departments/facilities/branches/" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                Branches
            </a>
            <a href="/organizations/departments/facilities/teams/" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                Teams
            </a>
            <a href="/organizations/departments/facilities/branches/buildings/" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                Buildings
            </a>
            <a href="/organizations/departments/facilities/branches/buildings/workstations/" class="btn btn-primary" style="padding: 1rem; text-align: center;">
                Workstations
            </a>
        </div>
    </div>

    <!-- Search & Filter Section -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <input
                type="text"
                id="search-input"
                placeholder="Search workstations..."
                style="flex: 1; min-width: 200px; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;"
            />
            <select id="building-filter" style="padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; min-width: 150px;">
                <option value="">All Buildings</option>
                <?php foreach ($buildings as $building): ?>
                    <option value="<?php echo htmlspecialchars($building->getId()); ?>">
                        <?php echo htmlspecialchars($building->getLabel()); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Active Workstations -->
    <div class="card">
        <h2 class="card-title">Active Workstations (<?php echo count($workstations); ?>)</h2>

        <?php if (empty($workstations)): ?>
            <!-- Empty State -->
            <div style="text-align: center; padding: 3rem 1rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">&#128187;</div>
                <h3 style="margin-bottom: 0.5rem;">No Workstations Yet</h3>
                <p class="text-muted" style="margin-bottom: 1.5rem;">Get started by creating your first workstation</p>
                <?php if (empty($buildings)): ?>
                    <p class="text-muted" style="margin-bottom: 1rem;">First, you need to create a building</p>
                    <a href="/organizations/departments/facilities/branches/buildings/form/" class="btn btn-primary">Create Building</a>
                <?php else: ?>
                    <a href="/organizations/departments/facilities/branches/buildings/workstations/form/" class="btn btn-primary">Create Your First Workstation</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Desktop Table View -->
            <div class="desktop-view" style="overflow-x: auto;">
                <table id="workstations-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Building</th>
                            <th style="padding: 1rem;">Location</th>
                            <th style="padding: 1rem;">Type</th>
                            <th style="padding: 1rem;">Capacity</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workstations as $workstation): ?>
                            <?php
                            // Get building for this workstation
                            $building = $buildingRepo->findById($workstation->getBuildingId());
                            ?>
                            <tr style="border-bottom: 1px solid var(--border-color);"
                                data-building="<?php echo htmlspecialchars($workstation->getBuildingId()); ?>"
                                data-search="<?php echo htmlspecialchars(strtolower($workstation->getName() . ' ' . $workstation->getFloor() . ' ' . $workstation->getRoom() . ' ' . $workstation->getCode())); ?>">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($workstation->getName()); ?></strong>
                                    <?php if ($workstation->getCode()): ?>
                                        <br><small class="text-muted">Code: <?php echo htmlspecialchars($workstation->getCode()); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $building ? htmlspecialchars($building->getName()) : '-'; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($workstation->getLocation()); ?>
                                </td>
                                <td style="padding: 1rem; text-transform: capitalize;">
                                    <?php echo $workstation->getWorkstationType() ? htmlspecialchars(str_replace('_', ' ', $workstation->getWorkstationType())) : '-'; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $workstation->getCapacity() ?? '1'; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <div>
                                        <?php if ($workstation->getIsOccupied()): ?>
                                            <span style="color: var(--warning-color);">&#x1F464; Occupied</span>
                                        <?php else: ?>
                                            <span style="color: var(--secondary-color);">&#x2713; Available</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!$workstation->getIsActive()): ?>
                                        <small style="color: var(--text-light);">Inactive</small>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/facilities/branches/buildings/workstations/form/?id=<?php echo $workstation->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Edit</a>
                                    <?php if ($isSuperAdmin): ?>
                                        <a href="/organizations/departments/facilities/branches/buildings/workstations/delete/?id=<?php echo $workstation->getId(); ?>" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Are you sure you want to delete this workstation?');">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="mobile-view" style="display: none;">
                <?php foreach ($workstations as $workstation): ?>
                    <?php
                    $building = $buildingRepo->findById($workstation->getBuildingId());
                    ?>
                    <div class="card" style="margin-bottom: 1rem; padding: 1rem;"
                         data-building="<?php echo htmlspecialchars($workstation->getBuildingId()); ?>"
                         data-search="<?php echo htmlspecialchars(strtolower($workstation->getName() . ' ' . $workstation->getFloor() . ' ' . $workstation->getRoom() . ' ' . $workstation->getCode())); ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <strong><?php echo htmlspecialchars($workstation->getName()); ?></strong>
                            <?php if ($workstation->getIsOccupied()): ?>
                                <span style="color: var(--warning-color); font-size: 0.9rem;">&#x1F464; Occupied</span>
                            <?php else: ?>
                                <span style="color: var(--secondary-color); font-size: 0.9rem;">&#x2713; Available</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($workstation->getCode()): ?>
                            <div class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.5rem;">
                                Code: <?php echo htmlspecialchars($workstation->getCode()); ?>
                            </div>
                        <?php endif; ?>
                        <div style="margin-bottom: 0.5rem;">
                            <span class="text-muted">Building:</span> <?php echo $building ? htmlspecialchars($building->getName()) : '-'; ?>
                        </div>
                        <div style="margin-bottom: 0.5rem;">
                            <span class="text-muted">Location:</span> <?php echo htmlspecialchars($workstation->getLocation()); ?>
                        </div>
                        <div style="margin-bottom: 0.5rem;">
                            <span class="text-muted">Type:</span> <?php echo htmlspecialchars(str_replace('_', ' ', $workstation->getWorkstationType())); ?>
                        </div>
                        <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                            <a href="/organizations/departments/facilities/branches/buildings/workstations/form/?id=<?php echo $workstation->getId(); ?>" class="btn btn-secondary" style="flex: 1; padding: 0.5rem; text-align: center;">Edit</a>
                            <?php if ($isSuperAdmin): ?>
                                <a href="/organizations/departments/facilities/branches/buildings/workstations/delete/?id=<?php echo $workstation->getId(); ?>" class="btn btn-danger" style="flex: 1; padding: 0.5rem; text-align: center;" onclick="return confirm('Are you sure you want to delete this workstation?');">Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Deleted Workstations (Super Admin Only) -->
    <?php if ($isSuperAdmin && !empty($deletedWorkstations)): ?>
        <div class="card" style="margin-top: 2rem;">
            <h2 class="card-title" style="color: var(--danger-color);">Deleted Workstations (<?php echo count($deletedWorkstations); ?>)</h2>
            <p class="text-muted" style="margin-bottom: 1rem;">These workstations have been soft-deleted and can be restored</p>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Location</th>
                            <th style="padding: 1rem;">Deleted At</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deletedWorkstations as $workstation): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); opacity: 0.6;">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($workstation->getName()); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($workstation->getLocation()); ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo date('M d, Y', strtotime($workstation->getDeletedAt())); ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/facilities/branches/buildings/workstations/restore/?id=<?php echo $workstation->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Restore</a>
                                    <a href="/organizations/departments/facilities/branches/buildings/workstations/delete/?id=<?php echo $workstation->getId(); ?>&permanent=1" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('PERMANENT DELETE: This action cannot be undone. Are you sure?');">Delete Permanently</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const buildingFilter = document.getElementById('building-filter');
    const desktopRows = document.querySelectorAll('#workstations-table tbody tr');
    const mobileCards = document.querySelectorAll('.mobile-view .card');

    function filterWorkstations() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedBuilding = buildingFilter.value;

        // Filter desktop rows
        desktopRows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            const buildingData = row.getAttribute('data-building') || '';

            const matchesSearch = searchData.includes(searchTerm);
            const matchesBuilding = !selectedBuilding || buildingData === selectedBuilding;

            row.style.display = (matchesSearch && matchesBuilding) ? '' : 'none';
        });

        // Filter mobile cards
        mobileCards.forEach(card => {
            const searchData = card.getAttribute('data-search') || '';
            const buildingData = card.getAttribute('data-building') || '';

            const matchesSearch = searchData.includes(searchTerm);
            const matchesBuilding = !selectedBuilding || buildingData === selectedBuilding;

            card.style.display = (matchesSearch && matchesBuilding) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterWorkstations);
    buildingFilter.addEventListener('change', filterWorkstations);

    // Responsive view switching
    function updateView() {
        const isMobile = window.innerWidth < 768;
        const desktopView = document.querySelector('.desktop-view');
        const mobileView = document.querySelector('.mobile-view');

        if (desktopView && mobileView) {
            desktopView.style.display = isMobile ? 'none' : 'block';
            mobileView.style.display = isMobile ? 'block' : 'none';
        }
    }

    updateView();
    window.addEventListener('resize', updateView);
});
</script>

<style>
@media (max-width: 767px) {
    .desktop-view {
        display: none !important;
    }
    .mobile-view {
        display: block !important;
    }
}
</style>

<?php include __DIR__ . '/../../../../../../../views/footer.php'; ?>
