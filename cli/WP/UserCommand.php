<?php
/**
 * WP-CLI User Commands for WordPress Permission
 */

namespace WordPressPermission\CLI\WP;

use \WP_CLI_Command;
use \WP_CLI;

class UserCommand extends \WP_CLI_Command
{
    /**
     * Grant capability to user.
     *
     * ## OPTIONS
     *
     * <user>
     * : User ID, login, or email
     *
     * <capability>
     * : Capability to grant
     *
     * ## EXAMPLES
     *
     *     wp permission:user-grant admin manage_products
     *     wp permission:user-grant 123 view_analytics
     *     wp permission:user-grant user@example.com edit_others_posts
     *
     * @when after_wp_load
     */
    public function grant($args, $assoc_args)
    {
        $user_identifier = $args[0];
        $capability = $args[1];
        $manager = wppermission()->getUserPermissionManager();

        $user = $this->getUser($user_identifier);
        if (!$user) {
            \WP_CLI::error("User '{$user_identifier}' not found.");
        }

        if ($manager->userCan($user->ID, $capability)) {
            \WP_CLI::warning(
                "User '{$user->user_login}' already has capability '{$capability}'.",
            );
            return;
        }

        if ($manager->grantCapability($user->ID, $capability)) {
            \WP_CLI::success(
                "Capability '{$capability}' granted to user '{$user->user_login}'.",
            );
        } else {
            \WP_CLI::error(
                "Failed to grant capability '{$capability}' to user '{$user->user_login}'.",
            );
        }
    }

    /**
     * Revoke capability from user.
     *
     * ## OPTIONS
     *
     * <user>
     * : User ID, login, or email
     *
     * <capability>
     * : Capability to revoke
     *
     * ## EXAMPLES
     *
     *     wp permission:user-revoke admin delete_users
     *     wp permission:user-revoke 123 manage_products
     *
     * @when after_wp_load
     */
    public function revoke($args, $assoc_args)
    {
        $user_identifier = $args[0];
        $capability = $args[1];
        $manager = wppermission()->getUserPermissionManager();

        $user = $this->getUser($user_identifier);
        if (!$user) {
            \WP_CLI::error("User '{$user_identifier}' not found.");
        }

        if (!$manager->userCan($user->ID, $capability)) {
            \WP_CLI::warning(
                "User '{$user->user_login}' does not have capability '{$capability}'.",
            );
            return;
        }

        if ($manager->revokeCapability($user->ID, $capability)) {
            \WP_CLI::success(
                "Capability '{$capability}' revoked from user '{$user->user_login}'.",
            );
        } else {
            \WP_CLI::error(
                "Failed to revoke capability '{$capability}' from user '{$user->user_login}'.",
            );
        }
    }

    /**
     * Assign role to user.
     *
     * ## OPTIONS
     *
     * <user>
     * : User ID, login, or email
     *
     * <role>
     * : Role to assign
     *
     * [--replace]
     * : Replace all existing roles
     *
     * ## EXAMPLES
     *
     *     wp permission:user-assign-role admin shop_manager
     *     wp permission:user-assign-role 123 editor --replace
     *
     * @when after_wp_load
     */
    public function assignRole($args, $assoc_args)
    {
        $user_identifier = $args[0];
        $role = $args[1];
        $replace = isset($assoc_args["replace"]);
        $manager = wppermission()->getUserPermissionManager();

        $user = $this->getUser($user_identifier);
        if (!$user) {
            \WP_CLI::error("User '{$user_identifier}' not found.");
        }

        $role_manager = wppermission()->getRoleManager();
        if (!$role_manager->exists($role)) {
            \WP_CLI::error("Role '{$role}' does not exist.");
        }

        if ($replace) {
            if ($manager->setRole($user->ID, $role)) {
                \WP_CLI::success(
                    "Role '{$role}' set for user '{$user->user_login}' (replaced all existing roles).",
                );
            } else {
                \WP_CLI::error(
                    "Failed to set role '{$role}' for user '{$user->user_login}'.",
                );
            }
        } else {
            if ($manager->userHasRole($user->ID, $role)) {
                \WP_CLI::warning(
                    "User '{$user->user_login}' already has role '{$role}'.",
                );
                return;
            }

            if ($manager->assignRole($user->ID, $role)) {
                \WP_CLI::success(
                    "Role '{$role}' assigned to user '{$user->user_login}'.",
                );
            } else {
                \WP_CLI::error(
                    "Failed to assign role '{$role}' to user '{$user->user_login}'.",
                );
            }
        }
    }

