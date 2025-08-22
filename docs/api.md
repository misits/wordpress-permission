# API Reference

Complete API documentation for the WordPress Permission library.

## Overview

The wp-permission library provides a comprehensive API for managing WordPress permissions through manager classes, helper functions, and middleware. This reference covers all public methods and their usage.

## Main Permission Class

### Access Methods

```php
// Get the main permission instance
$permission = wppermission();

// Static access to managers
$capManager = WordPressPermission\Core\Permission::capabilities();
$roleManager = WordPressPermission\Core\Permission::roles();
$userManager = WordPressPermission\Core\Permission::users();
$middlewareManager = WordPressPermission\Core\Permission::middleware();
```

### Core Methods

```php
class Permission {
    
    // Check if user has capability
    public function can($capability, $user_id = null): bool
    
    // Get capability manager
    public function getCapabilityManager(): CapabilityManager
    
    // Get role manager  
    public function getRoleManager(): RoleManager
    
    // Get user permission manager
    public function getUserPermissionManager(): UserPermissionManager
    
    // Get middleware manager
    public function getMiddlewareManager(): MiddlewareManager
    
    // Create new capability
    public function createCapability($capability, $description = '', $group = 'custom'): bool
    
    // Create new role
    public function createRole($role, $display_name, $capabilities = []): bool
}
```

## Capability Manager

### CapabilityManager Class

```php
class CapabilityManager {
    
    // Create a new capability
    public function create($capability, $description = '', $group = 'custom'): bool
    
    // Check if capability exists
    public function exists($capability): bool
    
    // Delete custom capability
    public function delete($capability): bool
    
    // Get all capabilities
    public function getAll(): array
    
    // Get custom capabilities only
    public function getCustomCapabilities(): array
    
    // Get capabilities by group
    public function getByGroup($group): array
    
    // Get capability information
    public function getInfo($capability): ?array
    
    // Sanitize capability name
    public function sanitizeCapabilityName($name): string
}
```

### Usage Examples

```php
$manager = wppermission()->getCapabilityManager();

// Create capability
$manager->create('manage_products', 'Manage product catalog', 'ecommerce');

// Check existence
if ($manager->exists('manage_products')) {
    echo 'Capability exists';
}

// Get information
$info = $manager->getInfo('manage_products');
// Returns: ['description' => '...', 'group' => '...', 'type' => '...']

// Get by group
$ecommerce_caps = $manager->getByGroup('ecommerce');
```

## Role Manager

### RoleManager Class

```php
class RoleManager {
    
    // Create new role
    public function create($role, $display_name, $capabilities = []): bool
    
    // Clone existing role
    public function clone($source_role, $new_role, $display_name): bool
    
    // Delete custom role
    public function delete($role): bool
    
    // Check if role exists
    public function exists($role): bool
    
    // Get all roles
    public function getAll(): array
    
    // Get custom roles only
    public function getCustomRoles(): array
    
    // Check if role is custom
    public function isCustomRole($role): bool
    
    // Get role display name
    public function getDisplayName($role): string
    
    // Get role capabilities
    public function getCapabilities($role): array
    
    // Add capability to role
    public function addCapability($role, $capability): bool
    
    // Remove capability from role
    public function removeCapability($role, $capability): bool
    
    // Check if role has capability
    public function hasCapability($role, $capability): bool
    
    // Get roles that have specific capability
    public function getRolesWithCapability($capability): array
}
```

### Usage Examples

```php
$manager = wppermission()->getRoleManager();

// Create role
$manager->create('shop_manager', 'Shop Manager', [
    'edit_posts' => true,
    'manage_products' => true
]);

// Clone role
$manager->clone('editor', 'content_manager', 'Content Manager');

// Add capability
$manager->addCapability('shop_manager', 'view_analytics');

// Get capabilities
$capabilities = $manager->getCapabilities('shop_manager');

// Find roles with capability
$roles = $manager->getRolesWithCapability('manage_products');
```

## User Permission Manager

### UserPermissionManager Class

```php
class UserPermissionManager {
    
    // Check if user has capability
    public function userCan($user_id, $capability): bool
    
    // Grant capability to user
    public function grantCapability($user_id, $capability): bool
    
    // Revoke capability from user
    public function revokeCapability($user_id, $capability): bool
    
    // Assign role to user (adds to existing)
    public function assignRole($user_id, $role): bool
    
    // Set user role (replaces existing)
    public function setRole($user_id, $role): bool
    
    // Remove role from user
    public function removeRole($user_id, $role): bool
    
    // Check if user has role
    public function userHasRole($user_id, $role): bool
    
    // Get user permission summary
    public function getUserPermissionSummary($user_id): array
    
    // Compare two users
    public function compareUsers($user_id_1, $user_id_2): array
    
    // Get users with specific capability
    public function getUsersWithCapability($capability): array
    
    // Get users with specific role
    public function getUsersByRole($role): array
    
    // Bulk grant capability
    public function bulkGrantCapability($user_ids, $capability): array
    
    // Bulk revoke capability
    public function bulkRevokeCapability($user_ids, $capability): array
    
    // Bulk assign role
    public function bulkAssignRole($user_ids, $role): array
    
    // Get capability statistics
    public function getCapabilityStats($capability): array
}
```

