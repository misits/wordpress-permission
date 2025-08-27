# User Permission Management

Complete guide to managing user permissions with wp-permission.

## Overview

User permission management handles individual user capabilities, role assignments, and permission analysis. The wp-permission library provides comprehensive tools for managing user-level permissions beyond WordPress's default system.

## Core Concepts

### Permission Sources

Users can have permissions from multiple sources:
- **Role Capabilities**: Inherited from assigned roles
- **Direct Capabilities**: Granted directly to the user
- **Super Admin**: Network admin privileges (multisite)

### Permission Hierarchy

```
Super Admin (multisite only)
├── Direct User Capabilities
├── Role 1 Capabilities
├── Role 2 Capabilities
└── WordPress Default Capabilities
```

## Checking User Permissions

### Basic Permission Checks

```php
// Check if current user has capability
if (can('manage_products')) {
    echo 'User can manage products';
}

// Check specific user
if (can('manage_products', 123)) {
    echo 'User 123 can manage products';
}

// Using the manager directly
$manager = wppermission()->getUserPermissionManager();
if ($manager->userCan(123, 'manage_products')) {
    echo 'User has permission';
}
```

### Global Helper Functions

```php
// Quick permission checks
can('capability', $user_id);           // Check capability
grant('capability', $user_id);         // Grant capability
revoke('capability', $user_id);        // Revoke capability
has_role('role', $user_id);            // Check role
assign_role('role', $user_id);         // Assign role
remove_role('role', $user_id);         // Remove role
```

## Granting Capabilities

### Direct Capability Grants

```php
$manager = wppermission()->getUserPermissionManager();

// Grant capability to user
$manager->grantCapability(123, 'manage_products');

// Grant multiple capabilities
$capabilities = ['view_analytics', 'export_data', 'manage_inventory'];
foreach ($capabilities as $cap) {
    $manager->grantCapability(123, $cap);
}

// Check if grant was successful
if ($manager->userCan(123, 'manage_products')) {
    echo 'Capability granted successfully';
}
```

### Via WP-CLI

```bash
# Grant capability to specific user
wp permission:user-grant admin manage_products
wp permission:user-grant 123 view_analytics
wp permission:user-grant user@example.com edit_others_posts

# Bulk grant to multiple users
wp permission:user-bulk-grant manage_products --users="admin,editor,123"

# Grant to all users with specific role
wp permission:user-bulk-grant view_analytics --role=editor
```

## Revoking Capabilities

### Remove Direct Capabilities

```php
$manager = wppermission()->getUserPermissionManager();

// Revoke specific capability
$manager->revokeCapability(123, 'delete_users');

// Revoke multiple capabilities
$capabilities = ['manage_options', 'edit_files', 'delete_plugins'];
foreach ($capabilities as $cap) {
    $manager->revokeCapability(123, $cap);
}
```

### Via WP-CLI

```bash
# Revoke capability from user
wp permission:user-revoke admin delete_users
wp permission:user-revoke 123 manage_products
```

**Note**: This only removes directly granted capabilities, not those inherited from roles.

## Role Management

### Assigning Roles

```php
$manager = wppermission()->getUserPermissionManager();

// Replace all roles with new role
$manager->setRole(123, 'shop_manager');

// Add additional role (keeps existing)
$manager->assignRole(123, 'content_editor');

// Check if user has specific role
if ($manager->userHasRole(123, 'shop_manager')) {
    echo 'User is a shop manager';
}
```

### Removing Roles

```php
$manager = wppermission()->getUserPermissionManager();

// Remove specific role
$manager->removeRole(123, 'old_role');

// Get user's current roles
$user = new WP_User(123);
$current_roles = $user->roles;
```

### Via WP-CLI

```bash
# Assign role to user
wp permission:user-assign-role admin shop_manager
wp permission:user-assign-role 123 editor

# Replace all existing roles
wp permission:user-assign-role admin shop_manager --replace

# Remove role from user
wp permission:user-remove-role admin editor
wp permission:user-remove-role 123 shop_manager

# Bulk assign role to multiple users
wp permission:user-bulk-assign shop_manager "admin,editor,123"
```

## User Information and Analysis

### Get User Permission Summary

```php
$manager = wppermission()->getUserPermissionManager();
$summary = $manager->getUserPermissionSummary(123);

// Returns comprehensive user data:
// - user_id, username, display_name, email
// - roles: array of assigned roles
// - capability_count: total number of capabilities
// - all_capabilities: array of all effective capabilities
// - direct_capabilities: capabilities granted directly
// - role_capabilities: capabilities from roles
// - is_super_admin: whether user is network admin
// - registration_date: when user registered
```

