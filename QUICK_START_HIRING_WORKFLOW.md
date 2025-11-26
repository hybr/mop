# Quick Start: Hiring Workflow

## How to Start the Hiring Process

The hiring workflow is now fully integrated with the vacancy system. Here's how to use it:

---

## Step-by-Step Guide

### 1. Create or Open a Vacancy

**Navigate to**: `/organizations/departments/human_resource/vacancies/`

- Click "**+ New Vacancy**" to create a new job opening
- OR click "**View**" on an existing vacancy

### 2. Publish the Vacancy (If Not Already)

The vacancy **must be published** before starting the workflow.

- If the vacancy shows "**DRAFT**" status, click "**Edit Vacancy**"
- Check the "**Published**" checkbox
- Save the vacancy

### 3. Start the Hiring Workflow

Once the vacancy is published, you'll see a **purple gradient card** on the vacancy view page:

```
┌──────────────────────────────────────────────────┐
│ Hiring Workflow                                   │
│                                                   │
│ Start the automated hiring process to manage     │
│ candidates from application to onboarding.       │
│                                                   │
│ [🚀 Start Hiring Workflow]  [Learn More]        │
│                                                   │
│ 💡 The workflow includes: Post Vacancy → Review  │
│    Applications → Screen → Interview → Offer →   │
│    Onboard                                        │
└──────────────────────────────────────────────────┘
```

Click the **"🚀 Start Hiring Workflow"** button.

### 4. Confirm Workflow Start

A confirmation dialog will appear:

```
Start hiring workflow for this vacancy?

This will:
- Create workflow instance
- Assign tasks to HR team
- Begin automated hiring process

[OK] [Cancel]
```

Click **OK** to start.

### 5. Workflow Started!

You'll be redirected to the **Workflow Instances** page with a success message:

```
✓ Hiring workflow started successfully! Tasks have been assigned.
```

---

## What Happens When You Start the Workflow?

### Automatic Actions:

1. **Workflow Instance Created**
   - Instance name: "Hiring: [Position Name] - Vacancy #[ID]"
   - Status: Active
   - Current Node: "Post Vacancy"

2. **First Tasks Assigned**
   - System finds all users with required positions:
     - HR Recruiter
     - HR Manager
   - Creates a task for each user:
     - Task: "Post Vacancy"
     - Due Date: 2 days from now (based on SLA)
     - Status: Pending

3. **Execution Log Created**
   - Records workflow start
   - Logs who initiated it
   - Timestamps all actions

---

## Completing Tasks & Progressing the Workflow

### Step 1: View Your Tasks

Navigate to: **`/tasks/`** (My Tasks)

You'll see:
```
Your Workflow Tasks

□ Post Vacancy
  Hiring: Senior Software Engineer - Vacancy #456
  Due: Nov 27, 2025 (2 days)
  [View Details] [Complete Task]
```

### Step 2: Complete the Task

Click **"Complete Task"**

You'll see a form:
```
Task Details
Task Name: Post Vacancy
Workflow: Hiring: Senior Software Engineer - Vacancy #456
Current Node: post_vacancy
Due Date: November 27, 2025

Complete Task
┌─────────────────────────────────────────┐
│ Execution Result *                       │
│ [Select outcome...             ▼]       │
│                                          │
│ Options:                                 │
│ - Posted (vacancy_posted)               │
│ - Cancelled (cancelled)                  │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ Comments (Optional)                      │
│ ┌─────────────────────────────────────┐ │
│ │ Add any notes...                    │ │
│ └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘

[Complete Task] [Cancel]
```

### Step 3: Select Outcome

Choose the execution result:
- **"Posted (vacancy_posted)"** → Moves workflow to "Review Applications"
- **"Cancelled (cancelled)"** → Ends workflow

Add optional comments, then click **"Complete Task"**

### Step 4: Workflow Automatically Transitions

**What happens automatically:**

1. Task marked as completed
2. Execution logged
3. Workflow evaluates edges:
   - Finds edge: `post_vacancy → review_applications` (condition: `vacancy_posted`)
4. Workflow moves to next node: "Review Applications"
5. System finds users with required positions:
   - HR Recruiter
   - Hiring Manager
6. New tasks created for those users
7. Success message displayed

You'll see:
```
✓ Workflow moved to: Review Applications
```

---

## Tracking Workflow Progress

### View All Active Workflows

Navigate to: **`/organizations/departments/human_resource/hiring/instances/`**

You'll see a list of all hiring processes:

