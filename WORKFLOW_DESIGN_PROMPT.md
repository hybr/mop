# Business Workflow Design Prompt

## Overview
This document serves as a comprehensive guide for designing business workflows within the application. Workflows are visual, executable processes that automate business logic by connecting nodes (entities) with edges (transition conditions).

---

## Workflow Architecture

### Core Components

#### 1. **Nodes (Entities)**
Nodes represent application entities that require action or processing at each stage of the workflow.

**Node Properties:**
- **ID**: Unique identifier for the node
- **Label**: Human-readable name (e.g., "Request Submitted", "Design Review")
- **Entity Type**: The application entity this node operates on (e.g., `OrganizationVacancy`, `Project`, `Task`)
- **Required Position(s)**: OrganizationPosition(s) authorized to execute this node
- **Allowed Actions**: CRUD operations and special actions (Create, Read, Update, Delete, Approve, Reject, Publish, Archive)
- **Status**: Current state of the node (Pending, In Progress, Completed, Rejected)

#### 2. **Edges (Transition Conditions)**
Edges define the flow logic between nodes, determining which node executes next based on the outcome of the current node.

**Edge Properties:**
- **Source Node**: The node where this edge originates
- **Target Node**: The node where this edge leads
- **Condition**: The execution result that triggers this transition
  - Examples: `approved`, `rejected`, `completed`, `failed`, `pending_review`
- **Label**: Description of the transition (e.g., "If Approved", "On Rejection")
- **Priority**: Order of evaluation when multiple edges exist (higher = evaluated first)

#### 3. **Workflow Execution**
The workflow is displayed as an interactive network diagram using **Cytoscape.js**, allowing users to:
- Visualize the entire process flow
- See current active nodes
- Track execution history
- Monitor task assignments

---

## Standard Workflow Steps

Every business workflow should include these seven standard stages. You may customize them based on specific business needs, but this serves as the recommended baseline.

### 1. **Request**
**Purpose**: Initial submission or request initiation
**Typical Actions**: Create, Submit
**Common Positions**: Requester, Employee, Customer Service
**Outcomes**:
- `submitted` → Proceed to Feasibility Check
- `cancelled` → End workflow
- `needs_revision` → Loop back to Request

**Example Node Configuration:**
```json
{
  "id": "request",
  "label": "Request Submitted",
  "entity_type": "Request",
  "required_positions": ["Employee", "Customer Service Representative"],
  "allowed_actions": ["create", "read", "update"],
  "status": "pending"
}
```

---

### 2. **Feasibility Check**
**Purpose**: Evaluate if the request is viable and aligns with resources, policies, or capabilities
**Typical Actions**: Review, Approve, Reject
**Common Positions**: Department Manager, Technical Lead, Business Analyst
**Outcomes**:
- `feasible` → Proceed to Design
- `not_feasible` → Reject and notify requester
- `needs_clarification` → Return to Request

**Example Node Configuration:**
```json
{
  "id": "feasibility_check",
  "label": "Feasibility Assessment",
  "entity_type": "FeasibilityReview",
  "required_positions": ["Department Manager", "Business Analyst"],
  "allowed_actions": ["read", "approve", "reject"],
  "status": "pending"
}
```

---

### 3. **Design**
**Purpose**: Create specifications, blueprints, or plans for the solution
**Typical Actions**: Create, Update, Submit for Review
**Common Positions**: Designer, Architect, Senior Engineer
**Outcomes**:
- `design_approved` → Proceed to Develop
- `design_rejected` → Return to Design or Feasibility Check
- `pending_review` → Await approval

**Example Node Configuration:**
```json
{
  "id": "design",
  "label": "Design Phase",
  "entity_type": "Design",
  "required_positions": ["System Architect", "Senior Designer"],
  "allowed_actions": ["create", "read", "update"],
  "status": "in_progress"
}
```

---

### 4. **Develop**
**Purpose**: Build or implement the solution based on approved designs
**Typical Actions**: Create, Update, Complete
**Common Positions**: Developer, Engineer, Technician
**Outcomes**:
- `development_complete` → Proceed to Test
- `blocked` → Escalate or return to Design
- `in_progress` → Continue development

**Example Node Configuration:**
```json
{
  "id": "develop",
  "label": "Development Phase",
  "entity_type": "Development",
  "required_positions": ["Software Developer", "Engineer"],
  "allowed_actions": ["create", "read", "update"],
  "status": "in_progress"
}
```

---

