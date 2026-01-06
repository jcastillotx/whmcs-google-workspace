<?php
/**
 * Google Workspace API Client
 *
 * Handles all communication with Google Workspace APIs including:
 * - Reseller API (customers, subscriptions)
 * - Directory API (users, groups)
 * - Site Verification API
 *
 * @package    GoogleWorkspace
 * @author     Google Workspace Module
 */

class GoogleWorkspaceClient
{
    /**
     * OAuth 2.0 credentials
     */
    private $clientId;
    private $clientSecret;
    private $refreshToken;
    private $accessToken;
    private $tokenExpires;

    /**
     * API endpoints
     */
    const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    const RESELLER_API = 'https://reseller.googleapis.com/apps/reseller/v1';
    const DIRECTORY_API = 'https://admin.googleapis.com/admin/directory/v1';
    const SITE_VERIFICATION_API = 'https://www.googleapis.com/siteVerification/v1';

    /**
     * Reseller domain
     */
    private $resellerDomain;

    /**
     * Constructor
     *
     * @param array $params WHMCS module parameters
     */
    public function __construct(array $params)
    {
        $this->clientId = $params['configoption1'] ?? '';
        $this->clientSecret = $params['configoption2'] ?? '';
        $this->refreshToken = $params['configoption3'] ?? '';
        $this->resellerDomain = $params['configoption4'] ?? '';

        if (empty($this->clientId) || empty($this->clientSecret) || empty($this->refreshToken)) {
            throw new Exception('Google API credentials not configured');
        }
    }

    /**
     * Get OAuth 2.0 access token
     *
     * @return string
     * @throws Exception
     */
    private function getAccessToken()
    {
        // Return cached token if still valid
        if ($this->accessToken && $this->tokenExpires > time()) {
            return $this->accessToken;
        }

        $response = $this->httpRequest(self::TOKEN_URL, 'POST', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
            'grant_type' => 'refresh_token',
        ], false);

        if (!$response || !isset($response['access_token'])) {
            throw new Exception('Failed to obtain access token');
        }

        $this->accessToken = $response['access_token'];
        $this->tokenExpires = time() + ($response['expires_in'] ?? 3600) - 60;

