<?php
/**
 * Google Workspace WHMCS Provisioning Module
 *
 * This module provides integration with Google Workspace (formerly G Suite)
 * for automated provisioning and management of Google Workspace subscriptions.
 *
 * @package    WHMCS
 * @author     Google Workspace Module
 * @copyright  Copyright (c) 2024
 * @license    MIT
 * @version    2.0.0
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

// Include the Google API client
require_once __DIR__ . '/lib/GoogleWorkspaceClient.php';
require_once __DIR__ . '/lib/GoogleWorkspaceHelper.php';

/**
 * Module metadata
 *
 * @return array
 */
function googleworkspace_MetaData()
{
    return [
        'DisplayName' => 'Google Workspace',
        'APIVersion' => '1.1',
        'RequiresServer' => false,
        'DefaultNonSSLPort' => '443',
        'DefaultSSLPort' => '443',
        'ServiceSingleSignOnLabel' => 'Login to Google Admin',
    ];
}

/**
 * Module configuration options
 *
 * @return array
 */
function googleworkspace_ConfigOptions()
{
    return [
        'client_id' => [
            'FriendlyName' => 'Client ID',
            'Type' => 'text',
            'Size' => '60',
            'Description' => 'Google Cloud OAuth 2.0 Client ID',
        ],
        'client_secret' => [
            'FriendlyName' => 'Client Secret',
            'Type' => 'password',
            'Size' => '60',
            'Description' => 'Google Cloud OAuth 2.0 Client Secret',
        ],
        'refresh_token' => [
            'FriendlyName' => 'Refresh Token',
            'Type' => 'password',
            'Size' => '60',
            'Description' => 'OAuth 2.0 Refresh Token (obtained after authorization)',
        ],
        'reseller_domain' => [
            'FriendlyName' => 'Reseller Domain',
            'Type' => 'text',
            'Size' => '40',
            'Description' => 'Your reseller domain (e.g., reseller.example.com)',
        ],
        'sku_id' => [
            'FriendlyName' => 'SKU ID',
            'Type' => 'dropdown',
            'Options' => [
                'Google-Apps-For-Business' => 'Business Starter',
                'Google-Apps-Unlimited' => 'Business Standard',
                'Google-Apps-For-Postini' => 'Business Plus',
                '1010020027' => 'Enterprise Standard',
                '1010020028' => 'Enterprise Plus',
                '1010020025' => 'Enterprise Essentials',
                'Google-Apps-Lite' => 'Frontline Starter',
                '1010020026' => 'Frontline Standard',
                '1010060003' => 'Essentials Starter',
            ],
            'Description' => 'Google Workspace subscription SKU',
            'Default' => 'Google-Apps-For-Business',
        ],
        'plan_type' => [
            'FriendlyName' => 'Plan Type',
            'Type' => 'dropdown',
            'Options' => [
                'FLEXIBLE' => 'Flexible (Monthly)',
                'ANNUAL_MONTHLY_PAY' => 'Annual (Monthly Payments)',
                'ANNUAL_YEARLY_PAY' => 'Annual (Yearly Payment)',
                'TRIAL' => 'Free Trial (30 days)',
            ],
            'Description' => 'Subscription payment plan type',
            'Default' => 'FLEXIBLE',
        ],
        'default_licenses' => [
            'FriendlyName' => 'Default Licenses',
            'Type' => 'text',
            'Size' => '10',
            'Default' => '5',
            'Description' => 'Default number of user licenses',
        ],
        'auto_create_admin' => [
            'FriendlyName' => 'Auto Create Admin',
            'Type' => 'yesno',
            'Description' => 'Automatically create admin user on provisioning',
        ],
        'terminate_action' => [
            'FriendlyName' => 'Termination Action',
            'Type' => 'dropdown',
            'Options' => [
                'cancel' => 'Cancel Subscription',
                'suspend' => 'Suspend Subscription',
                'transfer' => 'Transfer to Direct (Google)',
            ],
            'Description' => 'Action to take when service is terminated',
            'Default' => 'cancel',
        ],
        'show_password' => [
            'FriendlyName' => 'Show Password in Client Area',
            'Type' => 'yesno',
            'Description' => 'Allow customers to view admin password in client area',
        ],
        'enable_groups' => [
            'FriendlyName' => 'Enable Groups Management',
            'Type' => 'yesno',
            'Description' => 'Allow customers to manage groups in client area',
        ],
        'enable_aliases' => [
            'FriendlyName' => 'Enable Email Aliases',
            'Type' => 'yesno',
            'Description' => 'Allow customers to manage email aliases',
        ],
        'enable_org_units' => [
            'FriendlyName' => 'Enable Org Units',
            'Type' => 'yesno',
            'Description' => 'Allow customers to manage organizational units',
        ],
        'default_org_name' => [
            'FriendlyName' => 'Default Organization Name',
            'Type' => 'text',
            'Size' => '40',
            'Description' => 'Used when client has no company name',
        ],
        'welcome_email' => [
            'FriendlyName' => 'Send Welcome Email',
            'Type' => 'yesno',
            'Description' => 'Send welcome email with credentials after provisioning',
        ],
    ];
}

