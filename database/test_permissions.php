<?php
/**
 * Test OrganizationEntityPermission Functionality
 * Demonstrates how to check and use permissions
 */

require_once __DIR__ . '/../src/includes/autoload.php';

use App\Classes\OrganizationEntityPermissionRepository;
use App\Classes\OrganizationPositionRepository;

echo "=== OrganizationEntityPermission Test Script ===" . PHP_EOL . PHP_EOL;

$permRepo = new OrganizationEntityPermissionRepository();
$posRepo = new OrganizationPositionRepository();

// Get sample positions
$positions = $posRepo->findActive(10);

if (empty($positions)) {
    echo "ERROR: No positions found." . PHP_EOL;
    exit(1);
}

echo "Testing permissions for " . count($positions) . " positions..." . PHP_EOL . PHP_EOL;

// Test 1: Check specific permission
echo "TEST 1: Checking specific permissions" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

foreach ($positions as $position) {
    // Check if position can create vacancies
    $canCreate = $permRepo->hasPermission(
        $position->getId(),
        'OrganizationVacancy',
        'create'
    );

    if ($canCreate) {
        echo "✓ {$position->getName()} CAN create OrganizationVacancy";
        echo " (scope: {$canCreate->getScope()}, priority: {$canCreate->getPriority()})" . PHP_EOL;
    }
}

echo PHP_EOL;

// Test 2: Get all permissions for a position
echo "TEST 2: Get all permissions for a position" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$testPosition = $positions[0];
echo "Position: {$testPosition->getName()} ({$testPosition->getCode()})" . PHP_EOL . PHP_EOL;

$permissions = $permRepo->findByPosition($testPosition->getId());

if (empty($permissions)) {
    echo "  No permissions assigned to this position." . PHP_EOL;
} else {
    echo "  Has " . count($permissions) . " permissions:" . PHP_EOL;
    foreach ($permissions as $perm) {
        echo "  • {$perm->getAction()} {$perm->getEntityName()} (scope: {$perm->getScope()}, priority: {$perm->getPriority()})" . PHP_EOL;
    }
}

echo PHP_EOL;

// Test 3: Get permission matrix
echo "TEST 3: Permission matrix for a position" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$matrix = $permRepo->getPermissionMatrix($testPosition->getId());

if (empty($matrix)) {
    echo "  No permissions in matrix." . PHP_EOL;
} else {
    foreach ($matrix as $entity => $actions) {
        echo "  {$entity}:" . PHP_EOL;
        foreach ($actions as $action) {
            echo "    - {$action['action']} (scope: {$action['scope']}, priority: {$action['priority']})" . PHP_EOL;
        }
    }
}

echo PHP_EOL;

// Test 4: Check multiple actions for multiple positions
echo "TEST 4: Permission summary across all positions" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$entities = ['Organization', 'OrganizationVacancy', 'OrganizationPosition'];
$actions = ['create', 'read', 'update', 'delete', 'approve'];

echo str_pad("Position", 30) . " | ";
foreach ($actions as $action) {
    echo str_pad($action, 8) . " ";
}
echo PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;

foreach ($positions as $position) {
    $hasAnyPermission = false;

    // Check if this position has any permissions
    $posPerms = $permRepo->findByPosition($position->getId());
    if (empty($posPerms)) {
        continue;
    }

    $positionName = substr($position->getName(), 0, 28);
    echo str_pad($positionName, 30) . " | ";

    foreach ($actions as $action) {
        $hasPermission = false;
        foreach ($entities as $entity) {
            $perm = $permRepo->hasPermission($position->getId(), $entity, $action);
            if ($perm) {
                $hasPermission = true;
                break;
            }
        }
        echo str_pad($hasPermission ? "✓" : "-", 8) . " ";
    }
    echo PHP_EOL;
}

echo PHP_EOL;

// Test 5: Test permission priorities
echo "TEST 5: Permission priorities (higher priority takes precedence)" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$allPermissions = $permRepo->findAll(100);
$priorityGroups = [];

foreach ($allPermissions as $perm) {
    $priority = $perm->getPriority();
    if (!isset($priorityGroups[$priority])) {
        $priorityGroups[$priority] = [];
    }
    $priorityGroups[$priority][] = $perm;
}

krsort($priorityGroups); // Sort by priority descending

foreach ($priorityGroups as $priority => $perms) {
    echo "Priority {$priority}: " . count($perms) . " permissions" . PHP_EOL;
}

echo PHP_EOL . "=== Test Complete ===" . PHP_EOL;
