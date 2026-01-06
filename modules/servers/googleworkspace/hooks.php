<?php
/**
 * Google Workspace Module Hooks
 *
 * Automation hooks for Google Workspace provisioning module
 *
 * @package    GoogleWorkspace
 * @author     Google Workspace Module
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

/**
 * Hook: AfterProductUpgrade
 *
 * Handle license changes after product upgrade/downgrade
 */
add_hook('AfterProductUpgrade', 1, function ($vars) {
    $serviceId = $vars['serviceid'];

    // Get service details
    $service = Capsule::table('tblhosting')
        ->where('id', $serviceId)
        ->first();

    if (!$service) {
        return;
    }

    // Get product
    $product = Capsule::table('tblproducts')
        ->where('id', $service->packageid)
        ->first();

    if (!$product || $product->servertype !== 'googleworkspace') {
        return;
    }

    // Trigger license sync
    try {
        require_once __DIR__ . '/lib/GoogleWorkspaceClient.php';
        require_once __DIR__ . '/lib/GoogleWorkspaceHelper.php';

        // Get module params - simplified approach
        $params = [
            'serviceid' => $serviceId,
            'configoption1' => $product->configoption1,
            'configoption2' => $product->configoption2,
            'configoption3' => $product->configoption3,
            'configoption4' => $product->configoption4,
            'configoption5' => $product->configoption5,
            'configoption6' => $product->configoption6,
            'configoption7' => $product->configoption7,
        ];

        // Get new license count from configurable options
        $configOptions = Capsule::table('tblhostingconfigoptions')
            ->join('tblproductconfigoptions', 'tblhostingconfigoptions.configid', '=', 'tblproductconfigoptions.id')
            ->where('tblhostingconfigoptions.relid', $serviceId)
            ->where('tblproductconfigoptions.optionname', 'LIKE', '%license%')
            ->first();

        if ($configOptions) {
            $newLicenses = (int)$configOptions->qty;
            $serviceData = GoogleWorkspaceHelper::getServiceData($serviceId);

            if ($newLicenses > 0 && $newLicenses !== (int)$serviceData['licenses']) {
                $client = new GoogleWorkspaceClient($params);
                $client->updateSubscriptionSeats(
                    $serviceData['customer_id'],
                    $serviceData['subscription_id'],
                    $newLicenses
                );

                GoogleWorkspaceHelper::updateServiceData($serviceId, [
                    'licenses' => $newLicenses,
                ]);

                logModuleCall('googleworkspace', 'AfterProductUpgrade', $vars, 'Licenses updated to: ' . $newLicenses, 'success');
            }
        }
    } catch (Exception $e) {
        logModuleCall('googleworkspace', 'AfterProductUpgrade', $vars, $e->getMessage(), 'error');
    }
});

/**
 * Hook: ServiceDelete
 *
 * Clean up module data when service is deleted
 */
add_hook('ServiceDelete', 1, function ($vars) {
    $serviceId = $vars['serviceid'];

    try {
        require_once __DIR__ . '/lib/GoogleWorkspaceHelper.php';
        GoogleWorkspaceHelper::deleteServiceData($serviceId);
    } catch (Exception $e) {
        logModuleCall('googleworkspace', 'ServiceDelete', $vars, $e->getMessage(), 'error');
    }
});

/**
 * Hook: DailyCronJob
 *
 * Sync subscription data daily
 */
