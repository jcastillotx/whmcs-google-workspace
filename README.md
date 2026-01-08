# Google Workspace WHMCS Module

A comprehensive WHMCS provisioning module for Google Workspace (formerly G Suite) that enables automated provisioning and management of Google Workspace subscriptions through the Google Reseller API.

**Version 2.0.0** - Major Feature Update

## Features

### Provisioning
- Automatic customer account creation
- Subscription provisioning with configurable SKUs
- Support for all Google Workspace plans (Business Starter, Standard, Plus, Enterprise, Frontline, Essentials)
- Flexible, Annual, and Trial payment plan support
- Automatic admin user creation with secure password generation
- Deal code support for transferring existing customers
- Welcome email with credentials (optional)

### Account Management
- Suspend/Unsuspend subscriptions
- Terminate subscriptions (cancel, suspend, or transfer to Google)
- License quantity management
- Plan upgrades/downgrades
- Trial to Paid service conversion
- Subscription data sync

### User Management (Client Area)
- List all users in the domain with pagination
- Add new users with organizational unit assignment
- Delete users
- Reset user passwords (auto-generate or custom)
- Suspend/Unsuspend individual users
- Make users admin
- View user 2FA enrollment status
- **Bulk user import via CSV/JSON**

### Email Alias Management
- List user email aliases
- Add aliases to users
- Delete user aliases

### Group Management
- List all groups in domain
- Create new groups with name and description
- Delete groups
- List group members
- Add members to groups (MEMBER, MANAGER, OWNER roles)
- Remove members from groups

### Organizational Units
- List organizational units hierarchy
- Create new organizational units
- Delete organizational units
- Move users between organizational units

### Domain Management
- List all domains for customer
- Add secondary/alias domains
- Delete domains
- Domain verification workflow with DNS TXT tokens

### Admin Features
- Create Admin User command
- Sync Subscription data
- Reset Admin Password
- Start Paid Service (convert trial)
- Get Domain Verification Token
- View detailed subscription information
- Single Sign-On to Google Admin Console

### Automation
- Daily cron sync of subscription data
- Automatic license sync on upgrade/downgrade
- Invoice paid hooks for renewal handling
- Service cleanup on deletion
- Rate-limited syncing (12-hour cooldown)

### Client Area Features
- Modern, responsive UI with Google branding
- Real-time AJAX user management
- Quick access buttons to Google Admin Console, Gmail, Drive
- Subscription status and plan information
- Admin credentials display (configurable)
- Groups management tab (optional)
- Organizational units tab (optional)
- Email aliases management (optional)
- Trial conversion notification

## Requirements

- WHMCS 7.0 or higher
- PHP 7.2 or higher
- cURL extension enabled
- Google Cloud Platform project with OAuth 2.0 credentials
- Google Workspace Reseller account

## Installation

1. **Upload Files**

   Upload the `modules/servers/googleworkspace` folder to your WHMCS installation:
   ```
   /path/to/whmcs/modules/servers/googleworkspace/
   ```

2. **Set Permissions**
   ```bash
   chmod 755 modules/servers/googleworkspace
   chmod 644 modules/servers/googleworkspace/*.php
   chmod 755 modules/servers/googleworkspace/lib
   chmod 755 modules/servers/googleworkspace/templates
   chmod 755 modules/servers/googleworkspace/lang
   ```

