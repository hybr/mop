<?php

namespace App\Config;

class Env {
    private static $loaded = false;
    private static $vars = [];

    /**
     * Load environment variables from .env file
     */
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }

        if ($path === null) {
            $path = __DIR__ . '/../../.env';
        }

        if (!file_exists($path)) {
            // Use defaults if .env doesn't exist
            self::setDefaults();
            self::$loaded = true;
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                $value = trim($value, '"\'');

                // Store in class property and $_ENV
                self::$vars[$key] = $value;
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }

        self::$loaded = true;
    }

    /**
     * Set default values
     */
    private static function setDefaults() {
        $defaults = [
            'APP_ENV' => 'development',
            'DB_DRIVER' => 'sqlite',
            'SQLITE_DB_PATH' => 'database/app.db',
            'APP_NAME' => 'MyApp',
            'APP_DEBUG' => 'true',
            'BASE_URL' => 'http://localhost:8000',
            'SESSION_LIFETIME' => '86400',
            'SESSION_NAME' => 'myapp_session',
            'PASSWORD_MIN_LENGTH' => '6'
        ];

        foreach ($defaults as $key => $value) {
            self::$vars[$key] = $value;
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }

    /**
     * Get environment variable
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }

        if (isset(self::$vars[$key])) {
            return self::$vars[$key];
        }

        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        return $default;
    }

    /**
     * Check if environment is development
     */
    public static function isDevelopment() {
        return self::get('APP_ENV') === 'development';
    }

    /**
     * Check if environment is production
     */
    public static function isProduction() {
        return self::get('APP_ENV') === 'production';
    }

    /**
     * Get database driver
     */
    public static function getDbDriver() {
        return self::get('DB_DRIVER', 'sqlite');
    }
}