add_hook('DailyCronJob', 1, function ($vars) {
    try {
        require_once __DIR__ . '/lib/GoogleWorkspaceClient.php';
        require_once __DIR__ . '/lib/GoogleWorkspaceHelper.php';

        // Get all active Google Workspace services
        $services = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->where('tblproducts.servertype', 'googleworkspace')
            ->where('tblhosting.domainstatus', 'Active')
            ->select('tblhosting.*', 'tblproducts.configoption1', 'tblproducts.configoption2',
                'tblproducts.configoption3', 'tblproducts.configoption4')
            ->get();

        foreach ($services as $service) {
            $serviceData = GoogleWorkspaceHelper::getServiceData($service->id);

            if (empty($serviceData['customer_id']) || empty($serviceData['subscription_id'])) {
                continue;
            }

            // Skip if synced within last 12 hours
            if (!empty($serviceData['last_synced'])) {
                $lastSynced = strtotime($serviceData['last_synced']);
                if ($lastSynced > strtotime('-12 hours')) {
                    continue;
                }
            }

            try {
                $params = [
                    'configoption1' => $service->configoption1,
                    'configoption2' => $service->configoption2,
                    'configoption3' => $service->configoption3,
                    'configoption4' => $service->configoption4,
                ];

                $client = new GoogleWorkspaceClient($params);
                $subscription = $client->getSubscription(
                    $serviceData['customer_id'],
                    $serviceData['subscription_id']
                );

                if ($subscription) {
                    GoogleWorkspaceHelper::updateServiceData($service->id, [
                        'licenses' => $subscription['seats']['numberOfSeats'] ?? $serviceData['licenses'],
                        'status' => strtolower($subscription['status'] ?? 'active'),
                        'last_synced' => date('Y-m-d H:i:s'),
                    ]);
                }
            } catch (Exception $e) {
                // Log but continue with other services
                logModuleCall('googleworkspace', 'DailyCronSync', ['service_id' => $service->id], $e->getMessage(), 'error');
            }
        }

        logModuleCall('googleworkspace', 'DailyCronJob', [], 'Sync completed for ' . count($services) . ' services', 'success');
    } catch (Exception $e) {
        logModuleCall('googleworkspace', 'DailyCronJob', [], $e->getMessage(), 'error');
    }
});

/**
 * Hook: ClientAreaHeadOutput
 *
 * Add Font Awesome icons to client area
 */
add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    // Check if we're on a Google Workspace service page
    if (isset($vars['filename']) && $vars['filename'] === 'clientarea' &&
        isset($_GET['action']) && $_GET['action'] === 'productdetails') {

        return '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">';
    }

    return '';
});

/**
 * Hook: AdminAreaHeadOutput
 *
 * Add custom styles to admin area
 */
add_hook('AdminAreaHeadOutput', 1, function ($vars) {
    return '
    <style>
        .googleworkspace-admin-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .googleworkspace-admin-info table {
            width: 100%;
        }
        .googleworkspace-admin-info td {
            padding: 5px 10px;
        }
        .googleworkspace-admin-info td:first-child {
            font-weight: bold;
            width: 150px;
        }
    </style>
    ';
});

/**
 * Hook: InvoicePaid
 *
 * Handle automated provisioning or renewal when invoice is paid
 */
add_hook('InvoicePaid', 1, function ($vars) {
    $invoiceId = $vars['invoiceid'];

    // Get invoice items
    $items = Capsule::table('tblinvoiceitems')
        ->where('invoiceid', $invoiceId)
        ->where('type', 'Hosting')
        ->get();

    foreach ($items as $item) {
        if (empty($item->relid)) {
            continue;
        }

        // Get service
        $service = Capsule::table('tblhosting')
            ->where('id', $item->relid)
            ->first();

        if (!$service) {
            continue;
        }

        // Get product
        $product = Capsule::table('tblproducts')
            ->where('id', $service->packageid)
            ->first();

        if (!$product || $product->servertype !== 'googleworkspace') {
            continue;
        }

        // Check if this is a renewal (service already active)
        if ($service->domainstatus === 'Active') {
            try {
                require_once __DIR__ . '/lib/GoogleWorkspaceClient.php';
                require_once __DIR__ . '/lib/GoogleWorkspaceHelper.php';

                $serviceData = GoogleWorkspaceHelper::getServiceData($service->id);

                if (!empty($serviceData['plan_type']) && $serviceData['plan_type'] !== 'FLEXIBLE') {
                    // Annual plan - may need renewal handling
                    logModuleCall('googleworkspace', 'InvoicePaid', ['service_id' => $service->id], 'Renewal invoice paid', 'success');
                }
            } catch (Exception $e) {
                logModuleCall('googleworkspace', 'InvoicePaid', ['service_id' => $service->id], $e->getMessage(), 'error');
            }
        }
    }
});