3. **Create Google Cloud Project**

   a. Go to [Google Cloud Console](https://console.cloud.google.com/)

   b. Create a new project or select an existing one

   c. Enable the following APIs:
      - Google Workspace Reseller API
      - Admin SDK API
      - Site Verification API
      - Enterprise License Manager API (optional, for license management)
      - Admin Reports API (optional, for usage reports)

   d. Create OAuth 2.0 credentials:
      - Go to "APIs & Services" > "Credentials"
      - Click "Create Credentials" > "OAuth client ID"
      - Select "Web application"
      - Add authorized redirect URI (e.g., `https://yourdomain.com/oauth-callback.php`)
      - Note your Client ID and Client Secret

4. **Obtain Refresh Token**

   Use the Google OAuth 2.0 Playground or create a simple script to obtain a refresh token with the following scopes:
   - `https://www.googleapis.com/auth/apps.order`
   - `https://www.googleapis.com/auth/admin.directory.user`
   - `https://www.googleapis.com/auth/admin.directory.group`
   - `https://www.googleapis.com/auth/admin.directory.orgunit`
   - `https://www.googleapis.com/auth/admin.directory.domain`
   - `https://www.googleapis.com/auth/siteverification`
   - `https://www.googleapis.com/auth/apps.licensing` (optional)
   - `https://www.googleapis.com/auth/admin.reports.usage.readonly` (optional)

## Configuration

### Create a Product

1. Go to **Setup** > **Products/Services** > **Products/Services**
2. Click **Create a New Product**
3. Select **Module**: Google Workspace

### Module Settings

| Setting | Description |
|---------|-------------|
| Client ID | Your Google OAuth 2.0 Client ID |
| Client Secret | Your Google OAuth 2.0 Client Secret |
| Refresh Token | OAuth 2.0 Refresh Token |
| Reseller Domain | Your authorized reseller domain |
| SKU ID | Google Workspace plan to provision |
| Plan Type | Payment plan (Flexible, Annual Monthly, Annual Yearly, Trial) |
| Default Licenses | Default number of user licenses |
| Auto Create Admin | Automatically create admin user on provisioning |
| Termination Action | Action on service termination |
| Show Password | Allow customers to see admin password |
| Enable Groups | Allow customers to manage groups in client area |
| Enable Aliases | Allow customers to manage email aliases |
| Enable Org Units | Allow customers to manage organizational units |
| Default Org Name | Default organization name if client has no company |
| Welcome Email | Send welcome email with credentials after provisioning |

### SKU IDs

| SKU ID | Plan Name |
|--------|-----------|
| `Google-Apps-For-Business` | Business Starter |
| `Google-Apps-Unlimited` | Business Standard |
| `Google-Apps-For-Postini` | Business Plus |
| `1010020027` | Enterprise Standard |
| `1010020028` | Enterprise Plus |
| `1010020025` | Enterprise Essentials |
| `Google-Apps-Lite` | Frontline Starter |
| `1010020026` | Frontline Standard |
| `1010060003` | Essentials Starter |

### Custom Fields

Create a custom field named "Domain" to allow customers to specify their domain during order.

### Configurable Options

Create a configurable option named "Licenses" to allow customers to select the number of licenses.

## Usage

### Admin Commands

The module provides the following admin buttons:

- **Create Admin User**: Creates an admin user for the customer's domain
- **Sync Subscription**: Syncs subscription data from Google
- **Reset Admin Password**: Generates a new password for the admin user
- **Start Paid Service**: Converts trial subscription to paid
- **Get Verification Token**: Displays DNS TXT record for domain verification

### Client Area

Customers can:
- View subscription details (plan, licenses, status)
- Access Google Admin Console, Gmail, and Drive
- Manage users (add, delete, reset passwords, suspend/unsuspend)
- Manage email aliases (if enabled)
- Manage groups and group members (if enabled)
- Manage organizational units (if enabled)
- Bulk import users via CSV/JSON
- View 2FA enrollment status for users
- View admin credentials (if enabled)
- Convert trial to paid service

## API Reference

### Google Reseller API

The module uses the following Google Reseller API endpoints:
- `POST /customers` - Create customer
- `GET /customers/{customerId}` - Get customer
- `POST /customers/{customerId}/subscriptions` - Create subscription
- `GET /customers/{customerId}/subscriptions/{subscriptionId}` - Get subscription
- `POST /customers/{customerId}/subscriptions/{subscriptionId}/suspend` - Suspend
- `POST /customers/{customerId}/subscriptions/{subscriptionId}/activate` - Activate
- `POST /customers/{customerId}/subscriptions/{subscriptionId}/startPaidService` - Start paid
- `DELETE /customers/{customerId}/subscriptions/{subscriptionId}` - Delete
- `POST /customers/{customerId}/subscriptions/{subscriptionId}/changeSeats` - Update seats
- `POST /customers/{customerId}/subscriptions/{subscriptionId}/changePlan` - Change plan
- `POST /customers/{customerId}/subscriptions/{subscriptionId}/changeRenewalSettings` - Renewal

### Directory API

The module uses the following Directory API endpoints:
- `POST /users` - Create user
- `GET /users` - List users
- `GET /users/{userKey}` - Get user
- `PUT /users/{userKey}` - Update user
- `PATCH /users/{userKey}` - Partial update
- `DELETE /users/{userKey}` - Delete user
- `POST /users/{userKey}/makeAdmin` - Make user admin
- `GET /users/{userKey}/aliases` - List aliases
- `POST /users/{userKey}/aliases` - Add alias
- `DELETE /users/{userKey}/aliases/{alias}` - Delete alias
- `POST /groups` - Create group
- `GET /groups` - List groups
- `DELETE /groups/{groupKey}` - Delete group
- `GET /groups/{groupKey}/members` - List members
- `POST /groups/{groupKey}/members` - Add member
- `DELETE /groups/{groupKey}/members/{memberKey}` - Remove member
- `POST /customer/{customerId}/orgunits` - Create org unit
- `GET /customer/{customerId}/orgunits` - List org units
- `DELETE /customer/{customerId}/orgunits/{orgUnitPath}` - Delete org unit
- `GET /customer/{customerId}/domains` - List domains
- `POST /customer/{customerId}/domains` - Add domain
- `DELETE /customer/{customerId}/domains/{domainName}` - Delete domain

### Site Verification API

- `POST /token` - Get verification token
- `POST /webResource` - Verify domain

## Troubleshooting

### Common Issues

**"Failed to obtain access token"**
- Verify your Client ID, Client Secret, and Refresh Token
- Ensure the OAuth credentials have the required scopes

**"API Error (HTTP 403)"**
- Verify your reseller account is active
- Check that required APIs are enabled in Google Cloud Console
- Ensure the authenticated account has reseller privileges

**"Customer domain is required"**
- Add a custom field named "Domain" to the product
- Or ensure the customer has a company name that can be used as a domain

**Groups/Aliases/Org Units not showing**
- Ensure the corresponding feature is enabled in module settings
- Verify the OAuth token has the required scopes

### Debug Logging

Module calls are logged to WHMCS Module Log. Enable it at:
**Utilities** > **Logs** > **Module Log**

## File Structure

```
modules/servers/googleworkspace/
├── googleworkspace.php         # Main module file
├── hooks.php                   # WHMCS hooks
├── whmcs.json                  # Module metadata
├── lib/
│   ├── GoogleWorkspaceClient.php   # Google API client
│   └── GoogleWorkspaceHelper.php   # Helper functions
├── templates/
│   ├── clientarea.tpl          # Client area template
│   └── error.tpl               # Error template
└── lang/
    └── english.php             # Language file
```

## Database

The module creates a custom table `mod_googleworkspace` to store:
- Customer ID
- Subscription ID
- Customer domain
- SKU and plan information
- License count
- Status and sync timestamps
- Admin email
- Extra data (JSON)

## Security

- All API credentials are stored encrypted in WHMCS
- OAuth 2.0 tokens are refreshed automatically
- User passwords are generated with strong entropy (16+ chars, mixed case, numbers, symbols)
- All API communications use HTTPS with SSL verification
- Input validation and sanitization
- CSRF protection via WHMCS token system

## Support

For issues and feature requests, please open an issue on GitHub.

## License

MIT License - see LICENSE file for details.

## Changelog

### Version 2.0.0
- **NEW**: Group management (create, delete, manage members)
- **NEW**: Email alias management for users
- **NEW**: Organizational unit management
- **NEW**: Domain management (add/remove domains)
- **NEW**: Domain verification workflow with DNS tokens
- **NEW**: User suspend/unsuspend functionality
- **NEW**: Make user admin functionality
- **NEW**: 2FA status display for users
- **NEW**: Bulk user import via CSV/JSON
- **NEW**: Trial to Paid service conversion
- **NEW**: Start Paid Service admin command
- **NEW**: Get Verification Token admin command
- **NEW**: Additional SKUs (Frontline Standard, Essentials Starter)
- **NEW**: Trial plan type support
- **NEW**: Welcome email option
- **NEW**: Default organization name setting
- **NEW**: Feature toggles for groups, aliases, org units
- **IMPROVED**: Enhanced client area UI with tabs
- **IMPROVED**: More comprehensive API integration
- **IMPROVED**: Better error handling and logging
- **IMPROVED**: Extended API client with Reports and License APIs

### Version 1.0.0
- Initial release
- Full provisioning support
- User management
- Client area interface
- Admin commands
- Automation hooks