/**
 * Create a new Google Workspace subscription
 *
 * @param array $params
 * @return string
 */
function googleworkspace_CreateAccount(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);

        // Get customer domain from service domain or generate from client details
        $customerDomain = !empty($params['domain'])
            ? $params['domain']
            : GoogleWorkspaceHelper::generateDomain($params);

        if (empty($customerDomain)) {
            return 'Error: Customer domain is required';
        }

        // Check if customer already exists
        $existingCustomer = $client->getCustomer($customerDomain);

        if (!$existingCustomer) {
            // Create new customer
            $customerData = [
                'customerDomain' => $customerDomain,
                'alternateEmail' => $params['clientsdetails']['email'],
                'postalAddress' => [
                    'organizationName' => !empty($params['clientsdetails']['companyname'])
                        ? $params['clientsdetails']['companyname']
                        : $params['clientsdetails']['firstname'] . ' ' . $params['clientsdetails']['lastname'],
                    'contactName' => $params['clientsdetails']['firstname'] . ' ' . $params['clientsdetails']['lastname'],
                    'addressLine1' => $params['clientsdetails']['address1'],
                    'addressLine2' => $params['clientsdetails']['address2'] ?? '',
                    'locality' => $params['clientsdetails']['city'],
                    'region' => $params['clientsdetails']['state'],
                    'postalCode' => $params['clientsdetails']['postcode'],
                    'countryCode' => GoogleWorkspaceHelper::getCountryCode($params['clientsdetails']['country']),
                ],
            ];

            $customer = $client->createCustomer($customerData);

            if (!$customer || !isset($customer['customerId'])) {
                return 'Error: Failed to create customer account';
            }

            $customerId = $customer['customerId'];
        } else {
            $customerId = $existingCustomer['customerId'];
        }

        // Create subscription
        $numLicenses = !empty($params['configoptions']['licenses'])
            ? (int)$params['configoptions']['licenses']
            : (int)$params['configoption7'];

        $subscriptionData = [
            'customerId' => $customerId,
            'skuId' => $params['configoption5'],
            'plan' => [
                'planName' => $params['configoption6'],
            ],
            'seats' => [
                'numberOfSeats' => max(1, $numLicenses),
                'maximumNumberOfSeats' => max(1, $numLicenses),
            ],
            'purchaseOrderId' => 'WHMCS-' . $params['serviceid'],
        ];

        $subscription = $client->createSubscription($customerId, $subscriptionData);

        if (!$subscription || !isset($subscription['subscriptionId'])) {
            return 'Error: Failed to create subscription';
        }

        // Store subscription details
        GoogleWorkspaceHelper::saveServiceData($params['serviceid'], [
            'customer_id' => $customerId,
            'subscription_id' => $subscription['subscriptionId'],
            'customer_domain' => $customerDomain,
            'sku_id' => $params['configoption5'],
            'plan_type' => $params['configoption6'],
            'licenses' => $numLicenses,
        ]);

        // Create admin user if enabled
        if ($params['configoption8'] === 'on') {
            $adminPassword = GoogleWorkspaceHelper::generatePassword();

            $adminUser = $client->createUser($customerDomain, [
                'primaryEmail' => 'admin@' . $customerDomain,
                'name' => [
                    'givenName' => $params['clientsdetails']['firstname'],
                    'familyName' => $params['clientsdetails']['lastname'],
                ],
                'password' => $adminPassword,
                'changePasswordAtNextLogin' => true,
            ]);

            if ($adminUser) {
                // Make user admin
                $client->makeUserAdmin($customerDomain, 'admin@' . $customerDomain);

                // Update service with admin credentials
                Capsule::table('tblhosting')
                    ->where('id', $params['serviceid'])
                    ->update([
                        'username' => 'admin@' . $customerDomain,
                        'password' => encrypt($adminPassword),
                    ]);

                GoogleWorkspaceHelper::updateServiceData($params['serviceid'], [
                    'admin_email' => 'admin@' . $customerDomain,
                ]);
            }
        }

        // Update domain in service
        Capsule::table('tblhosting')
            ->where('id', $params['serviceid'])
            ->update(['domain' => $customerDomain]);

        return 'success';

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

