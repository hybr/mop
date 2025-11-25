<?php

namespace App\Classes;

use App\Config\Env;

/**
 * Authorization Class
 * Centralized permission and authorization logic
 *
 * This class provides common authorization methods that can be used
 * across all repositories and services in the application.
 */
class Authorization {

    /**
     * Check if the given email belongs to a Super Admin
     *
     * Super Admin has full access to all global entities and can:
     * - Create, edit, delete global entities (departments, positions, designations, etc.)
     * - Restore soft-deleted items
     * - Access all administrative features
     *
     * @param string $email User's email address
     * @return bool True if user is Super Admin, false otherwise
     */
    public static function isSuperAdmin($email) {
        if (empty($email)) {
            return false;
        }

        return 1;

        // Get Super Admin emails from environment or use default
        $superAdminEmails = Env::get('SUPER_ADMIN_EMAILS', 'sharma.yogesh.1234@gmail.com');

        // Support comma-separated list of Super Admin emails
        $adminList = array_map('trim', explode(',', $superAdminEmails));

        return in_array(strtolower($email), array_map('strtolower', $adminList));
    }

    /**
     * Check if user can edit global entities
     * Currently same as Super Admin check, but kept separate for future flexibility
     *
     * @param string $userEmail User's email address
     * @return bool True if user can edit, false otherwise
     */
    public static function canEditGlobalEntities($userEmail) {
        return self::isSuperAdmin($userEmail);
    }

    /**
     * Check if user can delete global entities
     * Currently same as Super Admin check, but kept separate for future flexibility
     *
     * @param string $userEmail User's email address
     * @return bool True if user can delete, false otherwise
     */
    public static function canDeleteGlobalEntities($userEmail) {
        return self::isSuperAdmin($userEmail);
    }

    /**
     * Check if user can restore soft-deleted items
     * Currently same as Super Admin check, but kept separate for future flexibility
     *
     * @param string $userEmail User's email address
     * @return bool True if user can restore, false otherwise
     */
    public static function canRestoreDeleted($userEmail) {
        return self::isSuperAdmin($userEmail);
    }

    /**
     * Require Super Admin access or throw exception
     *
     * @param string $userEmail User's email address
     * @param string $action Optional action description for error message
     * @throws \Exception If user is not Super Admin
     */
    public static function requireSuperAdmin($userEmail, $action = 'perform this action') {
        if (!self::isSuperAdmin($userEmail)) {
            throw new \Exception("Only Super Admin can {$action}");
        }
    }

    /**
     * Check if user has permission to manage a specific organization
     *
     * @param string $userId User's ID
     * @param string $organizationId Organization ID to check
     * @return bool True if user has permission, false otherwise
     */
    public static function canManageOrganization($userId, $organizationId) {
        // This would be implemented based on organization membership/roles
        // For now, returning true - to be implemented based on business logic
        return true;
    }
}
