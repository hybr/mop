<?php
/**
 * Router for PHP Built-in Server
 * Usage: php -S localhost:8000 -t public public/router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route mapping
$routes = [
    '/organizations/facilities/branches' => 'organizations-facilities-branches.php',
    '/organizations/facilities' => 'organizations-facilities.php',
];

// Check exact matches first
foreach ($routes as $route => $file) {
    if ($uri === $route || $uri === $route . '/') {
        $_SERVER['SCRIPT_NAME'] = '/' . $file;
        require __DIR__ . '/' . $file;
        return true;
    }
}

// Check if the requested file exists
$file = __DIR__ . $uri;

// Serve static files directly
if (is_file($file)) {
    return false; // Serve the file as-is
}

// Try to find a PHP file
if (is_file($file . '.php')) {
    require $file . '.php';
    return true;
}

// Check for index.php in directory
if (is_dir($file) && is_file($file . '/index.php')) {
    require $file . '/index.php';
    return true;
}

// If nothing matches and no file exists, try index.php in public root
if (is_file(__DIR__ . '/index.php')) {
    require __DIR__ . '/index.php';
    return true;
}

// 404 Not Found
http_response_code(404);
echo "404 - Page Not Found";
return true;