### 5. **Test**
**Purpose**: Validate that the solution meets requirements and quality standards
**Typical Actions**: Review, Test, Approve, Reject
**Common Positions**: QA Tester, Quality Assurance Lead
**Outcomes**:
- `tests_passed` → Proceed to Implement
- `tests_failed` → Return to Develop
- `partial_pass` → Conditional approval or re-test

**Example Node Configuration:**
```json
{
  "id": "test",
  "label": "Quality Assurance",
  "entity_type": "TestCase",
  "required_positions": ["QA Tester", "Quality Assurance Lead"],
  "allowed_actions": ["read", "update", "approve", "reject"],
  "status": "pending"
}
```

---

### 6. **Implement**
**Purpose**: Deploy or roll out the solution to production or end users
**Typical Actions**: Deploy, Publish, Activate
**Common Positions**: DevOps Engineer, System Administrator, Project Manager
**Outcomes**:
- `implementation_successful` → Proceed to Support
- `implementation_failed` → Rollback and return to Test or Develop
- `partial_deployment` → Monitor and continue

**Example Node Configuration:**
```json
{
  "id": "implement",
  "label": "Implementation/Deployment",
  "entity_type": "Deployment",
  "required_positions": ["DevOps Engineer", "System Administrator"],
  "allowed_actions": ["publish", "update"],
  "status": "pending"
}
```

---

### 7. **Support**
**Purpose**: Monitor, maintain, and provide ongoing assistance
**Typical Actions**: Monitor, Update, Close
**Common Positions**: Support Engineer, Help Desk, Maintenance Team
**Outcomes**:
- `support_complete` → End workflow
- `issue_found` → Return to appropriate stage (Test, Develop, or Design)
- `ongoing_support` → Continue monitoring

**Example Node Configuration:**
```json
{
  "id": "support",
  "label": "Support & Maintenance",
  "entity_type": "SupportTicket",
  "required_positions": ["Support Engineer", "Help Desk Technician"],
  "allowed_actions": ["read", "update", "archive"],
  "status": "active"
}
```

---

## Permission Model (OrganizationEntityPermission)

### How Permissions Work
Each node in the workflow requires specific **OrganizationPosition(s)** to execute. These positions are linked through the **OrganizationEntityPermission** system.

**Permission Structure:**
- **Organization Position**: The role required (e.g., "Senior Software Engineer", "Project Manager")
- **Entity Name**: The entity being acted upon (e.g., "Request", "Design", "Deployment")
- **Action**: What the position can do (create, read, update, delete, approve, reject, publish, archive)
- **Scope**: The reach of the permission (own, team, department, organization, all)
- **Priority**: Conflict resolution when multiple permissions exist

### Permission Assignment Flow
1. **User is hired** through an `OrganizationVacancy`
2. **User receives** an `OrganizationPosition` (e.g., "QA Tester")
3. **Position has** linked `OrganizationEntityPermission` entries
4. **Workflow checks** if user's position has permission to execute the node
5. **Task is assigned** to all eligible users with required positions

**Example Permission:**
```
Position: "QA Tester"
Can: "approve" and "reject"
Entity: "TestCase"
Scope: "team"
→ This allows QA Testers to approve or reject test cases within their team
```

---

## Task Assignment & Execution

### Task Creation
When a node becomes active in the workflow, the system:
1. **Identifies the node's required positions** (e.g., "Developer", "QA Tester")
2. **Finds all users** who hold those positions
3. **Creates Task entries** for each eligible user
4. **Notifies users** of the new task assignment

### Task Execution Tracking
Each node's execution is tracked through the **Task** entity:
- **Task ID**: Unique identifier
- **Node ID**: Reference to the workflow node
- **Assigned To**: User(s) with required OrganizationPosition
- **Status**: Pending, In Progress, Completed, Rejected
- **Execution Result**: The outcome that determines next steps
- **Timestamp**: When the task was created, started, completed

### Workflow Engine Logic
```
1. Current node is executed by authorized user
2. User performs action (e.g., Approve, Reject, Complete)
3. System captures execution result (e.g., "approved", "rejected")
4. Workflow engine evaluates outgoing edges from current node
5. Engine matches execution result to edge condition
6. Next node identified based on matching edge
7. New tasks created for users with required positions for next node
8. Notifications sent to newly assigned users
9. Workflow status updated
```

**Example Flow:**
```
Node: "Feasibility Check"
User Action: "Approve"
Execution Result: "feasible"
Outgoing Edges:
  - Edge 1: Condition = "feasible" → Target = "Design" ✓ MATCH
  - Edge 2: Condition = "not_feasible" → Target = "Reject"
Next Node: "Design"
Task Assignment: Create tasks for all "System Architects" and "Senior Designers"
```

---