### Usage Examples

```php
$manager = wppermission()->getUserPermissionManager();

// Check permission
if ($manager->userCan(123, 'manage_products')) {
    echo 'User has permission';
}

// Grant capability
$manager->grantCapability(123, 'view_analytics');

// Assign role
$manager->assignRole(123, 'shop_manager');

// Get summary
$summary = $manager->getUserPermissionSummary(123);
/*
Returns:
[
    'user_id' => 123,
    'username' => 'john_doe',
    'display_name' => 'John Doe',
    'email' => 'john@example.com',
    'roles' => ['editor', 'shop_manager'],
    'capability_count' => 45,
    'all_capabilities' => [...],
    'direct_capabilities' => [...],
    'role_capabilities' => [...],
    'is_super_admin' => false,
    'registration_date' => '2023-01-15 10:30:00'
]
*/

// Compare users
$comparison = $manager->compareUsers(123, 456);
/*
Returns:
[
    'user_1_total' => 45,
    'user_2_total' => 32,
    'shared_count' => 28,
    'shared_capabilities' => [...],
    'user_1_only' => [...],
    'user_2_only' => [...]
]
*/

// Bulk operations
$results = $manager->bulkGrantCapability([123, 456, 789], 'view_analytics');
```

## Middleware Manager

### MiddlewareManager Class

```php
class MiddlewareManager {
    
    // Basic requirement middleware
    public function requireCapability($capability): callable
    public function requireRole($role): callable
    public function requireAnyRole($roles): callable
    public function requireAllRoles($roles): callable
    public function requireUser($user_id): callable
    public function requireAnyUser($user_ids): callable
    public function requireLoggedIn(): callable
    public function requireSuperAdmin(): callable
    
    // Logical composition
    public function requireAll($middlewares): callable
    public function requireAny($middlewares): callable
    public function requireNot($middleware): callable
    
    // Advanced middleware
    public function requireTimeWindow($start_time, $end_time): callable
    public function requireDays($allowed_days): callable
    public function requireIP($ip_address): callable
    public function requireIPRange($ip_range): callable
    public function requireAnyIP($ip_addresses): callable
    public function requireRateLimit($max_requests, $time_window, $action = 'default'): callable
    
    // Apply middleware
    public function apply($middleware_name, ...$args): callable
    
    // Get statistics
    public function getStats(): array
}
```

### Usage Examples

```php
$manager = wppermission()->getMiddlewareManager();

// Basic middleware
$check = $manager->requireCapability('manage_products');
if ($check()) {
    echo 'User has permission';
}

// Composition
$complex_check = $manager->requireAll([
    $manager->requireCapability('manage_products'),
    $manager->requireRole('shop_manager'),
    $manager->requireTimeWindow('09:00', '17:00')
]);

// Advanced middleware
$rate_limited = $manager->requireRateLimit(10, 3600, 'api_access');
$time_restricted = $manager->requireTimeWindow('08:00', '18:00');
$ip_restricted = $manager->requireIPRange('192.168.1.0/24');
```

## Global Helper Functions

### Permission Check Functions

```php
// Check if user has capability
function can($capability, $user_id = null): bool

// Grant capability to user
function grant($capability, $user_id = null): bool

// Revoke capability from user  
function revoke($capability, $user_id = null): bool

// Check if user has role
function has_role($role, $user_id = null): bool

// Assign role to user
function assign_role($role, $user_id = null): bool

// Remove role from user
function remove_role($role, $user_id = null): bool
```

### Utility Functions

```php
// Check if capability exists
function wp_permission_capability_exists($capability): bool

// Get current user's capabilities
function wp_permission_get_user_capabilities($user_id = null): array

// Get current user's roles
function wp_permission_get_user_roles($user_id = null): array

// Check permission for AJAX requests
function wp_permission_check_ajax($capability, $user_id = null): void

// Create middleware helper
function wp_permission_middleware($callback): callable
```

### Usage Examples

