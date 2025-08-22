# Permission Middleware

Complete guide to using middleware for permission checking with wp-permission.

## Overview

Middleware provides a powerful, composable way to check permissions before executing code. The wp-permission library includes a comprehensive middleware system with built-in checks and support for custom middleware functions.

## Core Concepts

### What is Middleware?

Middleware functions are callable functions that return boolean values indicating whether permission is granted:

```php
// Simple middleware function
$middleware = function($user_id = null) {
    return can('manage_products', $user_id);
};

// Execute middleware
if ($middleware()) {
    echo 'Permission granted';
}
```

### Middleware Manager

The middleware manager provides built-in middleware and composition utilities:

```php
$manager = wppermission()->getMiddlewareManager();

// Use built-in middleware
$check = $manager->requireCapability('manage_products');
if ($check()) {
    echo 'User can manage products';
}
```

## Built-in Middleware

### Capability Requirements

```php
$manager = wppermission()->getMiddlewareManager();

// Require single capability
$middleware = $manager->requireCapability('manage_products');

// Execute check
if ($middleware()) {
    echo 'User has manage_products capability';
}

// Check specific user
if ($middleware(123)) {
    echo 'User 123 has manage_products capability';
}
```

### Role Requirements

```php
$manager = wppermission()->getMiddlewareManager();

// Require specific role
$middleware = $manager->requireRole('shop_manager');

// Require any of multiple roles
$middleware = $manager->requireAnyRole(['admin', 'shop_manager', 'editor']);

// Require all roles
$middleware = $manager->requireAllRoles(['editor', 'shop_manager']);
```

### User-Based Checks

```php
$manager = wppermission()->getMiddlewareManager();

// Require specific user
$middleware = $manager->requireUser(123);

// Require any of specific users
$middleware = $manager->requireAnyUser([123, 456, 789]);

// Require logged-in user
$middleware = $manager->requireLoggedIn();

// Require super admin (multisite)
$middleware = $manager->requireSuperAdmin();
```

## Middleware Composition

### Logical Operators

```php
$manager = wppermission()->getMiddlewareManager();

// AND - All conditions must pass
$middleware = $manager->requireAll([
    $manager->requireCapability('manage_products'),
    $manager->requireRole('shop_manager'),
    $manager->requireLoggedIn()
]);

// OR - Any condition can pass
$middleware = $manager->requireAny([
    $manager->requireCapability('manage_options'),
    $manager->requireRole('administrator'),
    $manager->requireSuperAdmin()
]);

// NOT - Invert condition
$middleware = $manager->requireNot(
    $manager->requireRole('banned_user')
);
```

### Complex Combinations

```php
$manager = wppermission()->getMiddlewareManager();

// Complex permission logic
$middleware = $manager->requireAll([
    $manager->requireLoggedIn(),
    $manager->requireAny([
        // Either admin privileges
        $manager->requireAll([
            $manager->requireRole('administrator'),
            $manager->requireCapability('manage_options')
        ]),
        // Or shop manager with product permissions
        $manager->requireAll([
            $manager->requireRole('shop_manager'),
            $manager->requireCapability('manage_products')
        ]),
        // Or specific user
        $manager->requireUser(123)
    ])
]);
```

## Advanced Middleware

### Time-Based Restrictions

```php
$manager = wppermission()->getMiddlewareManager();

// Only during business hours
$middleware = $manager->requireTimeWindow('09:00', '17:00');

// Only on specific days
$middleware = $manager->requireDays(['Monday', 'Tuesday', 'Wednesday']);

// Combine time and day restrictions
$business_hours = $manager->requireAll([
    $manager->requireTimeWindow('09:00', '17:00'),
    $manager->requireDays(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])
]);
```

### IP-Based Restrictions

```php
$manager = wppermission()->getMiddlewareManager();

// Allow specific IP
$middleware = $manager->requireIP('192.168.1.100');

// Allow IP range
$middleware = $manager->requireIPRange('192.168.1.0/24');

// Block specific IPs
$middleware = $manager->requireNot(
    $manager->requireAnyIP(['192.168.1.50', '10.0.0.25'])
);
```

### Rate Limiting

```php
$manager = wppermission()->getMiddlewareManager();

// Limit to 10 requests per hour per user
$middleware = $manager->requireRateLimit(10, 3600);

// Different limits for different actions
$view_limit = $manager->requireRateLimit(100, 3600, 'view_products');
$create_limit = $manager->requireRateLimit(5, 3600, 'create_products');
```

## Custom Middleware

### Simple Custom Middleware

