<?php
require_once __DIR__ . '/../../../../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationRepository;
use App\Classes\FacilityTeamRepository;
use App\Classes\DepartmentTeam;

$auth = new Auth();

// Debug: Check if logged in
if (!$auth->isLoggedIn()) {
    error_log("Not logged in - redirecting to /auth/login/");
    error_log("Session data: " . print_r($_SESSION, true));
}

$auth->requireAuth();

$user = $auth->getCurrentUser();
$orgRepo = new OrganizationRepository();
$facilityTeamRepo = new FacilityTeamRepository();

// Get organizations for current user
$organizations = $orgRepo->findAllByUser($user->getId());

// Get facility teams
$facilityTeams = $facilityTeamRepo->findAll();
$deletedTeams = [];

// Only Super Admin can view deleted teams
if ($facilityTeamRepo->isSuperAdmin($user->getEmail())) {
    try {
        $deletedTeams = $facilityTeamRepo->findDeleted($user->getEmail());
    } catch (Exception $e) {
        // Silently handle if not authorized
    }
}

$totalCount = $facilityTeamRepo->count(false);

$pageTitle = 'Facility Teams';
include __DIR__ . '/../../../../../views/header.php';
?>

<div class="py-4">
    <!-- Success/Error Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div style="background-color: rgba(76, 175, 80, 0.1); border-left: 4px solid var(--secondary-color); padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
            <strong style="color: var(--secondary-color);">&#x2713; Success!</strong>
            <p style="margin: 0.5rem 0 0 0;"><?php echo htmlspecialchars($_GET['success']); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div style="background-color: rgba(244, 67, 54, 0.1); border-left: 4px solid #f44336; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
            <strong style="color: #f44336;">&#x2715; Error!</strong>
            <p style="margin: 0.5rem 0 0 0;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        </div>
    <?php endif; ?>

    <!-- Breadcrumb Navigation -->
    <div style="margin-bottom: 1rem;">
        <a href="/organizations/" class="link">Organizations</a>
        <span style="color: var(--text-light);"> / </span>
        <span>Facilities</span>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <h1>Facility Teams</h1>
        <a href="/organizations/departments/facilities/teams/form/" class="btn btn-primary">+ New Facility Team</a>
    </div>

    <!-- Quick Access Links -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2 class="card-title">Facility Management</h2>
        <p class="text-muted" style="margin-bottom: 1rem;">Quick access to facility management sections</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="/organizations/departments/facilities/branches/" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                Branches
            </a>
            <a href="/organizations/departments/facilities/teams" class="btn btn-primary" style="padding: 1rem; text-align: center;">
                Teams
            </a>
            <a href="/organizations/departments/facilities/branches/buildings/" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                Buildings
            </a>
            <a href="/organizations/departments/facilities/branches/buildings/workstations" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
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
                placeholder="Search by name or code..."
                style="flex: 1; min-width: 250px; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;"
                onkeyup="filterTable()"
            >
            <select
                id="status-filter"
                style="padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;"
                onchange="filterTable()"
            >
                <option value="">All Status</option>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <!-- Active Facility Teams -->
    <div class="card">
        <h2 class="card-title">Facility Teams (<?php echo count($facilityTeams); ?>)</h2>

        <?php if (empty($facilityTeams)): ?>
            <!-- Empty State -->
            <div style="text-align: center; padding: 3rem 1rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">&#128194;</div>
                <h3 style="margin-bottom: 0.5rem;">No Facility Teams Yet</h3>
                <p class="text-muted" style="margin-bottom: 1.5rem;">Get started by creating your first facility team</p>
                <a href="/organizations/departments/facilities/teams/form/" class="btn btn-primary">Create Your First Facility Team</a>
            </div>
        <?php else: ?>
            <!-- Desktop Table View -->
            <div class="desktop-view" style="overflow-x: auto;">
                <table id="teams-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Code</th>
                            <th style="padding: 1rem;">Description</th>
                            <th style="padding: 1rem;">Organization</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Created</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($facilityTeams as $dept): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);" data-status="<?php echo $dept->getIsActive() ? 'active' : 'inactive'; ?>">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($dept->getName()); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <code style="background-color: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 3px;">
                                        <?php echo htmlspecialchars($dept->getCode()); ?>
                                    </code>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($dept->getDescription()): ?>
                                        <span class="text-muted"><?php echo htmlspecialchars(substr($dept->getDescription(), 0, 50)); ?><?php echo strlen($dept->getDescription()) > 50 ? '...' : ''; ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php
                                    if ($dept->getOrganizationId()) {
                                        $org = $orgRepo->findById($dept->getOrganizationId(), $user->getId());
                                        echo $org ? htmlspecialchars($org->getName()) : 'Unknown';
                                    } else {
                                        echo '<span class="text-muted">Global</span>';
                                    }
                                    ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($dept->getIsActive()): ?>
                                        <span style="color: var(--secondary-color); font-weight: 500;">&#x2713; Active</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">&#x2715; Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <?php echo date('M j, Y', strtotime($dept->getCreatedAt())); ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/facilities/teams/form/?id=<?php echo $dept->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Edit</a>
                                    <?php if ($facilityTeamRepo->isSuperAdmin($user->getEmail())): ?>
                                        <a href="/organizations/departments/facilities/teams/delete/?id=<?php echo $dept->getId(); ?>" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Are you sure you want to delete this facility team?');">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="mobile-view" style="display: none;">
                <?php foreach ($facilityTeams as $dept): ?>
                    <div class="card" style="margin-bottom: 1rem; padding: 1rem;" data-status="<?php echo $dept->getIsActive() ? 'active' : 'inactive'; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <strong style="font-size: 1.1rem;"><?php echo htmlspecialchars($dept->getName()); ?></strong>
                            <?php if ($dept->getIsActive()): ?>
                                <span style="color: var(--secondary-color); font-weight: 500; font-size: 0.9rem;">&#x2713; Active</span>
                            <?php else: ?>
                                <span style="color: var(--text-light); font-size: 0.9rem;">&#x2715; Inactive</span>
                            <?php endif; ?>
                        </div>
                        <div style="margin-bottom: 0.5rem;">
                            <code style="background-color: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.85rem;">
                                <?php echo htmlspecialchars($dept->getCode()); ?>
                            </code>
                        </div>
                        <?php if ($dept->getDescription()): ?>
                            <p class="text-muted" style="margin-bottom: 0.5rem; font-size: 0.9rem;">
                                <?php echo htmlspecialchars($dept->getDescription()); ?>
                            </p>
                        <?php endif; ?>
                        <div class="text-muted" style="font-size: 0.85rem; margin-bottom: 1rem;">
                            Created: <?php echo date('M j, Y', strtotime($dept->getCreatedAt())); ?>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="/organizations/departments/facilities/teams/form/?id=<?php echo $dept->getId(); ?>" class="btn btn-secondary" style="flex: 1; text-align: center;">Edit</a>
                            <?php if ($facilityTeamRepo->isSuperAdmin($user->getEmail())): ?>
                                <a href="/organizations/departments/facilities/teams/delete/?id=<?php echo $dept->getId(); ?>" class="btn btn-danger" style="flex: 1; text-align: center;" onclick="return confirm('Are you sure you want to delete this facility team?');">Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Deleted Facility Teams (Trash) - Only for Super Admin -->
    <?php if (!empty($deletedTeams)): ?>
        <div class="card">
            <h2 class="card-title">Trash (<?php echo count($deletedTeams); ?>)</h2>
            <p class="text-muted text-small">Deleted facility teams can be restored or permanently deleted.</p>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Code</th>
                            <th style="padding: 1rem;">Deleted</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deletedTeams as $dept): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); opacity: 0.6;">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($dept->getName()); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <code style="background-color: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 3px;">
                                        <?php echo htmlspecialchars($dept->getCode()); ?>
                                    </code>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo date('M j, Y', strtotime($dept->getDeletedAt())); ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/organizations/departments/facilities/teams/restore/?id=<?php echo $dept->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Restore</a>
                                    <a href="/organizations/departments/facilities/teams/delete/?id=<?php echo $dept->getId(); ?>&permanent=1" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Permanently delete this facility team? This cannot be undone!');">Delete Forever</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Mobile-responsive JavaScript -->
