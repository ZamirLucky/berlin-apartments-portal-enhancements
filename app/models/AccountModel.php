<?php
require_once '../../config/config.php';

class AccountModel
{
    private $apiToken;
    private $apiUrlSmartLockAuths = "https://api.nuki.io/smartlock/auth";
    private $apiUrlSmartLocks = "https://api.nuki.io/smartlock";

    public function __construct()
    {
        $this->apiToken = API_TOKEN;
    }

    private function makeApiRequest($url)
    {
        $headers = [
            "Authorization: Bearer " . $this->apiToken,
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("API Error (HTTP $httpCode) - $url");
            return ["error" => "API Error (HTTP $httpCode)"];
        }

        return json_decode($response, true) ?: ["error" => "Invalid or empty response"];
    }

    public function deleteSmartLockAuth($smartlockId)
    {
        $url = "https://api.nuki.io/smartlock/auth";

        $headers = [
            "Authorization: Bearer " . $this->apiToken,
            "Content-Type: application/json"
        ];

        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '[
            "' . $smartlockId . '"
        ]');

        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseError = curl_error($ch);  // Capture any cURL error
        curl_close($ch);

        // Log additional information
        error_log("DELETE Request URL: $url");
        error_log("HTTP Code: $httpCode");
        error_log("cURL Error: $responseError");
        error_log("Response: $response");

        // Handle the response
        if ($httpCode === 204) {
            return ["success" => "Authorization deleted successfully"];
        } else {
            // Log full response details in case of error
            return [
                "error" => "Failed to delete authorization (HTTP $httpCode). Response: $responseError, Details: $response",
            ];
        }
    }

    public function getSmartLockAuths()
    {
        return $this->makeApiRequest($this->apiUrlSmartLockAuths);
    }

    public function getSmartLockStatus()
    {
        return $this->makeApiRequest($this->apiUrlSmartLocks);
    }
}