/**
 * Suspend Google Workspace subscription
 *
 * @param array $params
 * @return string
 */
function googleworkspace_SuspendAccount(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);
        $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

        if (empty($serviceData['customer_id']) || empty($serviceData['subscription_id'])) {
            return 'Error: Service data not found';
        }

        $result = $client->suspendSubscription(
            $serviceData['customer_id'],
            $serviceData['subscription_id']
        );

        if ($result) {
            GoogleWorkspaceHelper::updateServiceData($params['serviceid'], [
                'status' => 'suspended',
                'suspended_date' => date('Y-m-d H:i:s'),
            ]);
            return 'success';
        }

        return 'Error: Failed to suspend subscription';

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

/**
 * Unsuspend Google Workspace subscription
 *
 * @param array $params
 * @return string
 */
function googleworkspace_UnsuspendAccount(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);
        $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

        if (empty($serviceData['customer_id']) || empty($serviceData['subscription_id'])) {
            return 'Error: Service data not found';
        }

        $result = $client->activateSubscription(
            $serviceData['customer_id'],
            $serviceData['subscription_id']
        );

        if ($result) {
            GoogleWorkspaceHelper::updateServiceData($params['serviceid'], [
                'status' => 'active',
                'suspended_date' => null,
            ]);
            return 'success';
        }

        return 'Error: Failed to activate subscription';

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

/**
 * Terminate Google Workspace subscription
 *
 * @param array $params
 * @return string
 */
function googleworkspace_TerminateAccount(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);
        $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

        if (empty($serviceData['customer_id']) || empty($serviceData['subscription_id'])) {
            return 'Error: Service data not found';
        }

        $terminateAction = $params['configoption9'] ?? 'cancel';

        switch ($terminateAction) {
            case 'transfer':
                $result = $client->transferSubscriptionToGoogle(
                    $serviceData['customer_id'],
                    $serviceData['subscription_id']
                );
                break;
            case 'suspend':
                $result = $client->suspendSubscription(
                    $serviceData['customer_id'],
                    $serviceData['subscription_id']
                );
                break;
            case 'cancel':
            default:
                $result = $client->deleteSubscription(
                    $serviceData['customer_id'],
                    $serviceData['subscription_id'],
                    'cancel'
                );
                break;
        }

        if ($result) {
            GoogleWorkspaceHelper::updateServiceData($params['serviceid'], [
                'status' => 'terminated',
                'terminated_date' => date('Y-m-d H:i:s'),
            ]);
            return 'success';
        }

        return 'Error: Failed to terminate subscription';

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

/**
 * Change the service password (admin user password)
 *
 * @param array $params
 * @return string
 */
function googleworkspace_ChangePassword(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);
        $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

        if (empty($serviceData['admin_email'])) {
            return 'Error: Admin user not found';
        }

        $result = $client->updateUserPassword(
            $serviceData['customer_domain'],
            $serviceData['admin_email'],
            $params['password']
        );

        if ($result) {
            return 'success';
        }

        return 'Error: Failed to change password';

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

/**
 * Test connection to Google APIs
 *
 * @param array $params
 * @return array
 */
function googleworkspace_TestConnection(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);
        $result = $client->testConnection();

        if ($result) {
            return [
                'success' => true,
                'message' => 'Connection successful',
            ];
        }

        return [
            'success' => false,
            'error' => 'Connection test failed',
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}

