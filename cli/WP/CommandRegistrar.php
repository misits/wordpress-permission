<?php

namespace WordPressPermission\CLI\WP;

/**
 * WP-CLI Command Registrar for WordPress Permission
 */
class CommandRegistrar
{
    /**
     * Register all WP-CLI commands
     */
    public static function register()
    {
        if (!class_exists("WP_CLI")) {
            return;
        }

        // Load command classes
        require_once __DIR__ . "/CapabilityCommand.php";
        require_once __DIR__ . "/RoleCommand.php";
        require_once __DIR__ . "/UserCommand.php";
        require_once __DIR__ . "/HelpCommand.php";

        // Register help command
        \WP_CLI::add_command("permission:help", HelpCommand::class);

        // Register capability commands
        \WP_CLI::add_command("permission:capability-create", [
            CapabilityCommand::class,
            "create",
        ]);
        \WP_CLI::add_command("permission:capability-delete", [
            CapabilityCommand::class,
            "delete",
        ]);
        \WP_CLI::add_command("permission:capability-list", [
            CapabilityCommand::class,
            "listCapabilities",
        ]);
        \WP_CLI::add_command("permission:capability-info", [
            CapabilityCommand::class,
            "info",
        ]);
        \WP_CLI::add_command("permission:capability-stats", [
            CapabilityCommand::class,
            "stats",
        ]);

        // Register role commands
        \WP_CLI::add_command("permission:role-create", [
            RoleCommand::class,
            "create",
        ]);
        \WP_CLI::add_command("permission:role-delete", [
            RoleCommand::class,
            "delete",
        ]);
        \WP_CLI::add_command("permission:role-clone", [
            RoleCommand::class,
            "clone",
        ]);
        \WP_CLI::add_command("permission:role-list", [
            RoleCommand::class,
            "listRoles",
        ]);
        \WP_CLI::add_command("permission:role-info", [
            RoleCommand::class,
            "info",
        ]);
        \WP_CLI::add_command("permission:role-add-cap", [
            RoleCommand::class,
            "addCapability",
        ]);
        \WP_CLI::add_command("permission:role-remove-cap", [
            RoleCommand::class,
            "removeCapability",
        ]);

        // Register user commands
        \WP_CLI::add_command("permission:user-grant", [
            UserCommand::class,
            "grant",
        ]);
        \WP_CLI::add_command("permission:user-revoke", [
            UserCommand::class,
            "revoke",
        ]);
        \WP_CLI::add_command("permission:user-assign-role", [
            UserCommand::class,
            "assignRole",
        ]);
        \WP_CLI::add_command("permission:user-remove-role", [
            UserCommand::class,
            "removeRole",
        ]);
        \WP_CLI::add_command("permission:user-info", [
            UserCommand::class,
            "info",
        ]);
        \WP_CLI::add_command("permission:user-compare", [
            UserCommand::class,
            "compare",
        ]);
        \WP_CLI::add_command("permission:user-bulk-grant", [
            UserCommand::class,
            "bulkGrant",
        ]);
        \WP_CLI::add_command("permission:user-bulk-assign", [
            UserCommand::class,
            "bulkAssign",
        ]);
    }
}
