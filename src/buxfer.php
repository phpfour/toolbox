<?php

require_once './vendor/autoload.php';

$response = \Zttp\Zttp::get('https://www.buxfer.com/api/login?userid=phpfour@gmail.com&password=123456')->json();
$token = $response['response']['token'];

