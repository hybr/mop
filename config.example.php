<?php
/**
 * Configuration Example File
 *
 * Copy this file to config.php and update the values
 * for your environment
 */

// Application Settings
define('APP_NAME', 'MyApp');
define('APP_ENV', 'development'); // development, staging, production
define('APP_DEBUG', true); // Set to false in production

// Supabase Configuration
define('SUPABASE_URL', 'https://famnnqgqobqthfeygjzx.supabase.co');
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZhbW5ucWdxb2JxdGhmZXlnanp4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjMzNzE1MzUsImV4cCI6MjA3ODk0NzUzNX0.i6kJLeT6ymY__NFCVpEbUQs02yn7pdht01ocSdoI8yY');

// Base URL
define('BASE_URL', 'http://localhost/mop/public');

// Session Configuration
define('SESSION_LIFETIME', 86400); // 24 hours in seconds
define('SESSION_NAME', 'myapp_session');

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (adjust for production)
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
}

// Security Settings
define('PASSWORD_MIN_LENGTH', 6);
define('ENABLE_EMAIL_VERIFICATION', true);
define('ENABLE_2FA', false); // Two-factor authentication

// File Upload Settings
define('UPLOAD_MAX_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_UPLOAD_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_DIR', __DIR__ . '/public/uploads');

// Pagination
define('ITEMS_PER_PAGE', 20);

// Email Settings (for future use)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@example.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_FROM_EMAIL', 'noreply@example.com');
define('SMTP_FROM_NAME', APP_NAME);

// API Rate Limiting
define('API_RATE_LIMIT', 100); // Requests per minute
define('API_RATE_LIMIT_WINDOW', 60); // Seconds

// Cache Settings
define('CACHE_ENABLED', false);
define('CACHE_LIFETIME', 3600); // 1 hour

// Logging
define('LOG_LEVEL', 'debug'); // debug, info, warning, error
define('LOG_FILE', __DIR__ . '/logs/app.log');
