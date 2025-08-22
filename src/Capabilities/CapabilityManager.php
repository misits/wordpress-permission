<?php
/**
 * Capability Manager
 *
 * Manages WordPress capabilities with enhanced functionality
 *
 * @package WordPressPermission
 */

namespace WordPressPermission\Capabilities;

defined('ABSPATH') or exit;

class CapabilityManager {
    
    /**
     * Custom capabilities option key
     */
    const CUSTOM_CAPABILITIES_OPTION = 'wp_permission_custom_capabilities';
    
    /**
     * WordPress default capabilities by group
     */
    private $defaultCapabilities = [
        'post' => [
            'publish_posts', 'edit_posts', 'edit_others_posts', 'edit_private_posts',
            'edit_published_posts', 'delete_posts', 'delete_others_posts', 
            'delete_private_posts', 'delete_published_posts', 'read_private_posts'
        ],
        'page' => [
            'publish_pages', 'edit_pages', 'edit_others_pages', 'edit_private_pages',
            'edit_published_pages', 'delete_pages', 'delete_others_pages', 
            'delete_private_pages', 'delete_published_pages', 'read_private_pages'
        ],
        'media' => [
            'upload_files', 'edit_files'
        ],
        'comment' => [
            'edit_comment', 'moderate_comments'
        ],
        'theme' => [
            'switch_themes', 'edit_themes', 'activate_plugins', 'edit_plugins',
            'install_themes', 'install_plugins', 'delete_themes', 'delete_plugins',
            'update_themes', 'update_plugins', 'update_core'
        ],
        'user' => [
            'list_users', 'create_users', 'edit_users', 'delete_users', 'promote_users'
        ],
        'admin' => [
            'manage_options', 'manage_categories', 'manage_links', 'read'
        ],
        'multisite' => [
            'manage_sites', 'manage_network', 'manage_network_users', 
            'manage_network_themes', 'manage_network_plugins'
        ]
    ];
    
    /**
     * Create a new custom capability
     */
    public function create($capability, $description = '', $group = 'custom') {
        $custom_caps = $this->getCustomCapabilities();
        
        if (isset($custom_caps[$capability])) {
            return false; // Already exists
        }
        
        $custom_caps[$capability] = [
            'description' => $description,
            'group' => $group,
            'created_at' => current_time('mysql'),
            'created_by' => get_current_user_id()
        ];
        
        update_option(self::CUSTOM_CAPABILITIES_OPTION, $custom_caps);
        
        do_action('wp_permission_capability_created', $capability, $description, $group);
        
        return true;
    }
    
    /**
     * Check if capability exists
     */
    public function exists($capability) {
        // Check WordPress default capabilities
        foreach ($this->defaultCapabilities as $caps) {
            if (in_array($capability, $caps)) {
                return true;
            }
        }
        
        // Check custom capabilities
        $custom_caps = $this->getCustomCapabilities();
        return isset($custom_caps[$capability]);
    }
    
    /**
     * Get all custom capabilities
     */
    public function getCustomCapabilities() {
        return get_option(self::CUSTOM_CAPABILITIES_OPTION, []);
    }
    
    /**
     * Get all capabilities by group
     */
    public function getGroups() {
        $groups = [];
        
        // Add default groups
        foreach ($this->defaultCapabilities as $group => $caps) {
            $groups[$group] = [
                'name' => $group,
                'capabilities' => $caps,
                'type' => 'default'
            ];
        }
        
        // Add custom capabilities
        $custom_caps = $this->getCustomCapabilities();
        foreach ($custom_caps as $cap => $data) {
            $group = $data['group'] ?? 'custom';
            if (!isset($groups[$group])) {
                $groups[$group] = [
                    'name' => $group,
                    'capabilities' => [],
                    'type' => 'custom'
                ];
            }
            $groups[$group]['capabilities'][] = $cap;
        }
        
        return $groups;
    }
    
    /**
     * Get capabilities by group
     */
    public function getByGroup($group) {
        $groups = $this->getGroups();
        return $groups[$group] ?? null;
    }
    
    /**
     * Delete a custom capability
     */
    public function delete($capability) {
        $custom_caps = $this->getCustomCapabilities();
        
        if (!isset($custom_caps[$capability])) {
            return false; // Doesn't exist or is default
        }
        
        // Remove from all roles that have it
        $wp_roles = wp_roles();
        foreach ($wp_roles->roles as $role => $data) {
            if (isset($data['capabilities'][$capability])) {
                $wp_roles->remove_cap($role, $capability);
            }
        }
        
        // Remove from custom capabilities
        unset($custom_caps[$capability]);
        update_option(self::CUSTOM_CAPABILITIES_OPTION, $custom_caps);
        
        do_action('wp_permission_capability_deleted', $capability);
        
        return true;
    }
    
    /**
     * Update capability description
     */
    public function updateDescription($capability, $description) {
        $custom_caps = $this->getCustomCapabilities();
        
        if (!isset($custom_caps[$capability])) {
            return false;
        }
        
        $custom_caps[$capability]['description'] = $description;
        $custom_caps[$capability]['updated_at'] = current_time('mysql');
        
        update_option(self::CUSTOM_CAPABILITIES_OPTION, $custom_caps);
        
        return true;
    }
    
    /**
     * Get capability info
     */
    public function getInfo($capability) {
        $custom_caps = $this->getCustomCapabilities();
        
        if (isset($custom_caps[$capability])) {
            return $custom_caps[$capability];
        }
        
        // Check if it's a default capability
        foreach ($this->defaultCapabilities as $group => $caps) {
            if (in_array($capability, $caps)) {
                return [
                    'group' => $group,
                    'type' => 'default',
                    'description' => "WordPress default {$group} capability"
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Get all capabilities (default + custom)
     */
    public function getAll() {
        $all_caps = [];
        
        // Add default capabilities
        foreach ($this->defaultCapabilities as $group => $caps) {
            foreach ($caps as $cap) {
                $all_caps[$cap] = [
                    'group' => $group,
                    'type' => 'default',
                    'description' => "WordPress default {$group} capability"
                ];
            }
        }
        
        // Add custom capabilities
        $custom_caps = $this->getCustomCapabilities();
        foreach ($custom_caps as $cap => $data) {
            $all_caps[$cap] = array_merge($data, ['type' => 'custom']);
        }
        
        return $all_caps;
    }
    
    /**
     * Search capabilities
     */
    public function search($query) {
        $all_caps = $this->getAll();
        $results = [];
        
        foreach ($all_caps as $cap => $data) {
            if (stripos($cap, $query) !== false || 
                stripos($data['description'] ?? '', $query) !== false) {
                $results[$cap] = $data;
            }
        }
        
        return $results;
    }
}