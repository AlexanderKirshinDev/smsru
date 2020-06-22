<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';


use Kirshin\Services\SmsClient;

$client = new SmsClient('YOUR_SMS_RU_API_KEY');
$client->sendSms('70000000000,70000000000','тестовое сообщение');
