# Practical Examples

Real-world examples and use cases for the WordPress Permission library.

## Overview

This guide provides practical examples of implementing permission systems for common WordPress scenarios. Each example includes complete code and explains the reasoning behind the permission structure.

## E-commerce Store

### Setting Up Shop Permissions

```php
class EcommercePermissions {
    
    public static function setup() {
        self::createCapabilities();
        self::createRoles();
        self::assignDefaultPermissions();
    }
    
    private static function createCapabilities() {
        $capabilities = [
            // Product management
            'manage_products' => ['Manage product catalog', 'ecommerce'],
            'edit_products' => ['Edit existing products', 'ecommerce'],
            'delete_products' => ['Delete products', 'ecommerce'],
            'view_product_analytics' => ['View product performance', 'analytics'],
            
            // Order management
            'manage_orders' => ['Manage customer orders', 'orders'],
            'view_orders' => ['View order list', 'orders'],
            'edit_orders' => ['Modify order details', 'orders'],
            'process_refunds' => ['Process customer refunds', 'orders'],
            
            // Customer management
            'manage_customers' => ['Manage customer accounts', 'customers'],
            'view_customer_data' => ['View customer information', 'customers'],
            'export_customer_data' => ['Export customer data', 'customers'],
            
            // Inventory
            'manage_inventory' => ['Manage stock levels', 'inventory'],
            'view_inventory_reports' => ['View inventory reports', 'inventory'],
            
            // Marketing
            'manage_promotions' => ['Create and manage promotions', 'marketing'],
            'send_marketing_emails' => ['Send promotional emails', 'marketing'],
        ];
        
        foreach ($capabilities as $cap => $data) {
            wppermission()->createCapability($cap, $data[0], $data[1]);
        }
    }
    
    private static function createRoles() {
        // Store Manager - Full store control
        wppermission()->createRole('store_manager', 'Store Manager', [
            'read' => true,
            'edit_posts' => true,
            'manage_products' => true,
            'edit_products' => true,
            'delete_products' => true,
            'manage_orders' => true,
            'view_orders' => true,
            'edit_orders' => true,
            'process_refunds' => true,
            'manage_customers' => true,
            'view_customer_data' => true,
            'manage_inventory' => true,
            'view_inventory_reports' => true,
            'view_product_analytics' => true,
            'manage_promotions' => true,
        ]);
        
        // Product Manager - Product focus
        wppermission()->createRole('product_manager', 'Product Manager', [
            'read' => true,
            'manage_products' => true,
            'edit_products' => true,
            'view_product_analytics' => true,
            'manage_inventory' => true,
            'view_inventory_reports' => true,
        ]);
        
        // Customer Service - Customer focus
        wppermission()->createRole('customer_service', 'Customer Service', [
            'read' => true,
            'view_orders' => true,
            'edit_orders' => true,
            'process_refunds' => true,
            'view_customer_data' => true,
            'manage_customers' => true,
        ]);
        
        // Marketing Manager - Marketing focus
        wppermission()->createRole('marketing_manager', 'Marketing Manager', [
            'read' => true,
            'view_product_analytics' => true,
            'manage_promotions' => true,
            'send_marketing_emails' => true,
            'export_customer_data' => true,
        ]);
    }
    
    private static function assignDefaultPermissions() {
        // Give administrators all ecommerce permissions
        $role_manager = wppermission()->getRoleManager();
        $ecommerce_caps = wppermission()->getCapabilityManager()->getByGroup('ecommerce');
        
        foreach ($ecommerce_caps as $cap => $data) {
            $role_manager->addCapability('administrator', $cap);
        }
    }
}

// Initialize ecommerce permissions
add_action('init', [EcommercePermissions::class, 'setup']);
```

### Product Management with Permissions

