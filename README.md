# WordPress Permission

A comprehensive permission management system for WordPress with capabilities, roles, user management, and middleware support.

## Features

- **Capability Management**: Create, manage, and track custom capabilities with groups and descriptions
- **Role Management**: Create, clone, and manage WordPress roles with advanced functionality  
- **User Permissions**: Grant/revoke capabilities, assign/remove roles, bulk operations
- **Middleware System**: Permission middleware for route and action protection
- **CLI Commands**: Powerful WP-CLI commands for all operations
- **Developer-Friendly**: Global helper functions, fluent API, and comprehensive hooks

## Quick Start

### Installation

```php
// In your theme's functions.php or plugin file
require_once 'path/to/wp-permission/bootstrap.php';
```

### Basic Usage

```php
// Check if user has capability
if (can('manage_products')) {
    // User can manage products
}

// Check if user has role
if (has_role('shop_manager')) {
    // User is a shop manager
}

// Grant capability to user
grant('manage_products', $user_id);

// Create a new capability
wppermission()->createCapability('manage_analytics', 'Manage analytics dashboard', 'analytics');

// Create a new role
wppermission()->createRole('content_manager', 'Content Manager', ['edit_posts', 'publish_posts']);
```

## Core API

### Permission Class

```php
// Get main instance
$permission = wppermission();

// Static access to managers
Permission::capabilities()->create('new_cap', 'Description');
Permission::roles()->create('new_role', 'Display Name');
Permission::users()->grantCapability($user_id, 'capability');
Permission::middleware()->requireCapability('manage_options');
```

### Capability Management

```php
// Create capability
wppermission()->createCapability('manage_products', 'Manage product catalog', 'ecommerce');

// Check if exists
if (wppermission()->capabilityExists('manage_products')) {
    // Capability exists
}

// Get all custom capabilities
$custom_caps = wppermission()->getCustomCapabilities();

// Delete capability
wppermission()->deleteCapability('old_capability');
```

### Role Management

```php
// Create role
wppermission()->createRole('shop_manager', 'Shop Manager', [
    'edit_posts' => true,
    'manage_products' => true
]);

// Clone existing role
wppermission()->cloneRole('editor', 'content_editor', 'Content Editor');

// Add capability to role
wppermission()->grant('new_capability', 'shop_manager', true); // true = is_role

// Get role capabilities
$capabilities = Permission::roles()->getCapabilities('shop_manager');
```

### User Permissions

```php
// Grant capability to user
grant('manage_products', $user_id);

// Revoke capability
revoke('manage_products', $user_id);

// Assign role
wppermission()->assignRole($user_id, 'shop_manager');

// Get user permission summary
$summary = wppermission()->getUserPermissionManager()->getUserPermissionSummary($user_id);

// Bulk operations
wppermission()->getUserPermissionManager()->bulkGrantCapability([$user1, $user2], 'capability');
```

### Middleware System

```php
// Register custom middleware
wppermission()->middleware('custom_check', function($user_id) {
    return some_custom_logic($user_id);
});

// Use built-in middleware
$can_edit = wppermission()->requireCapability('edit_posts')();
$is_admin = wppermission()->requireRole('administrator')();
$is_logged_in = wppermission()->requireLogin()();

// Composite middleware (AND logic)
$middleware = wppermission()->requireAll([
    'login',
    ['capability', 'edit_posts'],
    ['role', 'editor']
]);

// Composite middleware (OR logic)  
$middleware = wppermission()->requireAny([
    ['role', 'administrator'],
    ['capability', 'manage_options']
]);

// Advanced middleware
$rate_limit = wppermission()->getMiddlewareManager()->requireRateLimit(10, 3600); // 10 requests per hour
$ip_restrict = wppermission()->getMiddlewareManager()->requireIP(['192.168.1.0/24']);
$time_restrict = wppermission()->getMiddlewareManager()->requireTime('09:00', '17:00', 'UTC');
```

## Helper Functions

### Global Functions

```php
// Permission checks
can($capability, $user_id = null)
has_role($role, $user_id = null)
grant($capability, $user_or_role_id, $is_role = false)
revoke($capability, $user_or_role_id, $is_role = false)

// Capability management
wp_permission_create_capability($capability, $description, $group)
wp_permission_capability_exists($capability)
wp_permission_get_custom_capabilities()

// Role management  
wp_permission_create_role($role, $display_name, $capabilities)
wp_permission_role_exists($role)
wp_permission_get_custom_roles()

// User management
wp_permission_user_summary($user_id)
wp_permission_get_users_by_capability($capability)
wp_permission_get_users_by_role($role)

// Middleware helpers
wp_permission_require_cap($capability)
wp_permission_require_role($role)
wp_permission_require_login()

// Error handling
wp_permission_check_or_die($capability, $message, $user_id)
wp_permission_check_ajax($capability, $user_id)
```

### Utility Functions

```php
// Statistics and analysis
wp_permission_capability_stats($capability)
wp_permission_compare_users($user_id_1, $user_id_2)
wp_permission_get_capability_groups()
wp_permission_get_role_hierarchy()

// Bulk operations
wp_permission_bulk_assign_role($user_ids, $role)
wp_permission_bulk_grant_capability($user_ids, $capability)

// Context information
wp_permission_get_context() // Current page/action context
```

## WP-CLI Commands

### Capability Commands