## Workflow Design Template

Use this template when designing a new workflow:

### Workflow Metadata
- **Workflow Name**: [e.g., "Software Development Workflow"]
- **Workflow ID**: [unique identifier]
- **Description**: [brief description of purpose]
- **Owner**: [OrganizationPosition responsible for workflow]
- **Version**: [1.0]
- **Status**: [Active, Draft, Archived]

### Nodes Definition
```json
{
  "nodes": [
    {
      "id": "node_1",
      "label": "Node Label",
      "entity_type": "EntityName",
      "required_positions": ["Position1", "Position2"],
      "allowed_actions": ["action1", "action2"],
      "description": "What happens at this stage",
      "estimated_duration": "2 days",
      "sla": "3 days"
    }
  ]
}
```

### Edges Definition
```json
{
  "edges": [
    {
      "id": "edge_1",
      "source_node": "node_1",
      "target_node": "node_2",
      "condition": "approved",
      "label": "If Approved",
      "priority": 1,
      "description": "Transition when approval is granted"
    }
  ]
}
```

### Example: Complete Minimal Workflow

```json
{
  "workflow": {
    "name": "Simple Request Approval Workflow",
    "id": "request_approval_v1",
    "description": "Basic request submission and approval process",
    "owner": "Workflow Administrator",
    "version": "1.0",
    "status": "active"
  },
  "nodes": [
    {
      "id": "request",
      "label": "Request Submitted",
      "entity_type": "Request",
      "required_positions": ["Employee"],
      "allowed_actions": ["create", "read", "update"]
    },
    {
      "id": "feasibility_check",
      "label": "Feasibility Assessment",
      "entity_type": "FeasibilityReview",
      "required_positions": ["Manager"],
      "allowed_actions": ["read", "approve", "reject"]
    },
    {
      "id": "design",
      "label": "Design Phase",
      "entity_type": "Design",
      "required_positions": ["Designer"],
      "allowed_actions": ["create", "read", "update"]
    },
    {
      "id": "develop",
      "label": "Development",
      "entity_type": "Development",
      "required_positions": ["Developer"],
      "allowed_actions": ["create", "read", "update"]
    },
    {
      "id": "test",
      "label": "Testing",
      "entity_type": "TestCase",
      "required_positions": ["QA Tester"],
      "allowed_actions": ["read", "approve", "reject"]
    },
    {
      "id": "implement",
      "label": "Deployment",
      "entity_type": "Deployment",
      "required_positions": ["DevOps Engineer"],
      "allowed_actions": ["publish"]
    },
    {
      "id": "support",
      "label": "Support",
      "entity_type": "SupportTicket",
      "required_positions": ["Support Engineer"],
      "allowed_actions": ["read", "update", "archive"]
    }
  ],
  "edges": [
    {
      "source_node": "request",
      "target_node": "feasibility_check",
      "condition": "submitted",
      "label": "Submit for Review",
      "priority": 1
    },
    {
      "source_node": "feasibility_check",
      "target_node": "design",
      "condition": "feasible",
      "label": "Approved - Proceed to Design",
      "priority": 1
    },
    {
      "source_node": "feasibility_check",
      "target_node": "request",
      "condition": "needs_clarification",
      "label": "Return for Clarification",
      "priority": 2
    },
    {
      "source_node": "design",
      "target_node": "develop",
      "condition": "design_approved",
      "label": "Design Approved",
      "priority": 1
    },
    {
      "source_node": "develop",
      "target_node": "test",
      "condition": "development_complete",
      "label": "Ready for Testing",
      "priority": 1
    },
    {
      "source_node": "test",
      "target_node": "implement",
      "condition": "tests_passed",
      "label": "Tests Passed",
      "priority": 1
    },
    {
      "source_node": "test",
      "target_node": "develop",
      "condition": "tests_failed",
      "label": "Return to Development",
      "priority": 2
    },
    {
      "source_node": "implement",
      "target_node": "support",
      "condition": "implementation_successful",
      "label": "Deployed Successfully",
      "priority": 1
    },
    {
      "source_node": "support",
      "target_node": "support",
      "condition": "ongoing_support",
      "label": "Continue Monitoring",
      "priority": 1
    }
  ]
}
```

---

## Cytoscape.js Visualization

### Display Requirements
The workflow execution page displays the workflow as an interactive network diagram using **Cytoscape.js**.

**Visual Elements:**
- **Nodes**: Displayed as shapes (circles, rectangles, rounded rectangles)
  - Color-coded by status (Pending, In Progress, Completed, Rejected)
  - Size reflects importance or SLA urgency
  - Label shows node name
