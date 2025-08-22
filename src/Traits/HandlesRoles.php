<?php
/**
 * Handles Roles Trait
 *
 * Provides role management functionality
 *
 * @package WordPressPermission
 */

namespace WordPressPermission\Traits;

defined('ABSPATH') or exit;

trait HandlesRoles {
    
    /**
     * Create a new role
     */
    public function createRole($role, $display_name, $capabilities = []) {
        return $this->getRoleManager()->create($role, $display_name, $capabilities);
    }
    
    /**
     * Check if role exists
     */
    public function roleExists($role) {
        return $this->getRoleManager()->exists($role);
    }
    
    /**
     * Get all roles
     */
    public function getAllRoles() {
        return $this->getRoleManager()->getAll();
    }
    
    /**
     * Get custom roles (non-WordPress default)
     */
    public function getCustomRoles() {
        return $this->getRoleManager()->getCustomRoles();
    }
    
    /**
     * Delete a custom role
     */
    public function deleteRole($role) {
        return $this->getRoleManager()->delete($role);
    }
    
    /**
     * Clone a role
     */
    public function cloneRole($source_role, $new_role, $display_name) {
        return $this->getRoleManager()->clone($source_role, $new_role, $display_name);
    }
    
    /**
     * Get role capabilities
     */
    public function getRoleCapabilities($role) {
        return $this->getRoleManager()->getCapabilities($role);
    }
}