```php
class ProductController {
    
    public function __construct() {
        add_action('wp_ajax_create_product', [$this, 'createProduct']);
        add_action('wp_ajax_edit_product', [$this, 'editProduct']);
        add_action('wp_ajax_delete_product', [$this, 'deleteProduct']);
    }
    
    public function createProduct() {
        // Check permissions with middleware
        $middleware = wppermission()->getMiddlewareManager()->requireAll([
            wppermission()->getMiddlewareManager()->requireCapability('manage_products'),
            wppermission()->getMiddlewareManager()->requireRateLimit(10, 3600, 'create_product')
        ]);
        
        if (!$middleware()) {
            wp_send_json_error('Permission denied', 403);
        }
        
        // Validate nonce
        if (!wp_verify_nonce($_POST['nonce'], 'create_product')) {
            wp_send_json_error('Invalid nonce', 403);
        }
        
        // Create product logic
        $product_data = $this->sanitizeProductData($_POST);
        $product_id = $this->insertProduct($product_data);
        
        // Log the action
        do_action('ecommerce_product_created', $product_id, get_current_user_id());
        
        wp_send_json_success(['product_id' => $product_id]);
    }
    
    public function editProduct() {
        $product_id = intval($_POST['product_id']);
        
        // Check if user can edit this specific product
        if (!$this->canEditProduct($product_id)) {
            wp_send_json_error('Cannot edit this product', 403);
        }
        
        // Update product logic
        $this->updateProduct($product_id, $_POST);
        
        wp_send_json_success();
    }
    
    private function canEditProduct($product_id) {
        // Admins and store managers can edit any product
        if (can('manage_products')) {
            return true;
        }
        
        // Product managers can edit products they created
        if (can('edit_products')) {
            $product = get_post($product_id);
            return $product && $product->post_author == get_current_user_id();
        }
        
        return false;
    }
}

new ProductController();
```

## Content Management System

### Editorial Workflow

```php
class EditorialWorkflow {
    
    public static function setup() {
        self::createEditorialCapabilities();
        self::createEditorialRoles();
        self::setupWorkflowHooks();
    }
    
    private static function createEditorialCapabilities() {
        $capabilities = [
            // Content creation
            'create_articles' => ['Create new articles', 'content'],
            'edit_own_articles' => ['Edit own articles', 'content'],
            'edit_others_articles' => ['Edit others articles', 'content'],
            'delete_own_articles' => ['Delete own articles', 'content'],
            'delete_others_articles' => ['Delete others articles', 'content'],
            
            // Editorial process
            'submit_for_review' => ['Submit articles for review', 'editorial'],
            'review_articles' => ['Review submitted articles', 'editorial'],
            'approve_articles' => ['Approve articles for publication', 'editorial'],
            'reject_articles' => ['Reject submitted articles', 'editorial'],
            'schedule_articles' => ['Schedule article publication', 'editorial'],
            
            // Content management
            'manage_categories' => ['Manage article categories', 'content'],
            'manage_tags' => ['Manage article tags', 'content'],
            'manage_featured_content' => ['Manage featured content', 'content'],
            
            // SEO and optimization
            'edit_seo_meta' => ['Edit SEO metadata', 'seo'],
            'view_content_analytics' => ['View content performance', 'analytics'],
        ];
        
        foreach ($capabilities as $cap => $data) {
            wppermission()->createCapability($cap, $data[0], $data[1]);
        }
    }
    
    private static function createEditorialRoles() {
        // Content Writer
        wppermission()->createRole('content_writer', 'Content Writer', [
            'read' => true,
            'create_articles' => true,
            'edit_own_articles' => true,
            'delete_own_articles' => true,
            'submit_for_review' => true,
            'upload_files' => true,
        ]);
        
        // Senior Writer
        wppermission()->createRole('senior_writer', 'Senior Writer', [
            'read' => true,
            'create_articles' => true,
            'edit_own_articles' => true,
            'edit_others_articles' => true,
            'delete_own_articles' => true,
            'submit_for_review' => true,
            'manage_categories' => true,
            'manage_tags' => true,
            'edit_seo_meta' => true,
            'upload_files' => true,
        ]);
        
        // Content Editor
        wppermission()->createRole('content_editor', 'Content Editor', [
            'read' => true,
            'create_articles' => true,
            'edit_own_articles' => true,
            'edit_others_articles' => true,
            'delete_others_articles' => true,
            'review_articles' => true,
            'approve_articles' => true,
            'reject_articles' => true,
            'schedule_articles' => true,
            'manage_categories' => true,
            'manage_tags' => true,
            'edit_seo_meta' => true,
            'view_content_analytics' => true,
            'upload_files' => true,
        ]);
        
        // Managing Editor
        wppermission()->createRole('managing_editor', 'Managing Editor', [
            'read' => true,
            'create_articles' => true,
            'edit_own_articles' => true,
            'edit_others_articles' => true,
            'delete_others_articles' => true,
            'review_articles' => true,
            'approve_articles' => true,
            'reject_articles' => true,
            'schedule_articles' => true,
            'manage_categories' => true,
            'manage_tags' => true,
            'manage_featured_content' => true,
            'edit_seo_meta' => true,
            'view_content_analytics' => true,
            'upload_files' => true,
        ]);
    }
    
    private static function setupWorkflowHooks() {
        // Restrict post status changes based on permissions
        add_filter('wp_insert_post_data', [self::class, 'filterPostStatus'], 10, 2);
        
        // Add editorial metabox
        add_action('add_meta_boxes', [self::class, 'addEditorialMetabox']);
        
        // Handle workflow actions
        add_action('wp_ajax_submit_for_review', [self::class, 'submitForReview']);
        add_action('wp_ajax_approve_article', [self::class, 'approveArticle']);
        add_action('wp_ajax_reject_article', [self::class, 'rejectArticle']);
    }
    
    public static function filterPostStatus($data, $postarr) {
        // Only apply to articles
        if ($data['post_type'] !== 'article') {
            return $data;
        }
        
        $current_status = $data['post_status'];
        $user_id = get_current_user_id();
        
        // Writers can only create drafts or submit for review
        if (has_role('content_writer', $user_id)) {
            if (!in_array($current_status, ['draft', 'pending'])) {
                $data['post_status'] = 'draft';
            }
        }
        
        // Only editors can publish directly
        if ($current_status === 'publish' && !can('approve_articles', $user_id)) {
            $data['post_status'] = 'pending';
        }
        
        return $data;
    }
    
    public static function submitForReview() {
        $post_id = intval($_POST['post_id']);
        
        if (!can('submit_for_review') || !wp_verify_nonce($_POST['nonce'], 'editorial_action')) {
            wp_send_json_error('Permission denied');
        }
        
        // Update post status
        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'pending'
        ]);
        
        // Notify editors
        $editors = get_users(['role' => 'content_editor']);
        foreach ($editors as $editor) {
            wp_mail(
                $editor->user_email,
                'Article submitted for review',
                "Article #{$post_id} has been submitted for review."
            );
        }
        
        wp_send_json_success();
    }
    
    public static function approveArticle() {
        $post_id = intval($_POST['post_id']);
        
        if (!can('approve_articles') || !wp_verify_nonce($_POST['nonce'], 'editorial_action')) {
            wp_send_json_error('Permission denied');
        }
        
        // Publish the article
        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'publish'
        ]);
        
        // Log approval
        update_post_meta($post_id, '_approved_by', get_current_user_id());
        update_post_meta($post_id, '_approved_at', current_time('mysql'));
        
        wp_send_json_success();
    }
}

add_action('init', [EditorialWorkflow::class, 'setup']);
```

