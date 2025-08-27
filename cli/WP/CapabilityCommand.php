<?php
/**
 * WP-CLI Capability Commands for WordPress Permission
 */

namespace WordPressPermission\CLI\WP;

use \WP_CLI_Command;
use \WP_CLI;

class CapabilityCommand extends \WP_CLI_Command
{
    /**
     * Create a new capability.
     *
     * ## OPTIONS
     *
     * <capability>
     * : Capability name
     *
     * [--description=<description>]
     * : Capability description
     *
     * [--group=<group>]
     * : Capability group (default: custom)
     *
     * ## EXAMPLES
     *
     *     wp permission:capability-create manage_products
     *     wp permission:capability-create view_analytics --description="View analytics dashboard" --group="analytics"
     *
     * @when after_wp_load
     */
    public function create($args, $assoc_args)
    {
        $capability = $args[0];
        $description = $assoc_args["description"] ?? "";
        $group = $assoc_args["group"] ?? "custom";

        $manager = wppermission()->getCapabilityManager();

        if ($manager->exists($capability)) {
            \WP_CLI::error("Capability '{$capability}' already exists.");
        }

        if ($manager->create($capability, $description, $group)) {
            \WP_CLI::success(
                "Capability '{$capability}' created successfully.",
            );
        } else {
            \WP_CLI::error("Failed to create capability '{$capability}'.");
        }
    }

    /**
     * Delete a custom capability.
     *
     * ## OPTIONS
     *
     * <capability>
     * : Capability name to delete
     *
     * [--yes]
     * : Skip confirmation
     *
     * ## EXAMPLES
     *
     *     wp permission:capability-delete manage_products
     *     wp permission:capability-delete manage_products --yes
     *
     * @when after_wp_load
     */
    public function delete($args, $assoc_args)
    {
        $capability = $args[0];
        $skip_confirmation = isset($assoc_args["yes"]);

        $manager = wppermission()->getCapabilityManager();

        if (!$manager->exists($capability)) {
            \WP_CLI::error("Capability '{$capability}' does not exist.");
        }

        $info = $manager->getInfo($capability);
        if ($info && $info["type"] === "default") {
            \WP_CLI::error(
                "Cannot delete WordPress default capability '{$capability}'.",
            );
        }

        if (!$skip_confirmation) {
            \WP_CLI::confirm(
                "Are you sure you want to delete capability '{$capability}'? This will remove it from all roles and users.",
            );
        }

        if ($manager->delete($capability)) {
            \WP_CLI::success(
                "Capability '{$capability}' deleted successfully.",
            );
        } else {
            \WP_CLI::error("Failed to delete capability '{$capability}'.");
        }
    }

    /**
     * List capabilities.
     *
     * ## OPTIONS
     *
     * [--group=<group>]
     * : Filter by group
     *
     * [--type=<type>]
     * : Filter by type (default, custom)
     *
     * [--format=<format>]
     * : Output format (table, csv, json). Default: table
     *
     * ## EXAMPLES
     *
     *     wp permission:capability-list
     *     wp permission:capability-list --group=custom
     *     wp permission:capability-list --type=default --format=json
     *
     * @when after_wp_load
     */
    public function listCapabilities($args, $assoc_args)
    {
        $manager = wppermission()->getCapabilityManager();
        $capabilities = $manager->getAll();

        // Apply filters
        if (isset($assoc_args["group"])) {
            $group = $assoc_args["group"];
            $capabilities = array_filter($capabilities, function ($cap) use (
                $group,
            ) {
                return ($cap["group"] ?? "unknown") === $group;
            });
        }

        if (isset($assoc_args["type"])) {
            $type = $assoc_args["type"];
            $capabilities = array_filter($capabilities, function ($cap) use (
                $type,
            ) {
                return ($cap["type"] ?? "unknown") === $type;
            });
        }

        // Format for display
        $items = [];
        foreach ($capabilities as $cap => $data) {
            $items[] = [
                "capability" => $cap,
                "group" => $data["group"] ?? "unknown",
                "type" => $data["type"] ?? "unknown",
                "description" => $data["description"] ?? "",
            ];
        }

        $format = $assoc_args["format"] ?? "table";
        \WP_CLI\Utils\format_items($format, $items, [
            "capability",
            "group",
            "type",
            "description",
        ]);
    }

    /**
     * Get capability information.
     *
     * ## OPTIONS
     *
     * <capability>
     * : Capability name
     *
     * [--format=<format>]
     * : Output format (table, json, yaml). Default: table
     *
     * ## EXAMPLES
     *
     *     wp permission:capability-info manage_products
     *     wp permission:capability-info edit_posts --format=json
     *
     * @when after_wp_load
     */
    public function info($args, $assoc_args)
    {
        $capability = $args[0];
        $manager = wppermission()->getCapabilityManager();

        if (!$manager->exists($capability)) {
            \WP_CLI::error("Capability '{$capability}' does not exist.");
        }

        $info = $manager->getInfo($capability);
        $role_manager = wppermission()->getRoleManager();
        $roles_with_cap = $role_manager->getRolesWithCapability($capability);

        $data = [
            "capability" => $capability,
            "group" => $info["group"] ?? "unknown",
            "type" => $info["type"] ?? "unknown",
            "description" => $info["description"] ?? "",
            "roles_with_capability" => implode(", ", $roles_with_cap),
            "role_count" => count($roles_with_cap),
        ];

        if (isset($info["created_at"])) {
            $data["created_at"] = $info["created_at"];
        }

        if (isset($info["created_by"])) {
            $user = get_user_by("id", $info["created_by"]);
            $data["created_by"] = $user
                ? $user->user_login
                : $info["created_by"];
        }

        $format = $assoc_args["format"] ?? "table";
        \WP_CLI\Utils\format_items($format, [$data], array_keys($data));
    }

    /**
     * Get capability usage statistics.
     *
     * ## OPTIONS
     *
     * <capability>
     * : Capability name
     *
     * [--format=<format>]
     * : Output format (table, json, yaml). Default: table
     *
     * ## EXAMPLES
     *
     *     wp permission:capability-stats edit_posts
     *     wp permission:capability-stats manage_products --format=json
     *
     * @when after_wp_load
     */
    public function stats($args, $assoc_args)
    {
        $capability = $args[0];
        $manager = wppermission()->getCapabilityManager();
        $user_manager = wppermission()->getUserPermissionManager();

        if (!$manager->exists($capability)) {
            \WP_CLI::error("Capability '{$capability}' does not exist.");
        }

        $stats = $user_manager->getCapabilityStats($capability);

        $format = $assoc_args["format"] ?? "table";
        \WP_CLI\Utils\format_items($format, [$stats], array_keys($stats));
    }
}
