<?php

include '../vendor/autoload.php';

use Att\M2X\MQTT\MQTTClient;
use Att\M2X\MQTT\Error\M2XException;

$apiKey = getenv("API_KEY");
$deviceId  = getenv("DEVICE");

function loadAvg() {
  $pattern = '/(\d+\.\d+),? (\d+\.\d+),? (\d+\.\d+)$/';
  preg_match($pattern, shell_exec('uptime'), $matches);
  array_shift($matches);
  return $matches;
}

$m2x = new MQTTClient($apiKey);

# Get the device
$device = $m2x->device($deviceId);

# Create the streams if they don't exist yet
$device->updateStream('load_1m');
$device->updateStream('load_5m');
$device->updateStream('load_15m');

while (true) {
  list($load_1m, $load_5m, $load_15m) = loadAvg();
  $now = date('c');

  $values = array(
    'load_1m'  => array(array('value' => $load_1m,  'timestamp' => $now)),
    'load_5m'  => array(array('value' => $load_5m,  'timestamp' => $now)),
    'load_15m' => array(array('value' => $load_15m, 'timestamp' => $now))
  );

  try {
    $device->postUpdates($values);
  } catch (M2XException $ex) {
    echo 'Error: ' . $ex->getMessage();
    echo $ex->response->raw;
    break;
  }

  sleep(1);
}