    /**
     * Remove role from user.
     *
     * ## OPTIONS
     *
     * <user>
     * : User ID, login, or email
     *
     * <role>
     * : Role to remove
     *
     * ## EXAMPLES
     *
     *     wp permission:user-remove-role admin editor
     *     wp permission:user-remove-role 123 shop_manager
     *
     * @when after_wp_load
     */
    public function removeRole($args, $assoc_args)
    {
        $user_identifier = $args[0];
        $role = $args[1];
        $manager = wppermission()->getUserPermissionManager();

        $user = $this->getUser($user_identifier);
        if (!$user) {
            \WP_CLI::error("User '{$user_identifier}' not found.");
        }

        if (!$manager->userHasRole($user->ID, $role)) {
            \WP_CLI::warning(
                "User '{$user->user_login}' does not have role '{$role}'.",
            );
            return;
        }

        if ($manager->removeRole($user->ID, $role)) {
            \WP_CLI::success(
                "Role '{$role}' removed from user '{$user->user_login}'.",
            );
        } else {
            \WP_CLI::error(
                "Failed to remove role '{$role}' from user '{$user->user_login}'.",
            );
        }
    }

    /**
     * Get user permission information.
     *
     * ## OPTIONS
     *
     * <user>
     * : User ID, login, or email
     *
     * [--format=<format>]
     * : Output format (table, json, yaml). Default: table
     *
     * [--show-capabilities]
     * : Show all capabilities
     *
     * ## EXAMPLES
     *
     *     wp permission:user-info admin
     *     wp permission:user-info 123 --show-capabilities --format=json
     *
     * @when after_wp_load
     */
    public function info($args, $assoc_args)
    {
        $user_identifier = $args[0];
        $show_capabilities = isset($assoc_args["show-capabilities"]);
        $manager = wppermission()->getUserPermissionManager();

        $user = $this->getUser($user_identifier);
        if (!$user) {
            \WP_CLI::error("User '{$user_identifier}' not found.");
        }

        $summary = $manager->getUserPermissionSummary($user->ID);

        $data = [
            "user_id" => $summary["user_id"],
            "username" => $summary["username"],
            "display_name" => $summary["display_name"],
            "email" => $summary["email"],
            "roles" => implode(", ", $summary["roles"]),
            "capability_count" => $summary["capability_count"],
            "is_super_admin" => $summary["is_super_admin"] ? "Yes" : "No",
            "registration_date" => $summary["registration_date"],
        ];

        if ($show_capabilities) {
            $data["capabilities"] = implode(
                ", ",
                array_keys($summary["all_capabilities"]),
            );
        }

        $format = $assoc_args["format"] ?? "table";
        \WP_CLI\Utils\format_items($format, [$data], array_keys($data));
    }

    /**
     * Compare capabilities between two users.
     *
     * ## OPTIONS
     *
     * <user1>
     * : First user (ID, login, or email)
     *
     * <user2>
     * : Second user (ID, login, or email)
     *
     * [--format=<format>]
     * : Output format (table, json, yaml). Default: table
     *
     * ## EXAMPLES
     *
     *     wp permission:user-compare admin editor
     *     wp permission:user-compare 123 456 --format=json
     *
     * @when after_wp_load
     */
    public function compare($args, $assoc_args)
    {
        $user1_identifier = $args[0];
        $user2_identifier = $args[1];
        $manager = wppermission()->getUserPermissionManager();

        $user1 = $this->getUser($user1_identifier);
        $user2 = $this->getUser($user2_identifier);

        if (!$user1) {
            \WP_CLI::error("User '{$user1_identifier}' not found.");
        }

        if (!$user2) {
            \WP_CLI::error("User '{$user2_identifier}' not found.");
        }

        $comparison = $manager->compareUsers($user1->ID, $user2->ID);

        $data = [
            "user_1" => $user1->user_login,
            "user_2" => $user2->user_login,
            "user_1_total_capabilities" => $comparison["user_1_total"],
            "user_2_total_capabilities" => $comparison["user_2_total"],
            "shared_capabilities" => $comparison["shared_count"],
            "user_1_unique_capabilities" => count($comparison["user_1_only"]),
            "user_2_unique_capabilities" => count($comparison["user_2_only"]),
        ];

        $format = $assoc_args["format"] ?? "table";
        \WP_CLI\Utils\format_items($format, [$data], array_keys($data));
    }