## Learning Management System

### Course and Student Management

```php
class LMSPermissions {
    
    public static function setup() {
        self::createLMSCapabilities();
        self::createLMSRoles();
        self::setupCourseRestrictions();
    }
    
    private static function createLMSCapabilities() {
        $capabilities = [
            // Course management
            'create_courses' => ['Create new courses', 'courses'],
            'edit_courses' => ['Edit course content', 'courses'],
            'delete_courses' => ['Delete courses', 'courses'],
            'publish_courses' => ['Publish courses', 'courses'],
            'manage_course_categories' => ['Manage course categories', 'courses'],
            
            // Lesson management
            'create_lessons' => ['Create course lessons', 'lessons'],
            'edit_lessons' => ['Edit lesson content', 'lessons'],
            'delete_lessons' => ['Delete lessons', 'lessons'],
            'reorder_lessons' => ['Reorder course lessons', 'lessons'],
            
            // Student management
            'enroll_students' => ['Enroll students in courses', 'students'],
            'view_student_progress' => ['View student progress', 'students'],
            'grade_assignments' => ['Grade student assignments', 'students'],
            'export_grades' => ['Export grade reports', 'students'],
            
            // Assessment
            'create_quizzes' => ['Create course quizzes', 'assessment'],
            'grade_quizzes' => ['Grade quiz submissions', 'assessment'],
            'view_quiz_analytics' => ['View quiz performance data', 'assessment'],
            
            // Learning
            'access_courses' => ['Access enrolled courses', 'learning'],
            'submit_assignments' => ['Submit assignments', 'learning'],
            'take_quizzes' => ['Take course quizzes', 'learning'],
            'view_own_progress' => ['View own learning progress', 'learning'],
        ];
        
        foreach ($capabilities as $cap => $data) {
            wppermission()->createCapability($cap, $data[0], $data[1]);
        }
    }
    
    private static function createLMSRoles() {
        // Instructor
        wppermission()->createRole('instructor', 'Instructor', [
            'read' => true,
            'create_courses' => true,
            'edit_courses' => true,
            'publish_courses' => true,
            'create_lessons' => true,
            'edit_lessons' => true,
            'reorder_lessons' => true,
            'enroll_students' => true,
            'view_student_progress' => true,
            'create_quizzes' => true,
            'grade_quizzes' => true,
            'grade_assignments' => true,
            'view_quiz_analytics' => true,
            'upload_files' => true,
        ]);
        
        // Teaching Assistant
        wppermission()->createRole('teaching_assistant', 'Teaching Assistant', [
            'read' => true,
            'edit_lessons' => true,
            'view_student_progress' => true,
            'grade_assignments' => true,
            'grade_quizzes' => true,
            'upload_files' => true,
        ]);
        
        // Student
        wppermission()->createRole('student', 'Student', [
            'read' => true,
            'access_courses' => true,
            'submit_assignments' => true,
            'take_quizzes' => true,
            'view_own_progress' => true,
            'upload_files' => true,
        ]);
        
        // Course Coordinator
        wppermission()->createRole('course_coordinator', 'Course Coordinator', [
            'read' => true,
            'create_courses' => true,
            'edit_courses' => true,
            'delete_courses' => true,
            'publish_courses' => true,
            'manage_course_categories' => true,
            'enroll_students' => true,
            'view_student_progress' => true,
            'export_grades' => true,
            'view_quiz_analytics' => true,
        ]);
    }
    
    private static function setupCourseRestrictions() {
        // Restrict course access based on enrollment
        add_action('template_redirect', [self::class, 'restrictCourseAccess']);
        
        // Filter course queries for students
        add_action('pre_get_posts', [self::class, 'filterCourseQuery']);
        
        // Add enrollment check to REST API
        add_filter('rest_course_query', [self::class, 'filterRESTCourseQuery'], 10, 2);
    }
    
    public static function restrictCourseAccess() {
        if (!is_singular('course')) {
            return;
        }
        
        $course_id = get_the_ID();
        $user_id = get_current_user_id();
        
        // Allow instructors and coordinators
        if (can('edit_courses') || can('view_student_progress')) {
            return;
        }
        
        // Check if student is enrolled
        if (!self::isStudentEnrolled($user_id, $course_id)) {
            wp_die('You are not enrolled in this course.', 403);
        }
    }
    
    public static function isStudentEnrolled($user_id, $course_id) {
        $enrolled_courses = get_user_meta($user_id, 'enrolled_courses', true) ?: [];
        return in_array($course_id, $enrolled_courses);
    }
    
    public static function filterCourseQuery($query) {
        if (!is_admin() && $query->is_main_query() && $query->get('post_type') === 'course') {
            $user_id = get_current_user_id();
            
            // Only filter for students
            if (has_role('student', $user_id)) {
                $enrolled_courses = get_user_meta($user_id, 'enrolled_courses', true) ?: [];
                $query->set('post__in', $enrolled_courses);
            }
        }
    }
}

add_action('init', [LMSPermissions::class, 'setup']);
```