### Compare Users

```php
$manager = wppermission()->getUserPermissionManager();
$comparison = $manager->compareUsers(123, 456);

// Returns comparison data:
// - user_1_total: User 1's total capabilities
// - user_2_total: User 2's total capabilities
// - shared_count: Number of shared capabilities
// - shared_capabilities: Array of shared capabilities
// - user_1_only: Capabilities only User 1 has
// - user_2_only: Capabilities only User 2 has
```

### Via WP-CLI

```bash
# Get detailed user information
wp permission:user-info admin
wp permission:user-info 123 --show-capabilities
wp permission:user-info user@example.com --format=json

# Compare two users
wp permission:user-compare admin editor
wp permission:user-compare 123 456 --format=json
```

## Bulk Operations

### Bulk Capability Management

```php
$manager = wppermission()->getUserPermissionManager();

// Grant capability to multiple users
$user_ids = [123, 456, 789];
$results = $manager->bulkGrantCapability($user_ids, 'view_analytics');

// Check results
$success_count = count(array_filter($results));
echo "Granted to {$success_count}/" . count($user_ids) . " users";

// Revoke from multiple users
$results = $manager->bulkRevokeCapability($user_ids, 'old_capability');
```

### Bulk Role Assignment

```php
$manager = wppermission()->getUserPermissionManager();

// Assign role to multiple users
$user_ids = [123, 456, 789];
$results = $manager->bulkAssignRole($user_ids, 'content_editor');

// Get users by role
$editors = $manager->getUsersByRole('editor');
$shop_managers = $manager->getUsersByRole('shop_manager');
```

### Via WP-CLI

```bash
# Bulk operations with dry-run
wp permission:user-bulk-grant manage_products --role=editor --dry-run
wp permission:user-bulk-assign shop_manager "user1,user2,user3" --dry-run

# Execute bulk operations
wp permission:user-bulk-grant view_analytics --role=editor
wp permission:user-bulk-assign contributor "new_user1,new_user2"
```

## Advanced User Management

### Conditional Permissions

```php
// Grant permissions based on user data
function grant_conditional_permissions($user_id) {
    $user = get_user_by('id', $user_id);
    $manager = wppermission()->getUserPermissionManager();

    // Grant based on registration date
    $registration = strtotime($user->user_registered);
    if ($registration < strtotime('-1 year')) {
        $manager->grantCapability($user_id, 'veteran_user_perks');
    }

    // Grant based on post count
    $post_count = count_user_posts($user_id);
    if ($post_count > 100) {
        $manager->grantCapability($user_id, 'prolific_author');
    }

    // Grant based on role combinations
    if ($manager->userHasRole($user_id, 'editor') &&
        $manager->userHasRole($user_id, 'shop_manager')) {
        $manager->grantCapability($user_id, 'hybrid_manager');
    }
}
```

### Permission Inheritance Analysis

```php
function analyze_user_permissions($user_id) {
    $manager = wppermission()->getUserPermissionManager();
    $summary = $manager->getUserPermissionSummary($user_id);

    echo "=== Permission Analysis for User {$user_id} ===\n";
    echo "Username: {$summary['username']}\n";
    echo "Total Capabilities: {$summary['capability_count']}\n";
    echo "Roles: " . implode(', ', $summary['roles']) . "\n";

    // Direct vs inherited capabilities
    $direct_count = count($summary['direct_capabilities']);
    $role_count = count($summary['role_capabilities']);

    echo "Direct Capabilities: {$direct_count}\n";
    echo "Role-based Capabilities: {$role_count}\n";

    // Unique direct capabilities
    $unique_direct = array_diff(
        array_keys($summary['direct_capabilities']),
        array_keys($summary['role_capabilities'])
    );

    if (!empty($unique_direct)) {
        echo "Unique Direct Capabilities:\n";
        foreach ($unique_direct as $cap) {
            echo "  - {$cap}\n";
        }
    }
}
```

### User Permission History

```php
class UserPermissionHistory {

    public static function logCapabilityGrant($user_id, $capability) {
        $log_entry = [
            'action' => 'grant',
            'user_id' => $user_id,
            'capability' => $capability,
            'timestamp' => current_time('mysql'),
            'admin_user' => get_current_user_id()
        ];

        $history = get_user_meta($user_id, 'permission_history', true) ?: [];
        $history[] = $log_entry;
        update_user_meta($user_id, 'permission_history', $history);
    }

    public static function getHistory($user_id) {
        return get_user_meta($user_id, 'permission_history', true) ?: [];
    }

    public static function clearHistory($user_id) {
        delete_user_meta($user_id, 'permission_history');
    }
}

// Hook into permission changes
add_action('wp_permission_capability_granted', function($user_id, $capability) {
    UserPermissionHistory::logCapabilityGrant($user_id, $capability);
}, 10, 2);
```

