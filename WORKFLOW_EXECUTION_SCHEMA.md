# Workflow Execution Database Schema

## Overview
This document defines the database schema for tracking workflow instances and their execution history.

---

## Tables

### 1. workflows
Stores workflow definitions (templates)

```sql
CREATE TABLE workflows (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    workflow_type TEXT NOT NULL, -- 'hiring', 'procurement', 'bug_fix', etc.
    version TEXT NOT NULL DEFAULT '1.0',
    config JSON NOT NULL, -- Full workflow configuration (nodes, edges)
    owner_position_id TEXT, -- OrganizationPosition that owns this workflow
    is_active INTEGER DEFAULT 1,
    created_by TEXT NOT NULL,
    created_at TEXT NOT NULL,
    updated_by TEXT,
    updated_at TEXT,
    deleted_by TEXT,
    deleted_at TEXT,
    FOREIGN KEY (owner_position_id) REFERENCES organization_positions(id)
);
```

### 2. workflow_instances
Tracks individual workflow executions

```sql
CREATE TABLE workflow_instances (
    id TEXT PRIMARY KEY,
    workflow_id TEXT NOT NULL,
    instance_name TEXT NOT NULL, -- e.g., "Hiring: Senior Developer - John Doe"
    entity_id TEXT, -- ID of the main entity (e.g., vacancy_id, project_id)
    entity_type TEXT, -- Entity class name (e.g., "OrganizationVacancy")
    current_node_id TEXT NOT NULL, -- Current active node
    status TEXT NOT NULL DEFAULT 'active', -- 'active', 'completed', 'cancelled', 'failed'
    initiated_by TEXT NOT NULL, -- User ID who started the workflow
    started_at TEXT NOT NULL,
    completed_at TEXT,
    cancelled_at TEXT,
    cancelled_by TEXT,
    cancellation_reason TEXT,
    metadata JSON, -- Additional instance-specific data
    created_by TEXT NOT NULL,
    created_at TEXT NOT NULL,
    updated_by TEXT,
    updated_at TEXT,
    FOREIGN KEY (workflow_id) REFERENCES workflows(id),
    FOREIGN KEY (initiated_by) REFERENCES users(id),
    FOREIGN KEY (cancelled_by) REFERENCES users(id)
);
```

### 3. workflow_nodes
Stores node definitions for workflows

```sql
CREATE TABLE workflow_nodes (
    id TEXT PRIMARY KEY,
    workflow_id TEXT NOT NULL,
    node_id TEXT NOT NULL, -- e.g., "post_vacancy", "review_applications"
    label TEXT NOT NULL,
    entity_type TEXT NOT NULL,
    required_positions JSON NOT NULL, -- Array of position names/IDs
    allowed_actions JSON NOT NULL, -- Array of actions (create, read, update, etc.)
    estimated_duration TEXT,
    sla TEXT,
    config JSON, -- Additional node configuration
    sort_order INTEGER DEFAULT 0,
    created_at TEXT NOT NULL,
    FOREIGN KEY (workflow_id) REFERENCES workflows(id),
    UNIQUE(workflow_id, node_id)
);
```

### 4. workflow_edges
Stores transition rules between nodes

```sql
CREATE TABLE workflow_edges (
    id TEXT PRIMARY KEY,
    workflow_id TEXT NOT NULL,
    source_node_id TEXT NOT NULL,
    target_node_id TEXT NOT NULL,
    condition TEXT NOT NULL, -- e.g., "approved", "rejected", "completed"
    label TEXT NOT NULL,
    priority INTEGER DEFAULT 1,
    style TEXT DEFAULT 'solid', -- 'solid', 'dashed', 'dotted'
    created_at TEXT NOT NULL,
    FOREIGN KEY (workflow_id) REFERENCES workflows(id),
    FOREIGN KEY (source_node_id) REFERENCES workflow_nodes(id),
    FOREIGN KEY (target_node_id) REFERENCES workflow_nodes(id)
);
```

### 5. workflow_execution_log
Tracks every action performed in a workflow instance