```
Active Hiring Processes (2)

| Instance                          | Current Stage         | Progress  | Started | Elapsed | Actions      |
|-----------------------------------|-----------------------|-----------|---------|---------|--------------|
| Hiring: Senior Developer - #456  | review_applications   | 22% (2/9) | Nov 25  | 2 days  | View Details |
| Hiring: Product Manager - #789   | post_vacancy          | 11% (1/9) | Nov 26  | 1 day   | View Details |
```

### View Specific Workflow Instance

Click **"View Details"** to see:
- Current node highlighted
- Progress percentage
- Execution timeline
- Active tasks
- Complete audit log

---

## Complete Hiring Process Flow

Here's how the full workflow progresses:

### Node 1: Post Vacancy ✅
**Assigned to**: HR Recruiter, HR Manager
**Task**: Create and publish vacancy
**Result**: `vacancy_posted`
**Next**: Review Applications

### Node 2: Review Applications
**Assigned to**: HR Recruiter, Hiring Manager
**Task**: Review and shortlist candidates
**Result**: `candidates_shortlisted`
**Next**: Screen Candidates

### Node 3: Screen Candidates
**Assigned to**: HR Recruiter
**Task**: Conduct phone/video screening
**Result**: `screening_passed`
**Next**: Technical Interview

### Node 4: Technical Interview
**Assigned to**: Technical Lead, Senior Engineer
**Task**: Assess technical skills
**Result**: `technical_approved`
**Next**: HR Interview

### Node 5: HR Interview
**Assigned to**: HR Manager, Department Head
**Task**: Evaluate cultural fit
**Result**: `hr_approved`
**Next**: Make Offer

### Node 6: Make Offer
**Assigned to**: HR Manager
**Task**: Prepare and send offer letter
**Result**: `offer_sent`
**Next**: Await Response

### Node 7: Await Candidate Response
**Assigned to**: HR Recruiter, HR Manager
**Task**: Follow up on offer
**Result**: `offer_accepted`
**Next**: Onboarding

### Node 8: Onboarding
**Assigned to**: HR Manager, IT Admin, Dept Manager
**Task**: Complete pre-joining formalities
**Result**: `onboarding_complete`
**Next**: **Workflow Complete!** 🎉

---

## Viewing Workflow from Vacancy Page

### When Workflow is Active:

The vacancy view page shows:
```
┌──────────────────────────────────────────────────┐
│ Hiring Workflow                                   │
│                                                   │
│ A hiring workflow is currently active for this   │
│ vacancy.                                          │
│                                                   │
│ [View Workflow Progress]  [View My Tasks]       │
└──────────────────────────────────────────────────┘
```

Click:
- **"View Workflow Progress"** → See instance details
- **"View My Tasks"** → See your assigned tasks

---

## Key URLs

- **My Tasks**: `/tasks/`
- **Workflow Instances**: `/organizations/departments/human_resource/hiring/instances/`
- **Hiring Overview**: `/organizations/departments/human_resource/hiring/`
- **Vacancies**: `/organizations/departments/human_resource/vacancies/`
- **Start Workflow**: `/workflows/start/?entity_type=OrganizationVacancy&entity_id=[ID]`

---

## Important Notes

### ✅ Workflow Can Only Be Started:
- Once per vacancy
- When vacancy is published
- By authenticated users

### ✅ Tasks Are Automatically:
- Created for users with required positions
- Assigned due dates based on SLA
- Tracked with full audit trail

### ✅ Workflow Automatically:
- Transitions between nodes
- Assigns new tasks
- Logs all actions
- Calculates progress

### ✅ Users See:
- Only their assigned tasks
- Task due dates and priorities
- Overdue warnings
- Workflow context

---

## Troubleshooting

### "Workflow already active for this vacancy"
- Only one active workflow allowed per vacancy
- View the existing workflow instead
- Complete or cancel existing workflow first

### "No users found with required positions"
- System couldn't find users with required OrganizationPositions
- Currently returns all active users as fallback
- Will need position-based user lookup implementation

### "Permission denied"
- User must be authenticated
- User must have access to the vacancy

---

## Summary

Starting a hiring workflow is simple:

1. **Create/Open** a published vacancy
2. **Click** "🚀 Start Hiring Workflow"
3. **Tasks** automatically assigned
4. **Users** complete tasks
5. **Workflow** automatically progresses
6. **Track** progress in real-time

The system handles all the complexity of task assignment, transitions, and tracking automatically!
