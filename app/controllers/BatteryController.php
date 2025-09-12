<?php

require_once '../../config/config.php'; // Include configuration file

class BatteryController {
    // Method to get the Smartlock Data from the Nuki API
    public function getSmartlockData() {
        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, API_URL_NUKI_DEVICES); // Set the URL for the API request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Set to return the response as a string
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . API_TOKEN, // Set authorization header with the API token
            'Content-Type: application/json' // Set content type to JSON
        ]);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Handle any cURL errors
        if (curl_errno($ch)) {
            curl_close($ch);
            return ['error' => 'Error during request: ' . curl_error($ch)]; // Return an error if one occurs
        }

        // Get HTTP response code and check for success
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            curl_close($ch);
            return ['error' => 'Error during request: HTTP ' . $httpCode]; // Return an error if the response code is not 200
        }

        // Close the cURL session to free resources
        curl_close($ch);

        // Decode the JSON response
        $data = json_decode($response, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Failed to decode JSON response: ' . json_last_error_msg()]; // Handle JSON decoding errors
        }

        // Return the decoded data
        return $data;
    }

    // Additional method to sort the smartlocks by their name alphabetically
    public function getSortedSmartlockData() {
        $smartlocks = $this->getSmartlockData();

        // Check if there is an error in the data
        if (isset($smartlocks['error'])) {
            return $smartlocks; // Return error if fetching data failed
        }

        // Sort smartlocks by name in alphabetical order
        usort($smartlocks, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $smartlocks;
    }
}

// Example usage:
// Instantiate the controller and fetch sorted data
$batteryController = new BatteryController();
$smartlocks = $batteryController->getSortedSmartlockData();
?>
