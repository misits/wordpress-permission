# WordPress Permission (WP-Permission)

A comprehensive permission management system for WordPress with **capabilities, roles, user management, and middleware support** for building secure, enterprise-grade applications.

## Features

- 🔐 **Advanced Capabilities** - Custom capabilities with groups, descriptions, and metadata
- 👥 **Smart Role Management** - Create, clone, and manage roles with inheritance
- 🎯 **User Permissions** - Grant/revoke capabilities, bulk operations, and detailed analytics
- 🛡️ **Middleware System** - Composable permission checks for routes and actions
- ⚡ **WP-CLI Integration** - Powerful CLI commands for all operations
- 🚀 **Developer-Friendly** - Global helpers, fluent API, and comprehensive hooks
- 📊 **Analytics & Monitoring** - Track usage, performance, and security metrics

## Installation

Include WP-Permission in your WordPress theme or plugin:

```php
// In your theme's functions.php
require_once get_template_directory() . '/vendor/wordpress-permission/bootstrap.php';
```

## Zero-Configuration Setup

WP-Permission automatically integrates with WordPress and provides immediate access to enhanced permission management:

### Global Helper Functions
```php
// Permission checks (works immediately)
if (can('manage_products')) {
    // User can manage products
}

if (has_role('shop_manager')) {
    // User is a shop manager
}

// Grant/revoke capabilities
grant('manage_products', $user_id);
revoke('old_capability', $user_id);
```

### Quick Start - Create Your First Custom Permission
```php
// 1. Create custom capability
wppermission()->createCapability('manage_analytics', 'Access analytics dashboard', 'analytics');

// 2. Create custom role with capabilities
wppermission()->createRole('analytics_manager', 'Analytics Manager', [
    'read' => true,
    'manage_analytics' => true,
    'view_reports' => true
]);

// 3. Assign role to user
assign_role('analytics_manager', $user_id);
```

## Middleware Integration

### With WordPress Routes
```php
// Protect routes with permission middleware
Route::get('/admin/analytics', 'AnalyticsController@index')
    ->middleware(wp_permission_require_cap('view_analytics'));

Route::post('/api/products', 'ProductController@store')
    ->middleware(wp_permission_require_all([
        wp_permission_require_cap('manage_products'),
        wp_permission_require_role('shop_manager')
    ]));
```

### Advanced Middleware Composition
```php
$manager = wppermission()->getMiddlewareManager();

// Complex permission logic
$middleware = $manager->requireAll([
    $manager->requireCapability('manage_products'),
    $manager->requireAny([
        $manager->requireRole('administrator'),
        $manager->requireRole('shop_manager')
    ]),
    $manager->requireTimeWindow('09:00', '17:00'),
    $manager->requireRateLimit(100, 3600)
]);

if ($middleware()) {
    // All conditions met
}
```

### AJAX & REST API Protection
```php
// Protect AJAX endpoints
add_action('wp_ajax_manage_products', function() {
    wp_permission_check_ajax('manage_products');
    // Your secure AJAX logic
});

// Protect REST API endpoints
register_rest_route('api/v1', '/products', [
    'methods' => 'POST',
    'callback' => 'create_product',
    'permission_callback' => function() {
        return can('manage_products');
    }
]);
```

## WP-CLI Commands

Powerful command-line tools for permission management:

```bash
# Capability management
wp permission:capability-create manage_products --description="Manage product catalog"
wp permission:capability-list --type=custom --format=json
wp permission:capability-info manage_products

# Role management
wp permission:role-create shop_manager "Shop Manager" --capabilities="edit_posts,manage_products"
wp permission:role-clone editor content_editor "Content Editor"
wp permission:role-list --type=custom

# User management
wp permission:user-grant admin manage_products
wp permission:user-assign-role user123 shop_manager
wp permission:user-bulk-grant view_analytics --role=editor

# Get help
wp permission:help
wp help permission:capability-create
```

## Integration with Other Libraries

WP-Permission works seamlessly with your complete WordPress stack:

### With WordPress Routes
```php
// Protect routes with permission middleware
Route::get('/admin/analytics', 'AnalyticsController@index')
    ->middleware(wp_permission_require_cap('view_analytics'));
```

### With WordPress ORM
```php
// Scope models based on permissions
class Product extends WordPressModel {
    public function scopeUserCanEdit($query) {
        if (!can('edit_others_products')) {
            $query->where('author_id', get_current_user_id());
        }
    }
}
```

### With WordPress Skin
```php
// Conditional template rendering
if (can('manage_products')) {
    skin_component('admin.product-manager', ['products' => $products]);
} else {
    skin_component('access-denied', ['message' => 'Permission required']);
}
```

## Documentation

For comprehensive documentation, see the [docs/](docs/) directory:

- [Installation](docs/installation.md) - Setup and configuration guide
- [Capabilities](docs/capabilities.md) - Creating and managing custom capabilities
- [Roles](docs/roles.md) - Role creation, cloning, and management
- [Users](docs/users.md) - User permission management and analytics
- [Middleware](docs/middleware.md) - Permission middleware and composition
- [CLI Commands](docs/cli.md) - Complete WP-CLI reference
- [API Reference](docs/api.md) - Full API documentation
- [Examples](docs/examples.md) - Real-world implementation examples

## Philosophy

WP-Permission follows a **security-first, developer-friendly** approach:
- Security by default with granular permissions
- Zero-configuration setup with intelligent defaults
- Composable middleware for complex permission logic
- WordPress-native integration with modern development patterns

## Requirements

- WordPress 5.0+
- PHP 8.0+
- WP-CLI 2.0+ (for CLI commands)

## License

Open-source software, following WordPress GPL licensing.