```sql
CREATE TABLE workflow_execution_log (
    id TEXT PRIMARY KEY,
    workflow_instance_id TEXT NOT NULL,
    node_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    action TEXT NOT NULL, -- 'create', 'read', 'update', 'approve', 'reject', etc.
    execution_result TEXT NOT NULL, -- Result that triggered transition
    comments TEXT,
    metadata JSON, -- Additional execution data
    executed_at TEXT NOT NULL,
    FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instances(id),
    FOREIGN KEY (node_id) REFERENCES workflow_nodes(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 6. workflow_tasks
Tasks assigned to users for workflow execution

```sql
CREATE TABLE workflow_tasks (
    id TEXT PRIMARY KEY,
    workflow_instance_id TEXT NOT NULL,
    node_id TEXT NOT NULL,
    assigned_to_user_id TEXT NOT NULL,
    task_name TEXT NOT NULL,
    task_description TEXT,
    status TEXT NOT NULL DEFAULT 'pending', -- 'pending', 'in_progress', 'completed', 'skipped'
    priority INTEGER DEFAULT 0,
    due_date TEXT,
    started_at TEXT,
    completed_at TEXT,
    completed_by TEXT,
    execution_result TEXT, -- Result when completed
    comments TEXT,
    created_by TEXT NOT NULL,
    created_at TEXT NOT NULL,
    updated_by TEXT,
    updated_at TEXT,
    FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instances(id),
    FOREIGN KEY (node_id) REFERENCES workflow_nodes(id),
    FOREIGN KEY (assigned_to_user_id) REFERENCES users(id),
    FOREIGN KEY (completed_by) REFERENCES users(id)
);
```

### 7. workflow_notifications
Tracks notifications sent for workflow events

```sql
CREATE TABLE workflow_notifications (
    id TEXT PRIMARY KEY,
    workflow_instance_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    notification_type TEXT NOT NULL, -- 'task_assigned', 'task_completed', 'workflow_completed', etc.
    message TEXT NOT NULL,
    is_read INTEGER DEFAULT 0,
    sent_at TEXT NOT NULL,
    read_at TEXT,
    FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instances(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## Indexes for Performance

```sql
-- workflow_instances indexes
CREATE INDEX idx_workflow_instances_workflow_id ON workflow_instances(workflow_id);
CREATE INDEX idx_workflow_instances_status ON workflow_instances(status);
CREATE INDEX idx_workflow_instances_initiated_by ON workflow_instances(initiated_by);
CREATE INDEX idx_workflow_instances_entity ON workflow_instances(entity_type, entity_id);

-- workflow_execution_log indexes
CREATE INDEX idx_workflow_execution_log_instance ON workflow_execution_log(workflow_instance_id);
CREATE INDEX idx_workflow_execution_log_user ON workflow_execution_log(user_id);
CREATE INDEX idx_workflow_execution_log_node ON workflow_execution_log(node_id);

-- workflow_tasks indexes
CREATE INDEX idx_workflow_tasks_instance ON workflow_tasks(workflow_instance_id);
CREATE INDEX idx_workflow_tasks_assigned_to ON workflow_tasks(assigned_to_user_id);
CREATE INDEX idx_workflow_tasks_status ON workflow_tasks(status);
CREATE INDEX idx_workflow_tasks_due_date ON workflow_tasks(due_date);

-- workflow_notifications indexes
CREATE INDEX idx_workflow_notifications_user ON workflow_notifications(user_id);
CREATE INDEX idx_workflow_notifications_unread ON workflow_notifications(user_id, is_read);
```

---

## Workflow Execution Flow

### 1. Starting a Workflow Instance

```php
// User creates a vacancy (triggers hiring workflow)
$vacancy = new OrganizationVacancy(...);
$vacancyRepo->create($vacancy, $userId);

// Start workflow instance
$workflowInstance = new WorkflowInstance();
$workflowInstance->setWorkflowId('hiring_workflow_v1');
$workflowInstance->setInstanceName("Hiring: {$vacancy->getPositionName()} - {$vacancyId}");
$workflowInstance->setEntityId($vacancy->getId());
$workflowInstance->setEntityType('OrganizationVacancy');
$workflowInstance->setCurrentNodeId('post_vacancy');
$workflowInstance->setInitiatedBy($userId);

$instanceId = $workflowEngine->startInstance($workflowInstance);

// This automatically:
// 1. Creates workflow_instance record
// 2. Identifies users with required positions for first node
// 3. Creates workflow_tasks for those users
// 4. Sends notifications
```

### 2. Executing a Node (User completes task)

```php
// User completes the "post_vacancy" task
$task = $taskRepo->findById($taskId);
$task->setStatus('completed');
$task->setExecutionResult('vacancy_posted'); // This triggers the transition
$task->setCompletedBy($userId);

$workflowEngine->executeTask($task);

// This automatically:
// 1. Marks task as completed
// 2. Logs execution in workflow_execution_log
// 3. Evaluates edges from current node
// 4. Finds matching edge based on execution_result
// 5. Moves workflow to next node
// 6. Creates tasks for next node
// 7. Sends notifications
```

### 3. Tracking Workflow Progress

```php
// Get workflow instance details
$instance = $workflowRepo->findById($instanceId);

// Get execution history
$history = $workflowRepo->getExecutionHistory($instanceId);

// Get active tasks
$activeTasks = $taskRepo->findByInstance($instanceId, ['status' => 'pending']);

// Get workflow metrics
$metrics = [
    'elapsed_time' => $workflowRepo->getElapsedTime($instanceId),
    'completed_nodes' => $workflowRepo->getCompletedNodesCount($instanceId),
    'total_nodes' => $workflowRepo->getTotalNodesCount($instanceId),
    'current_node' => $instance->getCurrentNodeId()
];
```

---

## Example: Hiring Workflow Instance

### Instance Creation
```json
{
  "id": "abc123-instance",
  "workflow_id": "hiring_workflow_v1",
  "instance_name": "Hiring: Senior Software Engineer - Vacancy #456",
  "entity_id": "vacancy-456",
  "entity_type": "OrganizationVacancy",
  "current_node_id": "post_vacancy",
  "status": "active",
  "initiated_by": "user-123",
  "started_at": "2025-11-25 10:00:00"
}
```

### Task Assignment
```json
{
  "id": "task-001",
  "workflow_instance_id": "abc123-instance",
  "node_id": "post_vacancy",
  "assigned_to_user_id": "hr-recruiter-1",
  "task_name": "Post Vacancy for Senior Software Engineer",
  "task_description": "Create and publish job posting",
  "status": "pending",
  "due_date": "2025-11-27 17:00:00"
}
```

### Execution Log Entry
```json
{
  "id": "log-001",
  "workflow_instance_id": "abc123-instance",
  "node_id": "post_vacancy",
  "user_id": "hr-recruiter-1",
  "action": "create",
  "execution_result": "vacancy_posted",
  "comments": "Vacancy posted on all job boards",
  "executed_at": "2025-11-25 14:30:00"
}
```

---

## Workflow States

### Instance Status
- **active**: Workflow is in progress
- **completed**: Workflow successfully completed
- **cancelled**: Workflow was cancelled by user
- **failed**: Workflow failed due to error

### Task Status
- **pending**: Task assigned but not started
- **in_progress**: User is working on the task
- **completed**: Task successfully completed
- **skipped**: Task was skipped (optional nodes)

### Node Execution Results
- **vacancy_posted**: Vacancy was posted
- **candidates_shortlisted**: Candidates selected for next round
- **screening_passed**: Candidate passed screening
- **technical_approved**: Technical interview approved
- **hr_approved**: HR interview approved
- **offer_sent**: Offer letter sent
- **offer_accepted**: Candidate accepted offer
- **onboarding_complete**: Employee successfully onboarded

---

## Key Features

1. **Automatic Task Assignment**: Based on OrganizationEntityPermission
2. **Transition Logic**: Edges evaluated based on execution results
3. **Complete Audit Trail**: Every action logged
4. **SLA Tracking**: Monitor if nodes are completed within SLA
5. **Multi-Instance**: Multiple workflow instances can run simultaneously
6. **Flexible**: Same workflow template can be reused for different entities
