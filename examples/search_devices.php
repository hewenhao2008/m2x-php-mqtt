<?php

include '../vendor/autoload.php';

use Att\M2X\MQTT\MQTTClient;
use Att\M2X\MQTT\Error\M2XException;

$api_key = getenv("API_KEY");

try {
    $m2x = new MQTTClient($api_key);
    $m2x->connect();
    $data = array("status" => "enabled", "limit" => "1");
    $response = $m2x->searchDevices($data);
    echo  $response->raw();
    $m2x->disconnect();
}
catch (M2XException $ex) {
    echo 'Error: ' . $ex->getMessage();
    echo $ex->response->raw;
}