    /**
     * Bulk grant capability to multiple users.
     *
     * ## OPTIONS
     *
     * <capability>
     * : Capability to grant
     *
     * [--users=<users>]
     * : Comma-separated list of user IDs, logins, or emails
     *
     * [--role=<role>]
     * : Grant to all users with specific role
     *
     * [--dry-run]
     * : Show what would be done without actually doing it
     *
     * ## EXAMPLES
     *
     *     wp permission:user-bulk-grant manage_products --users="admin,editor,123"
     *     wp permission:user-bulk-grant view_analytics --role=editor
     *     wp permission:user-bulk-grant manage_products --role=shop_manager --dry-run
     *
     * @when after_wp_load
     */
    public function bulkGrant($args, $assoc_args)
    {
        $capability = $args[0];
        $dry_run = isset($assoc_args["dry-run"]);
        $manager = wppermission()->getUserPermissionManager();

        $users = [];

        if (isset($assoc_args["users"])) {
            $user_identifiers = explode(",", $assoc_args["users"]);
            foreach ($user_identifiers as $identifier) {
                $user = $this->getUser(trim($identifier));
                if ($user) {
                    $users[] = $user->ID;
                } else {
                    \WP_CLI::warning("User '{$identifier}' not found.");
                }
            }
        }

        if (isset($assoc_args["role"])) {
            $role = $assoc_args["role"];
            $role_users = $manager->getUsersByRole($role);
            foreach ($role_users as $user) {
                if (!in_array($user->ID, $users)) {
                    $users[] = $user->ID;
                }
            }
        }

        if (empty($users)) {
            \WP_CLI::error("No users specified. Use --users or --role option.");
        }

        if ($dry_run) {
            \WP_CLI::line(
                "Would grant capability '{$capability}' to " .
                    count($users) .
                    " users:",
            );
            foreach ($users as $user_id) {
                $user = get_user_by("id", $user_id);
                \WP_CLI::line("  - {$user->user_login} (ID: {$user_id})");
            }
            return;
        }

        $results = $manager->bulkGrantCapability($users, $capability);
        $success_count = count(array_filter($results));
        $total_count = count($results);

        \WP_CLI::success(
            "Granted capability '{$capability}' to {$success_count}/{$total_count} users.",
        );

        if ($success_count < $total_count) {
            \WP_CLI::warning(
                "Some operations failed. Check individual user permissions.",
            );
        }
    }

    /**
     * Bulk assign role to multiple users.
     *
     * ## OPTIONS
     *
     * <role>
     * : Role to assign
     *
     * <users>
     * : Comma-separated list of user IDs, logins, or emails
     *
     * [--dry-run]
     * : Show what would be done without actually doing it
     *
     * ## EXAMPLES
     *
     *     wp permission:user-bulk-assign shop_manager "admin,editor,123"
     *     wp permission:user-bulk-assign contributor "user1,user2" --dry-run
     *
     * @when after_wp_load
     */
    public function bulkAssign($args, $assoc_args)
    {
        $role = $args[0];
        $user_identifiers = explode(",", $args[1]);
        $dry_run = isset($assoc_args["dry-run"]);
        $manager = wppermission()->getUserPermissionManager();

        $role_manager = wppermission()->getRoleManager();
        if (!$role_manager->exists($role)) {
            \WP_CLI::error("Role '{$role}' does not exist.");
        }

        $users = [];
        foreach ($user_identifiers as $identifier) {
            $user = $this->getUser(trim($identifier));
            if ($user) {
                $users[] = $user->ID;
            } else {
                \WP_CLI::warning("User '{$identifier}' not found.");
            }
        }

        if (empty($users)) {
            \WP_CLI::error("No valid users found.");
        }

        if ($dry_run) {
            \WP_CLI::line(
                "Would assign role '{$role}' to " . count($users) . " users:",
            );
            foreach ($users as $user_id) {
                $user = get_user_by("id", $user_id);
                \WP_CLI::line("  - {$user->user_login} (ID: {$user_id})");
            }
            return;
        }

        $results = $manager->bulkAssignRole($users, $role);
        $success_count = count(array_filter($results));
        $total_count = count($results);

        \WP_CLI::success(
            "Assigned role '{$role}' to {$success_count}/{$total_count} users.",
        );

        if ($success_count < $total_count) {
            \WP_CLI::warning(
                "Some operations failed. Check individual user permissions.",
            );
        }
    }

    /**
     * Get user by various identifiers
     */
    private function getUser($identifier)
    {
        if (is_numeric($identifier)) {
            return get_user_by("id", $identifier);
        }

        if (strpos($identifier, "@") !== false) {
            return get_user_by("email", $identifier);
        }

        return get_user_by("login", $identifier);
    }
}
