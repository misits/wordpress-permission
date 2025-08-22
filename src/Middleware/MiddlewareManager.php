<?php
/**
 * Middleware Manager
 *
 * Manages permission middleware for route and action protection
 *
 * @package WordPressPermission
 */

namespace WordPressPermission\Middleware;

defined('ABSPATH') or exit;

class MiddlewareManager {
    
    /**
     * Registered middleware
     */
    private $middleware = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->registerBuiltInMiddleware();
    }
    
    /**
     * Register built-in middleware
     */
    private function registerBuiltInMiddleware() {
        // Capability middleware
        $this->register('capability', function($capability, $user_id = null) {
            if ($user_id === null) {
                $user_id = get_current_user_id();
            }
            return user_can($user_id, $capability);
        });
        
        // Role middleware
        $this->register('role', function($role, $user_id = null) {
            if ($user_id === null) {
                $user_id = get_current_user_id();
            }
            $user = get_user_by('id', $user_id);
            return $user && in_array($role, $user->roles);
        });
        
        // Login middleware
        $this->register('login', function($user_id = null) {
            if ($user_id === null) {
                $user_id = get_current_user_id();
            }
            return $user_id > 0;
        });
        
        // Ownership middleware
        $this->register('ownership', function($object_id, $user_id = null) {
            if ($user_id === null) {
                $user_id = get_current_user_id();
            }
            
            // Check post ownership
            $post = get_post($object_id);
            if ($post) {
                return $post->post_author == $user_id;
            }
            
            // Check comment ownership
            $comment = get_comment($object_id);
            if ($comment) {
                return $comment->user_id == $user_id;
            }
            
            return false;
        });
        
        // Super admin middleware
        $this->register('super_admin', function($user_id = null) {
            if ($user_id === null) {
                $user_id = get_current_user_id();
            }
            return is_super_admin($user_id);
        });
        
        // Network admin middleware
        $this->register('network_admin', function($user_id = null) {
            if (!is_multisite()) {
                return false;
            }
            if ($user_id === null) {
                $user_id = get_current_user_id();
            }
            return user_can($user_id, 'manage_network');
        });
    }
    
    /**
     * Register middleware
     */
    public function register($name, $callback) {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Middleware callback must be callable");
        }
        
        $this->middleware[$name] = $callback;
        
        do_action('wp_permission_middleware_registered', $name, $callback);
        
        return $this;
    }
    
    /**
     * Apply middleware
     */
    public function apply($name, ...$args) {
        if (!isset($this->middleware[$name])) {
            throw new \InvalidArgumentException("Middleware '{$name}' not found");
        }
        
        $result = call_user_func_array($this->middleware[$name], $args);
        
        do_action('wp_permission_middleware_applied', $name, $args, $result);
        
        return $result;
    }
    
    /**
     * Check if middleware exists
     */
    public function exists($name) {
        return isset($this->middleware[$name]);
    }
    
    /**
     * Get all registered middleware
     */
    public function getAll() {
        return array_keys($this->middleware);
    }
    
    /**
     * Remove middleware
     */
    public function remove($name) {
        if (isset($this->middleware[$name])) {
            unset($this->middleware[$name]);
            do_action('wp_permission_middleware_removed', $name);
            return true;
        }
        return false;
    }
    
    /**
     * Create capability middleware
     */
    public function requireCapability($capability) {
        return function($user_id = null) use ($capability) {
            return $this->apply('capability', $capability, $user_id);
        };
    }
    
    /**
     * Create role middleware
     */
    public function requireRole($role) {
        return function($user_id = null) use ($role) {
            return $this->apply('role', $role, $user_id);
        };
    }
    
    /**
     * Create login middleware
     */
    public function requireLogin() {
        return function($user_id = null) {
            return $this->apply('login', $user_id);
        };
    }
    
    /**
     * Create ownership middleware
     */
    public function requireOwnership($object_id = null) {
        return function($user_id = null) use ($object_id) {
            $id = $object_id ?: get_the_ID();
            return $this->apply('ownership', $id, $user_id);
        };
    }
    
    /**
     * Create composite middleware (AND logic)
     */
    public function requireAll($middlewares) {
        return function($user_id = null) use ($middlewares) {
            foreach ($middlewares as $middleware) {
                if (is_string($middleware)) {
                    // Simple middleware name
                    if (!$this->apply($middleware, $user_id)) {
                        return false;
                    }
                } elseif (is_array($middleware)) {
                    // Middleware with parameters [name, ...args]
                    $name = array_shift($middleware);
                    $args = array_merge($middleware, [$user_id]);
                    if (!$this->apply($name, ...$args)) {
                        return false;
                    }
                } elseif (is_callable($middleware)) {
                    // Direct callback
                    if (!call_user_func($middleware, $user_id)) {
                        return false;
                    }
                }
            }
            return true;
        };
    }
    
    /**
     * Create composite middleware (OR logic)
     */
    public function requireAny($middlewares) {
        return function($user_id = null) use ($middlewares) {
            foreach ($middlewares as $middleware) {
                if (is_string($middleware)) {
                    // Simple middleware name
                    if ($this->apply($middleware, $user_id)) {
                        return true;
                    }
                } elseif (is_array($middleware)) {
                    // Middleware with parameters [name, ...args]
                    $name = array_shift($middleware);
                    $args = array_merge($middleware, [$user_id]);
                    if ($this->apply($name, ...$args)) {
                        return true;
                    }
                } elseif (is_callable($middleware)) {
                    // Direct callback
                    if (call_user_func($middleware, $user_id)) {
                        return true;
                    }
                }
            }
            return false;
        };
    }
    
    /**
     * Create time-based middleware
     */
    public function requireTime($start_time, $end_time, $timezone = 'UTC') {
        return function() use ($start_time, $end_time, $timezone) {
            $now = new \DateTime('now', new \DateTimeZone($timezone));
            $start = new \DateTime($start_time, new \DateTimeZone($timezone));
            $end = new \DateTime($end_time, new \DateTimeZone($timezone));
            
            return $now >= $start && $now <= $end;
        };
    }
    
    /**
     * Create IP-based middleware
     */
    public function requireIP($allowed_ips) {
        return function() use ($allowed_ips) {
            $user_ip = $this->getUserIP();
            
            if (!is_array($allowed_ips)) {
                $allowed_ips = [$allowed_ips];
            }
            
            foreach ($allowed_ips as $allowed_ip) {
                if ($this->matchIP($user_ip, $allowed_ip)) {
                    return true;
                }
            }
            
            return false;
        };
    }
    
    /**
     * Create rate limiting middleware
     */
    public function requireRateLimit($max_requests, $time_window, $key_callback = null) {
        return function($user_id = null) use ($max_requests, $time_window, $key_callback) {
            if ($key_callback && is_callable($key_callback)) {
                $key = call_user_func($key_callback, $user_id);
            } else {
                $key = $user_id ?: $this->getUserIP();
            }
            
            $cache_key = "wp_permission_rate_limit_{$key}";
            $requests = get_transient($cache_key) ?: [];
            $now = time();
            
            // Remove old requests outside the time window
            $requests = array_filter($requests, function($timestamp) use ($now, $time_window) {
                return ($now - $timestamp) < $time_window;
            });
            
            // Check if limit exceeded
            if (count($requests) >= $max_requests) {
                return false;
            }
            
            // Add current request
            $requests[] = $now;
            set_transient($cache_key, $requests, $time_window);
            
            return true;
        };
    }
    
    /**
     * Get user IP address
     */
    private function getUserIP() {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Handle comma-separated IPs
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }
        
        return $ip;
    }
    
    /**
     * Match IP address against pattern
     */
    private function matchIP($ip, $pattern) {
        // Exact match
        if ($ip === $pattern) {
            return true;
        }
        
        // CIDR notation
        if (strpos($pattern, '/') !== false) {
            list($subnet, $mask) = explode('/', $pattern);
            return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
        }
        
        // Wildcard pattern
        if (strpos($pattern, '*') !== false) {
            $pattern = str_replace('*', '.*', preg_quote($pattern, '/'));
            return preg_match("/^{$pattern}$/", $ip);
        }
        
        return false;
    }
    
    /**
     * Apply middleware with error handling
     */
    public function check($name, ...$args) {
        try {
            return $this->apply($name, ...$args);
        } catch (\Exception $e) {
            do_action('wp_permission_middleware_error', $name, $args, $e);
            return false;
        }
    }
    
    /**
     * Get middleware statistics
     */
    public function getStats() {
        return [
            'total_middleware' => count($this->middleware),
            'middleware_names' => array_keys($this->middleware),
            'built_in_count' => 6, // Number of built-in middleware
            'custom_count' => count($this->middleware) - 6
        ];
    }
}