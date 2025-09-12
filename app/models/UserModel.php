<?php
// ../models/UserModel.php

require_once '../../config/config.php';

class ModelUser implements JsonSerializable
{
    private $userId;
    private $accountId;
    private $email;
    private $userName;
    private $name;
    private $creationDate;

    public function __construct(array $data)
    {
        // Adjust these keys if your API fields differ
        $this->userId       = $data['accountUserId'] ?? "Server Error";
        $this->accountId    = $data['accountId']     ?? "Server Error";
        $this->email        = $data['email']         ?? "Server Error";
        $this->userName     = $data['userName']      ?? "Server Error";
        $this->name         = $data['name']          ?? "Server Error";
        $this->creationDate = $data['creationDate']  ?? null;
    }

    public function jsonSerialize(): array
    {
        return [
            'userId'       => $this->userId,
            'accountId'    => $this->accountId,
            'email'        => $this->email,
            'userName'     => $this->userName,
            'name'         => $this->name,
            'creationDate' => $this->creationDate,
        ];
    }

    // Getters if needed
    public function getUserId()       { return $this->userId; }
    public function getAccountId()    { return $this->accountId; }
    public function getEmail()        { return $this->email; }
    public function getUserName()     { return $this->userName; }
    public function getName()         { return $this->name; }
    public function getCreationDate() { return $this->creationDate; }
}