<script>
function filterTable() {
    const searchInput = document.getElementById('search-input').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;
    const table = document.getElementById('teams-table');

    if (table) {
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const text = row.textContent.toLowerCase();
            const status = row.getAttribute('data-status');

            let showRow = true;

            // Search filter
            if (searchInput && !text.includes(searchInput)) {
                showRow = false;
            }

            // Status filter
            if (statusFilter && status !== statusFilter) {
                showRow = false;
            }

            row.style.display = showRow ? '' : 'none';
        }
    }

    // Also filter mobile cards
    const mobileCards = document.querySelectorAll('.mobile-view .card');
    mobileCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        const status = card.getAttribute('data-status');

        let showCard = true;

        if (searchInput && !text.includes(searchInput)) {
            showCard = false;
        }

        if (statusFilter && status !== statusFilter) {
            showCard = false;
        }

        card.style.display = showCard ? '' : 'none';
    });
}

// Responsive design: Toggle between table and card view
function handleResponsive() {
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

// Run on load and resize
window.addEventListener('load', handleResponsive);
window.addEventListener('resize', handleResponsive);
</script>

<style>
/* Mobile-specific styles */
@media (max-width: 768px) {
    .card {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .btn {
        font-size: 0.9rem;
        padding: 0.6rem 1rem;
    }
}

/* Status badge improvements */
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-active {
    background-color: rgba(46, 125, 50, 0.1);
    color: var(--secondary-color);
}

.status-inactive {
    background-color: rgba(158, 158, 158, 0.1);
    color: var(--text-light);
}
</style>

<?php include __DIR__ . '/../../../../../views/footer.php'; ?>
