# How to Start and Track Workflow Instances

## Overview
This guide explains how to initiate workflow instances and track their execution through the hiring process.

---

## Database Setup ✅

The workflow infrastructure is now ready:

### Tables Created:
1. **workflows** - Workflow definitions/templates
2. **workflow_instances** - Individual workflow executions
3. **workflow_nodes** - Node definitions for each workflow
4. **workflow_edges** - Transition rules between nodes
5. **workflow_execution_log** - Complete audit trail
6. **workflow_tasks** - Tasks assigned to users
7. **workflow_notifications** - Notification tracking

### Hiring Workflow Seeded:
- **Workflow ID**: `hiring_workflow_v1`
- **9 Nodes**: Post Vacancy → Review → Screen → Technical → HR → Final → Offer → Response → Onboarding
- **16 Edges**: All transitions configured with conditions

---

## Starting a Workflow Instance

### Method 1: Manual Start from Vacancy

When you visit the **Vacancies** page and create/publish a vacancy:

1. Go to `/organizations/departments/human_resource/vacancies/`
2. Create a new vacancy or view an existing one
3. Click **"Start Hiring Workflow"** button
4. This creates:
   - A `workflow_instance` record
   - Initial tasks for HR Recruiter/HR Manager
   - Notifications to assigned users

### Method 2: Automatic Trigger (Recommended for Production)

Modify `OrganizationVacancyRepository::create()` to auto-start workflow:

```php
// After creating vacancy
$vacancy = $vacancyRepo->create($vacancy, $userId);

// Auto-start hiring workflow
if ($vacancy->getStatus() === 'published') {
    $workflowEngine = new WorkflowEngine();
    $workflowEngine->startHiringWorkflow($vacancy->getId(), $userId);
}
```

---

## Workflow Execution Flow

### Step 1: Post Vacancy (Node 1)
**Who**: HR Recruiter or HR Manager
**Action**: Create and publish vacancy
**Task**: "Post Vacancy for [Position Name]"

**What happens**:
- User receives task notification
- User creates vacancy via UI
- When complete, marks task with result: `vacancy_posted`
- Workflow engine evaluates edges
- Finds matching edge: `post_vacancy` → `review_applications`
- Moves workflow to next node

### Step 2: Review Applications (Node 2)
**Who**: HR Recruiter, Hiring Manager
**Action**: Review submitted applications, shortlist candidates
**Task**: "Review Applications for [Position Name]"

**What happens**:
- Tasks auto-created for all HR Recruiters and Hiring Managers
- Users review applications (external or integrated system)
- Mark task complete with result:
  - `candidates_shortlisted` → Move to Screen Candidates
  - `no_suitable_candidates` → Loop back to Post Vacancy
  - `extend_deadline` → Stay in Review Applications

### Step 3: Screen Candidates (Node 3)
**Who**: HR Recruiter
**Action**: Conduct phone/video screening
**Task**: "Screen Candidate: [Candidate Name]"

**Execution results**:
- `screening_passed` → Technical Interview
- `screening_failed` → Back to Review Applications
- `reschedule` → Stay in Screening

### Step 4: Technical Interview (Node 4)
**Who**: Technical Lead, Senior Engineer, Department Manager
**Action**: Assess technical skills
**Task**: "Conduct Technical Interview: [Candidate Name]"

**Execution results**:
- `technical_approved` → HR Interview
- `technical_rejected` → Back to Review Applications
- `needs_second_round` → Loop back to Technical Interview

### Step 5: HR Interview (Node 5)
**Who**: HR Manager, Department Head
**Action**: Evaluate cultural fit
**Task**: "Conduct HR Interview: [Candidate Name]"

**Execution results**:
- `hr_approved` → Make Offer
- `final_round_needed` → Final Interview (Optional)
- `hr_rejected` → Back to Review Applications

### Step 6: Final Interview (Node 6) - Optional
**Who**: Department Head, CEO, CTO
**Action**: Final round with senior management
**Task**: "Conduct Final Interview: [Candidate Name]"

**Execution results**:
- `final_approved` → Make Offer
- `final_rejected` → Back to Review Applications

### Step 7: Make Offer (Node 7)
**Who**: HR Manager
**Action**: Prepare and send job offer
**Task**: "Prepare Offer for [Candidate Name]"

