<?php
require_once '../../config/config.php';

class Authorization
{
    public $smartlockId;
    public $userName;
    public $accountUserId;
    public $invitationData;

    public function __construct($data)
    {
        $this->smartlockId     = $data['smartlockId'];
        $this->userName        = $data['userName'];
        $this->accountUserId   = $data['accountUserId'];
        $this->prepareInvitationData();
    }

    private function prepareInvitationData()
    {
        $this->invitationData = [
            'accountUserId'       => (int)$this->accountUserId,
            'name'                => $this->userName,
            'allowedFromDate'     => date('c'),
            'allowedUntilDate'    => date('c', strtotime('+1 day')),
            'allowedWeekDays'     => 127,
            'allowedFromTime'     => 0,
            'allowedUntilTime'    => 0,
            'remoteAllowed'       => true,
            'smartActionsEnabled' => true,
            'type'                => 0
        ];
    }
}
