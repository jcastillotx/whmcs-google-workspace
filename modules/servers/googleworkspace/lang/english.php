<?php
/**
 * Google Workspace Module - English Language File
 */

$_LANG['googleworkspace'] = [
    // General
    'module_name' => 'Google Workspace',
    'module_description' => 'Automated provisioning and management of Google Workspace subscriptions',

    // Subscription Info
    'subscription_info' => 'Subscription Information',
    'domain' => 'Domain',
    'plan' => 'Plan',
    'billing_type' => 'Billing Type',
    'licenses' => 'Licenses',
    'status' => 'Status',
    'admin_email' => 'Admin Email',

    // Status Labels
    'status_active' => 'Active',
    'status_suspended' => 'Suspended',
    'status_pending' => 'Pending',
    'status_terminated' => 'Terminated',

    // Plan Names
    'plan_business_starter' => 'Business Starter',
    'plan_business_standard' => 'Business Standard',
    'plan_business_plus' => 'Business Plus',
    'plan_enterprise_standard' => 'Enterprise Standard',
    'plan_enterprise_plus' => 'Enterprise Plus',
    'plan_enterprise_essentials' => 'Enterprise Essentials',
    'plan_frontline_starter' => 'Frontline Starter',

    // Billing Types
    'billing_flexible' => 'Flexible (Monthly)',
    'billing_annual_monthly' => 'Annual (Monthly Payments)',
    'billing_annual_yearly' => 'Annual (Yearly Payment)',

    // Buttons
    'btn_add_user' => 'Add User',
    'btn_refresh' => 'Refresh',
    'btn_create_user' => 'Create User',
    'btn_reset_password' => 'Reset Password',
    'btn_delete_user' => 'Delete User',
    'btn_admin_console' => 'Google Admin Console',
    'btn_gmail' => 'Gmail',
    'btn_drive' => 'Drive',

    // User Management
    'user_management' => 'User Management',
    'users_list' => 'Users List',
    'user_email' => 'Email',
    'user_name' => 'Name',
    'user_admin' => 'Admin',
    'user_status' => 'Status',
    'user_actions' => 'Actions',
    'no_users' => 'No users found. Click "Add User" to create one.',
    'loading_users' => 'Loading users...',

    // Add User Modal
    'add_user_title' => 'Add New User',
    'add_user_email' => 'Email Address',
    'add_user_firstname' => 'First Name',
    'add_user_lastname' => 'Last Name',
    'add_user_password' => 'Password (leave empty to auto-generate)',

    // Reset Password Modal
    'reset_password_title' => 'Reset Password',
    'reset_password_new' => 'New Password (leave empty to auto-generate)',

    // Admin Credentials
    'admin_credentials' => 'Admin Credentials',
    'admin_username' => 'Admin Username',
    'admin_password' => 'Admin Password',

    // Success Messages
    'success_user_created' => 'User created successfully',
    'success_user_deleted' => 'User deleted successfully',
    'success_password_reset' => 'Password reset successfully',
    'success_password_copied' => 'Password copied to clipboard',
    'success_subscription_synced' => 'Subscription synced successfully',

    // Error Messages
    'error_generic' => 'An error occurred. Please try again.',
    'error_user_create' => 'Failed to create user',
    'error_user_delete' => 'Failed to delete user',
    'error_password_reset' => 'Failed to reset password',
    'error_load_users' => 'Failed to load users',
    'error_domain_required' => 'Customer domain is required',
    'error_api_credentials' => 'Google API credentials not configured',
    'error_service_data' => 'Service data not found',

    // Confirmation Messages
    'confirm_delete_user' => 'Are you sure you want to delete this user? This action cannot be undone.',

    // Admin Area
    'admin_customer_id' => 'Customer ID',
    'admin_subscription_id' => 'Subscription ID',
    'admin_last_synced' => 'Last Synced',
    'admin_not_provisioned' => 'Not provisioned',
];