**Execution results**:
- `offer_sent` → Await Candidate Response

### Step 8: Await Response (Node 8)
**Who**: HR Recruiter, HR Manager
**Action**: Wait for candidate decision
**Task**: "Follow up on Offer: [Candidate Name]"

**Execution results**:
- `offer_accepted` → Onboarding
- `offer_rejected` → Back to Review Applications
- `candidate_negotiating` → Back to Make Offer

### Step 9: Onboarding (Node 9)
**Who**: HR Manager, IT Admin, Department Manager
**Action**: Complete pre-joining formalities
**Task**: "Onboard New Employee: [Candidate Name]"

**Execution results**:
- `onboarding_complete` → Workflow Complete ✅
- `candidate_withdrew` → Back to Review Applications
- `documentation_pending` → Stay in Onboarding

---

## Tracking Workflow Instances

### View All Active Workflows

Visit: `/organizations/departments/human_resource/hiring/instances/`

This page shows:
- All active hiring workflows
- Current node for each instance
- Time elapsed
- Assigned tasks
- Progress percentage

### View Specific Workflow Instance

Visit: `/organizations/departments/human_resource/hiring/instances/view/?id=[INSTANCE_ID]`

This page displays:
- **Workflow Diagram** with current node highlighted
- **Execution Timeline** showing completed nodes
- **Active Tasks** assigned to users
- **Execution Log** (audit trail)
- **Metrics**:
  - Time elapsed
  - Nodes completed
  - Current SLA status
  - Next steps

### Example Instance View:

```
Hiring: Senior Software Engineer - Vacancy #456

Status: Active
Current Stage: Technical Interview (Node 4/9)
Started: Nov 25, 2025, 10:00 AM
Elapsed Time: 5 days

Progress: ████████░░░░░░░░░░ 44% (4/9 nodes)

Timeline:
✓ Post Vacancy (Completed - 1 day)
✓ Review Applications (Completed - 3 days)
✓ Screen Candidates (Completed - 2 days)
→ Technical Interview (In Progress - 1 day)
  Await HR Interview
  Await Final Interview (Optional)
  Await Make Offer
  Await Candidate Response
  Await Onboarding

Active Tasks:
- Technical Interview for John Doe
  Assigned to: Tech Lead A, Senior Engineer B
  Due: Nov 30, 2025
  SLA: 10 days (5 days remaining)

Recent Activity:
- Nov 27, 14:30: HR Recruiter completed screening
- Nov 25, 16:00: 3 candidates shortlisted
- Nov 25, 10:00: Vacancy posted
```

---

## My Tasks View

Users can see their assigned workflow tasks:

Visit: `/tasks/` or `/my-tasks/`

Shows:
- **Pending Tasks** from all workflows
- Organized by workflow instance
- Due dates and SLA warnings
- One-click to complete task

Example:
```
Your Workflow Tasks

□ Technical Interview for John Doe
  Hiring: Senior Software Engineer
  Due: Nov 30, 2025 (5 days)
  [View Details] [Complete Task]

□ Screen Candidate: Jane Smith
  Hiring: Product Manager
  Due: Nov 28, 2025 (3 days - SLA Warning!)
  [View Details] [Complete Task]
```

---

## Completing Tasks

### Option 1: Quick Complete
1. Go to "My Tasks"
2. Click "Complete Task"
3. Select execution result from dropdown
4. Add optional comments
5. Submit

### Option 2: Detailed View
1. Click task to view details
2. See full context (vacancy, candidate info, previous steps)
3. Perform necessary action (interview, review, etc.)
4. Mark task complete with result
5. System automatically transitions workflow

---

## Workflow Analytics

### Dashboard Metrics

Visit: `/organizations/departments/human_resource/hiring/analytics/`

Displays:
- **Active Workflows**: Current hiring processes
- **Completed Hires**: Successfully onboarded employees
- **Average Time-to-Hire**: Across all workflows
- **Bottleneck Analysis**: Which nodes take longest
- **Conversion Rates**: % moving from each node to next
- **SLA Compliance**: % of tasks completed within SLA

