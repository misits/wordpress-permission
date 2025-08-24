# WP-CLI Commands

Complete reference for wp-permission WP-CLI commands.

## Overview

The wp-permission library provides comprehensive WP-CLI commands for managing capabilities, roles, and user permissions. All commands are available under the `borps permission:` namespace.

## Getting Started

### Check Installation

```bash
# Verify wp-permission is loaded
wp borps permission:help
```

### Command Structure

All commands follow this pattern:
```bash
wp borps permission: <category>-<action> [arguments] [options]
```

Categories:
- `capability:*` - Capability management
- `role:*` - Role management
- `user:*` - User permission management

## Capability Management

### Create Capabilities

```bash
# Basic capability creation
wp borps permission:capability-create manage_products

# With description and group
wp borps permission:capability-create view_analytics \
  --description="Access analytics dashboard" \
  --group="analytics"

# Multiple capabilities at once
wp borps permission:capability-create manage_inventory \
  --description="Manage product inventory" \
  --group="ecommerce"
```

### List Capabilities

```bash
# List all capabilities
wp borps permission:capability-list

# Filter by type
wp borps permission:capability-list --type=custom
wp borps permission:capability-list --type=default

# Filter by group
wp borps permission:capability-list --group=analytics

# Different output formats
wp borps permission:capability-list --format=json
wp borps permission:capability-list --format=csv
wp borps permission:capability-list --format=yaml
```

### Capability Information

```bash
# Get detailed capability info
wp borps permission:capability-info manage_products

# Usage statistics
wp borps permission: capability:stats manage_products

# JSON output for automation
wp borps permission:capability-info edit_posts --format=json
```

### Delete Capabilities

```bash
# Delete with confirmation
wp borps permission:capability-delete old_capability

# Skip confirmation
wp borps permission:capability-delete old_capability --yes

# Note: Cannot delete WordPress default capabilities
```

## Role Management

### Create Roles

```bash
# Basic role creation
wp borps permission:role-create shop_manager "Shop Manager"

# With initial capabilities
wp borps permission:role-create product_editor "Product Editor" \
  --capabilities="edit_posts,publish_posts,manage_products"

# Clone existing role
wp borps permission:role-create custom_editor "Custom Editor" \
  --clone=editor
```

### Clone Roles

```bash
# Clone with new name
wp borps permission:role-clone editor content_manager "Content Manager"

# Clone administrator for backup
wp borps permission:role-clone administrator super_admin "Super Administrator"
```

### List Roles

```bash
# List all roles
wp borps permission:role-list

# Filter by type
wp borps permission:role-list --type=custom
wp borps permission:role-list --type=default

# Different formats
wp borps permission:role-list --format=table
wp borps permission:role-list --format=json
```

### Role Information

```bash
# Basic role info
wp borps permission:role-info shop_manager

# Show all capabilities
wp borps permission:role-info editor --show-capabilities

# JSON output
wp borps permission:role-info administrator --format=json
```

### Manage Role Capabilities

```bash
# Add capability to role
wp borps permission:role-add-cap editor manage_products
wp borps permission:role-add-cap shop_manager view_analytics

# Remove capability from role
wp borps permission:role-remove-cap editor delete_posts
wp borps permission:role-remove-cap shop_manager manage_options
```

### Delete Roles

```bash
# Delete with confirmation
wp borps permission:role-delete shop_manager

# Skip confirmation
wp borps permission:role-delete old_role --yes

# Note: Cannot delete WordPress default roles
```

## User Permission Management

### Grant Capabilities

```bash
# Grant capability to user
wp borps permission:user-grant admin manage_products
wp borps permission:user-grant 123 view_analytics
wp borps permission:user-grant user@example.com edit_others_posts

# Bulk grant to multiple users
wp borps permission:user-bulk-grant manage_products \
  --users="admin,editor,123"

# Grant to all users with specific role
wp borps permission:user-bulk-grant view_analytics --role=editor

# Dry run to preview changes
wp borps permission:user-bulk-grant manage_products \
  --role=shop_manager --dry-run
```

### Revoke Capabilities

```bash
# Revoke capability from user
wp borps permission:user-revoke admin delete_users
wp borps permission:user-revoke 123 manage_products
```

### Role Assignment

```bash
# Assign role to user
wp borps permission:user-assign-role admin shop_manager
wp borps permission:user-assign-role 123 editor

# Replace all existing roles
wp borps permission:user-assign-role admin shop_manager --replace

# Remove role from user
wp borps permission:user-remove-role admin editor
wp borps permission:user-remove-role 123 shop_manager

# Bulk assign role to multiple users
wp borps permission:user-bulk-assign shop_manager "admin,editor,123"

# Dry run bulk assignment
wp borps permission:user-bulk-assign contributor "user1,user2" --dry-run
```

### User Information

```bash
# Get user permission summary
wp borps permission:user-info admin
wp borps permission:user-info 123
wp borps permission:user-info user@example.com

# Show all capabilities
wp borps permission:user-info admin --show-capabilities

# JSON output for automation
wp borps permission:user-info 123 --format=json
```

### Compare Users

```bash
# Compare capabilities between users
wp borps permission:user-compare admin editor
wp borps permission:user-compare 123 456

# JSON output for detailed analysis
wp borps permission:user-compare admin editor --format=json
```

## Global Options

Most commands support these global options:

### Output Formats

```bash
--format=table    # Default table format
--format=json     # JSON output
--format=csv      # CSV format
--format=yaml     # YAML format
```

### Confirmation Control

```bash
--yes            # Skip confirmation prompts
--dry-run        # Show what would be done without doing it
```

### Examples