        return $this->accessToken;
    }

    /**
     * Make HTTP request
     *
     * @param string $url
     * @param string $method
     * @param array $data
     * @param bool $auth Include authorization header
     * @return array|null
     * @throws Exception
     */
    private function httpRequest($url, $method = 'GET', $data = [], $auth = true)
    {
        $ch = curl_init();

        $headers = ['Content-Type: application/json'];

        if ($auth) {
            $headers[] = 'Authorization: Bearer ' . $this->getAccessToken();
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($auth) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    $headers[0] = 'Content-Type: application/x-www-form-urlencoded';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMsg = isset($decoded['error']['message'])
                ? $decoded['error']['message']
                : 'API Error (HTTP ' . $httpCode . ')';
            throw new Exception($errorMsg);
        }

        return $decoded;
    }

    /**
     * Test API connection
     *
     * @return bool
     */
    public function testConnection()
    {
        try {
            $this->getAccessToken();
            // Try to list subscriptions to verify reseller access
            $this->httpRequest(self::RESELLER_API . '/subscriptions?maxResults=1');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================================
    // Customer Management (Reseller API)
    // ========================================

    /**
     * Get customer by domain
     *
     * @param string $customerDomain
     * @return array|null
     */
    public function getCustomer($customerDomain)
    {
        try {
            return $this->httpRequest(
                self::RESELLER_API . '/customers/' . urlencode($customerDomain)
            );
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Create a new customer
     *
     * @param array $customerData
     * @return array|null
     */
    public function createCustomer(array $customerData)
    {
        return $this->httpRequest(
            self::RESELLER_API . '/customers',
            'POST',
            $customerData
        );
    }

    /**
     * Update customer
     *
     * @param string $customerId
     * @param array $customerData
     * @return array|null
     */
    public function updateCustomer($customerId, array $customerData)
    {
        return $this->httpRequest(
            self::RESELLER_API . '/customers/' . urlencode($customerId),
            'PUT',
            $customerData
        );
    }

    // ========================================
    // Subscription Management (Reseller API)
    // ========================================

    /**
     * Create subscription
     *
     * @param string $customerId
     * @param array $subscriptionData
     * @return array|null
     */
    public function createSubscription($customerId, array $subscriptionData)
    {
        return $this->httpRequest(
            self::RESELLER_API . '/customers/' . urlencode($customerId) . '/subscriptions',
            'POST',
            $subscriptionData
        );
    }

    /**
     * Get subscription
     *
     * @param string $customerId
     * @param string $subscriptionId
     * @return array|null
     */
    public function getSubscription($customerId, $subscriptionId)
    {
        try {
            return $this->httpRequest(
                self::RESELLER_API . '/customers/' . urlencode($customerId)
                . '/subscriptions/' . urlencode($subscriptionId)
            );
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * List all subscriptions for a customer
     *
     * @param string $customerId
     * @return array
     */
    public function listSubscriptions($customerId)
    {
        try {
            $response = $this->httpRequest(
                self::RESELLER_API . '/subscriptions?customerId=' . urlencode($customerId)
            );
            return $response['subscriptions'] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Update subscription seats
     *
     * @param string $customerId
     * @param string $subscriptionId
     * @param int $numberOfSeats
     * @return array|null
     */
    public function updateSubscriptionSeats($customerId, $subscriptionId, $numberOfSeats)
    {
        return $this->httpRequest(
            self::RESELLER_API . '/customers/' . urlencode($customerId)
            . '/subscriptions/' . urlencode($subscriptionId) . '/changeSeats',
            'POST',
            [
                'seats' => [
                    'numberOfSeats' => $numberOfSeats,
                    'maximumNumberOfSeats' => $numberOfSeats,
                ],
            ]
        );
    }

    /**
     * Suspend subscription
     *
     * @param string $customerId
     * @param string $subscriptionId
     * @return bool
     */
    public function suspendSubscription($customerId, $subscriptionId)
    {
        try {
            $this->httpRequest(
                self::RESELLER_API . '/customers/' . urlencode($customerId)
                . '/subscriptions/' . urlencode($subscriptionId) . '/suspend',
                'POST'
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Activate (unsuspend) subscription
     *
     * @param string $customerId
     * @param string $subscriptionId
     * @return bool
     */
    public function activateSubscription($customerId, $subscriptionId)
    {
        try {
            $this->httpRequest(
                self::RESELLER_API . '/customers/' . urlencode($customerId)
                . '/subscriptions/' . urlencode($subscriptionId) . '/activate',
                'POST'
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete subscription
     *
     * @param string $customerId
     * @param string $subscriptionId
     * @param string $deletionType cancel|transfer_to_direct
     * @return bool
     */
    public function deleteSubscription($customerId, $subscriptionId, $deletionType = 'cancel')
    {
        try {
            $this->httpRequest(
                self::RESELLER_API . '/customers/' . urlencode($customerId)
                . '/subscriptions/' . urlencode($subscriptionId)
                . '?deletionType=' . urlencode($deletionType),
                'DELETE'
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Transfer subscription to Google (direct)
     *
     * @param string $customerId
     * @param string $subscriptionId
     * @return bool
     */
    public function transferSubscriptionToGoogle($customerId, $subscriptionId)
    {
        return $this->deleteSubscription($customerId, $subscriptionId, 'transfer_to_direct');
    }

    /**
     * Change subscription renewal settings
     *
     * @param string $customerId
     * @param string $subscriptionId
     * @param string $renewalType
     * @return array|null
     */
    public function changeSubscriptionRenewal($customerId, $subscriptionId, $renewalType)
    {
        return $this->httpRequest(
            self::RESELLER_API . '/customers/' . urlencode($customerId)
            . '/subscriptions/' . urlencode($subscriptionId) . '/changeRenewalSettings',
            'POST',
            ['renewalType' => $renewalType]
        );
    }

    /**
     * Change subscription plan
     *
     * @param string $customerId
     * @param string $subscriptionId
     * @param string $planName
     * @param string|null $skuId
     * @return array|null
     */
    public function changeSubscriptionPlan($customerId, $subscriptionId, $planName, $skuId = null)
    {
        $data = ['planName' => $planName];

        if ($skuId) {
            $data['skuId'] = $skuId;
        }

        return $this->httpRequest(
            self::RESELLER_API . '/customers/' . urlencode($customerId)
            . '/subscriptions/' . urlencode($subscriptionId) . '/changePlan',
            'POST',
            $data
        );
    }

    /**
     * Start paid service (end trial)
     *
     * @param string $customerId
     * @param string $subscriptionId
     * @return bool
     */
    public function startPaidService($customerId, $subscriptionId)
    {
        try {
            $this->httpRequest(
                self::RESELLER_API . '/customers/' . urlencode($customerId)
                . '/subscriptions/' . urlencode($subscriptionId) . '/startPaidService',
                'POST'
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================================
    // User Management (Directory API)
    // ========================================

    /**
     * Create a new user
     *
     * @param string $customerDomain
     * @param array $userData
     * @return array|null
     */
    public function createUser($customerDomain, array $userData)
    {
        // Ensure email has domain
        if (!strpos($userData['primaryEmail'], '@')) {
            $userData['primaryEmail'] .= '@' . $customerDomain;
        }

        return $this->httpRequest(
            self::DIRECTORY_API . '/users',
            'POST',
            $userData
        );
    }

    /**
     * Get user
     *
     * @param string $customerDomain
     * @param string $userEmail
     * @return array|null
     */
    public function getUser($customerDomain, $userEmail)
    {
        try {
            return $this->httpRequest(
                self::DIRECTORY_API . '/users/' . urlencode($userEmail)
            );
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * List users in domain
     *
     * @param string $customerDomain
     * @param int $maxResults
     * @return array
     */
    public function listUsers($customerDomain, $maxResults = 100)
    {
        try {
            $response = $this->httpRequest(
                self::DIRECTORY_API . '/users?domain=' . urlencode($customerDomain)
                . '&maxResults=' . $maxResults
            );
            return $response['users'] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Update user
     *
     * @param string $customerDomain
     * @param string $userEmail
     * @param array $userData
     * @return array|null
     */
    public function updateUser($customerDomain, $userEmail, array $userData)
    {
        return $this->httpRequest(
            self::DIRECTORY_API . '/users/' . urlencode($userEmail),
            'PUT',
            $userData
        );
    }

    /**
     * Update user password
     *
     * @param string $customerDomain
     * @param string $userEmail
     * @param string $password
     * @param bool $changeAtNextLogin
     * @return bool
     */
    public function updateUserPassword($customerDomain, $userEmail, $password, $changeAtNextLogin = false)
    {
        try {
            $this->httpRequest(
                self::DIRECTORY_API . '/users/' . urlencode($userEmail),
                'PATCH',
                [
                    'password' => $password,
                    'changePasswordAtNextLogin' => $changeAtNextLogin,
                ]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete user
     *
     * @param string $customerDomain
     * @param string $userEmail
     * @return bool
     */
    public function deleteUser($customerDomain, $userEmail)
    {
        try {
            $this->httpRequest(
                self::DIRECTORY_API . '/users/' . urlencode($userEmail),
                'DELETE'
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Make user an admin
     *
     * @param string $customerDomain
     * @param string $userEmail
     * @return bool
     */
    public function makeUserAdmin($customerDomain, $userEmail)
    {
        try {
            $this->httpRequest(
                self::DIRECTORY_API . '/users/' . urlencode($userEmail) . '/makeAdmin',
                'POST',
                ['status' => true]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Suspend user
     *
     * @param string $customerDomain
     * @param string $userEmail
     * @return bool
     */
    public function suspendUser($customerDomain, $userEmail)
    {
        try {
            $this->httpRequest(
                self::DIRECTORY_API . '/users/' . urlencode($userEmail),
                'PATCH',
                ['suspended' => true]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Unsuspend user
     *
     * @param string $customerDomain
     * @param string $userEmail
     * @return bool
     */
    public function unsuspendUser($customerDomain, $userEmail)
    {
        try {
            $this->httpRequest(
                self::DIRECTORY_API . '/users/' . urlencode($userEmail),
                'PATCH',
                ['suspended' => false]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================================
    // Group Management (Directory API)
    // ========================================

    /**
     * Create a group
     *
     * @param string $customerDomain
     * @param array $groupData
     * @return array|null
     */
    public function createGroup($customerDomain, array $groupData)
    {
        return $this->httpRequest(
            self::DIRECTORY_API . '/groups',
            'POST',
            $groupData
        );
    }

    /**
     * List groups in domain
     *
     * @param string $customerDomain
     * @return array
     */
    public function listGroups($customerDomain)
    {
        try {
            $response = $this->httpRequest(
                self::DIRECTORY_API . '/groups?domain=' . urlencode($customerDomain)
            );
            return $response['groups'] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Delete group
     *
     * @param string $groupEmail
     * @return bool
     */
    public function deleteGroup($groupEmail)
    {
        try {
            $this->httpRequest(
                self::DIRECTORY_API . '/groups/' . urlencode($groupEmail),
                'DELETE'
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Add member to group
     *
     * @param string $groupEmail
     * @param string $memberEmail
     * @param string $role OWNER|MANAGER|MEMBER
     * @return array|null
     */
    public function addGroupMember($groupEmail, $memberEmail, $role = 'MEMBER')
    {
        return $this->httpRequest(
            self::DIRECTORY_API . '/groups/' . urlencode($groupEmail) . '/members',
            'POST',
            [
                'email' => $memberEmail,
                'role' => $role,
            ]
        );
    }

    /**
     * Remove member from group
     *
     * @param string $groupEmail
     * @param string $memberEmail
     * @return bool
     */
    public function removeGroupMember($groupEmail, $memberEmail)
    {
        try {
            $this->httpRequest(
                self::DIRECTORY_API . '/groups/' . urlencode($groupEmail)
                . '/members/' . urlencode($memberEmail),
                'DELETE'
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ========================================
    // Domain Verification (Site Verification API)
    // ========================================

    /**
     * Get domain verification token
     *
     * @param string $domain
     * @param string $verificationMethod DNS|FILE|META
     * @return array|null
     */
    public function getVerificationToken($domain, $verificationMethod = 'DNS')
    {
        $site = [
            'type' => 'INET_DOMAIN',
            'identifier' => $domain,
        ];

        return $this->httpRequest(
            self::SITE_VERIFICATION_API . '/token',
            'POST',
            [
                'site' => $site,
                'verificationMethod' => $verificationMethod,
            ]
        );
    }

    /**
     * Verify domain
     *
     * @param string $domain
     * @param string $verificationMethod
     * @return array|null
     */
    public function verifyDomain($domain, $verificationMethod = 'DNS')
    {
        $site = [
            'type' => 'INET_DOMAIN',
            'identifier' => $domain,
        ];

        return $this->httpRequest(
            self::SITE_VERIFICATION_API . '/webResource?verificationMethod=' . $verificationMethod,
            'POST',
            ['site' => $site]
        );
    }

    // ========================================
    // Organizational Units (Directory API)
    // ========================================

    /**
     * Create organizational unit
     *
     * @param string $customerId
     * @param array $orgUnitData
     * @return array|null
     */
    public function createOrgUnit($customerId, array $orgUnitData)
    {
        return $this->httpRequest(
            self::DIRECTORY_API . '/customer/' . urlencode($customerId) . '/orgunits',
            'POST',
            $orgUnitData
        );
    }

    /**
     * List organizational units
     *
     * @param string $customerId
     * @return array
     */
    public function listOrgUnits($customerId)
    {
        try {
            $response = $this->httpRequest(
                self::DIRECTORY_API . '/customer/' . urlencode($customerId) . '/orgunits'
            );
            return $response['organizationUnits'] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Delete organizational unit
     *
     * @param string $customerId
     * @param string $orgUnitPath
     * @return bool
     */
    public function deleteOrgUnit($customerId, $orgUnitPath)
    {
        try {
            $this->httpRequest(
                self::DIRECTORY_API . '/customer/' . urlencode($customerId)
                . '/orgunits/' . urlencode($orgUnitPath),
                'DELETE'
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
