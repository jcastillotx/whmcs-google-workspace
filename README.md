# Google Workspace WHMCS Module

A comprehensive WHMCS provisioning module for Google Workspace (formerly G Suite) that enables automated provisioning and management of Google Workspace subscriptions through the Google Reseller API.

## Features

### Provisioning
- Automatic customer account creation
- Subscription provisioning with configurable SKUs
- Support for all Google Workspace plans (Business Starter, Standard, Plus, Enterprise)
- Flexible and Annual payment plan support
- Automatic admin user creation

### Account Management
- Suspend/Unsuspend subscriptions
- Terminate subscriptions (cancel, suspend, or transfer to Google)
- License quantity management
- Plan upgrades/downgrades

### User Management (Client Area)
- List all users in the domain
- Add new users
- Delete users
- Reset user passwords
- View user admin status

### Admin Features
- Create Admin User command
- Sync Subscription data
- Reset Admin Password
- View detailed subscription information
- Single Sign-On to Google Admin Console

### Automation
- Daily cron sync of subscription data
- Automatic license sync on upgrade/downgrade
- Invoice paid hooks for renewal handling
- Service cleanup on deletion

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
   - `https://www.googleapis.com/auth/siteverification`

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
| Plan Type | Payment plan (Flexible, Annual Monthly, Annual Yearly) |
| Default Licenses | Default number of user licenses |
| Auto Create Admin | Automatically create admin user on provisioning |
| Termination Action | Action on service termination |
| Show Password | Allow customers to see admin password |

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
- **View Users**: Quick link to user management

### Client Area

Customers can:
- View subscription details
- Access Google Admin Console, Gmail, and Drive
- Manage users (add, delete, reset passwords)
- View admin credentials (if enabled)

## API Reference

### Google Reseller API

The module uses the following Google Reseller API endpoints:
- `POST /customers` - Create customer
- `POST /customers/{customerId}/subscriptions` - Create subscription
- `POST /customers/{customerId}/subscriptions/{subscriptionId}/suspend` - Suspend
- `POST /customers/{customerId}/subscriptions/{subscriptionId}/activate` - Activate
- `DELETE /customers/{customerId}/subscriptions/{subscriptionId}` - Delete
- `POST /customers/{customerId}/subscriptions/{subscriptionId}/changeSeats` - Update seats

### Directory API

The module uses the following Directory API endpoints:
- `POST /users` - Create user
- `GET /users` - List users
- `PATCH /users/{userKey}` - Update user
- `DELETE /users/{userKey}` - Delete user
- `POST /users/{userKey}/makeAdmin` - Make user admin

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

### Debug Logging

Module calls are logged to WHMCS Module Log. Enable it at:
**Utilities** > **Logs** > **Module Log**

## File Structure

```
modules/servers/googleworkspace/
├── googleworkspace.php     # Main module file
├── hooks.php               # WHMCS hooks
├── lib/
│   ├── GoogleWorkspaceClient.php   # Google API client
│   └── GoogleWorkspaceHelper.php   # Helper functions
├── templates/
│   ├── clientarea.tpl      # Client area template
│   └── error.tpl           # Error template
└── lang/
    └── english.php         # Language file
```

## Database

The module creates a custom table `mod_googleworkspace` to store:
- Customer ID
- Subscription ID
- Customer domain
- SKU and plan information
- License count
- Status and sync timestamps

## Security

- All API credentials are stored encrypted in WHMCS
- OAuth 2.0 tokens are refreshed automatically
- User passwords are generated with strong entropy
- All API communications use HTTPS

## Support

For issues and feature requests, please open an issue on GitHub.

## License

MIT License - see LICENSE file for details.

## Changelog

### Version 1.0.0
- Initial release
- Full provisioning support
- User management
- Client area interface
- Admin commands
- Automation hooks
