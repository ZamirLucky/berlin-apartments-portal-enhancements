<?php
// app/controllers/SmartlockController.php

require_once '../../config/config.php';
require_once '../models/SmartlockModel.php';

class SmartlockController
{
    private $authApiUrl;   // Endpoint for authorizations
    private $deviceApiUrl; // Endpoint for devices
    private $userApiUrl;   // Endpoint for user
    private $token;

    public function __construct()
    {
        $this->authApiUrl   = API_URL;               // e.g. https://api.nuki.io/smartlock/auth
        $this->deviceApiUrl = API_URL_NUKI_DEVICES;  // e.g. https://api.nuki.io/smartlock/
        $this->userApiUrl   = API_URL_USER;          // e.g. https://api.nuki.io/account/user
        $this->token        = API_TOKEN;
    }

    /**
     * Fetch the Smartlock data in JSON format (string).
     */
    public function fetchSmartlockDataAsJson()
    {
        try {
            $smartlocks = $this->fetchSmartlockAuthData();

            // Sort them alphabetically by device name
            usort($smartlocks, function ($a, $b) {
                return strcmp($a->getDeviceName(), $b->getDeviceName());
            });

            // Build the final data structure for JSON
            $formattedData = array_map(function ($s) {
                return [
                    'id'               => $s->getId(),
                    'smartlockId'      => $s->getSmartlockId(),
                    'deviceName'       => $s->getDeviceName(),
                    'userName'         => $s->getUserName(),
                    'creationDate'     => $s->getCreationDate() ? date('Y-m-d', strtotime($s->getCreationDate())) : null,
                    'allowedFromDate'  => $s->getAllowedFromDate() ? date('Y-m-d', strtotime($s->getAllowedFromDate())) : null,
                    'allowedUntilDate' => $s->getAllowedUntilDate() ? date('Y-m-d', strtotime($s->getAllowedUntilDate())) : null,
                    'accountUserId'    => $s->getAccountUserId(),
                    // Include the new field (email)
                    'accountEmail'     => $s->getAccountEmail()
                ];
            }, $smartlocks);

            return json_encode($formattedData);

        } catch (\Exception $e) {
            error_log("fetchSmartlockDataAsJson: Exception => " . $e->getMessage());
            return json_encode(['error' => 'Failed to load Smartlock data.']);
        }
    }

    /**
     * Fetch authorizations, fetch devices, fetch user data, and assemble the Smartlock objects.
     */
    private function fetchSmartlockAuthData()
    {
        try {
            // 1) Authorizations
            $authData = $this->fetchDataFromApi($this->authApiUrl);

            // 2) Devices
            $deviceData = $this->fetchDataFromApi($this->deviceApiUrl);

            // 3) Users
            // This endpoint may return an array or a single object. 
            // The official docs might say it returns an array with { "accountUserId", "email", etc. }
            $userData = $this->fetchDataFromApi($this->userApiUrl);

        } catch (\Exception $e) {
            error_log("fetchSmartlockAuthData: Exception => " . $e->getMessage());
            throw $e;
        }

        // Build a map of smartlockId => deviceName
        $deviceNameMap = [];
        if (is_array($deviceData)) {
            foreach ($deviceData as $dev) {
                if (isset($dev['smartlockId']) && isset($dev['name'])) {
                    $deviceNameMap[$dev['smartlockId']] = $dev['name'];
                }
            }
        }

        // Build a map of accountUserId => email
        // Here, according to your provided response, the field is "accountUserId", not "id".
        // Also we have "email" as the key we want.
        $userMap = [];
        if (is_array($userData)) {
            // If the userData is an array of user objects:
            foreach ($userData as $u) {
                if (isset($u['accountUserId']) && isset($u['email'])) {
                    $userMap[$u['accountUserId']] = $u['email'];
                }
            }
        } 
        // If userData is just a single object, you'd do something like:
        // else if (isset($userData['accountUserId']) && isset($userData['email'])) {
        //     $userMap[$userData['accountUserId']] = $userData['email'];
        // }

        // Build Smartlock objects from authData
        $smartlocks = [];
        if (is_array($authData)) {
            foreach ($authData as $item) {
                $smartlockId = $item['smartlockId'] ?? null;
                $authId      = $item['authId']      ?? null;

                if (!$authId) {
                    throw new \Exception("Missing Auth ID for Smartlock: " . ($smartlockId ?? 'N/A'));
                }

                // Resolve device name from deviceNameMap
                $deviceName = $deviceNameMap[$smartlockId] ?? 'Unknown Device';

                // Check if we have an accountUserId from this auth item
                $accountUserId = $item['accountUserId'] ?? null;
                $accountEmail  = null;
                if ($accountUserId && isset($userMap[$accountUserId])) {
                    $accountEmail = $userMap[$accountUserId];
                }

                $smartlockData = [
                    'id'               => $item['id']               ?? null,
                    'smartlockId'      => $smartlockId,
                    'deviceName'       => $deviceName,
                    'name'             => $item['name']             ?? 'Unknown User',
                    'creationDate'     => $item['creationDate']     ?? null,
                    'allowedFromDate'  => $item['allowedFromDate']  ?? null,
                    'allowedUntilDate' => $item['allowedUntilDate'] ?? null,
                    'authId'           => $authId,
                    'accountUserId'    => $accountUserId,
                    // The new email field
                    'accountEmail'     => $accountEmail
                ];

                $smartlocks[] = new Smartlock($smartlockData);
            }
        }

        return $smartlocks;
    }

    /**
     * Helper function to fetch data from API using cURL.
     */
    private function fetchDataFromApi($apiUrl)
    {
        $ch = curl_init($apiUrl);
        if ($ch === false) {
            throw new \Exception("Could not init cURL for $apiUrl");
        }

        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Accept: application/json'
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Error fetching $apiUrl: $error");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $length   = strlen($response);
        error_log("fetchDataFromApi: HTTP code $httpCode, length $length, url => $apiUrl");

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("fetchDataFromApi: Non-2xx code => $httpCode. Response => " . substr($response, 0, 300));
            curl_close($ch);
            throw new \Exception("HTTP $httpCode from $apiUrl");
        }

        curl_close($ch);

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonErr = json_last_error_msg();
            error_log("JSON parse error => $jsonErr");
            throw new \Exception("JSON parse error: $jsonErr");
        }

        return $data;
    }
}