```php
// Simple permission checks
if (can('manage_products')) {
    // User can manage products
}

if (has_role('shop_manager', 123)) {
    // User 123 is a shop manager
}

// Grant permissions
grant('view_analytics', 123);
assign_role('content_editor', 456);

// AJAX protection
add_action('wp_ajax_my_action', function() {
    wp_permission_check_ajax('manage_products');
    // Protected AJAX handler
});
```

## WordPress Integration

### Hooks and Actions

The library fires WordPress actions for auditing and integration:

```php
// Capability events
do_action('wp_permission_capability_created', $capability, $group);
do_action('wp_permission_capability_deleted', $capability);
do_action('wp_permission_capability_granted', $user_id, $capability);
do_action('wp_permission_capability_revoked', $user_id, $capability);

// Role events
do_action('wp_permission_role_created', $role, $display_name);
do_action('wp_permission_role_deleted', $role);
do_action('wp_permission_role_assigned', $user_id, $role);
do_action('wp_permission_role_removed', $user_id, $role);

// Capability added/removed from role
do_action('wp_permission_role_capability_added', $role, $capability);
do_action('wp_permission_role_capability_removed', $role, $capability);
```

### Filter Hooks

```php
// Filter capability creation
apply_filters('wp_permission_can_create_capability', true, $capability, $user_id);

// Filter role creation
apply_filters('wp_permission_can_create_role', true, $role, $user_id);

// Filter capability grants
apply_filters('wp_permission_can_grant_capability', true, $capability, $user_id, $target_user_id);

// Filter middleware execution
apply_filters('wp_permission_middleware_result', $result, $middleware_name, $args);
```

### REST API Endpoints

The library can register REST endpoints for permission management:

```php
// Enable REST API (add to wp-config.php or plugin)
define('WP_PERMISSION_REST_API', true);

// Endpoints will be available at:
// GET /wp-json/wp-permission/v1/capabilities
// POST /wp-json/wp-permission/v1/capabilities
// GET /wp-json/wp-permission/v1/roles
// POST /wp-json/wp-permission/v1/roles
// GET /wp-json/wp-permission/v1/users/{id}/permissions
```

## Error Handling

### Exception Classes

```php
namespace WordPressPermission\Exceptions;

class PermissionException extends \Exception {}
class CapabilityNotFoundException extends PermissionException {}
class RoleNotFoundException extends PermissionException {}
class UserNotFoundException extends PermissionException {}
class MiddlewareException extends PermissionException {}
```

### Error Codes

```php
// Capability errors
const CAPABILITY_NOT_FOUND = 1001;
const CAPABILITY_ALREADY_EXISTS = 1002;
const CAPABILITY_CREATION_FAILED = 1003;

// Role errors  
const ROLE_NOT_FOUND = 2001;
const ROLE_ALREADY_EXISTS = 2002;
const ROLE_CREATION_FAILED = 2003;

// User errors
const USER_NOT_FOUND = 3001;
const PERMISSION_DENIED = 3002;
const INVALID_USER_ID = 3003;
```

### Usage Examples

```php
try {
    $manager = wppermission()->getCapabilityManager();
    $manager->create('existing_capability');
} catch (CapabilityAlreadyExistsException $e) {
    wp_die('Capability already exists: ' . $e->getMessage());
} catch (PermissionException $e) {
    wp_die('Permission error: ' . $e->getMessage());
}
```

## Performance Considerations

### Caching

The library includes built-in caching for performance:

```php
// Capability cache (300 seconds default)
wp_cache_get('wp_permission_capabilities');

// Role cache (300 seconds default)  
wp_cache_get('wp_permission_roles');

// User permission cache (per request)
wp_cache_get("wp_permission_user_{$user_id}");
```

### Database Queries

The library minimizes database impact:

- Uses WordPress options table for custom data
- Leverages WordPress user meta for user-specific grants
- Batches database operations where possible
- Implements query result caching

### Memory Usage

- Singleton pattern for manager classes
- Lazy loading of heavy operations
- Efficient array operations
- Minimal object overhead

## Security Features

### Input Validation

```php
// All inputs are sanitized
$clean_capability = sanitize_key($capability);
$clean_role = sanitize_key($role);
$clean_user_id = absint($user_id);
```

### Permission Verification

```php
// All operations verify current user permissions
if (!current_user_can('manage_options')) {
    throw new PermissionException('Insufficient privileges');
}
```

### Audit Trail

```php
// All permission changes are logged
add_action('wp_permission_capability_granted', function($user_id, $capability) {
    error_log("Granted {$capability} to user {$user_id} by " . get_current_user_id());
});
```

## Next Steps

- [Check out Practical Examples](examples.md)
- [Learn about CLI Commands](cli.md)
- [Explore Middleware](middleware.md)
- [Review Installation Guide](installation.md)