/**
 * Admin area custom button actions
 *
 * @return array
 */
function googleworkspace_AdminCustomButtonArray()
{
    return [
        'Create Admin User' => 'createAdminUser',
        'Sync Subscription' => 'syncSubscription',
        'Reset Admin Password' => 'resetAdminPassword',
        'Start Paid Service' => 'startPaidService',
        'Get Verification Token' => 'getVerificationToken',
    ];
}

/**
 * Start paid service (convert trial to paid)
 *
 * @param array $params
 * @return string
 */
function googleworkspace_startPaidService(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);
        $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

        if (empty($serviceData['customer_id']) || empty($serviceData['subscription_id'])) {
            return 'Error: Service data not found';
        }

        $result = $client->startPaidService(
            $serviceData['customer_id'],
            $serviceData['subscription_id']
        );

        if ($result) {
            GoogleWorkspaceHelper::updateServiceData($params['serviceid'], [
                'plan_type' => 'FLEXIBLE',
                'status' => 'active',
            ]);
            return 'success';
        }

        return 'Error: Failed to start paid service';

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

/**
 * Get domain verification token
 *
 * @param array $params
 * @return string
 */
function googleworkspace_getVerificationToken(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);
        $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

        if (empty($serviceData['customer_domain'])) {
            return 'Error: Customer domain not found';
        }

        $token = $client->getVerificationToken($serviceData['customer_domain'], 'DNS_TXT');

        if ($token && isset($token['token'])) {
            return 'DNS TXT Record: ' . $token['token'];
        }

        return 'Error: Failed to get verification token';

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

/**
 * Create admin user for existing subscription
 *
 * @param array $params
 * @return string
 */
function googleworkspace_createAdminUser(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);
        $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

        if (empty($serviceData['customer_domain'])) {
            return 'Error: Customer domain not found';
        }

        $customerDomain = $serviceData['customer_domain'];
        $adminPassword = GoogleWorkspaceHelper::generatePassword();

        $adminUser = $client->createUser($customerDomain, [
            'primaryEmail' => 'admin@' . $customerDomain,
            'name' => [
                'givenName' => $params['clientsdetails']['firstname'],
                'familyName' => $params['clientsdetails']['lastname'],
            ],
            'password' => $adminPassword,
            'changePasswordAtNextLogin' => true,
        ]);

        if ($adminUser) {
            $client->makeUserAdmin($customerDomain, 'admin@' . $customerDomain);

            Capsule::table('tblhosting')
                ->where('id', $params['serviceid'])
                ->update([
                    'username' => 'admin@' . $customerDomain,
                    'password' => encrypt($adminPassword),
                ]);

            GoogleWorkspaceHelper::updateServiceData($params['serviceid'], [
                'admin_email' => 'admin@' . $customerDomain,
            ]);

            return 'success';
        }

        return 'Error: Failed to create admin user';

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

/**
 * Sync subscription data from Google
 *
 * @param array $params
 * @return string
 */
function googleworkspace_syncSubscription(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);
        $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

        if (empty($serviceData['customer_id']) || empty($serviceData['subscription_id'])) {
            return 'Error: Service data not found';
        }

        $subscription = $client->getSubscription(
            $serviceData['customer_id'],
            $serviceData['subscription_id']
        );

        if ($subscription) {
            GoogleWorkspaceHelper::updateServiceData($params['serviceid'], [
                'sku_id' => $subscription['skuId'] ?? $serviceData['sku_id'],
                'licenses' => $subscription['seats']['numberOfSeats'] ?? $serviceData['licenses'],
                'status' => strtolower($subscription['status'] ?? 'active'),
                'last_synced' => date('Y-m-d H:i:s'),
            ]);

            return 'success';
        }

        return 'Error: Failed to sync subscription';

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

/**
 * Reset admin user password
 *
 * @param array $params
 * @return string
 */
function googleworkspace_resetAdminPassword(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);
        $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

        if (empty($serviceData['admin_email']) || empty($serviceData['customer_domain'])) {
            return 'Error: Admin user not found';
        }

        $newPassword = GoogleWorkspaceHelper::generatePassword();

        $result = $client->updateUserPassword(
            $serviceData['customer_domain'],
            $serviceData['admin_email'],
            $newPassword
        );

        if ($result) {
            Capsule::table('tblhosting')
                ->where('id', $params['serviceid'])
                ->update(['password' => encrypt($newPassword)]);

            return 'success';
        }

        return 'Error: Failed to reset password';

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

