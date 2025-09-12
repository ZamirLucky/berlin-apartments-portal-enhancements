<?php

require_once '../../config/config.php';
require_once '../models/SmartlockDevice.php';

class SmartlockDeviceController
{
    private $apiUrl;
    private $token;

    public function __construct()
    {
        $this->apiUrl = API_URL_NUKI_DEVICES;  // Ensure this is correct
        $this->token = API_TOKEN;  // Ensure this is correct
    }

    public function fetchSmartlockDevice()
    {
        // Using cURL instead of file_get_contents for better error handling
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->token,
            "Accept: application/json",
        ]);
        
        // Execute the cURL request and capture the response
        $response = curl_exec($ch);
        
        // Check for cURL errors
        if (curl_errno($ch)) {
            throw new Exception("cURL Error: " . curl_error($ch));
        }

        // Check for HTTP response code errors
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: Received HTTP code " . $httpCode . " from API");
        }

        curl_close($ch);

        // Decode the JSON response
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error decoding JSON: " . json_last_error_msg());
        }

        // Return devices data
        return array_map(function ($item) {
            return new SmartlockDevice(
                $item['id'] ?? null,
                $item['smartlockId'] ?? "Server Error",
                $item['accountId'] ?? "Server Error",
                $item['name'] ?? "Server Error"
            );
        }, $data);
    }
}