```bash
# Create capability
wp wppermission capability:create manage_products --description="Manage product catalog" --group="ecommerce"

# List capabilities
wp wppermission capability:list --type=custom --format=table

# Get capability info
wp wppermission capability:info manage_products

# Get usage statistics
wp wppermission capability:stats edit_posts

# Delete capability
wp wppermission capability:delete old_capability --yes
```

### Role Commands

```bash
# Create role
wp wppermission role:create shop_manager "Shop Manager" --capabilities="edit_posts,manage_products"

# Clone role
wp wppermission role:clone editor content_editor "Content Editor"

# List roles
wp wppermission role:list --type=custom

# Add capability to role
wp wppermission role:add-cap shop_manager view_analytics

# Remove capability from role
wp wppermission role:remove-cap shop_manager delete_users

# Get role info
wp wppermission role:info shop_manager --show-capabilities

# Delete role
wp wppermission role:delete old_role --yes
```

### User Commands

```bash
# Grant capability to user
wp wppermission user:grant admin manage_products

# Revoke capability
wp wppermission user:revoke admin delete_users

# Assign role
wp wppermission user:assign-role user123 shop_manager

# Remove role
wp wppermission user:remove-role user123 old_role

# Get user info
wp wppermission user:info admin --show-capabilities --format=json

# Compare users
wp wppermission user:compare admin editor

# Bulk operations
wp wppermission user:bulk-grant view_analytics --role=editor
wp wppermission user:bulk-assign shop_manager "user1,user2,user3"
```

### Help

```bash
# General help
wp wppermission help

# Command-specific help
wp help wppermission capability:create
wp help wppermission role:list
wp help wppermission user:bulk-grant
```

## Advanced Features

### Event Hooks

```php
// Capability events
add_action('wp_permission_capability_created', function($capability, $description, $group) {
    // Capability was created
});

add_action('wp_permission_capability_deleted', function($capability) {
    // Capability was deleted
});

// Role events
add_action('wp_permission_role_created', function($role, $display_name, $capabilities) {
    // Role was created
});

add_action('wp_permission_capability_added_to_role', function($role, $capability) {
    // Capability added to role
});

// User events
add_action('wp_permission_capability_granted_to_user', function($user_id, $capability) {
    // Capability granted to user
});

add_action('wp_permission_role_assigned_to_user', function($user_id, $role) {
    // Role assigned to user
});

// Middleware events
add_action('wp_permission_middleware_registered', function($name, $callback) {
    // Middleware was registered
});
```

### Custom Middleware

```php
// Register time-based access
wppermission()->middleware('business_hours', function() {
    $hour = (int) date('H');
    return $hour >= 9 && $hour <= 17;
});

// Register IP-based access
wppermission()->middleware('internal_network', function() {
    $ip = $_SERVER['REMOTE_ADDR'];
    return strpos($ip, '192.168.') === 0;
});

// Use custom middleware
if (wppermission()->applyMiddleware('business_hours')) {
    // Business hours access
}
```

### Integration Examples

#### With WordPress Routes

```php
// Protect routes with middleware
Route::get('/admin/analytics', 'AnalyticsController@index')
    ->middleware(wp_permission_require_cap('view_analytics'));

Route::post('/admin/products', 'ProductController@store')
    ->middleware(wp_permission_require_all([
        'login',
        ['capability', 'manage_products']
    ]));
```

#### AJAX Protection

```php
add_action('wp_ajax_manage_products', function() {
    wp_permission_check_ajax('manage_products');
    
    // Your AJAX logic here
});
```

#### REST API Protection  

```php
add_action('rest_api_init', function() {
    register_rest_route('myapi/v1', '/products', [
        'methods' => 'POST',
        'callback' => 'create_product',
        'permission_callback' => function() {
            return can('manage_products');
        }
    ]);
});
```

## Architecture

### Trait-Based Design

The Permission class uses traits for modular functionality:

- `HandlesCapabilities` - Capability management methods
- `HandlesRoles` - Role management methods  
- `HandlesUsers` - User permission methods
- `HandlesMiddleware` - Middleware functionality

### Manager Classes

- `CapabilityManager` - Core capability operations
- `RoleManager` - Role creation and management
- `UserPermissionManager` - User-specific permissions
- `MiddlewareManager` - Middleware registration and execution

### Database Storage

- Custom capabilities stored in `wp_options` table
- Custom roles tracked separately from WordPress roles
- Uses WordPress core functions for compatibility
- Automatic cleanup on capability/role deletion

## Configuration

### Mode Detection

```php
// Theme mode (default)
define('WPPERMISSION_MODE', 'theme');

// Plugin mode
define('WPPERMISSION_MODE', 'plugin');
```

### Constants

```php
WPPERMISSION_VERSION  // Library version
WPPERMISSION_DIR      // Library directory
WPPERMISSION_SRC_DIR  // Source directory
WPPERMISSION_MODE     // Operating mode
```

## Examples

See the [examples](examples/) directory for complete implementation examples:

- Basic permission setup
- Role-based access control
- Middleware integration
- Custom capability workflows
- CLI automation scripts

## Documentation

- [Installation Guide](docs/installation.md)
- [Capability Management](docs/capabilities.md)
- [Role Management](docs/roles.md)
- [User Permissions](docs/users.md)
- [Middleware System](docs/middleware.md)
- [CLI Reference](docs/cli.md)
- [API Reference](docs/api.md)
- [Examples](docs/examples.md)

## License

MIT License - see LICENSE file for details.

## Contributing

Contributions welcome! Please read CONTRIBUTING.md for guidelines.

---

WordPress Permission v1.0.0 - Modern permission management for WordPress.