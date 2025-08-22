<?php
/**
 * Role Manager
 *
 * Manages WordPress roles with enhanced functionality
 *
 * @package WordPressPermission
 */

namespace WordPressPermission\Roles;

defined('ABSPATH') or exit;

class RoleManager {
    
    /**
     * Custom roles option key
     */
    const CUSTOM_ROLES_OPTION = 'wp_permission_custom_roles';
    
    /**
     * WordPress default roles
     */
    private $defaultRoles = [
        'super_admin', 'administrator', 'editor', 'author', 'contributor', 'subscriber'
    ];
    
    /**
     * Create a new role
     */
    public function create($role, $display_name, $capabilities = []) {
        if ($this->exists($role)) {
            return false; // Role already exists
        }
        
        // Add role to WordPress
        $result = add_role($role, $display_name, $capabilities);
        
        if ($result) {
            // Track custom role
            $custom_roles = $this->getCustomRoles();
            $custom_roles[$role] = [
                'display_name' => $display_name,
                'capabilities' => $capabilities,
                'created_at' => current_time('mysql'),
                'created_by' => get_current_user_id()
            ];
            update_option(self::CUSTOM_ROLES_OPTION, $custom_roles);
            
            do_action('wp_permission_role_created', $role, $display_name, $capabilities);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if role exists
     */
    public function exists($role) {
        $wp_roles = wp_roles();
        return isset($wp_roles->roles[$role]);
    }
    
    /**
     * Get all roles
     */
    public function getAll() {
        $wp_roles = wp_roles();
        return $wp_roles->roles;
    }
    
    /**
     * Get custom roles (non-WordPress default)
     */
    public function getCustomRoles() {
        return get_option(self::CUSTOM_ROLES_OPTION, []);
    }
    
    /**
     * Get role display name
     */
    public function getDisplayName($role) {
        $wp_roles = wp_roles();
        return $wp_roles->roles[$role]['name'] ?? null;
    }
    
    /**
     * Get role capabilities
     */
    public function getCapabilities($role) {
        $wp_roles = wp_roles();
        return $wp_roles->roles[$role]['capabilities'] ?? [];
    }
    
    /**
     * Add capability to role
     */
    public function addCapability($role, $capability) {
        if (!$this->exists($role)) {
            return false;
        }
        
        $wp_roles = wp_roles();
        $wp_roles->add_cap($role, $capability);
        
        do_action('wp_permission_capability_added_to_role', $role, $capability);
        
        return true;
    }
    
    /**
     * Remove capability from role
     */
    public function removeCapability($role, $capability) {
        if (!$this->exists($role)) {
            return false;
        }
        
        $wp_roles = wp_roles();
        $wp_roles->remove_cap($role, $capability);
        
        do_action('wp_permission_capability_removed_from_role', $role, $capability);
        
        return true;
    }
    
    /**
     * Clone a role
     */
    public function clone($source_role, $new_role, $display_name) {
        if (!$this->exists($source_role) || $this->exists($new_role)) {
            return false;
        }
        
        $source_capabilities = $this->getCapabilities($source_role);
        return $this->create($new_role, $display_name, $source_capabilities);
    }
    
    /**
     * Delete a custom role
     */
    public function delete($role) {
        // Don't allow deletion of default roles
        if (in_array($role, $this->defaultRoles)) {
            return false;
        }
        
        if (!$this->exists($role)) {
            return false;
        }
        
        // Remove role from WordPress
        remove_role($role);
        
        // Remove from custom roles tracking
        $custom_roles = $this->getCustomRoles();
        if (isset($custom_roles[$role])) {
            unset($custom_roles[$role]);
            update_option(self::CUSTOM_ROLES_OPTION, $custom_roles);
        }
        
        do_action('wp_permission_role_deleted', $role);
        
        return true;
    }
    
    /**
     * Update role display name
     */
    public function updateDisplayName($role, $display_name) {
        if (!$this->exists($role)) {
            return false;
        }
        
        $wp_roles = wp_roles();
        $wp_roles->roles[$role]['name'] = $display_name;
        update_option($wp_roles->role_key, $wp_roles->roles);
        
        // Update custom roles tracking if applicable
        $custom_roles = $this->getCustomRoles();
        if (isset($custom_roles[$role])) {
            $custom_roles[$role]['display_name'] = $display_name;
            $custom_roles[$role]['updated_at'] = current_time('mysql');
            update_option(self::CUSTOM_ROLES_OPTION, $custom_roles);
        }
        
        do_action('wp_permission_role_updated', $role, $display_name);
        
        return true;
    }
    
    /**
     * Get role hierarchy (based on capability count)
     */
    public function getHierarchy() {
        $roles = $this->getAll();
        $hierarchy = [];
        
        foreach ($roles as $role => $data) {
            $cap_count = count($data['capabilities']);
            $hierarchy[$role] = [
                'name' => $data['name'],
                'capability_count' => $cap_count,
                'is_custom' => !in_array($role, $this->defaultRoles)
            ];
        }
        
        // Sort by capability count (descending)
        uasort($hierarchy, function($a, $b) {
            return $b['capability_count'] - $a['capability_count'];
        });
        
        return $hierarchy;
    }
    
    /**
     * Check if role has capability
     */
    public function hasCapability($role, $capability) {
        $capabilities = $this->getCapabilities($role);
        return isset($capabilities[$capability]) && $capabilities[$capability];
    }
    
    /**
     * Get roles with specific capability
     */
    public function getRolesWithCapability($capability) {
        $roles = $this->getAll();
        $matching_roles = [];
        
        foreach ($roles as $role => $data) {
            if (isset($data['capabilities'][$capability]) && $data['capabilities'][$capability]) {
                $matching_roles[] = $role;
            }
        }
        
        return $matching_roles;
    }
    
    /**
     * Set role capabilities (replace all)
     */
    public function setCapabilities($role, $capabilities) {
        if (!$this->exists($role)) {
            return false;
        }
        
        $wp_roles = wp_roles();
        
        // Remove all existing capabilities
        $current_caps = $this->getCapabilities($role);
        foreach ($current_caps as $cap => $granted) {
            if ($granted) {
                $wp_roles->remove_cap($role, $cap);
            }
        }
        
        // Add new capabilities
        foreach ($capabilities as $cap) {
            $wp_roles->add_cap($role, $cap);
        }
        
        do_action('wp_permission_role_capabilities_set', $role, $capabilities);
        
        return true;
    }
    
    /**
     * Get role statistics
     */
    public function getStats() {
        $roles = $this->getAll();
        $custom_roles = $this->getCustomRoles();
        
        return [
            'total_roles' => count($roles),
            'default_roles' => count($this->defaultRoles),
            'custom_roles' => count($custom_roles),
            'roles_by_capability_count' => $this->getHierarchy()
        ];
    }
}