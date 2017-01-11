<?php

include '../vendor/autoload.php';

use Att\M2X\MQTT\MQTTClient;

$apiKey = getenv("API_KEY");
$deviceId = getenv("DEVICE");

$hostname = 'api-m2x.att.com';

try {
  $m2x = new MQTTClient($apiKey, array(
    'clientId' => 'PHP Test Client',
    'host' => gethostbyname($hostname)
  ));

  $m2x->connect();
  echo "Connected to the broker\n";

  $device = $m2x->device($deviceId);

  $params = array(
    'timestamp' => '2016-10-06T07:13:47.870Z',
    'values' => array(
      'temperature' => '800',
      'humidity' => '200'
    )
  );

  $response = $device->postSingleValueToMultipleStreams($params);

  echo "Status code $response->statusCode\n";
  if ($response->statusCode == 202) {
    echo "Update Single Value to multiple streams is Successful.\n";
  } else {
    echo "Update Single Value to multiple streams is Failed. Please Try Again.\n";
  }
  $m2x->disconnect();
} catch (Exception $ex) {
  echo sprintf('Exception Error: %s', $ex->getMessage());
  throw $ex;
}
