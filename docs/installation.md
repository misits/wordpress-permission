# Installation Guide

Complete setup guide for WordPress Permission library.

## Requirements

- WordPress 5.0+
- PHP 8.0+
- WP-CLI (for command-line tools)

## Installation Methods

### Theme Integration

```php
// In your theme's functions.php
require_once get_template_directory() . '/lib/wp-permission/bootstrap.php';
```

### Plugin Integration

```php
// In your plugin's main file
require_once plugin_dir_path(__FILE__) . 'lib/wp-permission/bootstrap.php';
```

### Composer (if using Composer)

```bash
# Add to your composer.json
{
    "autoload": {
        "files": [
            "lib/wp-permission/bootstrap.php"
        ]
    }
}
```

## Configuration

### Mode Setting

The library automatically detects your environment, but you can set it explicitly:

```php
// Theme mode (default)
define('WPPERMISSION_MODE', 'theme');

// Plugin mode
define('WPPERMISSION_MODE', 'plugin');
```

### Constants

Available constants after initialization:

```php
WPPERMISSION_VERSION  // Library version (1.0.0)
WPPERMISSION_DIR      // Library directory path
WPPERMISSION_SRC_DIR  // Source directory path
WPPERMISSION_MODE     // Operating mode (theme/plugin)
```

## Verification

### Check Installation

```php
// Verify the library is loaded
if (function_exists('wppermission')) {
    echo 'WordPress Permission is loaded!';
}

// Check version
echo 'Version: ' . WPPERMISSION_VERSION;
```

### WP-CLI Verification

```bash
# List available commands
wp permission:help
```

## Quick Setup

### 1. Basic Permission Check

```php
// Check if current user can manage products
if (can('manage_products')) {
    echo 'You can manage products!';
}
```

### 2. Create Your First Custom Capability

```bash
# Via WP-CLI
wp permission:capability-create manage_products --description="Manage product catalog"

# Or via PHP
wppermission()->createCapability('manage_products', 'Manage product catalog', 'ecommerce');
```

### 3. Create a Custom Role

```bash
# Via WP-CLI
wp permission:role-create shop_manager "Shop Manager" --capabilities="edit_posts,manage_products"

# Or via PHP
wppermission()->createRole('shop_manager', 'Shop Manager', [
    'edit_posts' => true,
    'manage_products' => true
]);
```

### 4. Grant Capabilities to Users

```bash
# Via WP-CLI
wp permission:user-grant admin manage_products

# Or via PHP
grant('manage_products', $user_id);
```

## Integration Examples

### With WordPress Routes

```php
// Protect routes with permission middleware
Route::get('/admin/products', 'ProductController@index')
    ->middleware(wp_permission_require_cap('manage_products'));
```

### With WordPress ORM

```php
// Model with permission scoping
class Product extends WordPressModel {
    public function scopeUserCanEdit($query) {
        if (!can('edit_others_products')) {
            $query->where('author_id', get_current_user_id());
        }
    }
}
```

### AJAX Protection

```php
add_action('wp_ajax_manage_products', function() {
    wp_permission_check_ajax('manage_products');
    // Your AJAX logic here
});
```

### REST API Protection

```php
register_rest_route('myapi/v1', '/products', [
    'methods' => 'POST',
    'callback' => 'create_product',
    'permission_callback' => function() {
        return can('manage_products');
    }
]);
```

## Troubleshooting

### Common Issues

**1. Function not found errors**
```bash
# Make sure bootstrap.php is loaded
require_once 'path/to/wp-permission/bootstrap.php';
```

**2. WP-CLI commands not available**
```bash
# Clear WP-CLI cache
wp cli cache clear

# Verify WordPress is loaded
wp --info
```

**3. Permissions not working**
```php
// Check if capability exists
if (wp_permission_capability_exists('your_capability')) {
    echo 'Capability exists';
} else {
    echo 'Create the capability first';
}
```

### Debug Mode

Enable WordPress debug mode to see permission system logs:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Reset Permissions

If you need to reset custom permissions:

```bash
# List all custom capabilities
wp permission:capability-list --type=custom

# Delete specific capability
wp permission:capability-delete old_capability --yes

# List all custom roles
wp permission:role-list --type=custom

# Delete specific role
wp permission:role-delete old_role --yes
```

## Performance Considerations

### Caching

The library uses WordPress options for storage with automatic caching:

- Custom capabilities cached in `wp_permission_custom_capabilities`
- Custom roles cached in `wp_permission_custom_roles`
- Middleware results cached per request

### Database Impact

Minimal database impact:
- Uses existing WordPress capability system
- Only 2 additional option entries for custom data
- No additional tables required

### Memory Usage

Low memory footprint:
- Lazy loading of managers
- Singleton pattern for main class
- Efficient middleware execution

## Security Notes

### Best Practices

1. **Principle of Least Privilege**: Grant minimum necessary permissions
2. **Regular Audits**: Use CLI commands to review permissions
3. **Capability Naming**: Use descriptive, prefixed names
4. **Role Hierarchy**: Design clear role inheritance

### Security Features

- **Input Validation**: All capability/role names sanitized
- **WordPress Integration**: Uses core WordPress security functions
- **Permission Inheritance**: Respects WordPress role hierarchy
- **Audit Trail**: Events logged for all permission changes

## Next Steps

1. [Read the API Reference](api.md)
2. [Learn about Capabilities](capabilities.md)
3. [Explore Role Management](roles.md)
4. [Check out Examples](examples.md)
5. [Review CLI Commands](cli.md)

---

Installation complete! Your WordPress Permission system is ready to use.