- **Edges**: Displayed as arrows with labels
  - Arrow direction shows flow
  - Label shows condition
  - Color indicates path type (success = green, rejection = red, loop = orange)
- **Current Active Node**: Highlighted with animation or distinct color
- **Completed Nodes**: Dimmed or checked/marked
- **Path History**: Show executed path with different edge styling

**Interactive Features:**
- Click node to view details, tasks, and execution history
- Hover to see node description and permissions required
- Zoom and pan for complex workflows
- Filter view by status or position
- Export workflow as image or PDF

---

## Best Practices

### Workflow Design Guidelines

1. **Keep workflows modular**: Break complex processes into smaller, reusable sub-workflows
2. **Define clear outcomes**: Each node should have explicit execution results
3. **Avoid circular dependencies**: Ensure loops have exit conditions
4. **Set realistic SLAs**: Define expected durations for each node
5. **Plan for exceptions**: Include rejection, escalation, and rollback paths
6. **Document conditions**: Clearly describe what each edge condition means
7. **Test thoroughly**: Validate workflow logic before deploying to production
8. **Version control**: Track changes and maintain backward compatibility

### Permission Management

1. **Principle of least privilege**: Grant minimum required permissions
2. **Use scopes effectively**: Limit permissions to appropriate organizational levels
3. **Regular audits**: Review and update permissions periodically
4. **Role clarity**: Ensure positions map clearly to workflow responsibilities
5. **Document permission requirements**: Explain why each position needs specific permissions

### Task Assignment

1. **Balance workload**: Distribute tasks fairly across eligible users
2. **Priority management**: Use task priorities for urgent workflows
3. **Notification strategy**: Send timely notifications without overwhelming users
4. **Deadlines**: Set clear due dates based on SLAs
5. **Escalation paths**: Define what happens when tasks are overdue

---

## Example Use Cases

### 1. Employee Onboarding Workflow
**Nodes**: Application → Screening → Interview → Offer → Hire → Onboard → Training
**Key Positions**: HR Recruiter, Hiring Manager, Department Head, IT Admin

### 2. Procurement Request Workflow
**Nodes**: Request → Budget Check → Vendor Selection → Approval → Purchase → Delivery → Invoice
**Key Positions**: Requester, Finance Manager, Procurement Officer, Approver

### 3. Bug Fix Workflow
**Nodes**: Report → Triage → Assign → Fix → Review → Test → Deploy → Verify
**Key Positions**: Reporter, Triage Team, Developer, Code Reviewer, QA Tester, DevOps

### 4. Content Publishing Workflow
**Nodes**: Draft → Edit → Review → Approve → Publish → Monitor
**Key Positions**: Writer, Editor, Content Manager, Publisher

---

## Implementation Checklist

When implementing a new workflow:

- [ ] Define workflow metadata (name, ID, owner, version)
- [ ] Map all nodes with entity types and positions
- [ ] Define allowed actions for each node
- [ ] Create edges with clear conditions
- [ ] Set up OrganizationEntityPermission entries for all required positions
- [ ] Configure task creation logic
- [ ] Set up notifications for task assignments
- [ ] Implement Cytoscape.js visualization
- [ ] Test workflow execution end-to-end
- [ ] Document workflow for end users
- [ ] Train users on their roles and responsibilities
- [ ] Monitor workflow performance and optimize

---

## Workflow Execution Database Schema

### Suggested Tables

**workflows**
- id, name, description, owner_position_id, version, status, created_at, updated_at

**workflow_nodes**
- id, workflow_id, node_id, label, entity_type, required_positions (JSON), allowed_actions (JSON), config (JSON)

**workflow_edges**
- id, workflow_id, source_node_id, target_node_id, condition, label, priority

**workflow_instances**
- id, workflow_id, initiated_by, current_node_id, status, started_at, completed_at

**workflow_execution_log**
- id, workflow_instance_id, node_id, user_id, action, result, executed_at

**tasks**
- id, workflow_instance_id, node_id, assigned_to_user_id, status, due_date, completed_at

---

## Conclusion

This document provides a comprehensive framework for designing, implementing, and executing business workflows. By following these guidelines, you can create robust, permission-controlled, trackable workflows that automate complex business processes while maintaining security, accountability, and transparency.

Use the standard seven-step workflow (Request → Feasibility → Design → Develop → Test → Implement → Support) as your baseline, and customize as needed for your specific business requirements.

The integration of OrganizationEntityPermission with workflow nodes ensures that only authorized positions can execute specific stages, and the Task-based execution tracking provides full auditability and real-time visibility into workflow progress.
