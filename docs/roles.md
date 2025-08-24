# Role Management

Complete guide to managing WordPress roles with wp-permission.

## Overview

Roles are collections of capabilities that can be assigned to users. The wp-permission library extends WordPress's role system with advanced management, role cloning, and enhanced querying capabilities.

## Core Concepts

### WordPress Default Roles

WordPress includes these built-in roles:
- **Administrator**: Full access to everything
- **Editor**: Manage posts, pages, and comments
- **Author**: Create and manage own posts
- **Contributor**: Create posts but cannot publish
- **Subscriber**: Basic read access

### Custom Roles

Create application-specific roles:
- **Shop Manager**: E-commerce management
- **Content Moderator**: Review and approve content
- **Analytics Viewer**: Access to reports and statistics
- **Customer Support**: Help desk capabilities

## Creating Roles

### Basic Creation

```php
// Create a simple role
wppermission()->createRole('shop_manager', 'Shop Manager');

// Create with initial capabilities
wppermission()->createRole('shop_manager', 'Shop Manager', [
    'edit_posts' => true,
    'manage_products' => true,
    'view_analytics' => true
]);
```

### Via WP-CLI

```bash
# Basic role
wp borps permission:role-create shop_manager "Shop Manager"

# With capabilities
wp borps permission:role-create product_editor "Product Editor" \
  --capabilities="edit_posts,publish_posts,manage_products"

# Clone existing role
wp borps permission:role-create custom_editor "Custom Editor" \
  --clone=editor
```

### Role Manager API

```php
$manager = wppermission()->getRoleManager();

// Create role
$manager->create('custom_role', 'Custom Role', [
    'read' => true,
    'edit_posts' => true
]);

// Check if exists
if ($manager->exists('shop_manager')) {
    echo 'Role exists';
}

// Get role display name
$display_name = $manager->getDisplayName('shop_manager');
```

## Cloning Roles

### Clone Existing Roles

```php
$manager = wppermission()->getRoleManager();

// Clone a role with all its capabilities
$manager->clone('editor', 'content_manager', 'Content Manager');

// Clone and modify
$manager->clone('author', 'guest_author', 'Guest Author');
$manager->removeCapability('guest_author', 'delete_posts');
```

### Via WP-CLI

```bash
# Clone editor role
wp borps permission:role-clone editor content_manager "Content Manager"

# Clone administrator for backup
wp borps permission:role-clone administrator super_admin "Super Administrator"
```

## Managing Role Capabilities

### Add Capabilities

```php
$manager = wppermission()->getRoleManager();

// Add single capability
$manager->addCapability('shop_manager', 'manage_products');

// Add multiple capabilities
$capabilities = ['view_analytics', 'export_data', 'manage_inventory'];
foreach ($capabilities as $cap) {
    $manager->addCapability('shop_manager', $cap);
}
```

### Remove Capabilities

```php
$manager = wppermission()->getRoleManager();

// Remove capability
$manager->removeCapability('shop_manager', 'delete_users');

// Check before removing
if ($manager->hasCapability('shop_manager', 'manage_options')) {
    $manager->removeCapability('shop_manager', 'manage_options');
}
```

### Via WP-CLI

```bash
# Add capability to role
wp borps permission:role-add-cap editor manage_products
wp borps permission:role-add-cap shop_manager view_analytics

# Remove capability from role
wp borps permission:role-remove-cap editor delete_posts
wp borps permission:role-remove-cap shop_manager manage_options
```

## Querying Roles

### List Roles

```php
$manager = wppermission()->getRoleManager();

// Get all roles
$all_roles = $manager->getAll();

// Get only custom roles
$custom_roles = $manager->getCustomRoles();

// Check role type
$is_custom = $manager->isCustomRole('shop_manager');
```

### Get Role Information

```php
$manager = wppermission()->getRoleManager();

// Get role capabilities
$capabilities = $manager->getCapabilities('shop_manager');

// Get roles that have specific capability
$roles = $manager->getRolesWithCapability('manage_products');

// Count users with role
$user_count = count(get_users(['role' => 'shop_manager']));
```

### Via WP-CLI

