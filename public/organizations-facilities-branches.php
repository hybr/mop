<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationRepository;
use App\Classes\OrganizationBranchRepository;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$orgRepo = new OrganizationRepository();
$branchRepo = new OrganizationBranchRepository();

// Get organizations for current user
$organizations = $orgRepo->findAllByUser($user->getId());

// Get branches for user's organizations
$branches = $branchRepo->findByUser($user->getId());
$deletedBranches = [];

// Only Super Admin can view deleted
if ($branchRepo->isSuperAdmin($user->getEmail())) {
    try {
        $deletedBranches = $branchRepo->findDeleted($user->getEmail());
    } catch (Exception $e) {
        // Silently handle
    }
}

$totalCount = $branchRepo->count(false);

$pageTitle = 'Branches Management';
include __DIR__ . '/../views/header.php';
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
        <a href="/organizations.php" class="link">Organizations</a>
        <span style="color: var(--text-light);"> / </span>
        <a href="/organizations-facilities.php" class="link">Facilities</a>
        <span style="color: var(--text-light);"> / </span>
        <span>Branches</span>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <h1>Branch Locations</h1>
        <a href="/branch-form.php" class="btn btn-primary">+ New Branch</a>
    </div>

    <!-- Search & Filter Section -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <input
                type="text"
                id="search-input"
                placeholder="Search by name, city, or code..."
                style="flex: 1; min-width: 250px; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;"
                onkeyup="filterTable()"
            >
            <?php if (count($organizations) > 1): ?>
            <select
                id="org-filter"
                style="padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;"
                onchange="filterTable()"
            >
                <option value="">All Organizations</option>
                <?php foreach ($organizations as $org): ?>
                    <option value="<?php echo $org->getId(); ?>">
                        <?php echo htmlspecialchars($org->getName()); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
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

    <!-- Branches List -->
    <div class="card">
        <h2 class="card-title">Branches (<?php echo count($branches); ?>)</h2>

        <?php if (empty($branches)): ?>
            <!-- Empty State -->
            <div style="text-align: center; padding: 3rem 1rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">&#127970;</div>
                <h3 style="margin-bottom: 0.5rem;">No Branches Yet</h3>
                <p class="text-muted" style="margin-bottom: 1.5rem;">Get started by creating your first branch location</p>
                <?php if (empty($organizations)): ?>
                    <p class="text-muted" style="margin-bottom: 1rem;">First, you need to create an organization</p>
                    <a href="/organization-form.php" class="btn btn-primary">Create Organization</a>
                <?php else: ?>
                    <a href="/branch-form.php" class="btn btn-primary">Create Your First Branch</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Desktop Table View -->
            <div class="desktop-view" style="overflow-x: auto;">
                <table id="branches-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Branch Name</th>
                            <th style="padding: 1rem;">Organization</th>
                            <th style="padding: 1rem;">Location</th>
                            <th style="padding: 1rem;">Contact</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Created</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($branches as $branch): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);"
                                data-status="<?php echo $branch->getIsActive() ? 'active' : 'inactive'; ?>"
                                data-org="<?php echo $branch->getOrganizationId(); ?>">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($branch->getName()); ?></strong>
                                    <?php if ($branch->getCode()): ?>
                                        <br>
                                        <code style="background-color: var(--bg-light); padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.85rem;">
                                            <?php echo htmlspecialchars($branch->getCode()); ?>
                                        </code>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php
                                    $org = $orgRepo->findById($branch->getOrganizationId());
                                    echo $org ? htmlspecialchars($org->getName()) : 'Unknown';
                                    ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($branch->getCity()): ?>
                                        <div><?php echo htmlspecialchars($branch->getCity()); ?><?php echo $branch->getState() ? ', ' . htmlspecialchars($branch->getState()) : ''; ?></div>
                                        <?php if ($branch->getCountry()): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($branch->getCountry()); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($branch->getPhone()): ?>
                                        <div><?php echo htmlspecialchars($branch->getPhone()); ?></div>
                                    <?php endif; ?>
                                    <?php if ($branch->getEmail()): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($branch->getEmail()); ?></small>
                                    <?php endif; ?>
                                    <?php if (!$branch->getPhone() && !$branch->getEmail()): ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($branch->getIsActive()): ?>
                                        <span style="color: var(--secondary-color); font-weight: 500;">&#x2713; Active</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">&#x2715; Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <?php echo date('M j, Y', strtotime($branch->getCreatedAt())); ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/branch-form.php?id=<?php echo $branch->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Edit</a>
                                    <?php if ($branchRepo->isSuperAdmin($user->getEmail())): ?>
                                        <a href="/branch-delete.php?id=<?php echo $branch->getId(); ?>" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Are you sure you want to delete this branch?');">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="mobile-view" style="display: none;">
                <?php foreach ($branches as $branch): ?>
                    <div class="card" style="margin-bottom: 1rem; padding: 1rem;"
                         data-status="<?php echo $branch->getIsActive() ? 'active' : 'inactive'; ?>"
                         data-org="<?php echo $branch->getOrganizationId(); ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <strong style="font-size: 1.1rem;"><?php echo htmlspecialchars($branch->getName()); ?></strong>
                            <?php if ($branch->getIsActive()): ?>
                                <span style="color: var(--secondary-color); font-weight: 500; font-size: 0.9rem;">&#x2713; Active</span>
                            <?php else: ?>
                                <span style="color: var(--text-light); font-size: 0.9rem;">&#x2715; Inactive</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($branch->getCity()): ?>
                            <div class="text-muted" style="margin-bottom: 0.5rem; font-size: 0.9rem;">
                                &#128205; <?php echo htmlspecialchars($branch->getCity()); ?><?php echo $branch->getState() ? ', ' . htmlspecialchars($branch->getState()) : ''; ?>
                            </div>
                        <?php endif; ?>
                        <div class="text-muted" style="font-size: 0.85rem; margin-bottom: 1rem;">
                            Created: <?php echo date('M j, Y', strtotime($branch->getCreatedAt())); ?>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="/branch-form.php?id=<?php echo $branch->getId(); ?>" class="btn btn-secondary" style="flex: 1; text-align: center;">Edit</a>
                            <?php if ($branchRepo->isSuperAdmin($user->getEmail())): ?>
                                <a href="/branch-delete.php?id=<?php echo $branch->getId(); ?>" class="btn btn-danger" style="flex: 1; text-align: center;" onclick="return confirm('Are you sure?');">Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Deleted Branches (Trash) -->
    <?php if (!empty($deletedBranches)): ?>
        <div class="card">
            <h2 class="card-title">Trash (<?php echo count($deletedBranches); ?>)</h2>
            <p class="text-muted text-small">Deleted branches can be restored or permanently deleted.</p>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Location</th>
                            <th style="padding: 1rem;">Deleted</th>
                            <th style="padding: 1rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deletedBranches as $branch): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); opacity: 0.6;">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($branch->getName()); ?></strong>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo $branch->getCity() ? htmlspecialchars($branch->getCity()) : '-'; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo date('M j, Y', strtotime($branch->getDeletedAt())); ?>
                                </td>
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <a href="/branch-restore.php?id=<?php echo $branch->getId(); ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">Restore</a>
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
function filterTable() {
    const searchInput = document.getElementById('search-input').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;
    const orgFilter = document.getElementById('org-filter')?.value || '';
    const table = document.getElementById('branches-table');

    if (table) {
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const text = row.textContent.toLowerCase();
            const status = row.getAttribute('data-status');
            const org = row.getAttribute('data-org');

            let showRow = true;

            if (searchInput && !text.includes(searchInput)) {
                showRow = false;
            }

            if (statusFilter && status !== statusFilter) {
                showRow = false;
            }

            if (orgFilter && org !== orgFilter) {
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
        const org = card.getAttribute('data-org');

        let showCard = true;

        if (searchInput && !text.includes(searchInput)) {
            showCard = false;
        }

        if (statusFilter && status !== statusFilter) {
            showCard = false;
        }

        if (orgFilter && org !== orgFilter) {
            showCard = false;
        }

        card.style.display = showCard ? '' : 'none';
    });
}

// Responsive design
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

window.addEventListener('load', handleResponsive);
window.addEventListener('resize', handleResponsive);
</script>

<style>
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
</style>

<?php include __DIR__ . '/../views/footer.php'; ?>
