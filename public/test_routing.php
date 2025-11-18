<?php
/**
 * Routing Diagnostic Page
 * Access via: /test_routing.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Routing Diagnostics</title>
    <style>
        body { font-family: monospace; padding: 2rem; background: #f5f5f5; }
        .card { background: white; padding: 1.5rem; margin-bottom: 1rem; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; }
        pre { background: #f5f5f5; padding: 1rem; border-radius: 4px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
        a { color: #2196F3; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>🔍 Routing Diagnostics</h1>

    <div class="card">
        <h2>Server Information</h2>
        <pre><?php
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "Script Filename: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'Unknown') . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
        ?></pre>
    </div>

    <div class="card">
        <h2>File Check</h2>
        <pre><?php
$files_to_check = [
    'organizations-facilities.php',
    'organizations-facilities-branches.php',
    'facility-department-form.php',
    '.htaccess'
];

foreach ($files_to_check as $file) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    $readable = $exists && is_readable($path);

    echo ($exists ? '✓' : '✗') . " $file ";
    if ($exists) {
        echo "(" . number_format(filesize($path)) . " bytes)";
        if (!$readable) echo " - NOT READABLE!";
    } else {
        echo "- FILE NOT FOUND!";
    }
    echo "\n";
}
        ?></pre>
    </div>

    <div class="card">
        <h2>Apache mod_rewrite Check</h2>
        <pre><?php
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "<span class='success'>✓ mod_rewrite is ENABLED</span>\n";
    } else {
        echo "<span class='error'>✗ mod_rewrite is NOT enabled</span>\n";
    }
    echo "\nLoaded Apache modules:\n";
    foreach ($modules as $module) {
        if (strpos($module, 'rewrite') !== false || strpos($module, 'dir') !== false) {
            echo "  - $module\n";
        }
    }
} else {
    echo "<span class='error'>⚠ Cannot check Apache modules (not running Apache or function disabled)</span>\n";
    echo "\nThis is likely PHP built-in server or Nginx.\n";
}
        ?></pre>
    </div>

    <div class="card">
        <h2>Test Links</h2>
        <p>Click these links to test routing:</p>
        <ul>
            <li><a href="/organizations/facilities">✦ /organizations/facilities</a> (Should show Facility Departments)</li>
            <li><a href="/organizations-facilities.php">✦ /organizations-facilities.php</a> (Direct file access)</li>
            <li><a href="/organizations/facilities/branches">✦ /organizations/facilities/branches</a> (Should show Branches)</li>
            <li><a href="/facility-department-form.php">✦ /facility-department-form.php</a> (Direct form access)</li>
            <li><a href="/organizations.php">✦ /organizations.php</a> (Organizations page)</li>
        </ul>
    </div>

    <div class="card">
        <h2>.htaccess Content</h2>
        <pre><?php
$htaccess = __DIR__ . '/.htaccess';
if (file_exists($htaccess)) {
    echo htmlspecialchars(file_get_contents($htaccess));
} else {
    echo "<span class='error'>.htaccess file not found!</span>";
}
        ?></pre>
    </div>

    <div class="card">
        <h2>Recommendations</h2>
        <ul>
            <li><strong>If using Apache:</strong> Make sure mod_rewrite is enabled and .htaccess is being read</li>
            <li><strong>If using PHP built-in server:</strong> Start with: <code>php -S localhost:8000 -t public public/router.php</code></li>
            <li><strong>If using Nginx:</strong> You need to configure URL rewriting in nginx.conf</li>
            <li><strong>Quick test:</strong> Try accessing <a href="/organizations-facilities.php">/organizations-facilities.php</a> directly</li>
        </ul>
    </div>
</body>
</html>
