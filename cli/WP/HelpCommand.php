<?php

namespace WordPressPermission\CLI\WP;

use \WP_CLI_Command;
use \WP_CLI;

/**
 * WordPress Permission Help commands for WP-CLI
 */
class HelpCommand extends \WP_CLI_Command
{
    /**
     * Show WordPress Permission CLI help
     *
     * ## EXAMPLES
     *
     *     wp wppermission help
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke($args, $assoc_args)
    {
        \WP_CLI::line("");
        \WP_CLI::line("NAME");
        \WP_CLI::line("WordPress Permission");

        \WP_CLI::line("");
        \WP_CLI::line("DESCRIPTION");
        \WP_CLI::line(
            "WordPress Permission is a comprehensive permission management system for WordPress.",
        );
        \WP_CLI::line(
            "It provides capabilities, roles, user management, and middleware for secure applications.",
        );

        \WP_CLI::line("");
        \WP_CLI::line("USAGE");
        \WP_CLI::line("  wp wppermission <command> [<args>]");

        \WP_CLI::line("");
        
        \WP_CLI::line("CAPABILITY COMMANDS:");
        \WP_CLI::line("  wp wppermission capability:create <name>     Create a new capability");
        \WP_CLI::line("    --description=<description>                Capability description");
        \WP_CLI::line("    --group=<group>                            Capability group");
        \WP_CLI::line("  wp wppermission capability:delete <name>     Delete a custom capability");
        \WP_CLI::line("    --yes                                      Skip confirmation");
        \WP_CLI::line("  wp wppermission capability:list              List all capabilities");
        \WP_CLI::line("    --type=<type>                              Filter by type (default, custom)");
        \WP_CLI::line("    --group=<group>                            Filter by group");
        \WP_CLI::line("    --format=<format>                          Output format (table, csv, json, yaml)");
        \WP_CLI::line("  wp wppermission capability:info <name>       Get capability information");
        \WP_CLI::line("  wp wppermission capability:stats <name>      Get capability usage statistics");
        \WP_CLI::line("");
        
        \WP_CLI::line("ROLE COMMANDS:");
        \WP_CLI::line("  wp wppermission role:create <role> <name>    Create a new role");
        \WP_CLI::line("    --capabilities=<caps>                      Comma-separated capabilities");
        \WP_CLI::line("    --clone=<source_role>                      Clone from existing role");
        \WP_CLI::line("  wp wppermission role:delete <role>           Delete a custom role");
        \WP_CLI::line("    --yes                                      Skip confirmation");
        \WP_CLI::line("  wp wppermission role:clone <source> <new>    Clone an existing role");
        \WP_CLI::line("  wp wppermission role:list                    List all roles");
        \WP_CLI::line("    --type=<type>                              Filter by type (default, custom)");
        \WP_CLI::line("    --format=<format>                          Output format (table, csv, json, yaml)");
        \WP_CLI::line("  wp wppermission role:info <role>             Get role information");
        \WP_CLI::line("    --show-capabilities                        Show all capabilities");
        \WP_CLI::line("  wp wppermission role:add-cap <role> <cap>    Add capability to role");
        \WP_CLI::line("  wp wppermission role:remove-cap <role> <cap> Remove capability from role");
        \WP_CLI::line("");
        
        \WP_CLI::line("USER COMMANDS:");
        \WP_CLI::line("  wp wppermission user:grant <user> <cap>      Grant capability to user");
        \WP_CLI::line("  wp wppermission user:revoke <user> <cap>     Revoke capability from user");
        \WP_CLI::line("  wp wppermission user:assign-role <user> <role> Assign role to user");
        \WP_CLI::line("    --replace                                  Replace all existing roles");
        \WP_CLI::line("  wp wppermission user:remove-role <user> <role> Remove role from user");
        \WP_CLI::line("  wp wppermission user:info <user>             Get user permission information");
        \WP_CLI::line("    --show-capabilities                        Show all capabilities");
        \WP_CLI::line("    --format=<format>                          Output format (table, json, yaml)");
        \WP_CLI::line("  wp wppermission user:compare <user1> <user2> Compare capabilities between users");
        \WP_CLI::line("  wp wppermission user:bulk-grant <cap>        Bulk grant capability");
        \WP_CLI::line("    --users=<users>                            Comma-separated user list");
        \WP_CLI::line("    --role=<role>                              Grant to all users with role");
        \WP_CLI::line("    --dry-run                                  Show what would be done");
        \WP_CLI::line("  wp wppermission user:bulk-assign <role> <users> Bulk assign role to users");
        \WP_CLI::line("    --dry-run                                  Show what would be done");
        \WP_CLI::line("");
        
        \WP_CLI::line("EXAMPLES:");
        \WP_CLI::line("  wp wppermission capability:create manage_products --description=\"Manage products\"");
        \WP_CLI::line("  wp wppermission role:create shop_manager \"Shop Manager\" --capabilities=\"edit_posts,manage_products\"");
        \WP_CLI::line("  wp wppermission user:grant admin manage_products");
        \WP_CLI::line("  wp wppermission user:bulk-grant view_analytics --role=editor");
        \WP_CLI::line("");
        
        \WP_CLI::line("MODE CONFIGURATION:");
        \WP_CLI::line("Set WPPERMISSION_MODE in your functions.php or plugin:");
        \WP_CLI::line("  define('WPPERMISSION_MODE', 'theme');   // For theme development");
        \WP_CLI::line("  define('WPPERMISSION_MODE', 'plugin');  // For plugin development");
        \WP_CLI::line("");
        
        \WP_CLI::line("VERSION:");
        if (defined('WPPERMISSION_VERSION')) {
            \WP_CLI::line("  WordPress Permission: " . WPPERMISSION_VERSION);
        }
        \WP_CLI::line("");
    }
}
