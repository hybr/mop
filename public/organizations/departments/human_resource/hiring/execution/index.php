<?php
require_once __DIR__ . '/../../../../../../src/includes/autoload.php';

use App\Classes\Auth;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();

$pageTitle = 'Hiring Workflow - Execution Diagram';
include __DIR__ . '/../../../../../../views/header.php';
?>

<style>
#cy {
    width: 100%;
    height: 700px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: #fafafa;
}

.legend-item {
    display: inline-flex;
    align-items: center;
    margin-right: 1.5rem;
    margin-bottom: 0.5rem;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    margin-right: 0.5rem;
}

.node-info-panel {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.5rem;
    margin-top: 1rem;
    display: none;
}

.node-info-panel.active {
    display: block;
}
</style>

<div class="py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <a href="/organizations/departments/human_resource/hiring/" class="text-muted" style="text-decoration: none;">&larr; Back to Hiring</a>
            <h1 style="margin-top: 0.5rem;">Hiring Workflow Diagram</h1>
            <p class="text-muted">Interactive visualization of the hiring process flow</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button id="fit-btn" class="btn btn-secondary">Fit to Screen</button>
            <button id="reset-btn" class="btn btn-secondary">Reset View</button>
        </div>
    </div>

    <!-- Legend -->
    <div class="card" style="margin-bottom: 1rem;">
        <h3 style="margin: 0 0 1rem 0; font-size: 1rem;">Legend</h3>
        <div style="display: flex; flex-wrap: wrap;">
            <div class="legend-item">
                <div class="legend-color" style="background: #3b82f6;"></div>
                <span>Start</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #8b5cf6;"></div>
                <span>Review</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #10b981;"></div>
                <span>Screening</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #f59e0b;"></div>
                <span>Technical</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #ef4444;"></div>
                <span>HR Round</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #ec4899;"></div>
                <span>Offer</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #14b8a6;"></div>
                <span>Onboarding</span>
            </div>
        </div>
    </div>

    <!-- Cytoscape Container -->
    <div class="card" style="padding: 0;">
        <div id="cy"></div>
    </div>

    <!-- Node Information Panel -->
    <div id="node-info" class="node-info-panel">
        <h3 id="node-title" style="margin: 0 0 1rem 0;">Select a node</h3>
        <div id="node-details">
            <p class="text-muted">Click on any workflow node to see details</p>
        </div>
    </div>
</div>

<!-- Cytoscape.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.26.0/cytoscape.min.js"></script>

<script>
// Workflow data
const workflowNodes = [
    { id: 'post_vacancy', label: 'Post Vacancy', color: '#3b82f6', positions: 'HR Recruiter, HR Manager', entity: 'OrganizationVacancy', sla: '2 days' },
    { id: 'review_applications', label: 'Review Applications', color: '#8b5cf6', positions: 'HR Recruiter, Hiring Manager', entity: 'Application', sla: '7 days' },
    { id: 'screen_candidates', label: 'Screen Candidates', color: '#10b981', positions: 'HR Recruiter', entity: 'Screening', sla: '7 days' },
    { id: 'technical_interview', label: 'Technical Interview', color: '#f59e0b', positions: 'Technical Lead, Senior Engineer', entity: 'Interview', sla: '10 days' },
    { id: 'hr_interview', label: 'HR Interview', color: '#ef4444', positions: 'HR Manager, Department Head', entity: 'Interview', sla: '7 days' },
    { id: 'final_interview', label: 'Final Interview\n(Optional)', color: '#06b6d4', positions: 'Department Head, CEO, CTO', entity: 'Interview', sla: '10 days' },
    { id: 'make_offer', label: 'Make Offer', color: '#ec4899', positions: 'HR Manager', entity: 'Offer', sla: '5 days' },
    { id: 'await_response', label: 'Await Response', color: '#a855f7', positions: 'HR Recruiter, HR Manager', entity: 'Offer', sla: '14 days' },
    { id: 'onboarding', label: 'Onboarding', color: '#14b8a6', positions: 'HR Manager, IT Admin, Dept Manager', entity: 'User', sla: '15 days' }
];

