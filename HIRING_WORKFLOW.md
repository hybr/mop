# Hiring Workflow Design

## Overview
The Hiring workflow automates the employee recruitment process from vacancy posting through candidate onboarding. This workflow integrates with the existing OrganizationVacancy, OrganizationPosition, and User entities.

---

## Workflow Metadata
- **Workflow Name**: Employee Hiring Workflow
- **Workflow ID**: hiring_workflow_v1
- **Description**: End-to-end recruitment process from vacancy posting to employee onboarding
- **Owner Position**: HR Manager
- **Version**: 1.0
- **Status**: Active

---

## Workflow Nodes

### 1. Post Vacancy (Request)
**Purpose**: HR posts a job vacancy for a specific position
**Entity Type**: OrganizationVacancy
**Required Positions**: HR Recruiter, HR Manager
**Allowed Actions**: create, read, update
**Estimated Duration**: 1 day
**SLA**: 2 days

**Outcomes**:
- `vacancy_posted` → Proceed to Review Applications
- `cancelled` → End workflow
- `needs_revision` → Loop back to Post Vacancy

---

### 2. Review Applications (Feasibility Check)
**Purpose**: Review submitted applications and shortlist candidates
**Entity Type**: Application
**Required Positions**: HR Recruiter, Hiring Manager
**Allowed Actions**: read, update, approve, reject
**Estimated Duration**: 3-5 days
**SLA**: 7 days

**Outcomes**:
- `candidates_shortlisted` → Proceed to Screen Candidates
- `no_suitable_candidates` → Return to Post Vacancy (re-post)
- `extend_deadline` → Loop back to Review Applications

---

### 3. Screen Candidates (Design)
**Purpose**: Conduct initial phone/video screening
**Entity Type**: Screening
**Required Positions**: HR Recruiter
**Allowed Actions**: create, read, update
**Estimated Duration**: 3-5 days
**SLA**: 7 days

**Outcomes**:
- `screening_passed` → Proceed to Technical Interview
- `screening_failed` → Return to Review Applications
- `reschedule` → Loop back to Screen Candidates

---

### 4. Technical Interview (Develop)
**Purpose**: Assess technical skills and competency
**Entity Type**: Interview
**Required Positions**: Technical Lead, Senior Engineer, Department Manager
**Allowed Actions**: create, read, update, approve, reject
**Estimated Duration**: 5-7 days
**SLA**: 10 days

**Outcomes**:
- `technical_approved` → Proceed to HR Interview
- `technical_rejected` → Return to Review Applications
- `needs_second_round` → Loop back to Technical Interview

---

### 5. HR Interview (Test)
**Purpose**: Evaluate cultural fit, soft skills, and expectations
**Entity Type**: Interview
**Required Positions**: HR Manager, Department Head
**Allowed Actions**: create, read, update, approve, reject
**Estimated Duration**: 3-5 days
**SLA**: 7 days

**Outcomes**:
- `hr_approved` → Proceed to Make Offer
- `hr_rejected` → Return to Review Applications
- `final_round_needed` → Proceed to Final Interview

---

### 6. Final Interview (Optional)
**Purpose**: Final round with senior management or stakeholders
**Entity Type**: Interview
**Required Positions**: Department Head, CEO, CTO
**Allowed Actions**: create, read, update, approve, reject
**Estimated Duration**: 5-7 days
**SLA**: 10 days

**Outcomes**:
- `final_approved` → Proceed to Make Offer
- `final_rejected` → Return to Review Applications

---

### 7. Make Offer (Implement)
**Purpose**: Prepare and send job offer to selected candidate
**Entity Type**: Offer
**Required Positions**: HR Manager
**Allowed Actions**: create, read, update, publish
**Estimated Duration**: 2-3 days
**SLA**: 5 days

**Outcomes**:
- `offer_sent` → Proceed to Await Candidate Response
- `offer_cancelled` → Return to Review Applications

---

### 8. Await Candidate Response
**Purpose**: Wait for candidate to accept or reject the offer
**Entity Type**: Offer
**Required Positions**: HR Recruiter, HR Manager
**Allowed Actions**: read, update
**Estimated Duration**: 5-7 days
**SLA**: 14 days

**Outcomes**:
- `offer_accepted` → Proceed to Onboarding
- `offer_rejected` → Return to Review Applications
- `candidate_negotiating` → Loop back to Make Offer

---

### 9. Onboarding (Support)
**Purpose**: Complete pre-joining formalities and onboard employee
**Entity Type**: User (Employee)
**Required Positions**: HR Manager, IT Admin, Department Manager
**Allowed Actions**: create, read, update
**Estimated Duration**: 5-10 days
**SLA**: 15 days

