<?php

class SmartlockDevice
{
    private $id;
    private $smartlockId;
    private $accountId;
    private $name;

    public function __construct($id, $smartlockId, $accountId, $name)
    {
        $this->id = $id;
        $this->smartlockId = $smartlockId;
        $this->accountId = $accountId;
        $this->name = $name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSmartlockId()
    {
        return $this->smartlockId;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function getName()
    {
        return $this->name;
    }
}