## Multi-tenant SaaS Platform

### Tenant Isolation and Permissions

```php
class SaaSTenantPermissions {
    
    public static function setup() {
        self::createTenantCapabilities();
        self::createTenantRoles();
        self::setupTenantMiddleware();
    }
    
    private static function createTenantCapabilities() {
        $capabilities = [
            // Tenant management
            'manage_tenant_settings' => ['Manage tenant configuration', 'tenant'],
            'view_tenant_analytics' => ['View tenant usage analytics', 'tenant'],
            'manage_tenant_users' => ['Manage users within tenant', 'tenant'],
            'upgrade_tenant_plan' => ['Upgrade tenant subscription', 'tenant'],
            
            // User management within tenant
            'invite_tenant_users' => ['Invite users to tenant', 'users'],
            'remove_tenant_users' => ['Remove users from tenant', 'users'],
            'assign_tenant_roles' => ['Assign roles within tenant', 'users'],
            
            // Data management
            'export_tenant_data' => ['Export tenant data', 'data'],
            'import_tenant_data' => ['Import data to tenant', 'data'],
            'delete_tenant_data' => ['Delete tenant data', 'data'],
            
            // Billing
            'view_billing_info' => ['View billing information', 'billing'],
            'manage_billing' => ['Manage billing and payments', 'billing'],
            'download_invoices' => ['Download billing invoices', 'billing'],
        ];
        
        foreach ($capabilities as $cap => $data) {
            wppermission()->createCapability($cap, $data[0], $data[1]);
        }
    }
    
    private static function createTenantRoles() {
        // Tenant Owner
        wppermission()->createRole('tenant_owner', 'Tenant Owner', [
            'read' => true,
            'manage_tenant_settings' => true,
            'view_tenant_analytics' => true,
            'manage_tenant_users' => true,
            'invite_tenant_users' => true,
            'remove_tenant_users' => true,
            'assign_tenant_roles' => true,
            'export_tenant_data' => true,
            'import_tenant_data' => true,
            'upgrade_tenant_plan' => true,
            'view_billing_info' => true,
            'manage_billing' => true,
            'download_invoices' => true,
        ]);
        
        // Tenant Admin
        wppermission()->createRole('tenant_admin', 'Tenant Admin', [
            'read' => true,
            'manage_tenant_settings' => true,
            'view_tenant_analytics' => true,
            'manage_tenant_users' => true,
            'invite_tenant_users' => true,
            'assign_tenant_roles' => true,
            'export_tenant_data' => true,
            'import_tenant_data' => true,
        ]);
        
        // Tenant Manager
        wppermission()->createRole('tenant_manager', 'Tenant Manager', [
            'read' => true,
            'view_tenant_analytics' => true,
            'invite_tenant_users' => true,
            'export_tenant_data' => true,
        ]);
        
        // Tenant User
        wppermission()->createRole('tenant_user', 'Tenant User', [
            'read' => true,
        ]);
    }
    
    private static function setupTenantMiddleware() {
        // Create tenant-specific middleware
        add_action('init', function() {
            // Register custom middleware
            $manager = wppermission()->getMiddlewareManager();
            
            // Middleware to check tenant access
            $manager->register('tenant_access', function($tenant_id) {
                return function($user_id = null) use ($tenant_id) {
                    $user_id = $user_id ?: get_current_user_id();
                    $user_tenant = get_user_meta($user_id, 'tenant_id', true);
                    return $user_tenant == $tenant_id;
                };
            });
            
            // Middleware to check tenant plan limits
            $manager->register('plan_limit', function($feature, $limit) {
                return function($user_id = null) use ($feature, $limit) {
                    $user_id = $user_id ?: get_current_user_id();
                    $tenant_id = get_user_meta($user_id, 'tenant_id', true);
                    $usage = get_option("tenant_{$tenant_id}_{$feature}_usage", 0);
                    return $usage < $limit;
                };
            });
        });
    }
    
    public static function getCurrentTenantId($user_id = null) {
        $user_id = $user_id ?: get_current_user_id();
        return get_user_meta($user_id, 'tenant_id', true);
    }
    
    public static function getTenantPlan($tenant_id) {
        return get_option("tenant_{$tenant_id}_plan", 'basic');
    }
    
    public static function checkTenantFeatureLimit($tenant_id, $feature) {
        $plan = self::getTenantPlan($tenant_id);
        $limits = [
            'basic' => ['users' => 5, 'storage' => 1024, 'api_calls' => 1000],
            'pro' => ['users' => 25, 'storage' => 10240, 'api_calls' => 10000],
            'enterprise' => ['users' => -1, 'storage' => -1, 'api_calls' => -1], // unlimited
        ];
        
        $limit = $limits[$plan][$feature] ?? 0;
        if ($limit === -1) return true; // unlimited
        
        $usage = get_option("tenant_{$tenant_id}_{$feature}_usage", 0);
        return $usage < $limit;
    }
}

// Tenant-aware API endpoint
class TenantAPIController {
    
    public function getUsersEndpoint($request) {
        $user_id = get_current_user_id();
        $tenant_id = SaaSTenantPermissions::getCurrentTenantId($user_id);
        
        // Check permissions with tenant context
        $middleware = wppermission()->getMiddlewareManager()->requireAll([
            wppermission()->getMiddlewareManager()->requireCapability('manage_tenant_users'),
            wppermission()->getMiddlewareManager()->apply('tenant_access', $tenant_id),
        ]);
        
        if (!$middleware()) {
            return new WP_Error('permission_denied', 'Permission denied', ['status' => 403]);
        }
        
        // Get users only from this tenant
        $users = get_users(['meta_key' => 'tenant_id', 'meta_value' => $tenant_id]);
        
        return rest_ensure_response($users);
    }
    
    public function inviteUserEndpoint($request) {
        $user_id = get_current_user_id();
        $tenant_id = SaaSTenantPermissions::getCurrentTenantId($user_id);
        
        // Check permissions and plan limits
        $middleware = wppermission()->getMiddlewareManager()->requireAll([
            wppermission()->getMiddlewareManager()->requireCapability('invite_tenant_users'),
            wppermission()->getMiddlewareManager()->apply('tenant_access', $tenant_id),
            wppermission()->getMiddlewareManager()->apply('plan_limit', 'users', 
                $this->getTenantUserLimit($tenant_id)),
        ]);
        
        if (!$middleware()) {
            return new WP_Error('permission_denied', 'Permission denied or limit exceeded', ['status' => 403]);
        }
        
        // Process invitation
        $email = sanitize_email($request['email']);
        $role = sanitize_text_field($request['role']);
        
        $invitation_id = $this->createInvitation($tenant_id, $email, $role);
        
        return rest_ensure_response(['invitation_id' => $invitation_id]);
    }
    
    private function getTenantUserLimit($tenant_id) {
        $plan = SaaSTenantPermissions::getTenantPlan($tenant_id);
        $limits = ['basic' => 5, 'pro' => 25, 'enterprise' => -1];
        return $limits[$plan] ?? 5;
    }
}

add_action('init', [SaaSTenantPermissions::class, 'setup']);
```

