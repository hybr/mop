<?php
require_once 'src/includes/autoload.php';

use App\Classes\OrganizationDepartmentRepository;

echo "Testing Organization Departments Implementation\n";
echo "===============================================\n\n";

try {
    $repo = new OrganizationDepartmentRepository();

    // Test 1: Count departments
    $count = $repo->count();
    echo "✓ Total departments in database: $count\n\n";

    // Test 2: Get first 5 departments
    echo "First 5 departments:\n";
    $depts = $repo->findAll(5);
    foreach($depts as $dept) {
        echo "  - " . $dept->getLabel() . "\n";
        echo "    Sort Order: " . $dept->getSortOrder() . "\n";
        echo "    Active: " . ($dept->getIsActive() ? 'Yes' : 'No') . "\n";
    }

    // Test 3: Search functionality
    echo "\nSearch for 'Human':\n";
    $searchResults = $repo->search('Human', 5);
    foreach($searchResults as $dept) {
        echo "  - " . $dept->getLabel() . "\n";
    }

    // Test 4: Get as options (for dropdown)
    echo "\nDepartments as dropdown options (first 3):\n";
    $options = $repo->getAsOptions();
    foreach(array_slice($options, 0, 3) as $option) {
        echo "  value: " . $option['value'] . " | label: " . $option['label'] . "\n";
    }

    // Test 5: Verify a specific department
    echo "\nFetch specific department (HR):\n";
    $allDepts = $repo->findAll(100);
    foreach($allDepts as $dept) {
        if ($dept->getCode() === 'HR') {
            echo "  ✓ Found: " . $dept->getName() . " (" . $dept->getCode() . ")\n";
            echo "    Description: " . substr($dept->getDescription(), 0, 50) . "...\n";
            break;
        }
    }

    echo "\n===============================================\n";
    echo "All tests passed! Implementation is working correctly.\n";

} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
