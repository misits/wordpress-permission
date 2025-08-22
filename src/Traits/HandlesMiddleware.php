<?php
/**
 * Handles Middleware Trait
 *
 * Provides middleware management functionality for permission checking
 *
 * @package WordPressPermission
 */

namespace WordPressPermission\Traits;

defined('ABSPATH') or exit;

trait HandlesMiddleware {
    
    /**
     * Register permission middleware
     */
    public function middleware($name, $callback) {
        return $this->getMiddlewareManager()->register($name, $callback);
    }
    
    /**
     * Apply middleware check
     */
    public function applyMiddleware($middleware_name, ...$args) {
        return $this->getMiddlewareManager()->apply($middleware_name, ...$args);
    }
    
    /**
     * Create capability middleware
     */
    public function requireCapability($capability) {
        return $this->getMiddlewareManager()->requireCapability($capability);
    }
    
    /**
     * Create role middleware
     */
    public function requireRole($role) {
        return $this->getMiddlewareManager()->requireRole($role);
    }
    
    /**
     * Create logged-in middleware
     */
    public function requireLogin() {
        return $this->getMiddlewareManager()->requireLogin();
    }
    
    /**
     * Create ownership middleware
     */
    public function requireOwnership($object_id = null) {
        return $this->getMiddlewareManager()->requireOwnership($object_id);
    }
    
    /**
     * Create composite middleware (AND logic)
     */
    public function requireAll($middlewares) {
        return $this->getMiddlewareManager()->requireAll($middlewares);
    }
    
    /**
     * Create composite middleware (OR logic)
     */
    public function requireAny($middlewares) {
        return $this->getMiddlewareManager()->requireAny($middlewares);
    }
}