**Outcomes**:
- `onboarding_complete` → End workflow (Success)
- `candidate_withdrew` → Return to Review Applications
- `documentation_pending` → Loop back to Onboarding

---

## Workflow Edges

```json
{
  "edges": [
    {
      "id": "edge_1",
      "source_node": "post_vacancy",
      "target_node": "review_applications",
      "condition": "vacancy_posted",
      "label": "Vacancy Posted",
      "priority": 1
    },
    {
      "id": "edge_2",
      "source_node": "post_vacancy",
      "target_node": "end",
      "condition": "cancelled",
      "label": "Cancelled",
      "priority": 2
    },
    {
      "id": "edge_3",
      "source_node": "review_applications",
      "target_node": "screen_candidates",
      "condition": "candidates_shortlisted",
      "label": "Candidates Shortlisted",
      "priority": 1
    },
    {
      "id": "edge_4",
      "source_node": "review_applications",
      "target_node": "post_vacancy",
      "condition": "no_suitable_candidates",
      "label": "Re-post Vacancy",
      "priority": 2
    },
    {
      "id": "edge_5",
      "source_node": "screen_candidates",
      "target_node": "technical_interview",
      "condition": "screening_passed",
      "label": "Screening Passed",
      "priority": 1
    },
    {
      "id": "edge_6",
      "source_node": "screen_candidates",
      "target_node": "review_applications",
      "condition": "screening_failed",
      "label": "Failed - Review More",
      "priority": 2
    },
    {
      "id": "edge_7",
      "source_node": "technical_interview",
      "target_node": "hr_interview",
      "condition": "technical_approved",
      "label": "Technical Approved",
      "priority": 1
    },
    {
      "id": "edge_8",
      "source_node": "technical_interview",
      "target_node": "review_applications",
      "condition": "technical_rejected",
      "label": "Rejected",
      "priority": 2
    },
    {
      "id": "edge_9",
      "source_node": "hr_interview",
      "target_node": "make_offer",
      "condition": "hr_approved",
      "label": "HR Approved",
      "priority": 1
    },
    {
      "id": "edge_10",
      "source_node": "hr_interview",
      "target_node": "final_interview",
      "condition": "final_round_needed",
      "label": "Final Round Required",
      "priority": 2
    },
    {
      "id": "edge_11",
      "source_node": "hr_interview",
      "target_node": "review_applications",
      "condition": "hr_rejected",
      "label": "Rejected",
      "priority": 3
    },
    {
      "id": "edge_12",
      "source_node": "final_interview",
      "target_node": "make_offer",
      "condition": "final_approved",
      "label": "Final Approved",
      "priority": 1
    },
    {
      "id": "edge_13",
      "source_node": "final_interview",
      "target_node": "review_applications",
      "condition": "final_rejected",
      "label": "Rejected",
      "priority": 2
    },
    {
      "id": "edge_14",
      "source_node": "make_offer",
      "target_node": "await_response",
      "condition": "offer_sent",
      "label": "Offer Sent",
      "priority": 1
    },
    {
      "id": "edge_15",
      "source_node": "await_response",
      "target_node": "onboarding",
      "condition": "offer_accepted",
      "label": "Offer Accepted",
      "priority": 1
    },
    {
      "id": "edge_16",
      "source_node": "await_response",
      "target_node": "review_applications",
      "condition": "offer_rejected",
      "label": "Offer Rejected",
      "priority": 2
    },
    {
      "id": "edge_17",
      "source_node": "await_response",
      "target_node": "make_offer",
      "condition": "candidate_negotiating",
      "label": "Negotiation",
      "priority": 3
    },
    {
      "id": "edge_18",
      "source_node": "onboarding",
      "target_node": "end",
      "condition": "onboarding_complete",
      "label": "Successfully Hired",
      "priority": 1
    },
    {
      "id": "edge_19",
      "source_node": "onboarding",
      "target_node": "review_applications",
      "condition": "candidate_withdrew",
      "label": "Candidate Withdrew",
      "priority": 2
    }
  ]
}
```

---

## Complete Workflow JSON

