<?php
/**
 * Google Workspace Helper Functions
 *
 * Utility functions for the Google Workspace WHMCS module
 *
 * @package    GoogleWorkspace
 * @author     Google Workspace Module
 */

use WHMCS\Database\Capsule;

class GoogleWorkspaceHelper
{
    /**
     * Service data table name
     */
    const TABLE_NAME = 'mod_googleworkspace';

    /**
     * Initialize database table if not exists
     */
    public static function initDatabase()
    {
        if (!Capsule::schema()->hasTable(self::TABLE_NAME)) {
            Capsule::schema()->create(self::TABLE_NAME, function ($table) {
                $table->increments('id');
                $table->integer('service_id')->unsigned()->unique();
                $table->string('customer_id')->nullable();
                $table->string('subscription_id')->nullable();
                $table->string('customer_domain')->nullable();
                $table->string('sku_id')->nullable();
                $table->string('plan_type')->nullable();
                $table->integer('licenses')->default(0);
                $table->string('status')->default('pending');
                $table->string('admin_email')->nullable();
                $table->timestamp('suspended_date')->nullable();
                $table->timestamp('terminated_date')->nullable();
                $table->timestamp('last_synced')->nullable();
                $table->text('extra_data')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Get service data
     *
     * @param int $serviceId
     * @return array
     */
    public static function getServiceData($serviceId)
    {
        self::initDatabase();

        $data = Capsule::table(self::TABLE_NAME)
            ->where('service_id', $serviceId)
            ->first();

        if (!$data) {
            return [];
        }

        $result = (array)$data;

        // Decode extra_data JSON
        if (!empty($result['extra_data'])) {
            $extra = json_decode($result['extra_data'], true);
            if (is_array($extra)) {
                $result = array_merge($result, $extra);
            }
        }

        return $result;
    }

    /**
     * Save service data
     *
     * @param int $serviceId
     * @param array $data
     * @return bool
     */
    public static function saveServiceData($serviceId, array $data)
    {
        self::initDatabase();

        // Standard columns
        $columns = [
            'customer_id', 'subscription_id', 'customer_domain', 'sku_id',
            'plan_type', 'licenses', 'status', 'admin_email',
            'suspended_date', 'terminated_date', 'last_synced'
        ];

        $insertData = ['service_id' => $serviceId];
        $extraData = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $columns)) {
                $insertData[$key] = $value;
            } else {
                $extraData[$key] = $value;
            }
        }

        if (!empty($extraData)) {
            $insertData['extra_data'] = json_encode($extraData);
        }

        $insertData['created_at'] = date('Y-m-d H:i:s');
        $insertData['updated_at'] = date('Y-m-d H:i:s');

        return Capsule::table(self::TABLE_NAME)->insert($insertData);
    }

    /**
     * Update service data
     *
     * @param int $serviceId
     * @param array $data
     * @return bool
     */
    public static function updateServiceData($serviceId, array $data)
    {
        self::initDatabase();

        // Standard columns
        $columns = [
            'customer_id', 'subscription_id', 'customer_domain', 'sku_id',
            'plan_type', 'licenses', 'status', 'admin_email',
            'suspended_date', 'terminated_date', 'last_synced'
        ];

        $updateData = [];
        $extraData = [];

        // Get existing extra_data
        $existing = Capsule::table(self::TABLE_NAME)
            ->where('service_id', $serviceId)
            ->first();

        if ($existing && !empty($existing->extra_data)) {
            $extraData = json_decode($existing->extra_data, true) ?: [];
        }

        foreach ($data as $key => $value) {
            if (in_array($key, $columns)) {
                $updateData[$key] = $value;
            } else {
                $extraData[$key] = $value;
            }
        }

        if (!empty($extraData)) {
            $updateData['extra_data'] = json_encode($extraData);
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        return Capsule::table(self::TABLE_NAME)
            ->where('service_id', $serviceId)
            ->update($updateData);
    }

    /**
     * Delete service data
     *
     * @param int $serviceId
     * @return bool
     */
    public static function deleteServiceData($serviceId)
    {
        return Capsule::table(self::TABLE_NAME)
            ->where('service_id', $serviceId)
            ->delete();
    }

    /**
     * Generate secure password
     *
     * @param int $length
     * @return string
     */
    public static function generatePassword($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';

        // Ensure at least one of each type
        $password .= chr(rand(97, 122));  // lowercase
        $password .= chr(rand(65, 90));   // uppercase
        $password .= chr(rand(48, 57));   // number
        $password .= '!@#$%^&*'[rand(0, 7)]; // special

        // Fill remaining characters
        for ($i = 4; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }

        // Shuffle the password
        return str_shuffle($password);
    }

    /**
     * Generate domain from client details
     *
     * @param array $params
     * @return string
     */
    public static function generateDomain(array $params)
    {
        // Try custom field first
        if (!empty($params['customfields']['Domain'])) {
            return $params['customfields']['Domain'];
        }

        if (!empty($params['customfields']['domain'])) {
            return $params['customfields']['domain'];
        }

        // Try company name
        if (!empty($params['clientsdetails']['companyname'])) {
            $domain = preg_replace('/[^a-z0-9]/', '', strtolower($params['clientsdetails']['companyname']));
            if (strlen($domain) > 3) {
                return $domain . '.com';
            }
        }

        return '';
    }

    /**
     * Get ISO country code from country name
     *
     * @param string $country
     * @return string
     */
    public static function getCountryCode($country)
    {
        $countries = [
            'Afghanistan' => 'AF', 'Albania' => 'AL', 'Algeria' => 'DZ',
            'Argentina' => 'AR', 'Australia' => 'AU', 'Austria' => 'AT',
            'Belgium' => 'BE', 'Brazil' => 'BR', 'Bulgaria' => 'BG',
            'Canada' => 'CA', 'Chile' => 'CL', 'China' => 'CN',
            'Colombia' => 'CO', 'Croatia' => 'HR', 'Czech Republic' => 'CZ',
            'Denmark' => 'DK', 'Egypt' => 'EG', 'Estonia' => 'EE',
            'Finland' => 'FI', 'France' => 'FR', 'Germany' => 'DE',
            'Greece' => 'GR', 'Hong Kong' => 'HK', 'Hungary' => 'HU',
            'India' => 'IN', 'Indonesia' => 'ID', 'Ireland' => 'IE',
            'Israel' => 'IL', 'Italy' => 'IT', 'Japan' => 'JP',
            'Latvia' => 'LV', 'Lithuania' => 'LT', 'Luxembourg' => 'LU',
            'Malaysia' => 'MY', 'Mexico' => 'MX', 'Netherlands' => 'NL',
            'New Zealand' => 'NZ', 'Norway' => 'NO', 'Pakistan' => 'PK',
            'Peru' => 'PE', 'Philippines' => 'PH', 'Poland' => 'PL',
            'Portugal' => 'PT', 'Romania' => 'RO', 'Russia' => 'RU',
            'Saudi Arabia' => 'SA', 'Singapore' => 'SG', 'Slovakia' => 'SK',
            'Slovenia' => 'SI', 'South Africa' => 'ZA', 'South Korea' => 'KR',
            'Spain' => 'ES', 'Sweden' => 'SE', 'Switzerland' => 'CH',
            'Taiwan' => 'TW', 'Thailand' => 'TH', 'Turkey' => 'TR',
            'Ukraine' => 'UA', 'United Arab Emirates' => 'AE',
            'United Kingdom' => 'GB', 'United States' => 'US',
            'Vietnam' => 'VN',
        ];

        // If already a code, return as-is
        if (strlen($country) === 2) {
            return strtoupper($country);
        }

        return $countries[$country] ?? 'US';
    }

    /**
     * Get SKU display names
     *
     * @return array
     */
    public static function getSkuNames()
    {
        return [
            'Google-Apps-For-Business' => 'Business Starter',
            'Google-Apps-Unlimited' => 'Business Standard',
            'Google-Apps-For-Postini' => 'Business Plus',
            '1010020027' => 'Enterprise Standard',
            '1010020028' => 'Enterprise Plus',
            '1010020025' => 'Enterprise Essentials',
            'Google-Apps-Lite' => 'Frontline Starter',
            '1010020026' => 'Frontline Standard',
            '1010060003' => 'Essentials Starter',
        ];
    }

    /**
     * Get plan type display names
     *
     * @return array
     */
    public static function getPlanNames()
    {
        return [
            'FLEXIBLE' => 'Flexible (Monthly)',
            'ANNUAL_MONTHLY_PAY' => 'Annual (Monthly Payments)',
            'ANNUAL_YEARLY_PAY' => 'Annual (Yearly Payment)',
            'TRIAL' => 'Free Trial',
        ];
    }

    /**
     * Get subscription status display name
     *
     * @param string $status
     * @return string
     */
    public static function getStatusDisplay($status)
    {
        $statuses = [
            'ACTIVE' => 'Active',
            'SUSPENDED' => 'Suspended',
            'PENDING' => 'Pending',
            'CANCELLED' => 'Cancelled',
        ];

        return $statuses[strtoupper($status)] ?? ucfirst($status);
    }

    /**
     * Log module activity
     *
     * @param int $serviceId
     * @param string $action
     * @param string $message
     * @param bool $success
     */
    public static function log($serviceId, $action, $message, $success = true)
    {
        logModuleCall(
            'googleworkspace',
            $action,
            ['service_id' => $serviceId],
            $message,
            $success ? 'success' : 'error'
        );
    }

    /**
     * Format bytes to human readable
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Validate email format
     *
     * @param string $email
     * @return bool
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate domain format
     *
     * @param string $domain
     * @return bool
     */
    public static function validateDomain($domain)
    {
        return preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i', $domain);
    }

    /**
     * Get WHMCS admin language
     *
     * @return string
     */
    public static function getAdminLanguage()
    {
        global $CONFIG;
        return $CONFIG['Language'] ?? 'english';
    }

    /**
     * Sanitize string for use in Google API
     *
     * @param string $string
     * @return string
     */
    public static function sanitize($string)
    {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
}