/**
 * View users - redirects to admin area
 *
 * @param array $params
 * @return string
 */
function googleworkspace_viewUsers(array $params)
{
    return 'success';
}

/**
 * Admin area output HTML
 *
 * @param array $params
 * @return array
 */
function googleworkspace_AdminServicesTabFields(array $params)
{
    try {
        $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

        $fields = [];

        if (!empty($serviceData)) {
            $fields['Customer ID'] = $serviceData['customer_id'] ?? 'N/A';
            $fields['Subscription ID'] = $serviceData['subscription_id'] ?? 'N/A';
            $fields['Customer Domain'] = $serviceData['customer_domain'] ?? 'N/A';
            $fields['SKU ID'] = $serviceData['sku_id'] ?? 'N/A';
            $fields['Licenses'] = $serviceData['licenses'] ?? 'N/A';
            $fields['Status'] = ucfirst($serviceData['status'] ?? 'unknown');
            $fields['Admin Email'] = $serviceData['admin_email'] ?? 'N/A';
            $fields['Last Synced'] = $serviceData['last_synced'] ?? 'Never';
        } else {
            $fields['Status'] = 'Not provisioned';
        }

        return $fields;

    } catch (Exception $e) {
        return ['Error' => $e->getMessage()];
    }
}

/**
 * Client area output
 *
 * @param array $params
 * @return array
 */
