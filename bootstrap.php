<?php
/**
 * WordPress Permission Bootstrap File
 *
 * This file initializes the WordPress Permission library.
 * Include this file in your plugin or theme to use the permission system.
 *
 * @package WordPressPermission
 * @version 1.0.0
 */

// Prevent direct access
if (!defined("ABSPATH")) {
    exit();
}

// Prevent multiple loading
if (defined("WPPERMISSION_LOADED")) {
    return;
}

// Define constants
define("WPPERMISSION_LOADED", true);
define("WPPERMISSION_VERSION", "1.0.0");
define("WPPERMISSION_DIR", __DIR__);
define("WPPERMISSION_SRC_DIR", __DIR__ . "/src");

// Auto-configure paths based on WPPERMISSION_MODE (or fallback to WPORM_MODE)
if (!defined("WPPERMISSION_MODE")) {
    $mode = defined("WPORM_MODE") ? WPORM_MODE : "theme";
    define("WPPERMISSION_MODE", $mode);
}

/**
 * Autoloader for WordPress Permission classes
 */
function wppermission_autoload($class) {
    $prefix = "WordPressPermission\\";
    $base_dir = WPPERMISSION_SRC_DIR . "/";
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace("\\", "/", $relative_class) . ".php";
    
    if (file_exists($file)) {
        require $file;
    }
}

spl_autoload_register("wppermission_autoload");

// Load helper functions
require_once __DIR__ . "/src/helpers.php";

/**
 * Get the main Permission instance
 */
function wppermission() {
    return \WordPressPermission\Core\Permission::getInstance();
}

/**
 * Check if user has capability
 */
function can($capability, $user_id = null) {
    return wppermission()->can($capability, $user_id);
}

/**
 * Check if user has role
 */
function has_role($role, $user_id = null) {
    return wppermission()->hasRole($role, $user_id);
}

/**
 * Grant capability to user or role
 */
function grant($capability, $user_or_role_id, $is_role = false) {
    return wppermission()->grant($capability, $user_or_role_id, $is_role);
}

/**
 * Revoke capability from user or role
 */
function revoke($capability, $user_or_role_id, $is_role = false) {
    return wppermission()->revoke($capability, $user_or_role_id, $is_role);
}

// Register WP-CLI commands after WordPress is initialized
if (defined("WP_CLI") && WP_CLI) {
    add_action(
        "init",
        function () {
            require_once __DIR__ . "/cli/WP/CommandRegistrar.php";
            \WordPressPermission\CLI\WP\CommandRegistrar::register();
        },
        10,
    );
}

// Initialize WordPress Permission when WordPress is loaded
add_action("init", function() {
    \WordPressPermission\Core\Permission::getInstance();
}, 1);