```bash
# List all roles
wp borps permission:role-list

# Filter by type
wp borps permission:role-list --type=custom
wp borps permission:role-list --type=default

# Different output formats
wp borps permission:role-list --format=json
wp borps permission:role-list --format=csv

# Detailed role information
wp borps permission:role-info shop_manager
wp borps permission:role-info editor --show-capabilities
```

## Deleting Roles

### Remove Custom Roles

```php
$manager = wppermission()->getRoleManager();

// Delete a custom role
if ($manager->delete('old_role')) {
    echo 'Role deleted successfully';
}
```

### Via WP-CLI

```bash
# Delete with confirmation
wp borps permission:role-delete shop_manager

# Skip confirmation
wp borps permission:role-delete shop_manager --yes
```

**Important**:
- Cannot delete WordPress default roles
- Users with deleted roles lose those permissions
- Consider reassigning users before deletion

## Role Hierarchies

### Understanding WordPress Role Hierarchy

WordPress doesn't have built-in role hierarchy, but capabilities create implicit levels:

```php
// Administrator (highest)
// - All capabilities including manage_options

// Editor
// - Content management capabilities
// - Cannot manage users or settings

// Author
// - Can create and edit own posts
// - Cannot edit others' content

// Contributor
// - Can create posts but not publish
// - Limited content capabilities

// Subscriber (lowest)
// - Basic read access only
```

### Creating Custom Hierarchies

```php
// Create hierarchical roles
function create_content_hierarchy() {
    $manager = wppermission()->getRoleManager();

    // Content Viewer (lowest)
    $manager->create('content_viewer', 'Content Viewer', [
        'read' => true
    ]);

    // Content Editor
    $manager->create('content_editor', 'Content Editor', [
        'read' => true,
        'edit_posts' => true,
        'publish_posts' => true
    ]);

    // Content Manager (highest)
    $manager->create('content_manager', 'Content Manager', [
        'read' => true,
        'edit_posts' => true,
        'publish_posts' => true,
        'edit_others_posts' => true,
        'delete_posts' => true,
        'manage_categories' => true
    ]);
}
```

## Role Templates

### Common Role Patterns

```php
class RoleTemplates {

    public static function createEcommerceRoles() {
        $manager = wppermission()->getRoleManager();

        // Shop Manager
        $manager->create('shop_manager', 'Shop Manager', [
            'read' => true,
            'edit_posts' => true,
            'manage_products' => true,
            'view_analytics' => true,
            'manage_orders' => true,
            'manage_inventory' => true
        ]);

        // Product Editor
        $manager->create('product_editor', 'Product Editor', [
            'read' => true,
            'manage_products' => true,
            'edit_posts' => true
        ]);

        // Customer Support
        $manager->create('customer_support', 'Customer Support', [
            'read' => true,
            'view_orders' => true,
            'edit_orders' => true,
            'contact_customers' => true
        ]);
    }

    public static function createContentRoles() {
        $manager = wppermission()->getRoleManager();

        // Content Moderator
        $manager->create('content_moderator', 'Content Moderator', [
            'read' => true,
            'edit_posts' => true,
            'edit_others_posts' => true,
            'moderate_comments' => true,
            'edit_published_posts' => true
        ]);

        // SEO Specialist
        $manager->create('seo_specialist', 'SEO Specialist', [
            'read' => true,
            'edit_posts' => true,
            'manage_categories' => true,
            'manage_links' => true,
            'view_analytics' => true
        ]);
    }
}
```

## Role Assignments

### Assign Roles to Users

```php
$user_manager = wppermission()->getUserPermissionManager();

// Assign single role (replaces existing)
$user_manager->setRole(123, 'shop_manager');

// Add additional role (keeps existing)
$user_manager->assignRole(123, 'content_editor');

// Remove specific role
$user_manager->removeRole(123, 'old_role');

// Check if user has role
if ($user_manager->userHasRole(123, 'shop_manager')) {
    echo 'User is a shop manager';
}
```

### Bulk Role Operations