```php
// Custom middleware function
function requireMinimumPostCount($min_posts = 10) {
    return function($user_id = null) use ($min_posts) {
        $user_id = $user_id ?: get_current_user_id();
        if (!$user_id) return false;
        
        $post_count = count_user_posts($user_id);
        return $post_count >= $min_posts;
    };
}

// Usage
$middleware = requireMinimumPostCount(5);
if ($middleware()) {
    echo 'User has at least 5 posts';
}
```

### Class-Based Middleware

```php
class UserLevelMiddleware {
    
    public static function requireLevel($minimum_level) {
        return function($user_id = null) use ($minimum_level) {
            $user_id = $user_id ?: get_current_user_id();
            if (!$user_id) return false;
            
            $user_level = get_user_meta($user_id, 'user_level', true);
            return (int)$user_level >= $minimum_level;
        };
    }
    
    public static function requireVerifiedEmail() {
        return function($user_id = null) {
            $user_id = $user_id ?: get_current_user_id();
            if (!$user_id) return false;
            
            return get_user_meta($user_id, 'email_verified', true) === 'yes';
        };
    }
    
    public static function requireProfileComplete() {
        return function($user_id = null) {
            $user_id = $user_id ?: get_current_user_id();
            if (!$user_id) return false;
            
            $user = get_user_by('id', $user_id);
            
            // Check required fields
            return !empty($user->first_name) && 
                   !empty($user->last_name) && 
                   !empty($user->description);
        };
    }
}

// Usage
$middleware = UserLevelMiddleware::requireLevel(5);
$email_check = UserLevelMiddleware::requireVerifiedEmail();
```

## Integration Examples

### With WordPress Routes

```php
// Protect routes with middleware
Route::get('/admin/products', 'ProductController@index')
    ->middleware(wp_permission_require_cap('manage_products'));

Route::post('/api/products', 'ProductController@create')
    ->middleware(wp_permission_require_all([
        wp_permission_require_cap('manage_products'),
        wp_permission_require_role('shop_manager'),
        wp_permission_require_rate_limit(5, 3600)
    ]));
```

### With AJAX Handlers

```php
// Protect AJAX endpoints
function handle_product_creation() {
    $manager = wppermission()->getMiddlewareManager();
    
    $middleware = $manager->requireAll([
        $manager->requireCapability('manage_products'),
        $manager->requireNonce('create_product_nonce'),
        $manager->requireRateLimit(10, 3600)
    ]);
    
    if (!$middleware()) {
        wp_die('Permission denied', 403);
    }
    
    // Handle product creation
    create_product($_POST['product_data']);
}

add_action('wp_ajax_create_product', 'handle_product_creation');
```

### With REST API

```php
// Protect REST endpoints
register_rest_route('myapi/v1', '/products', [
    'methods' => 'POST',
    'callback' => 'create_product_endpoint',
    'permission_callback' => function() {
        $manager = wppermission()->getMiddlewareManager();
        
        $middleware = $manager->requireAll([
            $manager->requireCapability('manage_products'),
            $manager->requireTimeWindow('09:00', '17:00'),
            $manager->requireRateLimit(5, 3600)
        ]);
        
        return $middleware();
    }
]);
```

### With WordPress Hooks

```php
// Protect hook execution
add_action('init', function() {
    $manager = wppermission()->getMiddlewareManager();
    
    $middleware = $manager->requireCapability('manage_products');
    
    if ($middleware()) {
        // Only run for users with manage_products capability
        add_filter('product_query_args', 'modify_product_query');
    }
});
```

## Helper Functions

### Global Middleware Helpers

```php
// Quick middleware creation
function wp_permission_require_cap($capability) {
    return wppermission()->getMiddlewareManager()
        ->requireCapability($capability);
}

function wp_permission_require_role($role) {
    return wppermission()->getMiddlewareManager()
        ->requireRole($role);
}

function wp_permission_require_all($middlewares) {
    return wppermission()->getMiddlewareManager()
        ->requireAll($middlewares);
}

function wp_permission_require_any($middlewares) {
    return wppermission()->getMiddlewareManager()
        ->requireAny($middlewares);
}

// Usage
$middleware = wp_permission_require_all([
    wp_permission_require_cap('manage_products'),
    wp_permission_require_role('shop_manager')
]);
```

### Middleware Execution Helpers

