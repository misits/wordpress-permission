<?php
/**
 * WordPress Permission Core Class
 *
 * Main class for managing WordPress permissions, capabilities, and roles
 *
 * @package WordPressPermission
 */

namespace WordPressPermission\Core;

use WordPressPermission\Traits\HandlesCapabilities;
use WordPressPermission\Traits\HandlesRoles;
use WordPressPermission\Traits\HandlesUsers;
use WordPressPermission\Traits\HandlesMiddleware;

defined('ABSPATH') or exit;

class Permission {
    use HandlesCapabilities;
    use HandlesRoles;
    use HandlesUsers;
    use HandlesMiddleware;
    
    /**
     * Single instance of Permission
     */
    private static $instance = null;
    
    /**
     * Capability Manager instance
     */
    private $capabilityManager;
    
    /**
     * Role Manager instance
     */
    private $roleManager;
    
    /**
     * User Permission Manager instance
     */
    private $userPermissionManager;
    
    /**
     * Middleware Manager instance
     */
    private $middlewareManager;
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize the permission system
     */
    private function init() {
        // Initialize managers
        $this->capabilityManager = new \WordPressPermission\Capabilities\CapabilityManager();
        $this->roleManager = new \WordPressPermission\Roles\RoleManager();
        $this->userPermissionManager = new \WordPressPermission\Users\UserPermissionManager();
        $this->middlewareManager = new \WordPressPermission\Middleware\MiddlewareManager();
        
        // Hook into WordPress
        add_action('wp_loaded', [$this, 'onWordPressLoaded']);
        
        // Fire initialization hook
        do_action('wppermission_loaded');
    }
    
    /**
     * Called when WordPress is fully loaded
     */
    public function onWordPressLoaded() {
        do_action('wppermission_ready');
    }
    
    /**
     * Get capability manager
     */
    public function getCapabilityManager() {
        return $this->capabilityManager;
    }
    
    /**
     * Get role manager
     */
    public function getRoleManager() {
        return $this->roleManager;
    }
    
    /**
     * Get user permission manager
     */
    public function getUserPermissionManager() {
        return $this->userPermissionManager;
    }
    
    /**
     * Get middleware manager
     */
    public function getMiddlewareManager() {
        return $this->middlewareManager;
    }
    
    /**
     * Check if user has capability
     */
    public function can($capability, $user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        return $this->userPermissionManager->userCan($user_id, $capability);
    }
    
    /**
     * Check if user has role
     */
    public function hasRole($role, $user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        return $this->userPermissionManager->userHasRole($user_id, $role);
    }
    
    /**
     * Grant capability to user or role
     */
    public function grant($capability, $user_or_role_id, $is_role = false) {
        if ($is_role) {
            return $this->roleManager->addCapability($user_or_role_id, $capability);
        } else {
            return $this->userPermissionManager->grantCapability($user_or_role_id, $capability);
        }
    }
    
    /**
     * Revoke capability from user or role
     */
    public function revoke($capability, $user_or_role_id, $is_role = false) {
        if ($is_role) {
            return $this->roleManager->removeCapability($user_or_role_id, $capability);
        } else {
            return $this->userPermissionManager->revokeCapability($user_or_role_id, $capability);
        }
    }
    
    /**
     * Static access to capabilities
     */
    public static function capabilities() {
        return self::getInstance()->getCapabilityManager();
    }
    
    /**
     * Static access to roles
     */
    public static function roles() {
        return self::getInstance()->getRoleManager();
    }
    
    /**
     * Static access to users
     */
    public static function users() {
        return self::getInstance()->getUserPermissionManager();
    }
    
    /**
     * Static access to middleware
     */
    public static function middleware() {
        return self::getInstance()->getMiddlewareManager();
    }
}