function googleworkspace_ClientArea(array $params)
{
    try {
        $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);
        $client = new GoogleWorkspaceClient($params);

        // Handle AJAX requests
        if (isset($_REQUEST['modop']) && $_REQUEST['modop'] === 'custom') {
            header('Content-Type: application/json');

            $action = $_REQUEST['a'] ?? '';
            $response = ['success' => false];

            switch ($action) {
                // User Management
                case 'getUsers':
                    $users = $client->listUsers($serviceData['customer_domain']);
                    $response = ['success' => true, 'users' => $users];
                    break;

                case 'addUser':
                    $email = $_POST['email'] ?? '';
                    $firstName = $_POST['firstName'] ?? '';
                    $lastName = $_POST['lastName'] ?? '';
                    $password = $_POST['password'] ?? GoogleWorkspaceHelper::generatePassword();
                    $orgUnit = $_POST['orgUnit'] ?? '/';

                    $userData = [
                        'primaryEmail' => $email,
                        'name' => ['givenName' => $firstName, 'familyName' => $lastName],
                        'password' => $password,
                        'changePasswordAtNextLogin' => true,
                    ];
                    if ($orgUnit !== '/') {
                        $userData['orgUnitPath'] = $orgUnit;
                    }

                    $result = $client->createUser($serviceData['customer_domain'], $userData);
                    $response = ['success' => (bool)$result, 'password' => $password];
                    break;

                case 'deleteUser':
                    $email = $_POST['email'] ?? '';
                    $result = $client->deleteUser($serviceData['customer_domain'], $email);
                    $response = ['success' => (bool)$result];
                    break;

                case 'resetUserPassword':
                    $email = $_POST['email'] ?? '';
                    $password = $_POST['password'] ?? GoogleWorkspaceHelper::generatePassword();
                    $result = $client->updateUserPassword(
                        $serviceData['customer_domain'],
                        $email,
                        $password
                    );
                    $response = ['success' => (bool)$result, 'password' => $password];
                    break;

                case 'suspendUser':
                    $email = $_POST['email'] ?? '';
                    $result = $client->suspendUser($serviceData['customer_domain'], $email);
                    $response = ['success' => (bool)$result];
                    break;

                case 'unsuspendUser':
                    $email = $_POST['email'] ?? '';
                    $result = $client->unsuspendUser($serviceData['customer_domain'], $email);
                    $response = ['success' => (bool)$result];
                    break;

                case 'makeAdmin':
                    $email = $_POST['email'] ?? '';
                    $result = $client->makeUserAdmin($serviceData['customer_domain'], $email);
                    $response = ['success' => (bool)$result];
                    break;

                // Group Management
                case 'getGroups':
                    $groups = $client->listGroups($serviceData['customer_domain']);
                    $response = ['success' => true, 'groups' => $groups];
                    break;

                case 'addGroup':
                    $email = $_POST['email'] ?? '';
                    $name = $_POST['name'] ?? '';
                    $description = $_POST['description'] ?? '';

                    $result = $client->createGroup($serviceData['customer_domain'], [
                        'email' => $email,
                        'name' => $name,
                        'description' => $description,
                    ]);
                    $response = ['success' => (bool)$result];
                    break;

                case 'deleteGroup':
                    $email = $_POST['email'] ?? '';
                    $result = $client->deleteGroup($email);
                    $response = ['success' => (bool)$result];
                    break;

                case 'getGroupMembers':
                    $email = $_GET['email'] ?? '';
                    $members = $client->listGroupMembers($email);
                    $response = ['success' => true, 'members' => $members];
                    break;

                case 'addGroupMember':
                    $groupEmail = $_POST['groupEmail'] ?? '';
                    $memberEmail = $_POST['memberEmail'] ?? '';
                    $role = $_POST['role'] ?? 'MEMBER';
                    $result = $client->addGroupMember($groupEmail, $memberEmail, $role);
                    $response = ['success' => (bool)$result];
                    break;

                case 'removeGroupMember':
                    $groupEmail = $_POST['groupEmail'] ?? '';
                    $memberEmail = $_POST['memberEmail'] ?? '';
                    $result = $client->removeGroupMember($groupEmail, $memberEmail);
                    $response = ['success' => (bool)$result];
                    break;

                // Alias Management
                case 'getUserAliases':
                    $email = $_GET['email'] ?? '';
                    $aliases = $client->listUserAliases($email);
                    $response = ['success' => true, 'aliases' => $aliases];
                    break;

                case 'addUserAlias':
                    $email = $_POST['email'] ?? '';
                    $alias = $_POST['alias'] ?? '';
                    $result = $client->addUserAlias($email, $alias);
                    $response = ['success' => (bool)$result];
                    break;

                case 'deleteUserAlias':
                    $email = $_POST['email'] ?? '';
                    $alias = $_POST['alias'] ?? '';
                    $result = $client->deleteUserAlias($email, $alias);
                    $response = ['success' => (bool)$result];
                    break;

                // Organizational Units
                case 'getOrgUnits':
                    $orgUnits = $client->listOrgUnits($serviceData['customer_id']);
                    $response = ['success' => true, 'orgUnits' => $orgUnits];
                    break;

                case 'addOrgUnit':
                    $name = $_POST['name'] ?? '';
                    $parentPath = $_POST['parentPath'] ?? '/';
                    $description = $_POST['description'] ?? '';

                    $result = $client->createOrgUnit($serviceData['customer_id'], [
                        'name' => $name,
                        'parentOrgUnitPath' => $parentPath,
                        'description' => $description,
                    ]);
                    $response = ['success' => (bool)$result];
                    break;

                case 'deleteOrgUnit':
                    $path = $_POST['path'] ?? '';
                    $result = $client->deleteOrgUnit($serviceData['customer_id'], $path);
                    $response = ['success' => (bool)$result];
                    break;

                case 'moveUserToOrgUnit':
                    $email = $_POST['email'] ?? '';
                    $orgUnit = $_POST['orgUnit'] ?? '/';
                    $result = $client->moveUserToOrgUnit($email, $orgUnit);
                    $response = ['success' => (bool)$result];
                    break;

                // Domain Verification
                case 'getDomainVerificationToken':
                    $domain = $_GET['domain'] ?? $serviceData['customer_domain'];
                    $method = $_GET['method'] ?? 'DNS';
                    $token = $client->getVerificationToken($domain, $method);
                    $response = ['success' => (bool)$token, 'token' => $token];
                    break;

                case 'verifyDomain':
                    $domain = $_POST['domain'] ?? '';
                    $method = $_POST['method'] ?? 'DNS';
                    $result = $client->verifyDomain($domain, $method);
                    $response = ['success' => (bool)$result, 'result' => $result];
                    break;

                // Domain Management
                case 'getDomains':
                    $domains = $client->listDomains($serviceData['customer_id']);
                    $response = ['success' => true, 'domains' => $domains];
                    break;

                case 'addDomain':
                    $domain = $_POST['domain'] ?? '';
                    $result = $client->addDomain($serviceData['customer_id'], $domain);
                    $response = ['success' => (bool)$result];
                    break;

                case 'deleteDomain':
                    $domain = $_POST['domain'] ?? '';
                    $result = $client->deleteDomain($serviceData['customer_id'], $domain);
                    $response = ['success' => (bool)$result];
                    break;

                // Subscription
                case 'getSubscription':
                    $subscription = $client->getSubscription(
                        $serviceData['customer_id'],
                        $serviceData['subscription_id']
                    );
                    $response = ['success' => true, 'subscription' => $subscription];
                    break;

                case 'startPaidService':
                    $result = $client->startPaidService(
                        $serviceData['customer_id'],
                        $serviceData['subscription_id']
                    );
                    $response = ['success' => (bool)$result];
                    break;

                // 2FA Status
                case 'getUser2FAStatus':
                    $email = $_GET['email'] ?? '';
                    $status = $client->getUser2FAStatus($email);
                    $response = ['success' => (bool)$status, 'status' => $status];
                    break;

                // Bulk Import
                case 'bulkImportUsers':
                    $usersData = json_decode($_POST['users'] ?? '[]', true);
                    if (empty($usersData)) {
                        $response = ['success' => false, 'error' => 'No users provided'];
                        break;
                    }
                    $results = $client->createUsersBatch($serviceData['customer_domain'], $usersData);
                    $response = ['success' => true, 'results' => $results];
                    break;

                default:
                    $response = ['success' => false, 'error' => 'Unknown action'];
            }

            echo json_encode($response);
            exit;
        }

        // Get subscription details
        $subscription = null;
        $users = [];
        $groups = [];
        $orgUnits = [];
        $domains = [];

        if (!empty($serviceData['customer_id']) && !empty($serviceData['subscription_id'])) {
            try {
                $subscription = $client->getSubscription(
                    $serviceData['customer_id'],
                    $serviceData['subscription_id']
                );
                $users = $client->listUsers($serviceData['customer_domain']);

                // Load groups if enabled
                if ($params['configoption11'] === 'on') {
                    $groups = $client->listGroups($serviceData['customer_domain']);
                }

                // Load org units if enabled
                if ($params['configoption13'] === 'on') {
                    $orgUnits = $client->listOrgUnits($serviceData['customer_id']);
                }

                // Load domains
                $domains = $client->listDomains($serviceData['customer_id']);
            } catch (Exception $e) {
                // Silently fail - will show cached data
            }
        }

        // Prepare template variables
        $templateVars = [
            'serviceData' => $serviceData,
            'subscription' => $subscription,
            'users' => $users,
            'groups' => $groups,
            'orgUnits' => $orgUnits,
            'domains' => $domains,
            'showPassword' => $params['configoption10'] === 'on',
            'adminPassword' => $params['configoption10'] === 'on' ? $params['password'] : '********',
            'enableGroups' => $params['configoption11'] === 'on',
            'enableAliases' => $params['configoption12'] === 'on',
            'enableOrgUnits' => $params['configoption13'] === 'on',
            'skuNames' => GoogleWorkspaceHelper::getSkuNames(),
            'planNames' => GoogleWorkspaceHelper::getPlanNames(),
            'isTrial' => ($subscription['plan']['planName'] ?? '') === 'TRIAL' || ($serviceData['plan_type'] ?? '') === 'TRIAL',
        ];

        return [
            'tabOverviewReplacementTemplate' => 'templates/clientarea.tpl',
            'templateVariables' => $templateVars,
        ];

    } catch (Exception $e) {
        return [
            'tabOverviewReplacementTemplate' => 'templates/error.tpl',
            'templateVariables' => ['error' => $e->getMessage()],
        ];
    }
}