### Example Metrics:
```
Hiring Analytics - Last 30 Days

Active Workflows: 5
Completed Hires: 2
Average Time-to-Hire: 38 days

Stage Performance:
Post Vacancy:        Avg 1.2 days  ✓ Within SLA
Review Applications: Avg 4.5 days  ✓ Within SLA
Screen Candidates:   Avg 3.8 days  ✓ Within SLA
Technical Interview: Avg 8.2 days  ✓ Within SLA
HR Interview:        Avg 5.1 days  ✓ Within SLA
Make Offer:          Avg 3.5 days  ✓ Within SLA
Await Response:      Avg 9.5 days  ✓ Within SLA
Onboarding:          Avg 12.1 days ⚠ Approaching SLA

Conversion Rates:
Review → Screen:     80%
Screen → Technical:  65%
Technical → HR:      75%
HR → Offer:          90%
Offer → Accept:      85%
```

---

## Database Queries for Tracking

### Get Active Workflow Instances
```sql
SELECT
    wi.id,
    wi.instance_name,
    wi.current_node_id,
    wi.status,
    wi.started_at,
    julianday('now') - julianday(wi.started_at) as days_elapsed
FROM workflow_instances wi
WHERE wi.status = 'active'
  AND wi.workflow_id = 'hiring_workflow_v1'
ORDER BY wi.started_at DESC;
```

### Get Tasks for User
```sql
SELECT
    wt.id,
    wt.task_name,
    wt.due_date,
    wi.instance_name,
    wt.status
FROM workflow_tasks wt
JOIN workflow_instances wi ON wt.workflow_instance_id = wi.id
WHERE wt.assigned_to_user_id = '[USER_ID]'
  AND wt.status = 'pending'
ORDER BY wt.due_date ASC;
```

### Get Execution History for Instance
```sql
SELECT
    wel.executed_at,
    wel.node_id,
    wel.action,
    wel.execution_result,
    wel.comments,
    u.name as user_name
FROM workflow_execution_log wel
JOIN users u ON wel.user_id = u.id
WHERE wel.workflow_instance_id = '[INSTANCE_ID]'
ORDER BY wel.executed_at ASC;
```

---

## Next Steps for Full Implementation

### 1. Create WorkflowInstance Entity Class
```php
class WorkflowInstance {
    private $id;
    private $workflowId;
    private $instanceName;
    private $currentNodeId;
    private $status;
    // ... getters/setters
}
```

### 2. Create WorkflowEngine Class
```php
class WorkflowEngine {
    public function startInstance($workflowId, $entityId, $userId) {
        // Create instance
        // Assign initial tasks
        // Send notifications
    }

    public function executeTask($taskId, $result, $userId) {
        // Mark task complete
        // Log execution
        // Evaluate edges
        // Transition to next node
        // Create new tasks
    }
}
```

### 3. Add UI Components
- **Start Workflow Button** on vacancy page
- **My Tasks** page for users
- **Workflow Instance Tracker** page
- **Complete Task** modal/form
- **Analytics Dashboard**

### 4. Add Notifications
- Email notifications for task assignments
- In-app notifications for workflow updates
- SLA warning notifications
- Workflow completion notifications

---

## Quick Start Checklist

- [x] Database tables created
- [x] Hiring workflow seeded
- [x] Workflow documentation complete
- [ ] Create WorkflowInstance class
- [ ] Create WorkflowEngine class
- [ ] Add "Start Workflow" button to vacancy page
- [ ] Create My Tasks page
- [ ] Create Instance Tracker page
- [ ] Add task completion UI
- [ ] Implement notification system
- [ ] Add analytics dashboard

---

## Summary

**Starting a workflow**:
1. Post a vacancy
2. Click "Start Hiring Workflow"
3. System creates instance and assigns first tasks

**Tracking progress**:
1. Visit `/hiring/instances/` to see all active workflows
2. Click instance to see detailed progress
3. View Cytoscape diagram with current node highlighted
4. Check execution log for audit trail

**User experience**:
1. Users receive notifications when tasks assigned
2. View tasks in "My Tasks"
3. Complete tasks with execution results
4. System automatically moves workflow forward
5. Next users receive their tasks automatically

The workflow engine handles all the complexity - users just complete their assigned tasks!
