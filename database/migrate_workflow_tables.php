<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

echo "Starting workflow tables migration...\n\n";

try {
    $pdo->beginTransaction();

    // 1. Create workflows table
    echo "Creating workflows table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS workflows (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            description TEXT,
            workflow_type TEXT NOT NULL,
            version TEXT NOT NULL DEFAULT '1.0',
            config TEXT NOT NULL,
            owner_position_id TEXT,
            is_active INTEGER DEFAULT 1,
            created_by TEXT NOT NULL,
            created_at TEXT NOT NULL,
            updated_by TEXT,
            updated_at TEXT,
            deleted_by TEXT,
            deleted_at TEXT,
            FOREIGN KEY (owner_position_id) REFERENCES organization_positions(id)
        )
    ");

    // 2. Create workflow_instances table
    echo "Creating workflow_instances table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS workflow_instances (
            id TEXT PRIMARY KEY,
            workflow_id TEXT NOT NULL,
            instance_name TEXT NOT NULL,
            entity_id TEXT,
            entity_type TEXT,
            current_node_id TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'active',
            initiated_by TEXT NOT NULL,
            started_at TEXT NOT NULL,
            completed_at TEXT,
            cancelled_at TEXT,
            cancelled_by TEXT,
            cancellation_reason TEXT,
            metadata TEXT,
            created_by TEXT NOT NULL,
            created_at TEXT NOT NULL,
            updated_by TEXT,
            updated_at TEXT,
            FOREIGN KEY (workflow_id) REFERENCES workflows(id),
            FOREIGN KEY (initiated_by) REFERENCES users(id),
            FOREIGN KEY (cancelled_by) REFERENCES users(id)
        )
    ");

    // 3. Create workflow_nodes table
    echo "Creating workflow_nodes table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS workflow_nodes (
            id TEXT PRIMARY KEY,
            workflow_id TEXT NOT NULL,
            node_id TEXT NOT NULL,
            label TEXT NOT NULL,
            entity_type TEXT NOT NULL,
            required_positions TEXT NOT NULL,
            allowed_actions TEXT NOT NULL,
            estimated_duration TEXT,
            sla TEXT,
            config TEXT,
            sort_order INTEGER DEFAULT 0,
            created_at TEXT NOT NULL,
            FOREIGN KEY (workflow_id) REFERENCES workflows(id),
            UNIQUE(workflow_id, node_id)
        )
    ");

    // 4. Create workflow_edges table
    echo "Creating workflow_edges table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS workflow_edges (
            id TEXT PRIMARY KEY,
            workflow_id TEXT NOT NULL,
            source_node_id TEXT NOT NULL,
            target_node_id TEXT NOT NULL,
            condition TEXT NOT NULL,
            label TEXT NOT NULL,
            priority INTEGER DEFAULT 1,
            style TEXT DEFAULT 'solid',
            created_at TEXT NOT NULL,
            FOREIGN KEY (workflow_id) REFERENCES workflows(id)
        )
    ");

    // 5. Create workflow_execution_log table
    echo "Creating workflow_execution_log table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS workflow_execution_log (
            id TEXT PRIMARY KEY,
            workflow_instance_id TEXT NOT NULL,
            node_id TEXT NOT NULL,
            user_id TEXT NOT NULL,
            action TEXT NOT NULL,
            execution_result TEXT NOT NULL,
            comments TEXT,
            metadata TEXT,
            executed_at TEXT NOT NULL,
            FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instances(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    // 6. Create workflow_tasks table
    echo "Creating workflow_tasks table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS workflow_tasks (
            id TEXT PRIMARY KEY,
            workflow_instance_id TEXT NOT NULL,
            node_id TEXT NOT NULL,
            assigned_to_user_id TEXT NOT NULL,
            task_name TEXT NOT NULL,
            task_description TEXT,
            status TEXT NOT NULL DEFAULT 'pending',
            priority INTEGER DEFAULT 0,
            due_date TEXT,
            started_at TEXT,
            completed_at TEXT,
            completed_by TEXT,
            execution_result TEXT,
            comments TEXT,
            created_by TEXT NOT NULL,
            created_at TEXT NOT NULL,
            updated_by TEXT,
            updated_at TEXT,
            FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instances(id),
            FOREIGN KEY (assigned_to_user_id) REFERENCES users(id),
            FOREIGN KEY (completed_by) REFERENCES users(id)
        )
    ");

    // 7. Create workflow_notifications table
    echo "Creating workflow_notifications table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS workflow_notifications (
            id TEXT PRIMARY KEY,
            workflow_instance_id TEXT NOT NULL,
            user_id TEXT NOT NULL,
            notification_type TEXT NOT NULL,
            message TEXT NOT NULL,
            is_read INTEGER DEFAULT 0,
            sent_at TEXT NOT NULL,
            read_at TEXT,
            FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instances(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    // Create indexes
    echo "Creating indexes...\n";

    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_workflow_instances_workflow_id ON workflow_instances(workflow_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_workflow_instances_status ON workflow_instances(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_workflow_instances_initiated_by ON workflow_instances(initiated_by)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_workflow_instances_entity ON workflow_instances(entity_type, entity_id)");

    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_workflow_execution_log_instance ON workflow_execution_log(workflow_instance_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_workflow_execution_log_user ON workflow_execution_log(user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_workflow_execution_log_node ON workflow_execution_log(node_id)");

    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_workflow_tasks_instance ON workflow_tasks(workflow_instance_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_workflow_tasks_assigned_to ON workflow_tasks(assigned_to_user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_workflow_tasks_status ON workflow_tasks(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_workflow_tasks_due_date ON workflow_tasks(due_date)");

    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_workflow_notifications_user ON workflow_notifications(user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_workflow_notifications_unread ON workflow_notifications(user_id, is_read)");

    $pdo->commit();

    echo "\n✓ Workflow tables migration completed successfully!\n";
    echo "\nCreated tables:\n";
    echo "  - workflows\n";
    echo "  - workflow_instances\n";
    echo "  - workflow_nodes\n";
    echo "  - workflow_edges\n";
    echo "  - workflow_execution_log\n";
    echo "  - workflow_tasks\n";
    echo "  - workflow_notifications\n";
    echo "\nCreated indexes for optimal performance.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "\n✗ Error during migration: " . $e->getMessage() . "\n";
    exit(1);
}
