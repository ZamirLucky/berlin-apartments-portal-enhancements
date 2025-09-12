<?php
// app/models/SmartlockModel.php

class Smartlock
{
    private $id;
    private $smartlockId;
    private $deviceName;
    private $userName;
    private $creationDate;
    private $allowedFromDate;
    private $allowedUntilDate;
    private $authId;
    private $accountUserId;

    // NEW property for user email
    private $accountEmail;

    public function __construct(array $data)
    {
        $this->id               = $data['id']               ?? null;
        $this->smartlockId      = $data['smartlockId']      ?? null;
        $this->deviceName       = $data['deviceName']       ?? null;
        $this->userName         = $data['name']             ?? null;
        $this->creationDate     = $data['creationDate']     ?? null;
        $this->allowedFromDate  = $data['allowedFromDate']  ?? null;
        $this->allowedUntilDate = $data['allowedUntilDate'] ?? null;
        $this->authId           = $data['authId']           ?? null;
        $this->accountUserId    = $data['accountUserId']    ?? null;

        // NEW: set accountEmail
        $this->accountEmail     = $data['accountEmail']     ?? null;
    }

    // Existing getters
    public function getId()               { return $this->id; }
    public function getSmartlockId()      { return $this->smartlockId; }
    public function getDeviceName()       { return $this->deviceName; }
    public function getUserName()         { return $this->userName; }
    public function getCreationDate()     { return $this->creationDate; }
    public function getAllowedFromDate()  { return $this->allowedFromDate; }
    public function getAllowedUntilDate() { return $this->allowedUntilDate; }
    public function getAuthId()           { return $this->authId; }
    public function getAccountUserId()    { return $this->accountUserId; }

    // NEW getter for accountEmail
    public function getAccountEmail()     { return $this->accountEmail; }
}
