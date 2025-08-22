# Capability Management

Complete guide to managing WordPress capabilities with wp-permission.

## Overview

Capabilities define what users can do within WordPress. The wp-permission library extends WordPress's native capability system with enhanced management, custom capabilities, and advanced querying.

## Core Concepts

### WordPress Default Capabilities

WordPress includes built-in capabilities like:
- `edit_posts`, `publish_posts`, `delete_posts`
- `manage_options`, `manage_users`
- `upload_files`, `edit_files`
- `read`, `level_0` through `level_10`

### Custom Capabilities

Create application-specific capabilities:
- `manage_products`, `view_analytics`
- `approve_comments`, `moderate_content`
- `export_data`, `import_data`

### Capability Groups

Organize capabilities by functionality:
- **ecommerce**: Product and order management
- **analytics**: Reporting and statistics
- **content**: Content creation and moderation
- **system**: Administrative functions

## Creating Capabilities

### Basic Creation

```php
// Create a simple capability
wppermission()->createCapability('manage_products');

// With description and group
wppermission()->createCapability(
    'view_analytics',
    'Access analytics dashboard',
    'analytics'
);
```

### Via WP-CLI

```bash
# Basic capability
wp wppermission capability:create manage_products

# With metadata
wp wppermission capability:create view_analytics \
  --description="View analytics dashboard" \
  --group="analytics"
```

### Capability Manager API

```php
$manager = wppermission()->getCapabilityManager();

// Create capability
$manager->create('custom_capability', 'Description', 'group');

// Check if exists
if ($manager->exists('manage_products')) {
    echo 'Capability exists';
}

// Get capability info
$info = $manager->getInfo('manage_products');
echo $info['description']; // Capability description
echo $info['group'];       // Capability group
echo $info['type'];        // 'custom' or 'default'
```

## Querying Capabilities

### List All Capabilities

```php
$manager = wppermission()->getCapabilityManager();

// Get all capabilities
$all_caps = $manager->getAll();

// Get only custom capabilities
$custom_caps = $manager->getCustomCapabilities();

// Get by group
$analytics_caps = $manager->getByGroup('analytics');
```

### Via WP-CLI

```bash
# List all capabilities
wp wppermission capability:list

# Filter by type
wp wppermission capability:list --type=custom
wp wppermission capability:list --type=default

# Filter by group
wp wppermission capability:list --group=analytics

# Different output formats
wp wppermission capability:list --format=json
wp wppermission capability:list --format=csv
```

## Capability Information

### Get Detailed Info

```php
$manager = wppermission()->getCapabilityManager();
$info = $manager->getInfo('manage_products');

// Returns array with:
// - description: Capability description
// - group: Capability group
// - type: 'custom' or 'default'
// - created_at: Creation timestamp (custom only)
// - created_by: Creator user ID (custom only)
```

### Check Usage

```php
$role_manager = wppermission()->getRoleManager();

// Get roles that have this capability
$roles = $role_manager->getRolesWithCapability('manage_products');

// Get users with this capability
$users = $user_manager->getUsersWithCapability('manage_products');
```

### Via WP-CLI

```bash
# Detailed capability information
wp wppermission capability:info manage_products

# Usage statistics
wp wppermission capability:stats manage_products
```

## Capability Validation

### Check Existence

```php
// Check if capability exists
if (wp_permission_capability_exists('manage_products')) {
    echo 'Capability is available';
}

// Using manager
$manager = wppermission()->getCapabilityManager();
if ($manager->exists('manage_products')) {
    echo 'Capability exists';
}
```

### Validate Names

```php
// Capability names are automatically sanitized
$clean_name = $manager->sanitizeCapabilityName('Manage Products!');
// Result: 'manage_products'
```

## Deleting Capabilities

### Remove Custom Capabilities

```php
$manager = wppermission()->getCapabilityManager();

// Delete a custom capability
if ($manager->delete('old_capability')) {
    echo 'Capability deleted';
}
```

