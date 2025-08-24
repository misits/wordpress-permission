<?php
/**
 * WP-CLI Role Commands for WordPress Permission
 */

namespace WordPressPermission\CLI\WP;

use \WP_CLI_Command;
use \WP_CLI;

class RoleCommand extends \WP_CLI_Command
{
    /**
     * Create a new role.
     *
     * ## OPTIONS
     *
     * <role>
     * : Role slug
     *
     * <display_name>
     * : Role display name
     *
     * [--capabilities=<capabilities>]
     * : Comma-separated list of capabilities
     *
     * [--clone=<source_role>]
     * : Clone capabilities from existing role
     *
     * ## EXAMPLES
     *
     *     wp borps permission:role-create shop_manager "Shop Manager"
     *     wp borps permission:role-create product_editor "Product Editor" --capabilities="edit_posts,publish_posts"
     *     wp borps permission:role-create custom_editor "Custom Editor" --clone=editor
     *
     * @when after_wp_load
     */
    public function create($args, $assoc_args)
    {
        $role = $args[0];
        $display_name = $args[1];
        $manager = wppermission()->getRoleManager();

        if ($manager->exists($role)) {
            \WP_CLI::error("Role '{$role}' already exists.");
        }

        $capabilities = [];

        // Handle clone option
        if (isset($assoc_args["clone"])) {
            $source_role = $assoc_args["clone"];
            if (!$manager->exists($source_role)) {
                \WP_CLI::error("Source role '{$source_role}' does not exist.");
            }
            $capabilities = array_keys($manager->getCapabilities($source_role));
        }

        // Handle capabilities option
        if (isset($assoc_args["capabilities"])) {
            $caps = explode(",", $assoc_args["capabilities"]);
            $capabilities = array_merge(
                $capabilities,
                array_map("trim", $caps),
            );
        }

        // Convert to format expected by add_role
        $cap_array = [];
        foreach ($capabilities as $cap) {
            $cap_array[trim($cap)] = true;
        }

        if ($manager->create($role, $display_name, $cap_array)) {
            \WP_CLI::success(
                "Role '{$role}' created successfully with " .
                    count($capabilities) .
                    " capabilities.",
            );
        } else {
            \WP_CLI::error("Failed to create role '{$role}'.");
        }
    }

    /**
     * Delete a custom role.
     *
     * ## OPTIONS
     *
     * <role>
     * : Role slug to delete
     *
     * [--yes]
     * : Skip confirmation
     *
     * ## EXAMPLES
     *
     *     wp borps permission:role-delete shop_manager
     *     wp borps permission:role-delete shop_manager --yes
     *
     * @when after_wp_load
     */
    public function delete($args, $assoc_args)
    {
        $role = $args[0];
        $skip_confirmation = isset($assoc_args["yes"]);
        $manager = wppermission()->getRoleManager();

        if (!$manager->exists($role)) {
            \WP_CLI::error("Role '{$role}' does not exist.");
        }

        // Check if it's a default role
        $default_roles = [
            "administrator",
            "editor",
            "author",
            "contributor",
            "subscriber",
        ];
        if (in_array($role, $default_roles)) {
            \WP_CLI::error("Cannot delete WordPress default role '{$role}'.");
        }

        if (!$skip_confirmation) {
            \WP_CLI::confirm(
                "Are you sure you want to delete role '{$role}'? Users with this role will lose access.",
            );
        }

        if ($manager->delete($role)) {
            \WP_CLI::success("Role '{$role}' deleted successfully.");
        } else {
            \WP_CLI::error("Failed to delete role '{$role}'.");
        }
    }

    /**
     * Clone an existing role.
     *
     * ## OPTIONS
     *
     * <source_role>
     * : Source role to clone
     *
     * <new_role>
     * : New role slug
     *
     * <display_name>
     * : New role display name
     *
     * ## EXAMPLES
     *
     *     wp borps permission:role-clone editor content_editor "Content Editor"
     *     wp borps permission:role-clone administrator super_admin "Super Administrator"
     *
     * @when after_wp_load
     */
    public function clone($args, $assoc_args)
    {
        $source_role = $args[0];
        $new_role = $args[1];
        $display_name = $args[2];
        $manager = wppermission()->getRoleManager();

        if (!$manager->exists($source_role)) {
            \WP_CLI::error("Source role '{$source_role}' does not exist.");
        }

        if ($manager->exists($new_role)) {
            \WP_CLI::error("Target role '{$new_role}' already exists.");
        }

        if ($manager->clone($source_role, $new_role, $display_name)) {
            $cap_count = count($manager->getCapabilities($new_role));
            \WP_CLI::success(
                "Role '{$new_role}' cloned from '{$source_role}' with {$cap_count} capabilities.",
            );
        } else {
            \WP_CLI::error("Failed to clone role.");
        }
    }

