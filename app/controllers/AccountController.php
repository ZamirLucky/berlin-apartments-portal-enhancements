<?php
require_once '../models/AccountModel.php';

class AccountController
{
    private $accountModel;

    public function __construct()
    {
        $this->accountModel = new AccountModel();
    }

    public function getSmartLockAuthorizations()
    {
        return $this->accountModel->getSmartLockAuths();
    }

    public function getSmartLockAuthList($search = "", $onlyExpired = false)
    {
        $auths = $this->accountModel->getSmartLockAuths();
        $smartLocks = $this->accountModel->getSmartLockStatus();

        if (isset($auths['error']) || isset($smartLocks['error'])) {
            return [];
        }

        // Mapping Smart Lock states
        $smartLockStates = [];
        foreach ($smartLocks as $lock) {
            $smartLockStates[$lock['smartlockId']] = $lock['serverState'] ?? null;
        }

        $search = strtolower(trim($search));
        $oneMonthAgo = new DateTime('-1 month');

        // Filtering authorizations
        $filteredAuths = array_filter($auths, function ($auth) use ($search, $oneMonthAgo, $onlyExpired) {
            $allowedUntilDate = !empty($auth['allowedUntilDate']) ? new DateTime($auth['allowedUntilDate']) : null;
            $isExpired = $allowedUntilDate && $allowedUntilDate < $oneMonthAgo;

            if ($onlyExpired && !$isExpired) {
                return false;
            }

            return empty($search) ||
                (function_exists('str_contains')
                    ? str_contains(strtolower($auth['id'] ?? ''), $search) ||
                    str_contains(strtolower($auth['smartlockId'] ?? ''), $search) ||
                    str_contains(strtolower($auth['name'] ?? ''), $search) ||
                    str_contains(strtolower($this->formatDate($auth)), $search)
                    : strpos(strtolower($auth['id'] ?? ''), $search) !== false ||
                    strpos(strtolower($auth['smartlockId'] ?? ''), $search) !== false ||
                    strpos(strtolower($auth['name'] ?? ''), $search) !== false ||
                    strpos(strtolower($this->formatDate($auth)), $search) !== false
                );
        });

        return array_map(function ($auth) use ($smartLockStates) {
            return [
                'id' => $auth['id'] ?? 'N/A',
                'smartlockId' => $auth['smartlockId'] ?? 'N/A',
                'authId' => $auth['authId'] ?? 'N/A',
                'name' => $auth['name'] ?? 'No Name',
                'allowedUntilTime' => $this->formatDate($auth),
                'state' => $this->getStateText($smartLockStates[$auth['smartlockId']] ?? null)
            ];
        }, $filteredAuths);
    }

    public function deleteSmartLockAuthorization($smartlockId)
    {
        if (empty($smartlockId)) {
            return ["error" => "smartlockId is required."];
        }

        error_log("deleteSmartLockAuthorization executed with smartlockId={$smartlockId}");
        return $this->accountModel->deleteSmartLockAuth($smartlockId);
    }

    private function formatDate($auth)
    {
        if (empty($auth['allowedUntilDate'])) {
            return "Permanent Access";
        }

        try {
            $date = new DateTime($auth['allowedUntilDate']);
            return $date->format('d/m/Y H:i:s');
        } catch (Exception $e) {
            return "Invalid Date";
        }
    }

    private function getStateText($serverState)
    {
        switch ($serverState) {
            case 0:
                return "🟢 Online";
            case 4:
                return "🔴 Offline";
            default:
                return "⚪ Unknown";
        }
    }
}