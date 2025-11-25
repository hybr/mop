<?php
/**
 * Migrate All Tables Script
 * Runs all SQL migrations in the database/seed directory in order
 */

require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

echo "=== Database Migration Script ===" . PHP_EOL . PHP_EOL;

$db = Database::getInstance();
$pdo = $db->getPdo();

// Get all SQL files in the seed directory
$seedDir = __DIR__ . '/seed';
$sqlFiles = glob($seedDir . '/*.sql');
sort($sqlFiles);

$successCount = 0;
$errorCount = 0;

foreach ($sqlFiles as $sqlFile) {
    $filename = basename($sqlFile);
    echo "Processing: $filename" . PHP_EOL;

    try {
        $sql = file_get_contents($sqlFile);

        // Split SQL file into individual statements
        $statements = explode(';', $sql);

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement) || strpos($statement, '--') === 0) {
                continue;
            }

            try {
                $pdo->exec($statement);
            } catch (Exception $e) {
                // Ignore "already exists" errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
            }
        }

        echo "  ✓ Success" . PHP_EOL;
        $successCount++;
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . PHP_EOL;
        $errorCount++;
    }

    echo PHP_EOL;
}

echo "=== Migration Complete ===" . PHP_EOL;
echo "Success: $successCount files" . PHP_EOL;
echo "Errors: $errorCount files" . PHP_EOL;
echo PHP_EOL;

// Display table summary
echo "=== Database Tables ===" . PHP_EOL;
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    echo "- $table: $count rows" . PHP_EOL;
}