## API and Webhook Management

### API Key and Webhook Permissions

```php
class APIPermissionSystem {
    
    public static function setup() {
        self::createAPICapabilities();
        self::createAPIRoles();
        self::setupAPIMiddleware();
        self::registerAPIEndpoints();
    }
    
    private static function createAPICapabilities() {
        $capabilities = [
            // API management
            'create_api_keys' => ['Create API keys', 'api'],
            'manage_api_keys' => ['Manage API key settings', 'api'],
            'revoke_api_keys' => ['Revoke API keys', 'api'],
            'view_api_usage' => ['View API usage statistics', 'api'],
            
            // API access levels
            'api_read_access' => ['Read data via API', 'api_access'],
            'api_write_access' => ['Write data via API', 'api_access'],
            'api_delete_access' => ['Delete data via API', 'api_access'],
            'api_admin_access' => ['Administrative API access', 'api_access'],
            
            // Webhook management
            'create_webhooks' => ['Create webhooks', 'webhooks'],
            'manage_webhooks' => ['Manage webhook settings', 'webhooks'],
            'test_webhooks' => ['Test webhook delivery', 'webhooks'],
            'view_webhook_logs' => ['View webhook delivery logs', 'webhooks'],
            
            // Rate limiting
            'unlimited_api_rate' => ['Unlimited API rate limit', 'api_limits'],
            'high_api_rate' => ['High API rate limit', 'api_limits'],
            'standard_api_rate' => ['Standard API rate limit', 'api_limits'],
        ];
        
        foreach ($capabilities as $cap => $data) {
            wppermission()->createCapability($cap, $data[0], $data[1]);
        }
    }
    
    private static function createAPIRoles() {
        // API Administrator
        wppermission()->createRole('api_admin', 'API Administrator', [
            'read' => true,
            'create_api_keys' => true,
            'manage_api_keys' => true,
            'revoke_api_keys' => true,
            'view_api_usage' => true,
            'api_read_access' => true,
            'api_write_access' => true,
            'api_delete_access' => true,
            'api_admin_access' => true,
            'create_webhooks' => true,
            'manage_webhooks' => true,
            'test_webhooks' => true,
            'view_webhook_logs' => true,
            'unlimited_api_rate' => true,
        ]);
        
        // API Developer
        wppermission()->createRole('api_developer', 'API Developer', [
            'read' => true,
            'create_api_keys' => true,
            'view_api_usage' => true,
            'api_read_access' => true,
            'api_write_access' => true,
            'create_webhooks' => true,
            'test_webhooks' => true,
            'view_webhook_logs' => true,
            'high_api_rate' => true,
        ]);
        
        // API User
        wppermission()->createRole('api_user', 'API User', [
            'read' => true,
            'api_read_access' => true,
            'standard_api_rate' => true,
        ]);
    }
    
    private static function setupAPIMiddleware() {
        add_action('init', function() {
            $manager = wppermission()->getMiddlewareManager();
            
            // API key validation middleware
            $manager->register('valid_api_key', function() {
                return function($user_id = null) {
                    $api_key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
                    if (!$api_key) return false;
                    
                    $key_data = get_option("api_key_{$api_key}");
                    if (!$key_data || $key_data['status'] !== 'active') {
                        return false;
                    }
                    
                    // Check expiration
                    if (isset($key_data['expires']) && strtotime($key_data['expires']) < time()) {
                        return false;
                    }
                    
                    // Set user context for permission checks
                    wp_set_current_user($key_data['user_id']);
                    return true;
                };
            });
            
            // Rate limiting based on user capabilities
            $manager->register('api_rate_limit', function() {
                return function($user_id = null) {
                    $user_id = $user_id ?: get_current_user_id();
                    
                    // Determine rate limit based on capabilities
                    if (can('unlimited_api_rate', $user_id)) {
                        return true; // No limit
                    }
                    
                    $limit = 100; // Default
                    if (can('high_api_rate', $user_id)) {
                        $limit = 1000;
                    } elseif (can('standard_api_rate', $user_id)) {
                        $limit = 100;
                    }
                    
                    return self::checkRateLimit($user_id, $limit, 3600);
                };
            });
        });
    }
    
    private static function registerAPIEndpoints() {
        add_action('rest_api_init', function() {
            // API key management endpoints
            register_rest_route('api/v1', '/keys', [
                'methods' => 'POST',
                'callback' => [self::class, 'createAPIKey'],
                'permission_callback' => function() {
                    return can('create_api_keys');
                }
            ]);
            
            register_rest_route('api/v1', '/keys/(?P<key_id>\\d+)', [
                'methods' => 'DELETE',
                'callback' => [self::class, 'revokeAPIKey'],
                'permission_callback' => function() {
                    return can('revoke_api_keys');
                }
            ]);
            
            // Protected data endpoint
            register_rest_route('api/v1', '/data', [
                'methods' => 'GET',
                'callback' => [self::class, 'getData'],
                'permission_callback' => function() {
                    $middleware = wppermission()->getMiddlewareManager()->requireAll([
                        wppermission()->getMiddlewareManager()->apply('valid_api_key'),
                        wppermission()->getMiddlewareManager()->requireCapability('api_read_access'),
                        wppermission()->getMiddlewareManager()->apply('api_rate_limit'),
                    ]);
                    
                    return $middleware();
                }
            ]);
        });
    }
    
    public static function createAPIKey($request) {
        $user_id = get_current_user_id();
        $name = sanitize_text_field($request['name']);
        $capabilities = $request['capabilities'] ?? ['api_read_access'];
        $expires = $request['expires'] ?? null;
        
        // Generate unique API key
        $api_key = wp_generate_password(32, false);
        
        // Store API key data
        $key_data = [
            'user_id' => $user_id,
            'name' => $name,
            'capabilities' => $capabilities,
            'created' => current_time('mysql'),
            'expires' => $expires,
            'status' => 'active',
            'last_used' => null,
            'usage_count' => 0,
        ];
        
        update_option("api_key_{$api_key}", $key_data);
        
        // Log API key creation
        do_action('api_key_created', $api_key, $user_id);
        
        return rest_ensure_response([
            'api_key' => $api_key,
            'name' => $name,
            'capabilities' => $capabilities,
            'expires' => $expires,
        ]);
    }
    
    public static function getData($request) {
        // Log API usage
        $api_key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
        if ($api_key) {
            self::logAPIUsage($api_key);
        }
        
        // Return data based on user permissions
        $data = [];
        
        if (can('api_admin_access')) {
            $data = self::getAllData();
        } elseif (can('api_read_access')) {
            $data = self::getPublicData();
        }
        
        return rest_ensure_response($data);
    }
    
    private static function checkRateLimit($user_id, $limit, $window) {
        $key = "api_rate_limit_{$user_id}";
        $current = get_transient($key) ?: 0;
        
        if ($current >= $limit) {
            return false;
        }
        
        set_transient($key, $current + 1, $window);
        return true;
    }
    
    private static function logAPIUsage($api_key) {
        $key_data = get_option("api_key_{$api_key}");
        if ($key_data) {
            $key_data['last_used'] = current_time('mysql');
            $key_data['usage_count']++;
            update_option("api_key_{$api_key}", $key_data);
        }
    }
}

add_action('init', [APIPermissionSystem::class, 'setup']);
```