```json
{
  "workflow": {
    "name": "Employee Hiring Workflow",
    "id": "hiring_workflow_v1",
    "description": "End-to-end recruitment process from vacancy posting to employee onboarding",
    "owner": "HR Manager",
    "version": "1.0",
    "status": "active"
  },
  "nodes": [
    {
      "id": "post_vacancy",
      "label": "Post Vacancy",
      "entity_type": "OrganizationVacancy",
      "required_positions": ["HR Recruiter", "HR Manager"],
      "allowed_actions": ["create", "read", "update"],
      "estimated_duration": "1 day",
      "sla": "2 days"
    },
    {
      "id": "review_applications",
      "label": "Review Applications",
      "entity_type": "Application",
      "required_positions": ["HR Recruiter", "Hiring Manager"],
      "allowed_actions": ["read", "update", "approve", "reject"],
      "estimated_duration": "3-5 days",
      "sla": "7 days"
    },
    {
      "id": "screen_candidates",
      "label": "Screen Candidates",
      "entity_type": "Screening",
      "required_positions": ["HR Recruiter"],
      "allowed_actions": ["create", "read", "update"],
      "estimated_duration": "3-5 days",
      "sla": "7 days"
    },
    {
      "id": "technical_interview",
      "label": "Technical Interview",
      "entity_type": "Interview",
      "required_positions": ["Technical Lead", "Senior Engineer", "Department Manager"],
      "allowed_actions": ["create", "read", "update", "approve", "reject"],
      "estimated_duration": "5-7 days",
      "sla": "10 days"
    },
    {
      "id": "hr_interview",
      "label": "HR Interview",
      "entity_type": "Interview",
      "required_positions": ["HR Manager", "Department Head"],
      "allowed_actions": ["create", "read", "update", "approve", "reject"],
      "estimated_duration": "3-5 days",
      "sla": "7 days"
    },
    {
      "id": "final_interview",
      "label": "Final Interview",
      "entity_type": "Interview",
      "required_positions": ["Department Head", "CEO", "CTO"],
      "allowed_actions": ["create", "read", "update", "approve", "reject"],
      "estimated_duration": "5-7 days",
      "sla": "10 days"
    },
    {
      "id": "make_offer",
      "label": "Make Offer",
      "entity_type": "Offer",
      "required_positions": ["HR Manager"],
      "allowed_actions": ["create", "read", "update", "publish"],
      "estimated_duration": "2-3 days",
      "sla": "5 days"
    },
    {
      "id": "await_response",
      "label": "Await Candidate Response",
      "entity_type": "Offer",
      "required_positions": ["HR Recruiter", "HR Manager"],
      "allowed_actions": ["read", "update"],
      "estimated_duration": "5-7 days",
      "sla": "14 days"
    },
    {
      "id": "onboarding",
      "label": "Onboarding",
      "entity_type": "User",
      "required_positions": ["HR Manager", "IT Admin", "Department Manager"],
      "allowed_actions": ["create", "read", "update"],
      "estimated_duration": "5-10 days",
      "sla": "15 days"
    }
  ]
}
```

---

## Permission Requirements

### Required OrganizationEntityPermissions

1. **HR Recruiter**
   - Can CREATE, READ, UPDATE OrganizationVacancy (department scope)
   - Can READ, UPDATE, APPROVE, REJECT Application (department scope)
   - Can CREATE, READ, UPDATE Screening (department scope)
   - Can READ Offer (department scope)

2. **HR Manager**
   - Can CREATE, READ, UPDATE, APPROVE OrganizationVacancy (organization scope)
   - Can READ, APPROVE, REJECT Application (organization scope)
   - Can CREATE, READ, UPDATE, APPROVE Interview (organization scope)
   - Can CREATE, READ, UPDATE, PUBLISH Offer (organization scope)
   - Can CREATE, READ, UPDATE User (organization scope)

3. **Technical Lead / Senior Engineer**
   - Can CREATE, READ, UPDATE, APPROVE, REJECT Interview (department scope)

4. **Department Manager / Department Head**
   - Can APPROVE, REJECT Interview (department scope)
   - Can READ OrganizationVacancy (department scope)

5. **CEO / CTO**
   - Can APPROVE, REJECT Interview (all scope)

---

## Implementation Notes

1. **Vacancy Integration**: Workflow starts when an OrganizationVacancy is published
2. **Application Tracking**: Need Application entity to track candidates
3. **Interview Management**: Need Interview entity to record interview feedback
4. **Offer Management**: Need Offer entity to handle job offers
5. **Task Assignment**: At each node, tasks are auto-assigned to users with required positions
6. **Notifications**: Email/SMS notifications at each stage transition
7. **Dashboard**: Real-time workflow visualization using Cytoscape.js
8. **Analytics**: Track metrics like time-to-hire, candidate pipeline, conversion rates

---

## Future Enhancements

1. AI-powered candidate matching
2. Automated scheduling for interviews
3. Integration with job boards (LinkedIn, Indeed)
4. Video interview integration
5. Assessment test automation
6. Reference check workflows
7. Background verification integration
8. Offer letter templates and e-signatures