```php
$user_manager = wppermission()->getUserPermissionManager();

// Assign role to multiple users
$user_ids = [123, 456, 789];
$results = $user_manager->bulkAssignRole($user_ids, 'shop_manager');

// Get all users with specific role
$shop_managers = $user_manager->getUsersByRole('shop_manager');
```

## Advanced Usage

### Conditional Role Creation

```php
// Create roles based on environment
if (wp_get_environment_type() === 'development') {
    wppermission()->createRole('developer', 'Developer', [
        'manage_options' => true,
        'edit_files' => true,
        'debug_mode' => true
    ]);
}

// Create roles based on installed plugins
if (class_exists('WooCommerce')) {
    create_woocommerce_roles();
}
```

### Dynamic Role Capabilities

```php
// Modify role capabilities based on conditions
add_action('init', function() {
    $manager = wppermission()->getRoleManager();

    // Add seasonal capabilities
    if (is_holiday_season()) {
        $manager->addCapability('shop_manager', 'manage_promotions');
    }

    // Add capabilities based on license
    if (has_premium_license()) {
        $manager->addCapability('editor', 'advanced_editing');
    }
});
```

### Role Backup and Restore

```php
class RoleBackup {

    public static function backup($role) {
        $manager = wppermission()->getRoleManager();
        $capabilities = $manager->getCapabilities($role);

        update_option("role_backup_{$role}", [
            'capabilities' => $capabilities,
            'display_name' => $manager->getDisplayName($role),
            'backup_date' => current_time('mysql')
        ]);
    }

    public static function restore($role) {
        $backup = get_option("role_backup_{$role}");
        if (!$backup) return false;

        $manager = wppermission()->getRoleManager();

        // Remove current role
        $manager->delete($role);

        // Recreate from backup
        $manager->create(
            $role,
            $backup['display_name'],
            $backup['capabilities']
        );

        return true;
    }
}
```

## Integration Examples

### With Membership Plugins

```php
// Auto-assign roles based on membership level
add_action('membership_level_changed', function($user_id, $new_level) {
    $role_map = [
        'bronze' => 'basic_member',
        'silver' => 'premium_member',
        'gold' => 'vip_member'
    ];

    if (isset($role_map[$new_level])) {
        wppermission()->getUserPermissionManager()
            ->setRole($user_id, $role_map[$new_level]);
    }
});
```

### With Custom Post Types

```php
// Create roles for custom post type management
function register_product_roles() {
    wppermission()->createRole('product_manager', 'Product Manager', [
        'read' => true,
        'edit_products' => true,
        'publish_products' => true,
        'edit_others_products' => true,
        'delete_products' => true
    ]);
}
add_action('init', 'register_product_roles');
```

## Best Practices

### Role Design Principles

- **Single Responsibility**: Each role should have a clear purpose
- **Minimal Privileges**: Grant only necessary capabilities
- **Logical Grouping**: Group related capabilities together
- **Descriptive Names**: Use clear, descriptive role names

### Security Considerations

- Regular role audits
- Remove unused roles
- Monitor role capability changes
- Document role purposes

### Performance Tips

- Cache role checks in heavy operations
- Use specific roles rather than capability checks when possible
- Avoid creating too many granular roles

## Troubleshooting

### Common Issues

**Role not appearing**
```php
// Check if role was created properly
$manager = wppermission()->getRoleManager();
if ($manager->exists('custom_role')) {
    echo 'Role exists';
} else {
    echo 'Role creation failed';
}
```

**User not getting role capabilities**
```php
// Debug user role assignments
$user = new WP_User(123);
print_r($user->roles);

// Check effective capabilities
$summary = wppermission()->getUserPermissionManager()
    ->getUserPermissionSummary(123);
print_r($summary['all_capabilities']);
```

### Debug Mode

```php
// Log role operations
add_action('wp_permission_role_created', function($role, $display_name) {
    error_log("Created role: {$role} ({$display_name})");
}, 10, 2);

add_action('wp_permission_role_deleted', function($role) {
    error_log("Deleted role: {$role}");
});
```

## Next Steps

- [Learn about User Permissions](users.md)
- [Explore Capability Management](capabilities.md)
- [Check out Middleware](middleware.md)
- [Review CLI Commands](cli.md)
