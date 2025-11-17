1. Entity UX
   1. Mobile UX Requirements
      1. Collapsible filters
      2. Bottom sticky button for "Add New"
      3. Card layout instead of table
   2. Error & Success Handling
      1. Toast notifications for:
         1. Entity added
         2. Entity updated
         3. Entity deleted
      2. Inline field errors for form validation.
         1. Retry option for network failures.
      3. Empty States
         1. Provide friendly empty-state screens:
            1. “No Entities Yet”
            2. Button: “Create Your First Entity”
            3. Illustration or minimal icon.
      4. Components to Include
         1. Searchable dropdowns
         2. Modal for delete confirmation
         3. Date picker
         4. File uploader with preview
         5. Custom tag component for metadata
         6. Status badge component
         7. Pagination & sorting
      5. UX Style Guidelines
         1. Clean, minimalistic, enterprise-grade look (similar to Notion / Linear / Superhuman).
         2. Use soft shadows, rounded corners, and ample spacing.
         3. Clear visual hierarchy using:
         4. Bold headers
         5. Medium-weight subheaders
         6. Light body text
         7. Keep form fields in a vertical layout for readability.
         8. Use a responsive grid for large screens.
      6. Entity Details View
         1. Read-only view showing:
         2. Header section with Name, ID, Status.
         3. Two-column layout:
         4. Left: Overview / Basic Info
         5. Right: Attributes / Meta / Additional Info
         6. Tabs for:
            1. Overview
            2. Documents
            3. Activity Log
            4. Related Entities (if applicable)
      7. Add New Entity Page
         1. Dynamic form with sections:
            1. Basic Details (Name, Code, Category)
            2. Metadata / Attributes (allow multiple)
            3. Description (rich text optional)
            4. Uploads (images/documents)
            5. Validation messages (inline + on submit).
            6. “Save & Continue” and “Save & Exit” options.
      8. Edit Entity Page
         1. Same layout as “Add New Entity” but pre-filled.
         2. Show activity log or history in a sidebar (optional).
         3. Add “Duplicate Entity” action.
      9. Entity List Page
         1. Display all entities in a paginated & searchable table.
         2. Columns:
            1. Entity Name
            2. Unique ID / Code
            3. Status (Active/Inactive)
            4. Created Date
            5. Actions (View / Edit / Delete)
         3. Filters:
            1. By Status
            2. Date Range
         4. Search box for name / code.
         5. “Add New Entity” floating button.
      10. Default attributes of entity
         1. Created by (user_id)
         2. Created at (date time)
         3. Updated by (user_id)
         4. Updated at (date time)
         5. Deleted by (user_id)
         6. Deleted at (date time) (soft delete)
         7. For Organization (organization_id)
      11. Default methods
         1. Returns the label fields of this entity when it is used as a foreign key in another entity. If any of the label fields themselves reference another entity (i.e., they are foreign keys), then their own labels are resolved first before constructing and returning the final label of this entity.
      12: Access
         1. Access is defined in file permissions.md