/**
 * Client area allowed functions
 *
 * @return array
 */
function googleworkspace_ClientAreaAllowedFunctions()
{
    return [
        'Manage Users' => 'manageUsers',
    ];
}

/**
 * Upgrade/downgrade handler
 *
 * @param array $params
 * @return string
 */
function googleworkspace_ChangePackage(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);
        $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

        if (empty($serviceData['customer_id']) || empty($serviceData['subscription_id'])) {
            return 'Error: Service data not found';
        }

        // Update license count if changed
        $newLicenses = !empty($params['configoptions']['licenses'])
            ? (int)$params['configoptions']['licenses']
            : (int)$params['configoption7'];

        if ($newLicenses !== (int)$serviceData['licenses']) {
            $result = $client->updateSubscriptionSeats(
                $serviceData['customer_id'],
                $serviceData['subscription_id'],
                $newLicenses
            );

            if ($result) {
                GoogleWorkspaceHelper::updateServiceData($params['serviceid'], [
                    'licenses' => $newLicenses,
                ]);
            }
        }

        // Update SKU if changed
        if ($params['configoption5'] !== $serviceData['sku_id']) {
            $result = $client->changeSubscriptionPlan(
                $serviceData['customer_id'],
                $serviceData['subscription_id'],
                $params['configoption5']
            );

            if ($result) {
                GoogleWorkspaceHelper::updateServiceData($params['serviceid'], [
                    'sku_id' => $params['configoption5'],
                ]);
            }
        }

        return 'success';

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

