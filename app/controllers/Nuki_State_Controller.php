<?php
// FILE: controllers/Nuki_State_Controller.php

require_once __DIR__ . '/../models/Nuki_State_Model.php';

class Nuki_StateController {
    private $model;

    public function __construct() {
        $this->model = new SmartLockModel();
    }

    /**
     * Fetch smartlocks from all API groups and log state changes.
     * Optionally clears the database before fetching.
     */
    public function fetchAndLogSmartlocks(bool $clearDB = false): void {
        if ($clearDB) {
            $this->model->clearDatabase();
        }

        foreach (API_GROUPS as $group => $token) {
            $this->fetchGroupSmartlocks($group, $token);
        }

        $this->model->autoCloseOngoingLogs();
    }

    /**
     * Internal function to fetch and store smartlock data for a group.
     */
    private function fetchGroupSmartlocks(string $group, string $token): void {
        $ch = curl_init(API_URL_NUKI_DEVICES);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Accept: application/json"
            ]
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true) ?: [];

        foreach ($data as $lock) {
            $id    = $lock['smartlockId'];
            $name  = $lock['name'];
            $state = $lock['serverState']; // 0 = Online, 1 = Offline

            $this->model->insertSmartlock($id, $name, $group);

            $last = $this->model->getLastStateLog($id);
            if (!$last || $last['state'] != $state) {
                if ($last) {
                    $this->model->updateStateLogEndTime($last['id']);
                }
                $this->model->insertStateLog($id, $state);
            }
        }
    }

    /**
     * Returns the full or filtered smartlock history.
     *
     * @param bool $offlineOnlyMoreThanOneDay
     * @return array
     */
    public function getSmartlockHistory(bool $offlineOnlyMoreThanOneDay = false): array {
        return $this->model->getSmartlockHistory($offlineOnlyMoreThanOneDay);
    }
}