## Integration Examples

### With User Registration

```php
// Auto-assign permissions to new users
add_action('user_register', function($user_id) {
    $manager = wppermission()->getUserPermissionManager();

    // Default role assignment
    $manager->setRole($user_id, 'basic_user');

    // Grant trial capabilities
    $manager->grantCapability($user_id, 'trial_access');

    // Schedule capability removal
    wp_schedule_single_event(
        time() + (7 * DAY_IN_SECONDS),
        'remove_trial_access',
        [$user_id]
    );
});
```

### With Profile Updates

```php
// Adjust permissions based on profile changes
add_action('profile_update', function($user_id) {
    $user = get_user_by('id', $user_id);
    $manager = wppermission()->getUserPermissionManager();

    // Grant based on bio length
    $bio_length = strlen($user->description);
    if ($bio_length > 500) {
        $manager->grantCapability($user_id, 'detailed_profile');
    }

    // Grant based on website
    if (!empty($user->user_url)) {
        $manager->grantCapability($user_id, 'has_website');
    }
});
```

### With E-commerce

```php
// Manage customer permissions
class CustomerPermissions {

    public static function upgradeToVip($user_id) {
        $manager = wppermission()->getUserPermissionManager();

        // Remove regular customer role
        $manager->removeRole($user_id, 'customer');

        // Assign VIP role
        $manager->assignRole($user_id, 'vip_customer');

        // Grant special capabilities
        $manager->grantCapability($user_id, 'early_access');
        $manager->grantCapability($user_id, 'premium_support');
        $manager->grantCapability($user_id, 'exclusive_discounts');
    }

    public static function handleOrderCompletion($user_id, $order_total) {
        $manager = wppermission()->getUserPermissionManager();

        // High-value customer perks
        if ($order_total > 1000) {
            $manager->grantCapability($user_id, 'high_value_customer');
        }

        // Loyalty points capability
        if (get_user_meta($user_id, 'total_orders', true) > 10) {
            $manager->grantCapability($user_id, 'loyalty_member');
        }
    }
}
```

## Best Practices

### Permission Management

- **Principle of Least Privilege**: Grant minimal necessary permissions
- **Regular Audits**: Review user permissions periodically
- **Role-Based Approach**: Prefer roles over direct capabilities
- **Documentation**: Document special permission grants

### Security Considerations

- Validate user IDs before permission changes
- Log significant permission modifications
- Monitor for privilege escalation attempts
- Use HTTPS for permission-sensitive operations

### Performance Tips

- Cache user permission checks
- Use bulk operations for multiple users
- Avoid excessive database queries in loops
- Consider permission inheritance chains

## Troubleshooting

### Common Issues

**User doesn't have expected capability**
```php
// Debug permission sources
$manager = wppermission()->getUserPermissionManager();
$summary = $manager->getUserPermissionSummary($user_id);

echo "Roles: " . implode(', ', $summary['roles']) . "\n";
echo "Direct capabilities: " . count($summary['direct_capabilities']) . "\n";
echo "Role capabilities: " . count($summary['role_capabilities']) . "\n";

// Check specific capability source
if (isset($summary['direct_capabilities']['manage_products'])) {
    echo "Has capability directly\n";
} elseif (isset($summary['role_capabilities']['manage_products'])) {
    echo "Has capability via role\n";
} else {
    echo "Does not have capability\n";
}
```

**Permission changes not taking effect**
```php
// Clear user capability cache
$user = new WP_User($user_id);
$user->get_role_caps(); // Refreshes capability cache

// Or force WordPress to rebuild user object
clean_user_cache($user_id);
```

### Debug Mode

```php
// Enable user permission logging
add_action('wp_permission_capability_granted', function($user_id, $capability) {
    error_log("Granted {$capability} to user {$user_id}");
}, 10, 2);

add_action('wp_permission_capability_revoked', function($user_id, $capability) {
    error_log("Revoked {$capability} from user {$user_id}");
}, 10, 2);

add_action('wp_permission_role_assigned', function($user_id, $role) {
    error_log("Assigned role {$role} to user {$user_id}");
}, 10, 2);
```

## Next Steps

- [Learn about Middleware](middleware.md)
- [Explore Role Management](roles.md)
- [Check out Capability Management](capabilities.md)
- [Review CLI Commands](cli.md)