### Via WP-CLI

```bash
# Delete with confirmation
wp wppermission capability:delete old_capability

# Skip confirmation
wp wppermission capability:delete old_capability --yes
```

**Note**: You cannot delete WordPress default capabilities.

## Capability Inheritance

### Role-Based Inheritance

```php
// Users inherit capabilities from their roles
$user = new WP_User(123);
$roles = $user->roles; // ['editor', 'shop_manager']

// Check inherited capabilities
if (can('edit_posts', 123)) {
    echo 'User can edit posts via role inheritance';
}
```

### Direct User Capabilities

```php
// Grant capability directly to user
grant('special_permission', 123);

// This capability is independent of roles
// User keeps it even if roles change
```

## Advanced Usage

### Conditional Capabilities

```php
// Create capabilities based on conditions
if (is_multisite()) {
    wppermission()->createCapability('manage_network_products');
}

if (class_exists('WooCommerce')) {
    wppermission()->createCapability('manage_woo_products');
}
```

### Capability Dependencies

```php
// Check prerequisites before granting
function grant_advanced_capability($user_id) {
    if (can('manage_products', $user_id)) {
        grant('advanced_product_management', $user_id);
    }
}
```

### Temporary Capabilities

```php
// Grant capability for specific operation
function perform_admin_task() {
    $user_id = get_current_user_id();
    
    // Store current capabilities
    $had_cap = can('manage_options', $user_id);
    
    if (!$had_cap) {
        grant('manage_options', $user_id);
    }
    
    // Perform admin operation
    do_admin_stuff();
    
    // Restore previous state
    if (!$had_cap) {
        revoke('manage_options', $user_id);
    }
}
```

## Integration Examples

### With WordPress Hooks

```php
// Auto-create capabilities on plugin activation
add_action('init', function() {
    if (!wp_permission_capability_exists('manage_products')) {
        wppermission()->createCapability(
            'manage_products',
            'Manage product catalog',
            'ecommerce'
        );
    }
});
```

### With REST API

```php
// Use in REST API permission callbacks
register_rest_route('myapi/v1', '/products', [
    'methods' => 'POST',
    'callback' => 'create_product_endpoint',
    'permission_callback' => function() {
        return can('manage_products');
    }
]);
```

### With Admin Pages

```php
// Control admin menu visibility
add_action('admin_menu', function() {
    if (can('view_analytics')) {
        add_menu_page(
            'Analytics',
            'Analytics', 
            'read', // Lower requirement, checked above
            'analytics',
            'show_analytics_page'
        );
    }
});
```

## Best Practices

### Naming Conventions

- Use descriptive, action-based names: `manage_products` not `products`
- Use underscores, not hyphens: `view_analytics` not `view-analytics`
- Prefix with your application: `myapp_manage_data`

### Security Considerations

- Always validate capability names
- Use principle of least privilege
- Regular audit of custom capabilities
- Document capability purposes

### Performance Tips

- Cache capability checks in long-running operations
- Use specific capabilities rather than broad ones
- Group related capabilities logically

## Troubleshooting

### Common Issues

**Capability not found**
```php
// Check if capability was created properly
$info = wppermission()->getCapabilityManager()->getInfo('missing_cap');
if (!$info) {
    echo 'Capability does not exist';
}
```

**Permission denied errors**
```php
// Debug user capabilities
$summary = wppermission()->getUserPermissionManager()
    ->getUserPermissionSummary($user_id);
print_r($summary['all_capabilities']);
```

### Debug Mode

```php
// Enable capability logging
add_action('wp_permission_capability_created', function($capability, $group) {
    error_log("Created capability: {$capability} in group: {$group}");
}, 10, 2);

add_action('wp_permission_capability_deleted', function($capability) {
    error_log("Deleted capability: {$capability}");
});
```

## Next Steps

- [Learn about Role Management](roles.md)
- [Explore User Permissions](users.md)
- [Check out Middleware](middleware.md)
- [Review CLI Commands](cli.md)