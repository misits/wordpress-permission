<?php
/**
 * Handles Users Trait
 *
 * Provides user permission management functionality
 *
 * @package WordPressPermission
 */

namespace WordPressPermission\Traits;

defined('ABSPATH') or exit;

trait HandlesUsers {
    
    /**
     * Assign role to user
     */
    public function assignRole($user_id, $role) {
        return $this->getUserPermissionManager()->assignRole($user_id, $role);
    }
    
    /**
     * Remove role from user
     */
    public function removeRole($user_id, $role) {
        return $this->getUserPermissionManager()->removeRole($user_id, $role);
    }
    
    /**
     * Get user roles
     */
    public function getUserRoles($user_id) {
        return $this->getUserPermissionManager()->getUserRoles($user_id);
    }
    
    /**
     * Get user capabilities
     */
    public function getUserCapabilities($user_id) {
        return $this->getUserPermissionManager()->getUserCapabilities($user_id);
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole($role) {
        return $this->getUserPermissionManager()->getUsersByRole($role);
    }
    
    /**
     * Get users by capability
     */
    public function getUsersByCapability($capability) {
        return $this->getUserPermissionManager()->getUsersByCapability($capability);
    }
    
    /**
     * Check if user is super admin
     */
    public function isSuperAdmin($user_id = null) {
        return $this->getUserPermissionManager()->isSuperAdmin($user_id);
    }
}