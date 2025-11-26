<?php
require_once __DIR__ . '/../../../../../src/includes/autoload.php';

use App\Classes\Auth;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();

$pageTitle = 'Hiring Workflow';
include __DIR__ . '/../../../../../views/header.php';

// Hiring workflow configuration
$workflowData = json_decode(file_get_contents(__DIR__ . '/workflow_config.json'), true);
?>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <a href="/organizations/departments/" class="text-muted" style="text-decoration: none;">&larr; Back to Departments</a>
            <h1 style="margin-top: 0.5rem;">Hiring Workflow</h1>
            <p class="text-muted">End-to-end employee recruitment process management</p>
        </div>
        <a href="/organizations/departments/human_resource/hiring/execution/" class="btn btn-primary">View Workflow Diagram</a>
    </div>

    <!-- Workflow Overview -->
    <div class="card" style="margin-bottom: 2rem; background: var(--bg-light);">
        <h2 style="margin-top: 0; margin-bottom: 0.5rem;">Workflow Overview</h2>
        <p style="margin: 0;">
            The Hiring Workflow automates the employee recruitment process from vacancy posting through candidate onboarding.
            Each stage requires specific organizational positions to execute, ensuring proper authorization and accountability.
        </p>
    </div>

    <!-- Workflow Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div class="card" style="text-align: center;">
            <h3 style="margin: 0; font-size: 2rem; color: var(--primary-color);">9</h3>
            <p class="text-muted" style="margin: 0.5rem 0 0 0;">Workflow Stages</p>
        </div>
        <div class="card" style="text-align: center;">
            <h3 style="margin: 0; font-size: 2rem; color: var(--secondary-color);">0</h3>
            <p class="text-muted" style="margin: 0.5rem 0 0 0;">Active Instances</p>
        </div>
        <div class="card" style="text-align: center;">
            <h3 style="margin: 0; font-size: 2rem; color: var(--text-color);">0</h3>
            <p class="text-muted" style="margin: 0.5rem 0 0 0;">Completed Hires</p>
        </div>
        <div class="card" style="text-align: center;">
            <h3 style="margin: 0; font-size: 2rem; color: var(--text-muted);">-</h3>
            <p class="text-muted" style="margin: 0.5rem 0 0 0;">Avg. Time to Hire</p>
        </div>
    </div>

    <!-- Workflow Stages -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2 class="card-title">Workflow Stages</h2>
        <p class="text-muted" style="margin-bottom: 1.5rem;">The hiring process consists of these sequential stages:</p>

        <div style="position: relative;">
            <!-- Stage 1: Post Vacancy -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; border-left: 4px solid #3b82f6;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; background: #3b82f6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    1
                </div>
                <div style="flex-grow: 1;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem;">Post Vacancy</h3>
                    <p class="text-muted" style="margin: 0 0 0.5rem 0; font-size: 0.875rem;">
                        HR posts a job vacancy for a specific position
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Positions:</strong> HR Recruiter, HR Manager
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Entity:</strong> OrganizationVacancy
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>SLA:</strong> 2 days
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stage 2: Review Applications -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; border-left: 4px solid #8b5cf6;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; background: #8b5cf6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    2
                </div>
                <div style="flex-grow: 1;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem;">Review Applications</h3>
                    <p class="text-muted" style="margin: 0 0 0.5rem 0; font-size: 0.875rem;">
                        Review submitted applications and shortlist candidates
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Positions:</strong> HR Recruiter, Hiring Manager
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Entity:</strong> Application
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>SLA:</strong> 7 days
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stage 3: Screen Candidates -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; border-left: 4px solid #10b981;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; background: #10b981; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    3
                </div>
                <div style="flex-grow: 1;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem;">Screen Candidates</h3>
                    <p class="text-muted" style="margin: 0 0 0.5rem 0; font-size: 0.875rem;">
                        Conduct initial phone/video screening
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Positions:</strong> HR Recruiter
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Entity:</strong> Screening
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>SLA:</strong> 7 days
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stage 4: Technical Interview -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; border-left: 4px solid #f59e0b;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; background: #f59e0b; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    4
                </div>
                <div style="flex-grow: 1;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem;">Technical Interview</h3>
                    <p class="text-muted" style="margin: 0 0 0.5rem 0; font-size: 0.875rem;">
                        Assess technical skills and competency
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Positions:</strong> Technical Lead, Senior Engineer
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Entity:</strong> Interview
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>SLA:</strong> 10 days
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stage 5: HR Interview -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; border-left: 4px solid #ef4444;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; background: #ef4444; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    5
                </div>
                <div style="flex-grow: 1;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem;">HR Interview</h3>
                    <p class="text-muted" style="margin: 0 0 0.5rem 0; font-size: 0.875rem;">
                        Evaluate cultural fit, soft skills, and expectations
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Positions:</strong> HR Manager, Department Head
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Entity:</strong> Interview
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>SLA:</strong> 7 days
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stage 6: Final Interview (Optional) -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; border-left: 4px solid #06b6d4; opacity: 0.7;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; background: #06b6d4; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    6
                </div>
                <div style="flex-grow: 1;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem;">Final Interview <span style="font-size: 0.75rem; color: var(--text-muted);">(Optional)</span></h3>
                    <p class="text-muted" style="margin: 0 0 0.5rem 0; font-size: 0.875rem;">
                        Final round with senior management or stakeholders
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Positions:</strong> Department Head, CEO, CTO
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Entity:</strong> Interview
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>SLA:</strong> 10 days
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stage 7: Make Offer -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; border-left: 4px solid #ec4899;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; background: #ec4899; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    7
                </div>
                <div style="flex-grow: 1;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem;">Make Offer</h3>
                    <p class="text-muted" style="margin: 0 0 0.5rem 0; font-size: 0.875rem;">
                        Prepare and send job offer to selected candidate
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Positions:</strong> HR Manager
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Entity:</strong> Offer
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>SLA:</strong> 5 days
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stage 8: Await Candidate Response -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; border-left: 4px solid #a855f7;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; background: #a855f7; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    8
                </div>
                <div style="flex-grow: 1;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem;">Await Candidate Response</h3>
                    <p class="text-muted" style="margin: 0 0 0.5rem 0; font-size: 0.875rem;">
                        Wait for candidate to accept or reject the offer
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Positions:</strong> HR Recruiter, HR Manager
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Entity:</strong> Offer
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>SLA:</strong> 14 days
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stage 9: Onboarding -->
            <div style="display: flex; gap: 1rem; margin-bottom: 0; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; border-left: 4px solid #14b8a6;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; background: #14b8a6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    9
                </div>
                <div style="flex-grow: 1;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem;">Onboarding</h3>
                    <p class="text-muted" style="margin: 0 0 0.5rem 0; font-size: 0.875rem;">
                        Complete pre-joining formalities and onboard employee
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Positions:</strong> HR Manager, IT Admin, Department Manager
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>Entity:</strong> User (Employee)
                        </span>
                        <span style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: var(--bg-light); border-radius: 4px;">
                            <strong>SLA:</strong> 15 days
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <h2 class="card-title">Quick Actions</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="/organizations/departments/human_resource/hiring/execution/" class="btn btn-primary" style="padding: 1rem; text-align: center;">
                View Workflow Diagram
            </a>
            <a href="/organizations/departments/human_resource/vacancies/" class="btn btn-secondary" style="padding: 1rem; text-align: center;">
                Manage Vacancies
            </a>
            <button class="btn btn-secondary" style="padding: 1rem; opacity: 0.6;" disabled title="Coming soon">
                View Active Hires
            </button>
            <button class="btn btn-secondary" style="padding: 1rem; opacity: 0.6;" disabled title="Coming soon">
                Hiring Analytics
            </button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../../../views/footer.php'; ?>