```bash
# Get all custom capabilities as JSON
wp borps permission:capability-list --type=custom --format=json

# Delete multiple roles without confirmation
wp borps permission:role-delete old_role1 --yes
wp borps permission:role-delete old_role2 --yes

# Preview bulk user assignment
wp borps permission:user-bulk-assign shop_manager "user1,user2,user3" --dry-run
```

## Automation Examples

### Backup and Restore

```bash
#!/bin/bash
# Backup custom capabilities and roles

# Export custom capabilities
wp borps permission:capability-list --type=custom --format=json > capabilities_backup.json

# Export custom roles
wp borps permission:role-list --type=custom --format=json > roles_backup.json

# Export user permission summaries
wp user list --format=csv --fields=ID | tail -n +2 | while read user_id; do
  wp borps permission:user-info $user_id --format=json >> users_backup.jsonl
done
```

### Batch Operations

```bash
#!/bin/bash
# Setup e-commerce permissions

# Create capabilities
wp borps permission:capability-create manage_products \
  --description="Manage product catalog" --group="ecommerce"
wp borps permission:capability-create manage_orders \
  --description="Manage customer orders" --group="ecommerce"
wp borps permission:capability-create view_analytics \
  --description="View analytics dashboard" --group="analytics"

# Create roles
wp borps permission:role-create shop_manager "Shop Manager" \
  --capabilities="edit_posts,manage_products,manage_orders,view_analytics"

# Assign roles to existing users
wp borps permission:user-bulk-assign shop_manager "admin,manager1,manager2"
```

### Cleanup Scripts

```bash
#!/bin/bash
# Clean up old permissions

# Remove deprecated capabilities
wp borps permission:capability-delete old_capability_1 --yes
wp borps permission:capability-delete old_capability_2 --yes

# Remove deprecated roles
wp borps permission:role-delete old_role_1 --yes
wp borps permission:role-delete old_role_2 --yes
```

## Scripting and Integration

### Exit Codes

Commands return standard exit codes:
- `0` - Success
- `1` - General error
- `2` - Command not found
- `3` - Permission denied

### JSON Output Processing

```bash
# Get capability count by group
wp borps permission:capability-list --format=json | \
  jq 'group_by(.group) | map({group: .[0].group, count: length})'

# List users with specific capability
wp borps permission: capability:stats manage_products --format=json | \
  jq '.users_with_capability[]'

# Find roles with most capabilities
wp borps permission:role-list --format=json | \
  jq 'sort_by(.capabilities) | reverse | .[0:5]'
```

### CSV Processing

```bash
# Export role assignments for spreadsheet
wp borps permission:role-list --format=csv > roles.csv

# Export user permissions summary
wp user list --format=csv --fields=ID,user_login | \
  tail -n +2 | while IFS=, read user_id username; do
    echo -n "$user_id,$username,"
    wp borps permission:user-info $user_id --format=json | \
      jq -r '.capability_count'
  done > user_permissions.csv
```

## Troubleshooting

### Common Issues

**Commands not found**
```bash
# Check if wp-permission is loaded
wp cli cache clear
wp --info

# Verify WordPress is loaded properly
wp core version
```

**Permission errors**
```bash
# Check WP-CLI can access WordPress
wp user list --format=count

# Verify database connectivity
wp db check
```

**Output formatting issues**
```bash
# Use different format if table is broken
wp borps permission:capability-list --format=json

# Check for shell encoding issues
export LANG=en_US.UTF-8
```

### Debug Mode

```bash
# Enable WP-CLI debug mode
wp --debug borps permission:capability-list

# Verbose output
wp --verbose borps permission:user-grant admin manage_products
```

### Performance for Large Sites

```bash
# Use pagination for large user lists
wp user list --number=100 --offset=0

# Process users in batches
for i in {0..10}; do
  offset=$((i * 100))
  wp user list --number=100 --offset=$offset --format=ids | \
    xargs -I {} wp borps permission:user-info {}
done
```

## Advanced Usage

### Custom Scripts Integration

```php
<?php
// custom-permission-setup.php
// Run with: wp eval-file custom-permission-setup.php

// Create permission structure
$capabilities = [
    'manage_products' => ['description' => 'Manage products', 'group' => 'ecommerce'],
    'view_analytics' => ['description' => 'View analytics', 'group' => 'analytics'],
];

foreach ($capabilities as $cap => $data) {
    WP_CLI::runcommand("borps permission: capability:create {$cap} --description='{$data['description']}' --group='{$data['group']}'");
}

WP_CLI::success('Permission structure created');
?>
```

### Monitoring Scripts

```bash
#!/bin/bash
# Monitor permission changes

# Check for new capabilities
CURRENT_CAPS=$(wp borps permission:capability-list --type=custom --format=json | jq length)
echo "Current custom capabilities: $CURRENT_CAPS"

# Check for users with admin privileges
ADMIN_USERS=$(wp user list --role=administrator --format=count)
echo "Administrator users: $ADMIN_USERS"

# Alert if too many admins
if [ $ADMIN_USERS -gt 5 ]; then
  echo "WARNING: Too many administrator users!"
fi
```

## Best Practices

### Command Organization

- Use consistent naming conventions
- Group related operations in scripts
- Always use `--dry-run` for bulk operations first
- Save important configurations as JSON backups

### Security Considerations

- Limit admin role assignments
- Regular audits of custom capabilities
- Monitor permission changes
- Use `--yes` carefully in automated scripts

### Performance Tips

- Use JSON format for programmatic processing
- Process large user lists in batches
- Cache command outputs when possible
- Use specific filters to reduce data transfer

## Next Steps

- [Explore API Reference](api.md)
- [Check out Practical Examples](examples.md)
- [Learn about Capability Management](capabilities.md)
- [Review User Management](users.md)