## Performance Monitoring

### Tracking Permission Usage

```php
class PermissionAnalytics {
    
    public static function setup() {
        self::setupLogging();
        self::setupDashboard();
    }
    
    private static function setupLogging() {
        // Log all permission checks
        add_action('wp_permission_capability_checked', [self::class, 'logCapabilityCheck'], 10, 3);
        add_action('wp_permission_middleware_executed', [self::class, 'logMiddlewareExecution'], 10, 3);
        
        // Log permission changes
        add_action('wp_permission_capability_granted', [self::class, 'logPermissionChange'], 10, 2);
        add_action('wp_permission_capability_revoked', [self::class, 'logPermissionChange'], 10, 2);
    }
    
    public static function logCapabilityCheck($capability, $user_id, $result) {
        $log_entry = [
            'timestamp' => microtime(true),
            'type' => 'capability_check',
            'capability' => $capability,
            'user_id' => $user_id,
            'result' => $result,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
        ];
        
        self::storeLogEntry($log_entry);
    }
    
    public static function generateUsageReport($start_date, $end_date) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'permission_logs';
        
        // Most checked capabilities
        $capability_stats = $wpdb->get_results($wpdb->prepare("
            SELECT capability, COUNT(*) as check_count, 
                   SUM(CASE WHEN result = 1 THEN 1 ELSE 0 END) as granted_count
            FROM {$table} 
            WHERE type = 'capability_check' 
            AND timestamp BETWEEN %s AND %s
            GROUP BY capability 
            ORDER BY check_count DESC 
            LIMIT 20
        ", $start_date, $end_date));
        
        // Most active users
        $user_stats = $wpdb->get_results($wpdb->prepare("
            SELECT user_id, COUNT(*) as activity_count
            FROM {$table} 
            WHERE timestamp BETWEEN %s AND %s
            GROUP BY user_id 
            ORDER BY activity_count DESC 
            LIMIT 20
        ", $start_date, $end_date));
        
        // Performance metrics
        $performance = $wpdb->get_row($wpdb->prepare("
            SELECT AVG(execution_time) as avg_time,
                   MAX(execution_time) as max_time,
                   COUNT(*) as total_checks
            FROM {$table} 
            WHERE type = 'middleware_execution'
            AND timestamp BETWEEN %s AND %s
        ", $start_date, $end_date));
        
        return [
            'capability_stats' => $capability_stats,
            'user_stats' => $user_stats,
            'performance' => $performance,
            'period' => ['start' => $start_date, 'end' => $end_date],
        ];
    }
    
    private static function storeLogEntry($entry) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'permission_logs';
        
        $wpdb->insert($table, $entry);
    }
    
    private static function setupDashboard() {
        add_action('wp_dashboard_setup', function() {
            if (can('view_permission_analytics')) {
                wp_add_dashboard_widget(
                    'permission_analytics',
                    'Permission Usage Analytics',
                    [self::class, 'renderDashboardWidget']
                );
            }
        });
    }
    
    public static function renderDashboardWidget() {
        $today = date('Y-m-d');
        $week_ago = date('Y-m-d', strtotime('-7 days'));
        
        $report = self::generateUsageReport($week_ago, $today);
        
        echo '<div class="permission-analytics">';
        echo '<h4>Last 7 Days</h4>';
        echo '<p>Total Permission Checks: ' . number_format($report['performance']->total_checks) . '</p>';
        echo '<p>Average Response Time: ' . round($report['performance']->avg_time * 1000, 2) . 'ms</p>';
        
        echo '<h5>Most Checked Capabilities</h5>';
        echo '<ul>';
        foreach (array_slice($report['capability_stats'], 0, 5) as $stat) {
            $success_rate = round(($stat->granted_count / $stat->check_count) * 100, 1);
            echo "<li>{$stat->capability}: {$stat->check_count} checks ({$success_rate}% granted)</li>";
        }
        echo '</ul>';
        echo '</div>';
    }
}

add_action('init', [PermissionAnalytics::class, 'setup']);
```

These examples demonstrate real-world implementations of the wp-permission library across different use cases. Each example shows how to structure permissions, create appropriate roles, implement middleware for access control, and integrate with WordPress hooks and APIs.

The key principles demonstrated include:
- **Granular Capabilities**: Creating specific, purpose-driven capabilities
- **Role Hierarchies**: Designing logical role structures
- **Middleware Composition**: Combining multiple permission checks
- **Performance Monitoring**: Tracking usage and performance
- **Security Integration**: Implementing comprehensive access controls

## Next Steps

- [Review the API Documentation](api.md)
- [Explore CLI Commands](cli.md)
- [Learn about Installation](installation.md)
- [Check out Middleware Patterns](middleware.md)