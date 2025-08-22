<?php
/**
 * WordPress Permission Helper Functions
 *
 * Global helper functions for WordPress Permission system
 *
 * @package WordPressPermission
 */

defined('ABSPATH') or exit;

/**
 * Check if current user can perform action
 */
if (!function_exists('wp_permission_can')) {
    function wp_permission_can($capability, $user_id = null) {
        return can($capability, $user_id);
    }
}

/**
 * Check if current user has role
 */
if (!function_exists('wp_permission_has_role')) {
    function wp_permission_has_role($role, $user_id = null) {
        return has_role($role, $user_id);
    }
}

/**
 * Grant capability to user or role
 */
if (!function_exists('wp_permission_grant')) {
    function wp_permission_grant($capability, $user_or_role_id, $is_role = false) {
        return grant($capability, $user_or_role_id, $is_role);
    }
}

/**
 * Revoke capability from user or role
 */
if (!function_exists('wp_permission_revoke')) {
    function wp_permission_revoke($capability, $user_or_role_id, $is_role = false) {
        return revoke($capability, $user_or_role_id, $is_role);
    }
}

/**
 * Create a new capability
 */
if (!function_exists('wp_permission_create_capability')) {
    function wp_permission_create_capability($capability, $description = '', $group = 'custom') {
        return wppermission()->createCapability($capability, $description, $group);
    }
}

/**
 * Create a new role
 */
if (!function_exists('wp_permission_create_role')) {
    function wp_permission_create_role($role, $display_name, $capabilities = []) {
        return wppermission()->createRole($role, $display_name, $capabilities);
    }
}

/**
 * Get user permission summary
 */
if (!function_exists('wp_permission_user_summary')) {
    function wp_permission_user_summary($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        return wppermission()->getUserPermissionManager()->getUserPermissionSummary($user_id);
    }
}

/**
 * Get all custom capabilities
 */
if (!function_exists('wp_permission_get_custom_capabilities')) {
    function wp_permission_get_custom_capabilities() {
        return wppermission()->getCapabilityManager()->getCustomCapabilities();
    }
}

/**
 * Get all custom roles
 */
if (!function_exists('wp_permission_get_custom_roles')) {
    function wp_permission_get_custom_roles() {
        return wppermission()->getRoleManager()->getCustomRoles();
    }
}

/**
 * Permission middleware helper
 */
if (!function_exists('wp_permission_middleware')) {
    function wp_permission_middleware($name, $callback = null) {
        if ($callback === null) {
            // Apply middleware
            return wppermission()->applyMiddleware($name);
        } else {
            // Register middleware
            return wppermission()->middleware($name, $callback);
        }
    }
}

/**
 * Require capability middleware
 */
if (!function_exists('wp_permission_require_cap')) {
    function wp_permission_require_cap($capability) {
        return wppermission()->requireCapability($capability);
    }
}

/**
 * Require role middleware
 */
if (!function_exists('wp_permission_require_role')) {
    function wp_permission_require_role($role) {
        return wppermission()->requireRole($role);
    }
}

/**
 * Require login middleware
 */
if (!function_exists('wp_permission_require_login')) {
    function wp_permission_require_login() {
        return wppermission()->requireLogin();
    }
}

/**
 * Get capability groups
 */
if (!function_exists('wp_permission_get_capability_groups')) {
    function wp_permission_get_capability_groups() {
        return wppermission()->getCapabilityManager()->getGroups();
    }
}

/**
 * Get role hierarchy
 */
if (!function_exists('wp_permission_get_role_hierarchy')) {
    function wp_permission_get_role_hierarchy() {
        return wppermission()->getRoleManager()->getHierarchy();
    }
}

/**
 * Compare two users' capabilities
 */
if (!function_exists('wp_permission_compare_users')) {
    function wp_permission_compare_users($user_id_1, $user_id_2) {
        return wppermission()->getUserPermissionManager()->compareUsers($user_id_1, $user_id_2);
    }
}

/**
 * Get capability usage statistics
 */
if (!function_exists('wp_permission_capability_stats')) {
    function wp_permission_capability_stats($capability) {
        return wppermission()->getUserPermissionManager()->getCapabilityStats($capability);
    }
}

/**
 * Check if capability exists
 */
if (!function_exists('wp_permission_capability_exists')) {
    function wp_permission_capability_exists($capability) {
        return wppermission()->getCapabilityManager()->exists($capability);
    }
}

/**
 * Check if role exists
 */
if (!function_exists('wp_permission_role_exists')) {
    function wp_permission_role_exists($role) {
        return wppermission()->getRoleManager()->exists($role);
    }
}

/**
 * Get users by capability
 */
if (!function_exists('wp_permission_get_users_by_capability')) {
    function wp_permission_get_users_by_capability($capability) {
        return wppermission()->getUserPermissionManager()->getUsersByCapability($capability);
    }
}

/**
 * Get users by role
 */
if (!function_exists('wp_permission_get_users_by_role')) {
    function wp_permission_get_users_by_role($role) {
        return wppermission()->getUserPermissionManager()->getUsersByRole($role);
    }
}

/**
 * Bulk assign role to multiple users
 */
if (!function_exists('wp_permission_bulk_assign_role')) {
    function wp_permission_bulk_assign_role($user_ids, $role) {
        return wppermission()->getUserPermissionManager()->bulkAssignRole($user_ids, $role);
    }
}

/**
 * Bulk grant capability to multiple users
 */
if (!function_exists('wp_permission_bulk_grant_capability')) {
    function wp_permission_bulk_grant_capability($user_ids, $capability) {
        return wppermission()->getUserPermissionManager()->bulkGrantCapability($user_ids, $capability);
    }
}

/**
 * Permission check with custom error handling
 */
if (!function_exists('wp_permission_check_or_die')) {
    function wp_permission_check_or_die($capability, $message = null, $user_id = null) {
        if (!can($capability, $user_id)) {
            if ($message === null) {
                $message = sprintf(__('You do not have permission to %s.', 'wp-permission'), $capability);
            }
            wp_die($message, __('Permission Denied', 'wp-permission'), ['response' => 403]);
        }
        return true;
    }
}

/**
 * Permission check with JSON response
 */
if (!function_exists('wp_permission_check_ajax')) {
    function wp_permission_check_ajax($capability, $user_id = null) {
        if (!can($capability, $user_id)) {
            wp_send_json_error([
                'message' => sprintf(__('You do not have permission to %s.', 'wp-permission'), $capability),
                'code' => 'insufficient_permissions'
            ], 403);
        }
        return true;
    }
}

/**
 * Get permission context for current page/action
 */
if (!function_exists('wp_permission_get_context')) {
    function wp_permission_get_context() {
        global $pagenow, $post;
        
        $context = [
            'page' => $pagenow,
            'is_admin' => is_admin(),
            'is_ajax' => wp_doing_ajax(),
            'is_rest' => defined('REST_REQUEST') && REST_REQUEST,
            'user_id' => get_current_user_id(),
            'user_roles' => wp_get_current_user()->roles ?? [],
        ];
        
        if ($post) {
            $context['post_id'] = $post->ID;
            $context['post_type'] = $post->post_type;
            $context['post_status'] = $post->post_status;
        }
        
        return apply_filters('wp_permission_context', $context);
    }
}