    /**
     * List roles.
     *
     * ## OPTIONS
     *
     * [--type=<type>]
     * : Filter by type (default, custom, all). Default: all
     *
     * [--format=<format>]
     * : Output format (table, csv, json). Default: table
     *
     * ## EXAMPLES
     *
     *     wp borps permission:role-list
     *     wp borps permission:role-list --type=custom
     *     wp borps permission:role-list --format=json
     *
     * @when after_wp_load
     */
    public function listRoles($args, $assoc_args)
    {
        $manager = wppermission()->getRoleManager();
        $type = $assoc_args["type"] ?? "all";

        if ($type === "custom") {
            $custom_roles = $manager->getCustomRoles();
            $roles = [];
            foreach ($custom_roles as $role => $data) {
                $roles[$role] = [
                    "name" => $data["display_name"],
                    "capabilities" => $data["capabilities"],
                ];
            }
        } else {
            $roles = $manager->getAll();
            if ($type === "default") {
                $custom_roles = array_keys($manager->getCustomRoles());
                $roles = array_diff_key($roles, array_flip($custom_roles));
            }
        }

        // Format for display
        $items = [];
        foreach ($roles as $role => $data) {
            $custom_roles = $manager->getCustomRoles();
            $is_custom = isset($custom_roles[$role]);

            $items[] = [
                "role" => $role,
                "display_name" => $data["name"],
                "capabilities" => count($data["capabilities"]),
                "type" => $is_custom ? "custom" : "default",
            ];
        }

        // Sort by capability count (descending)
        usort($items, function ($a, $b) {
            return $b["capabilities"] - $a["capabilities"];
        });

        $format = $assoc_args["format"] ?? "table";
        \WP_CLI\Utils\format_items($format, $items, [
            "role",
            "display_name",
            "capabilities",
            "type",
        ]);
    }

    /**
     * Get role information.
     *
     * ## OPTIONS
     *
     * <role>
     * : Role slug
     *
     * [--format=<format>]
     * : Output format (table, json, yaml). Default: table
     *
     * [--show-capabilities]
     * : Show all capabilities
     *
     * ## EXAMPLES
     *
     *     wp borps permission:role-info editor
     *     wp borps permission:role-info shop_manager --show-capabilities --format=json
     *
     * @when after_wp_load
     */
    public function info($args, $assoc_args)
    {
        $role = $args[0];
        $manager = wppermission()->getRoleManager();
        $show_capabilities = isset($assoc_args["show-capabilities"]);

        if (!$manager->exists($role)) {
            \WP_CLI::error("Role '{$role}' does not exist.");
        }

        $display_name = $manager->getDisplayName($role);
        $capabilities = $manager->getCapabilities($role);
        $custom_roles = $manager->getCustomRoles();
        $is_custom = isset($custom_roles[$role]);

        $data = [
            "role" => $role,
            "display_name" => $display_name,
            "type" => $is_custom ? "custom" : "default",
            "capability_count" => count($capabilities),
        ];

        if ($is_custom && isset($custom_roles[$role]["created_at"])) {
            $data["created_at"] = $custom_roles[$role]["created_at"];
        }

        if ($show_capabilities) {
            $data["capabilities"] = implode(", ", array_keys($capabilities));
        }

        // Get user count
        $users = get_users(["role" => $role]);
        $data["user_count"] = count($users);

        $format = $assoc_args["format"] ?? "table";
        \WP_CLI\Utils\format_items($format, [$data], array_keys($data));
    }

    /**
     * Add capability to role.
     *
     * ## OPTIONS
     *
     * <role>
     * : Role slug
     *
     * <capability>
     * : Capability to add
     *
     * ## EXAMPLES
     *
     *     wp borps permission:role-add-cap editor manage_products
     *     wp borps permission:role-add-cap shop_manager view_analytics
     *
     * @when after_wp_load
     */
    public function addCapability($args, $assoc_args)
    {
        $role = $args[0];
        $capability = $args[1];
        $manager = wppermission()->getRoleManager();

        if (!$manager->exists($role)) {
            \WP_CLI::error("Role '{$role}' does not exist.");
        }

        if ($manager->hasCapability($role, $capability)) {
            \WP_CLI::warning(
                "Role '{$role}' already has capability '{$capability}'.",
            );
            return;
        }

        if ($manager->addCapability($role, $capability)) {
            \WP_CLI::success(
                "Capability '{$capability}' added to role '{$role}'.",
            );
        } else {
            \WP_CLI::error(
                "Failed to add capability '{$capability}' to role '{$role}'.",
            );
        }
    }

    /**
     * Remove capability from role.
     *
     * ## OPTIONS
     *
     * <role>
     * : Role slug
     *
     * <capability>
     * : Capability to remove
     *
     * ## EXAMPLES
     *
     *     wp borps permission:role-remove-cap editor delete_posts
     *     wp borps permission:role-remove-cap shop_manager manage_options
     *
     * @when after_wp_load
     */
    public function removeCapability($args, $assoc_args)
    {
        $role = $args[0];
        $capability = $args[1];
        $manager = wppermission()->getRoleManager();

        if (!$manager->exists($role)) {
            \WP_CLI::error("Role '{$role}' does not exist.");
        }

        if (!$manager->hasCapability($role, $capability)) {
            \WP_CLI::warning(
                "Role '{$role}' does not have capability '{$capability}'.",
            );
            return;
        }

        if ($manager->removeCapability($role, $capability)) {
            \WP_CLI::success(
                "Capability '{$capability}' removed from role '{$role}'.",
            );
        } else {
            \WP_CLI::error(
                "Failed to remove capability '{$capability}' from role '{$role}'.",
            );
        }
    }
}
