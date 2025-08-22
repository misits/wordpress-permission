<?php
/**
 * WP-CLI Help Command for WordPress Permission
 */

namespace WordPressPermission\CLI\WP;

use \WP_CLI_Command;
use \WP_CLI;
class HelpCommand extends \WP_CLI_Command
{
    /**
     * Show WordPress Permission CLI help.
     *
     * ## DESCRIPTION
     *
     * WordPress Permission provides a comprehensive system for managing
     * WordPress capabilities, roles, and user permissions with an intuitive API.
     *
     * ## AVAILABLE COMMANDS
     *
     * ### Capability Management
     *   capability:create     Create a new capability
     *   capability:delete     Delete a custom capability
     *   capability:list       List all capabilities
     *   capability:info       Get capability information
     *   capability:stats      Get capability usage statistics
     *
     * ### Role Management
     *   role:create          Create a new role
     *   role:delete          Delete a custom role
     *   role:clone           Clone an existing role
     *   role:list            List all roles
     *   role:info            Get role information
     *   role:add-cap         Add capability to role
     *   role:remove-cap      Remove capability from role
     *
     * ### User Management
     *   user:grant           Grant capability to user
     *   user:revoke          Revoke capability from user
     *   user:assign-role     Assign role to user
     *   user:remove-role     Remove role from user
     *   user:info            Get user permission information
     *   user:compare         Compare capabilities between users
     *   user:bulk-grant      Bulk grant capability to multiple users
     *   user:bulk-assign     Bulk assign role to multiple users
     *
     * ## EXAMPLES
     *
     * # Create a new capability
     * wp wppermission capability:create manage_products --description="Manage product catalog"
     *
     * # Create a new role with capabilities
     * wp wppermission role:create shop_manager "Shop Manager" --capabilities="edit_posts,manage_products"
     *
     * # Grant capability to specific user
     * wp wppermission user:grant admin manage_products
     *
     * # Assign role to user
     * wp wppermission user:assign-role user123 shop_manager
     *
     * # List all custom capabilities
     * wp wppermission capability:list --type=custom
     *
     * # Get detailed user permission info
     * wp wppermission user:info admin --show-capabilities
     *
     * # Bulk operations
     * wp wppermission user:bulk-grant view_analytics --role=editor
     *
     * ## GLOBAL OPTIONS
     *
     * Most commands support these options:
     *   --format=<format>     Output format (table, json, csv, yaml)
     *   --yes                 Skip confirmation prompts
     *   --dry-run             Show what would be done without doing it
     *
     * ## MORE INFO
     *
     * For detailed help on any command:
     *   wp help wppermission <command>
     *
     * Examples:
     *   wp help wppermission capability:create
     *   wp help wppermission role:list
     *   wp help wppermission user:bulk-grant
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assoc_args)
    {
        \WP_CLI::line("");
        \WP_CLI::line("WordPress Permission CLI Help");
        \WP_CLI::line("============================");
        \WP_CLI::line("");

        \WP_CLI::line(
            "A comprehensive permission management system for WordPress.",
        );
        \WP_CLI::line(
            "Manage capabilities, roles, and user permissions with powerful CLI commands.",
        );
        \WP_CLI::line("");

        \WP_CLI::line("QUICK START:");
        \WP_CLI::line("");
        \WP_CLI::line("  # List all available commands");
        \WP_CLI::line("  wp wppermission --help");
        \WP_CLI::line("");
        \WP_CLI::line("  # Create a new capability");
        \WP_CLI::line("  wp wppermission capability:create manage_products");
        \WP_CLI::line("");
        \WP_CLI::line("  # Create a new role");
        \WP_CLI::line(
            '  wp wppermission role:create shop_manager "Shop Manager"',
        );
        \WP_CLI::line("");
        \WP_CLI::line("  # Grant capability to user");
        \WP_CLI::line("  wp wppermission user:grant admin manage_products");
        \WP_CLI::line("");

        \WP_CLI::line("COMMAND CATEGORIES:");
        \WP_CLI::line("");
        \WP_CLI::line(
            "  capability:*    Manage capabilities (create, delete, list, info, stats)",
        );
        \WP_CLI::line(
            "  role:*          Manage roles (create, delete, clone, list, info, add/remove caps)",
        );
        \WP_CLI::line(
            "  user:*          Manage user permissions (grant, revoke, assign, info, bulk operations)",
        );
        \WP_CLI::line("");

        \WP_CLI::line("GET DETAILED HELP:");
        \WP_CLI::line("");
        \WP_CLI::line("  wp help wppermission <command>");
        \WP_CLI::line("");
        \WP_CLI::line("Examples:");
        \WP_CLI::line("  wp help wppermission capability:create");
        \WP_CLI::line("  wp help wppermission role:list");
        \WP_CLI::line("  wp help wppermission user:bulk-grant");
        \WP_CLI::line("");

        // Show library stats
        $this->showStats();
    }

    /**
     * Show library statistics
     */
    private function showStats()
    {
        try {
            $capability_manager = wppermission()->getCapabilityManager();
            $role_manager = wppermission()->getRoleManager();
            $middleware_manager = wppermission()->getMiddlewareManager();

            $custom_caps = $capability_manager->getCustomCapabilities();
            $custom_roles = $role_manager->getCustomRoles();
            $middleware_stats = $middleware_manager->getStats();

            \WP_CLI::line("CURRENT SYSTEM STATUS:");
            \WP_CLI::line("");
            \WP_CLI::line("  Custom Capabilities: " . count($custom_caps));
            \WP_CLI::line("  Custom Roles: " . count($custom_roles));
            \WP_CLI::line(
                "  Available Middleware: " .
                    $middleware_stats["total_middleware"],
            );
            \WP_CLI::line("  Total Users: " . count_users()["total_users"]);
            \WP_CLI::line("");
        } catch (\Exception $e) {
            \WP_CLI::line("System status unavailable.");
            \WP_CLI::line("");
        }

        \WP_CLI::line("WordPress Permission v" . WPPERMISSION_VERSION);
        \WP_CLI::line("");
    }
}
