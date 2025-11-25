<?php
require_once __DIR__ . '/../../src/includes/autoload.php';

use App\Classes\Auth;
use App\Classes\OrganizationVacancyRepository;
use App\Classes\OrganizationVacancy;

$auth = new Auth();
$user = $auth->getCurrentUser(false); // Optional login

$vacancyRepo = new OrganizationVacancyRepository();

// Get filters from query string
$filters = [];
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (!empty($_GET['priority'])) {
    $filters['priority'] = $_GET['priority'];
}

// Get published vacancies (PUBLIC)
$vacanciesData = $vacancyRepo->findAllPublic(100, 0, $filters);
$totalCount = $vacancyRepo->countPublic($filters);

$pageTitle = 'Open Job Vacancies';
include __DIR__ . '/../../views/header.php';
?>

<div class="py-4">
    <div style="margin-bottom: 2rem;">
        <h1>Open Job Vacancies</h1>
        <p class="text-muted">Explore exciting career opportunities from our organizations</p>
    </div>

    <!-- Search and Filters -->
    <div class="card" style="margin-bottom: 2rem;">
        <form method="GET" action="/vacancies">
            <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 1rem; align-items: end;">
                <div>
                    <label for="search" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Search</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Search by title or description..." style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                </div>
                <div>
                    <label for="priority" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Priority</label>
                    <select id="priority" name="priority" style="padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; background: white;">
                        <option value="">All Priorities</option>
                        <option value="urgent" <?php echo (($_GET['priority'] ?? '') === 'urgent') ? 'selected' : ''; ?>>Urgent</option>
                        <option value="high" <?php echo (($_GET['priority'] ?? '') === 'high') ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo (($_GET['priority'] ?? '') === 'medium') ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo (($_GET['priority'] ?? '') === 'low') ? 'selected' : ''; ?>>Low</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">Search</button>
            </div>
        </form>
    </div>

    <!-- Results Count -->
    <div style="margin-bottom: 1.5rem;">
        <p class="text-muted">
            Found <strong><?php echo $totalCount; ?></strong> open <?php echo $totalCount === 1 ? 'vacancy' : 'vacancies'; ?>
            <?php if (!empty($_GET['search'])): ?>
                matching "<strong><?php echo htmlspecialchars($_GET['search']); ?></strong>"
            <?php endif; ?>
        </p>
    </div>

    <!-- Vacancies Grid -->
    <?php if (empty($vacanciesData)): ?>
        <div class="card" style="text-align: center; padding: 3rem 1rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">💼</div>
            <p class="text-muted" style="font-size: 1.2rem; margin-bottom: 1rem;">No Open Vacancies</p>
            <p class="text-muted" style="margin-bottom: 2rem;">
                <?php if (!empty($_GET['search'])): ?>
                    Try adjusting your search terms or <a href="/vacancies">view all vacancies</a>.
                <?php else: ?>
                    Check back later for new opportunities.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
            <?php foreach ($vacanciesData as $vacData): ?>
                <?php
                $vacancy = new OrganizationVacancy($vacData);
                $daysUntil = $vacancy->getDaysUntilDeadline();
                ?>
                <div class="card" style="display: flex; flex-direction: column; height: 100%; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" onclick="window.location.href='/vacancies/view?id=<?php echo $vacData['id']; ?>'">
                    <!-- Header -->
                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <h3 style="margin: 0; font-size: 1.125rem; line-height: 1.4;">
                                <?php echo htmlspecialchars($vacData['title']); ?>
                            </h3>
                            <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; white-space: nowrap; margin-left: 0.5rem; <?php echo $vacancy->getPriorityBadgeClass(); ?>">
                                <?php echo ucfirst($vacData['priority']); ?>
                            </span>
                        </div>
                        <?php if (!empty($vacData['code'])): ?>
                            <code style="background: var(--bg-light); padding: 0.125rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">
                                <?php echo htmlspecialchars($vacData['code']); ?>
                            </code>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <div style="margin-bottom: 1rem; flex-grow: 1;">
                        <p class="text-muted" style="line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo htmlspecialchars(substr($vacData['description'], 0, 200)); ?>
                            <?php if (strlen($vacData['description']) > 200): ?>...<?php endif; ?>
                        </p>
                    </div>

                    <!-- Details -->
                    <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: auto;">
                        <!-- Openings -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span class="text-muted" style="font-size: 0.875rem;">Openings:</span>
                            <strong><?php echo $vacData['openings_count']; ?> <?php echo $vacData['openings_count'] === 1 ? 'position' : 'positions'; ?></strong>
                        </div>

                        <!-- Salary -->
                        <?php if (!empty($vacData['salary_offered_min']) || !empty($vacData['salary_offered_max'])): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span class="text-muted" style="font-size: 0.875rem;">Salary:</span>
                                <strong><?php echo $vacancy->getSalaryRangeFormatted(); ?></strong>
                            </div>
                        <?php endif; ?>

                        <!-- Deadline -->
                        <?php if ($vacData['application_deadline']): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span class="text-muted" style="font-size: 0.875rem;">Deadline:</span>
                                <span style="<?php echo ($daysUntil !== null && $daysUntil < 7) ? 'color: #dc2626; font-weight: 600;' : ''; ?>">
                                    <?php echo date('M d, Y', strtotime($vacData['application_deadline'])); ?>
                                    <?php if ($daysUntil !== null && $daysUntil >= 0): ?>
                                        <span class="text-muted" style="font-size: 0.75rem;">
                                            (<?php echo $daysUntil === 0 ? 'Today!' : $daysUntil . ' days left'; ?>)
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- View Details Button -->
                        <a href="/vacancies/view?id=<?php echo $vacData['id']; ?>" class="btn btn-primary" style="width: 100%; margin-top: 1rem; text-align: center; display: block;" onclick="event.stopPropagation();">
                            View Details & Apply
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Call to Action for Organizations -->
    <?php if ($user): ?>
        <div class="card" style="margin-top: 3rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center; padding: 2rem;">
            <h2 style="color: white; margin-bottom: 1rem;">Have job openings?</h2>
            <p style="margin-bottom: 1.5rem; opacity: 0.9;">Post your vacancies and connect with qualified candidates.</p>
            <a href="/organizations/departments/human_resource/vacancies/" class="btn" style="background: white; color: #667eea; padding: 0.75rem 2rem; font-weight: 600;">
                Manage Your Vacancies
            </a>
        </div>
    <?php else: ?>
        <div class="card" style="margin-top: 3rem; background: var(--bg-light); text-align: center; padding: 2rem;">
            <h2 style="margin-bottom: 1rem;">Looking to hire?</h2>
            <p class="text-muted" style="margin-bottom: 1.5rem;">Create an account and start posting your job vacancies today.</p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="/auth/register" class="btn btn-primary">Sign Up Free</a>
                <a href="/auth/login" class="btn btn-secondary">Login</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>

<?php include __DIR__ . '/../../views/footer.php'; ?>
