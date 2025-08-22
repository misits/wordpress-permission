<?php
/**
 * User Permission Manager
 *
 * Manages user-specific permissions and capabilities
 *
 * @package WordPressPermission
 */

namespace WordPressPermission\Users;

defined('ABSPATH') or exit;

class UserPermissionManager {
    
    /**
     * Check if user has capability
     */
    public function userCan($user_id, $capability) {
        if (!$user_id) {
            return false;
        }
        
        return user_can($user_id, $capability);
    }
    
    /**
     * Check if user has role
     */
    public function userHasRole($user_id, $role) {
        if (!$user_id) {
            return false;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        return in_array($role, $user->roles);
    }
    
    /**
     * Grant capability to specific user
     */
    public function grantCapability($user_id, $capability) {
        if (!$user_id) {
            return false;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        $user->add_cap($capability);
        
        do_action('wp_permission_capability_granted_to_user', $user_id, $capability);
        
        return true;
    }
    
    /**
     * Revoke capability from specific user
     */
    public function revokeCapability($user_id, $capability) {
        if (!$user_id) {
            return false;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        $user->remove_cap($capability);
        
        do_action('wp_permission_capability_revoked_from_user', $user_id, $capability);
        
        return true;
    }
    
    /**
     * Assign role to user
     */
    public function assignRole($user_id, $role) {
        if (!$user_id) {
            return false;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        $user->add_role($role);
        
        do_action('wp_permission_role_assigned_to_user', $user_id, $role);
        
        return true;
    }
    
    /**
     * Remove role from user
     */
    public function removeRole($user_id, $role) {
        if (!$user_id) {
            return false;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        $user->remove_role($role);
        
        do_action('wp_permission_role_removed_from_user', $user_id, $role);
        
        return true;
    }
    
    /**
     * Set user role (replace all existing roles)
     */
    public function setRole($user_id, $role) {
        if (!$user_id) {
            return false;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        $user->set_role($role);
        
        do_action('wp_permission_user_role_set', $user_id, $role);
        
        return true;
    }
    
    /**
     * Get user roles
     */
    public function getUserRoles($user_id) {
        if (!$user_id) {
            return [];
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return [];
        }
        
        return $user->roles;
    }
    
    /**
     * Get user capabilities (including inherited from roles)
     */
    public function getUserCapabilities($user_id) {
        if (!$user_id) {
            return [];
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return [];
        }
        
        return $user->allcaps;
    }
    
    /**
     * Get user's direct capabilities (not inherited from roles)
     */
    public function getUserDirectCapabilities($user_id) {
        if (!$user_id) {
            return [];
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return [];
        }
        
        return $user->caps;
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole($role) {
        return get_users(['role' => $role]);
    }
    
    /**
     * Get users by capability
     */
    public function getUsersByCapability($capability) {
        $users = get_users();
        $matching_users = [];
        
        foreach ($users as $user) {
            if ($user->has_cap($capability)) {
                $matching_users[] = $user;
            }
        }
        
        return $matching_users;
    }
    
    /**
     * Check if user is super admin
     */
    public function isSuperAdmin($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        return is_super_admin($user_id);
    }
    
    /**
     * Get user permission summary
     */
    public function getUserPermissionSummary($user_id) {
        if (!$user_id) {
            return null;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return null;
        }
        
        return [
            'user_id' => $user_id,
            'username' => $user->user_login,
            'display_name' => $user->display_name,
            'email' => $user->user_email,
            'roles' => $user->roles,
            'direct_capabilities' => $user->caps,
            'all_capabilities' => $user->allcaps,
            'capability_count' => count($user->allcaps),
            'is_super_admin' => $this->isSuperAdmin($user_id),
            'registration_date' => $user->user_registered
        ];
    }
    
    /**
     * Compare capabilities between two users
     */
    public function compareUsers($user_id_1, $user_id_2) {
        $user1_caps = $this->getUserCapabilities($user_id_1);
        $user2_caps = $this->getUserCapabilities($user_id_2);
        
        $only_user1 = array_diff_key($user1_caps, $user2_caps);
        $only_user2 = array_diff_key($user2_caps, $user1_caps);
        $shared = array_intersect_key($user1_caps, $user2_caps);
        
        return [
            'user_1_only' => $only_user1,
            'user_2_only' => $only_user2,
            'shared' => $shared,
            'user_1_total' => count($user1_caps),
            'user_2_total' => count($user2_caps),
            'shared_count' => count($shared)
        ];
    }
    
    /**
     * Get capability usage statistics
     */
    public function getCapabilityStats($capability) {
        $users = get_users();
        $users_with_cap = 0;
        $roles_with_cap = [];
        
        foreach ($users as $user) {
            if ($user->has_cap($capability)) {
                $users_with_cap++;
                
                // Track which roles grant this capability
                foreach ($user->roles as $role) {
                    if (!in_array($role, $roles_with_cap)) {
                        $wp_roles = wp_roles();
                        if (isset($wp_roles->roles[$role]['capabilities'][$capability])) {
                            $roles_with_cap[] = $role;
                        }
                    }
                }
            }
        }
        
        return [
            'capability' => $capability,
            'users_with_capability' => $users_with_cap,
            'total_users' => count($users),
            'percentage' => count($users) > 0 ? round(($users_with_cap / count($users)) * 100, 2) : 0,
            'roles_granting_capability' => $roles_with_cap
        ];
    }
    
    /**
     * Bulk assign role to multiple users
     */
    public function bulkAssignRole($user_ids, $role) {
        $results = [];
        
        foreach ($user_ids as $user_id) {
            $results[$user_id] = $this->assignRole($user_id, $role);
        }
        
        do_action('wp_permission_bulk_role_assigned', $user_ids, $role, $results);
        
        return $results;
    }
    
    /**
     * Bulk grant capability to multiple users
     */
    public function bulkGrantCapability($user_ids, $capability) {
        $results = [];
        
        foreach ($user_ids as $user_id) {
            $results[$user_id] = $this->grantCapability($user_id, $capability);
        }
        
        do_action('wp_permission_bulk_capability_granted', $user_ids, $capability, $results);
        
        return $results;
    }
}