/**
 * Admin Single Sign-On
 *
 * @param array $params
 * @return array
 */
function googleworkspace_AdminSingleSignOn(array $params)
{
    $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

    if (empty($serviceData['customer_domain'])) {
        return ['success' => false, 'errorMsg' => 'Customer domain not found'];
    }

    return [
        'success' => true,
        'redirectTo' => 'https://admin.google.com/' . $serviceData['customer_domain'],
    ];
}

/**
 * Client Single Sign-On
 *
 * @param array $params
 * @return array
 */
function googleworkspace_ServiceSingleSignOn(array $params)
{
    $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

    if (empty($serviceData['customer_domain'])) {
        return ['success' => false, 'errorMsg' => 'Customer domain not found'];
    }

    return [
        'success' => true,
        'redirectTo' => 'https://admin.google.com/' . $serviceData['customer_domain'],
    ];
}

/**
 * Usage update for billing
 *
 * @param array $params
 * @return array
 */
function googleworkspace_UsageUpdate(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);

        // Get all services using this product
        $services = Capsule::table('tblhosting')
            ->where('server', $params['serverid'])
            ->where('domainstatus', 'Active')
            ->get();

        foreach ($services as $service) {
            $serviceData = GoogleWorkspaceHelper::getServiceData($service->id);

            if (!empty($serviceData['customer_id']) && !empty($serviceData['subscription_id'])) {
                $subscription = $client->getSubscription(
                    $serviceData['customer_id'],
                    $serviceData['subscription_id']
                );

                if ($subscription && isset($subscription['seats']['licensedNumberOfSeats'])) {
                    Capsule::table('tblhosting')
                        ->where('id', $service->id)
                        ->update([
                            'lastupdate' => date('Y-m-d H:i:s'),
                        ]);

                    // Update disk usage field with license count for metered billing
                    Capsule::table('tblhosting')
                        ->where('id', $service->id)
                        ->update([
                            'diskusage' => $subscription['seats']['licensedNumberOfSeats'],
                            'disklimit' => $subscription['seats']['maximumNumberOfSeats'],
                        ]);
                }
            }
        }

        return 'success';

    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

/**
 * Renew subscription
 *
 * @param array $params
 * @return string
 */
function googleworkspace_Renew(array $params)
{
    try {
        $client = new GoogleWorkspaceClient($params);
        $serviceData = GoogleWorkspaceHelper::getServiceData($params['serviceid']);

        if (empty($serviceData['customer_id']) || empty($serviceData['subscription_id'])) {
            return 'Error: Service data not found';
        }

        // For annual plans, handle renewal
        if ($serviceData['plan_type'] !== 'FLEXIBLE') {
            $result = $client->changeSubscriptionRenewal(
                $serviceData['customer_id'],
                $serviceData['subscription_id'],
                'RENEW_CURRENT_USERS_MONTHLY_PAY'
            );

            if (!$result) {
                return 'Error: Failed to renew subscription';
            }
        }

        return 'success';

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}