```php
// Execute with error handling
function wp_permission_check($middleware, $user_id = null, $error_message = 'Permission denied') {
    if (!$middleware($user_id)) {
        wp_die($error_message, 403);
    }
}

// Execute for AJAX
function wp_permission_check_ajax($middleware, $user_id = null) {
    if (!$middleware($user_id)) {
        wp_send_json_error('Permission denied', 403);
    }
}

// Usage
wp_permission_check(
    wp_permission_require_cap('manage_products'),
    null,
    'You need manage_products capability'
);
```

## Performance Optimization

### Middleware Caching

```php
class CachedMiddleware {
    
    private static $cache = [];
    
    public static function cached($key, $middleware, $ttl = 300) {
        return function($user_id = null) use ($key, $middleware, $ttl) {
            $user_id = $user_id ?: get_current_user_id();
            $cache_key = "{$key}_{$user_id}";
            
            // Check cache
            if (isset(self::$cache[$cache_key])) {
                $cached = self::$cache[$cache_key];
                if ($cached['expires'] > time()) {
                    return $cached['result'];
                }
            }
            
            // Execute middleware
            $result = $middleware($user_id);
            
            // Cache result
            self::$cache[$cache_key] = [
                'result' => $result,
                'expires' => time() + $ttl
            ];
            
            return $result;
        };
    }
}

// Usage
$cached_middleware = CachedMiddleware::cached(
    'manage_products_check',
    wp_permission_require_cap('manage_products'),
    600 // Cache for 10 minutes
);
```

### Lazy Evaluation

```php
// Only evaluate expensive middleware when needed
function createLazyMiddleware($primary, $expensive) {
    return function($user_id = null) use ($primary, $expensive) {
        // Check primary condition first
        if (!$primary($user_id)) {
            return false;
        }
        
        // Only check expensive condition if primary passes
        return $expensive($user_id);
    };
}

// Usage
$middleware = createLazyMiddleware(
    wp_permission_require_cap('manage_products'), // Fast check
    function($user_id) { // Expensive check
        return count_user_posts($user_id) > 100;
    }
);
```

## Debugging Middleware

### Logging Middleware Execution

```php
function loggedMiddleware($name, $middleware) {
    return function($user_id = null) use ($name, $middleware) {
        $start = microtime(true);
        $result = $middleware($user_id);
        $duration = microtime(true) - $start;
        
        error_log(sprintf(
            'Middleware %s: %s (%.4fs) for user %d',
            $name,
            $result ? 'PASS' : 'FAIL',
            $duration,
            $user_id ?: get_current_user_id()
        ));
        
        return $result;
    };
}

// Usage
$middleware = loggedMiddleware(
    'product_access',
    wp_permission_require_cap('manage_products')
);
```

### Middleware Testing

```php
function testMiddleware($middleware, $test_cases) {
    foreach ($test_cases as $case) {
        $user_id = $case['user_id'];
        $expected = $case['expected'];
        $description = $case['description'];
        
        $result = $middleware($user_id);
        
        if ($result === $expected) {
            echo "✓ {$description}\n";
        } else {
            echo "✗ {$description} - Expected " . 
                 ($expected ? 'true' : 'false') . 
                 ", got " . ($result ? 'true' : 'false') . "\n";
        }
    }
}

// Usage
$middleware = wp_permission_require_cap('manage_products');

testMiddleware($middleware, [
    [
        'user_id' => 1,
        'expected' => true,
        'description' => 'Admin should have access'
    ],
    [
        'user_id' => 2,
        'expected' => false,
        'description' => 'Subscriber should not have access'
    ]
]);
```

## Best Practices

### Design Principles

- **Single Responsibility**: Each middleware should check one thing
- **Composability**: Design middleware to work well together
- **Performance**: Cache expensive operations
- **Security**: Fail securely (default to deny)

### Common Patterns

```php
// Pattern: Progressive enhancement
$base_access = wp_permission_require_cap('read');
$admin_access = wp_permission_require_all([
    $base_access,
    wp_permission_require_cap('manage_options')
]);

// Pattern: Fallback permissions
$middleware = wp_permission_require_any([
    wp_permission_require_cap('manage_products'),
    wp_permission_require_all([
        wp_permission_require_role('shop_manager'),
        wp_permission_require_cap('edit_posts')
    ])
]);
```

### Error Handling

```php
// Always provide clear error messages
function requireWithMessage($middleware, $message) {
    return function($user_id = null) use ($middleware, $message) {
        if (!$middleware($user_id)) {
            throw new PermissionException($message);
        }
        return true;
    };
}
```

## Next Steps

- [Learn about CLI Commands](cli.md)
- [Explore API Reference](api.md)
- [Check out Examples](examples.md)
- [Review User Management](users.md)