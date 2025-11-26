<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "Testing error handling...\n";

try {
    require_once __DIR__ . '/../src/includes/autoload.php';
    echo "Autoload OK\n";

    use App\Classes\Auth;
    $auth = new Auth();
    echo "Auth instance created OK\n";

    $isLoggedIn = $auth->isLoggedIn();
    echo "isLoggedIn() returned: " . ($isLoggedIn ? 'true' : 'false') . "\n";

    echo "All tests passed!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
