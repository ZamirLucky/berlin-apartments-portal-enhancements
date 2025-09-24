<?php
require __DIR__.'/../app/controllers/Nuki_State_Controller.php';
require __DIR__.'/../config/config.php';

$ctl = new Nuki_StateController();
$_GET['token'] = CRON_TOKEN;   // pass token
$ctl->cronPoll();