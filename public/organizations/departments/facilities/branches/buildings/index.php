<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationRepository;
use App\Classes\OrganizationBranchRepository;
use App\Classes\OrganizationBuildingRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$orgRepo = new OrganizationRepository();
$branchRepo = new OrganizationBranchRepository();
$buildingRepo = new OrganizationBuildingRepository();

// Get organizations for current user
$organizations = $orgRepo->findAllByUser($user->getId());

// Get all buildings for user's organizations
$buildings = $buildingRepo->findByUser($user->getId());

// Get deleted buildings if Super Admin
$isSuperAdmin = $buildingRepo->isSuperAdmin($user->getEmail());
$deletedBuildings = $isSuperAdmin ? $buildingRepo->findDeleted($user->getEmail()) : [];

// Get all branches for branch dropdown
$branches = $branchRepo->findByUser($user->getId());

$totalCount = $buildingRepo->count(false);

$pageTitle = 'Buildings Management';
include __DIR__ . '/../../../../../../views/header.php';
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
        <span>Buildings</span>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <h1>Buildings</h1>
        <a href="/organizations/departments/facilities/branches/buildings/form/" class="btn btn-primary">+ New Building</a>
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
            <a href="/organizations/departments/facilities/branches/buildings/" class="btn btn-primary" style="padding: 1rem; text-align: center;">
                Buildings
            </a>
            <a href="/organizations/departments/facilities/branches/buildings/workstations/" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
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
                placeholder="Search buildings..."
                style="flex: 1; min-width: 200px; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;"
            />
            <select id="branch-filter" style="padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; min-width: 150px;">
                <option value="">All Branches</option>
                <?php foreach ($branches as $branch): ?>
                    <option value="<?php echo htmlspecialchars($branch->getId()); ?>">
                        <?php echo htmlspecialchars($branch->getLabel()); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Active Buildings -->
    <div class="card">
        <h2 class="card-title">Active Buildings (<?php echo count($buildings); ?>)</h2>

        <?php if (empty($buildings)): ?>
            <!-- Empty State -->
            <div style="text-align: center; padding: 3rem 1rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">&#127970;</div>
                <h3 style="margin-bottom: 0.5rem;">No Buildings Yet</h3>
                <p class="text-muted" style="margin-bottom: 1.5rem;">Get started by creating your first building location</p>
                <?php if (empty($branches)): ?>
                    <p class="text-muted" style="margin-bottom: 1rem;">First, you need to create a branch</p>
                    <a href="/organizations/departments/facilities/branches/form/" class="btn btn-primary">Create Branch</a>
                <?php else: ?>
                    <a href="/organizations/departments/facilities/branches/buildings/form/" class="btn btn-primary">Create Your First Building</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Desktop Table View -->
            <div class="desktop-view" style="overflow-x: auto;">
                <table id="buildings-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Branch</th>
                            <th style="padding: 1rem;">Address</th>
                            <th style="padding: 1rem;">Type</th>
                            <th style="padding: 1rem;">Area (sqft)</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($buildings as $building): ?>
                            <?php
                            // Get branch for this building
                            $branch = $branchRepo->findById($building->getBranchId());
                            ?>
                            <tr style="border-bottom: 1px solid var(--border-color);"
                                data-branch="<?php echo htmlspecialchars($building->getBranchId()); ?>"
                                data-search="<?php echo htmlspecialchars(strtolower($building->getName() . ' ' . $building->getCity() . ' ' . $building->getCode())); ?>">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($building->getName()); ?></strong>
                                    <?php if ($building->getCode()): ?>
                                        <br><small class="text-muted">Code: <?php echo htmlspecialchars($building->getCode()); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $branch ? htmlspecialchars($branch->getLabel()) : '-'; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <div><?php echo $building->getCity() ? htmlspecialchars($building->getCity()) : '-'; ?></div>
                                    <?php if ($building->getState()): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($building->getState()); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; text-transform: capitalize;">
                                    <?php echo $building->getBuildingType() ? htmlspecialchars(str_replace('_', ' ', $building->getBuildingType())) : '-'; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $building->getTotalAreaSqft() ? number_format($building->getTotalAreaSqft()) : '-'; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($building->getIsActive()): ?>
                                        <span style="color: var(--secondary-color);">&#x2713; Active</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">&#x2715; Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/facilities/branches/buildings/form/?id=<?php echo $building->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Edit</a>
                                    <?php if ($isSuperAdmin): ?>
                                        <a href="/organizations/departments/facilities/branches/buildings/delete/?id=<?php echo $building->getId(); ?>" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Are you sure you want to delete this building?');">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="mobile-view" style="display: none;">
                <?php foreach ($buildings as $building): ?>
                    <?php
                    $branch = $branchRepo->findById($building->getBranchId());
                    ?>
                    <div class="card" style="margin-bottom: 1rem; padding: 1rem;"
                         data-branch="<?php echo htmlspecialchars($building->getBranchId()); ?>"
                         data-search="<?php echo htmlspecialchars(strtolower($building->getName() . ' ' . $building->getCity() . ' ' . $building->getCode())); ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <strong><?php echo htmlspecialchars($building->getName()); ?></strong>
                            <?php if ($building->getIsActive()): ?>
                                <span style="color: var(--secondary-color); font-size: 0.9rem;">&#x2713; Active</span>
                            <?php else: ?>
                                <span style="color: var(--text-light); font-size: 0.9rem;">&#x2715; Inactive</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($branch): ?>
                            <div class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.5rem;">
                                Branch: <?php echo htmlspecialchars($branch->getLabel()); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($building->getCity()): ?>
                            <div class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($building->getCity()); ?><?php echo $building->getState() ? ', ' . htmlspecialchars($building->getState()) : ''; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($building->getBuildingType()): ?>
                            <div class="text-muted" style="margin-bottom: 0.5rem; font-size: 0.9rem; text-transform: capitalize;">
                                <?php echo htmlspecialchars(str_replace('_', ' ', $building->getBuildingType())); ?>
                            </div>
                        <?php endif; ?>
                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                            <a href="/organizations/departments/facilities/branches/buildings/form/?id=<?php echo $building->getId(); ?>" class="btn btn-secondary" style="flex: 1; text-align: center;">Edit</a>
                            <?php if ($isSuperAdmin): ?>
                                <a href="/organizations/departments/facilities/branches/buildings/delete/?id=<?php echo $building->getId(); ?>" class="btn btn-danger" style="flex: 1; text-align: center;" onclick="return confirm('Are you sure?');">Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Deleted Buildings (Trash) - Only for Super Admin -->
    <?php if ($isSuperAdmin && !empty($deletedBuildings)): ?>
        <div class="card">
            <h2 class="card-title">Trash (<?php echo count($deletedBuildings); ?>)</h2>
            <p class="text-muted text-small">Deleted buildings can be restored.</p>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Code</th>
                            <th style="padding: 1rem;">Address</th>
                            <th style="padding: 1rem;">Deleted</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deletedBuildings as $building): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); opacity: 0.6;">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($building->getName()); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($building->getCode()): ?>
                                        <code style="background-color: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 3px;">
                                            <?php echo htmlspecialchars($building->getCode()); ?>
                                        </code>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $building->getCity() ? htmlspecialchars($building->getCity()) : '-'; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo date('M j, Y', strtotime($building->getDeletedAt())); ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/facilities/branches/buildings/restore/?id=<?php echo $building->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Restore</a>
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
// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const branchFilter = document.getElementById('branch-filter');
    const tableRows = document.querySelectorAll('#buildings-table tbody tr');
    const mobileCards = document.querySelectorAll('.mobile-view .card[data-search]');

    function filterBuildings() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedBranch = branchFilter.value;

        // Filter table rows
        tableRows.forEach(row => {
            const searchText = row.getAttribute('data-search') || '';
            const branchId = row.getAttribute('data-branch') || '';

            const matchesSearch = searchText.includes(searchTerm);
            const matchesBranch = !selectedBranch || branchId === selectedBranch;

            row.style.display = (matchesSearch && matchesBranch) ? '' : 'none';
        });

        // Filter mobile cards
        mobileCards.forEach(card => {
            const searchText = card.getAttribute('data-search') || '';
            const branchId = card.getAttribute('data-branch') || '';

            const matchesSearch = searchText.includes(searchTerm);
            const matchesBranch = !selectedBranch || branchId === selectedBranch;

            card.style.display = (matchesSearch && matchesBranch) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterBuildings);
    branchFilter.addEventListener('change', filterBuildings);

    // Mobile responsive view
    function handleResize() {
        const desktopView = document.querySelector('.desktop-view');
        const mobileView = document.querySelector('.mobile-view');
        if (window.innerWidth < 768) {
            if (desktopView) desktopView.style.display = 'none';
            if (mobileView) mobileView.style.display = 'block';
        } else {
            if (desktopView) desktopView.style.display = 'block';
            if (mobileView) mobileView.style.display = 'none';
        }
    }

    handleResize();
    window.addEventListener('resize', handleResize);
});
</script>

<style>
/* Mobile responsive styles */
@media (max-width: 767px) {
    .desktop-view {
        display: none !important;
    }
    .mobile-view {
        display: block !important;
    }
}
</style>

<?php include __DIR__ . '/../../../../../../views/footer.php'; ?>