const workflowEdges = [
    { source: 'post_vacancy', target: 'review_applications', label: 'Posted' },
    { source: 'review_applications', target: 'screen_candidates', label: 'Shortlisted' },
    { source: 'review_applications', target: 'post_vacancy', label: 'No Candidates', style: 'dashed' },
    { source: 'screen_candidates', target: 'technical_interview', label: 'Passed' },
    { source: 'screen_candidates', target: 'review_applications', label: 'Failed', style: 'dashed' },
    { source: 'technical_interview', target: 'hr_interview', label: 'Approved' },
    { source: 'technical_interview', target: 'review_applications', label: 'Rejected', style: 'dashed' },
    { source: 'hr_interview', target: 'make_offer', label: 'Approved' },
    { source: 'hr_interview', target: 'final_interview', label: 'Final Round', style: 'dotted' },
    { source: 'hr_interview', target: 'review_applications', label: 'Rejected', style: 'dashed' },
    { source: 'final_interview', target: 'make_offer', label: 'Approved' },
    { source: 'final_interview', target: 'review_applications', label: 'Rejected', style: 'dashed' },
    { source: 'make_offer', target: 'await_response', label: 'Sent' },
    { source: 'await_response', target: 'onboarding', label: 'Accepted' },
    { source: 'await_response', target: 'review_applications', label: 'Rejected', style: 'dashed' },
    { source: 'await_response', target: 'make_offer', label: 'Negotiate', style: 'dotted' }
];

// Build Cytoscape elements
const cytoscapeElements = [];

// Add nodes
workflowNodes.forEach(node => {
    cytoscapeElements.push({
        data: {
            id: node.id,
            label: node.label,
            color: node.color,
            positions: node.positions,
            entity: node.entity,
            sla: node.sla
        }
    });
});

// Add edges
workflowEdges.forEach(edge => {
    cytoscapeElements.push({
        data: {
            id: `${edge.source}_${edge.target}`,
            source: edge.source,
            target: edge.target,
            label: edge.label,
            edgeStyle: edge.style || 'solid'
        }
    });
});

// Initialize Cytoscape
const cy = cytoscape({
    container: document.getElementById('cy'),
    elements: cytoscapeElements,
    style: [
        {
            selector: 'node',
            style: {
                'background-color': 'data(color)',
                'label': 'data(label)',
                'color': '#ffffff',
                'text-valign': 'center',
                'text-halign': 'center',
                'font-size': '12px',
                'font-weight': 'bold',
                'text-wrap': 'wrap',
                'text-max-width': '80px',
                'width': 100,
                'height': 100,
                'border-width': 3,
                'border-color': '#ffffff',
                'text-outline-color': 'data(color)',
                'text-outline-width': 2
            }
        },
        {
            selector: 'edge',
            style: {
                'width': 3,
                'line-color': '#94a3b8',
                'target-arrow-color': '#94a3b8',
                'target-arrow-shape': 'triangle',
                'curve-style': 'bezier',
                'label': 'data(label)',
                'font-size': '10px',
                'text-background-color': '#ffffff',
                'text-background-opacity': 1,
                'text-background-padding': '3px',
                'color': '#475569',
                'text-rotation': 'autorotate'
            }
        },
        {
            selector: 'edge[edgeStyle="dashed"]',
            style: {
                'line-style': 'dashed',
                'line-color': '#ef4444',
                'target-arrow-color': '#ef4444'
            }
        },
        {
            selector: 'edge[edgeStyle="dotted"]',
            style: {
                'line-style': 'dotted',
                'line-color': '#f59e0b',
                'target-arrow-color': '#f59e0b'
            }
        },
        {
            selector: 'node:selected',
            style: {
                'border-width': 5,
                'border-color': '#1e40af'
            }
        }
    ],
    layout: {
        name: 'breadthfirst',
        directed: true,
        spacingFactor: 1.5,
        padding: 30,
        avoidOverlap: true
    }
});

// Node click handler
cy.on('tap', 'node', function(evt) {
    const node = evt.target;
    const data = node.data();

    document.getElementById('node-title').textContent = data.label.replace('\n', ' ');
    document.getElementById('node-details').innerHTML = `
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 0.75rem 0; color: var(--text-light); width: 40%;">Required Positions</td>
                <td style="padding: 0.75rem 0;"><strong>${data.positions}</strong></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 0.75rem 0; color: var(--text-light);">Entity Type</td>
                <td style="padding: 0.75rem 0;"><strong>${data.entity}</strong></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 0.75rem 0; color: var(--text-light);">SLA</td>
                <td style="padding: 0.75rem 0;"><strong>${data.sla}</strong></td>
            </tr>
            <tr>
                <td style="padding: 0.75rem 0; color: var(--text-light);">Status</td>
                <td style="padding: 0.75rem 0;"><span style="color: var(--text-muted);">Not Active</span></td>
            </tr>
        </table>
    `;

    document.getElementById('node-info').classList.add('active');
});

// Background click handler
cy.on('tap', function(evt) {
    if (evt.target === cy) {
        document.getElementById('node-info').classList.remove('active');
    }
});

// Button handlers
document.getElementById('fit-btn').addEventListener('click', function() {
    cy.fit(null, 50);
});

document.getElementById('reset-btn').addEventListener('click', function() {
    cy.zoom(1);
    cy.center();
});

// Initial fit
cy.fit(null, 50);
</script>

<?php include __DIR__ . '/../../../../../../views/footer.php'; ?>
