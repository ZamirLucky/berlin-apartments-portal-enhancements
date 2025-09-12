<?php
require_once '../../config/config.php';  
require_once '../models/UserModel.php';  

class UserController
{
    private $apiUrlUser;
    private $token;

    public function __construct()
    {
        $this->apiUrlUser = API_URL_USER;
        $this->token      = API_TOKEN;
    }

    /**
     * Fetch user data as an array of ModelUser objects.
     */
    public function fetchUserData()
    {
        // Initialize cURL
        $ch = curl_init($this->apiUrlUser);
        if (!$ch) {
            throw new \Exception("Unable to init cURL for {$this->apiUrlUser}");
        }

        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Accept: application/json'
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // curl_setopt($ch, CURLOPT_TIMEOUT, 30); // optional

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("cURL error: $error");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("API returned HTTP status code $httpCode");
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Error decoding JSON: " . json_last_error_msg());
        }

        // Map each item to a ModelUser
        return array_map(function ($item) {
            return new ModelUser($item);
        }, $data);
    }

    /**
     * Fetch user data and return as JSON string (if needed).
     */
    public function fetchUserDataAsJson()
    {
        $userObjects = $this->fetchUserData();
        return json_encode($userObjects);
    }
}
