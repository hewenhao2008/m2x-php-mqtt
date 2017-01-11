<?php

include '../vendor/autoload.php';

use Att\M2X\MQTT\MQTTClient;

$apiKey = getenv("API_KEY");
$deviceId  = getenv("DEVICE");

$hostname = 'api-m2x.att.com';

try {
  $m2x = new MQTTClient($apiKey, array(
    'clientId' => 'PHP Test Client',
    'host' => gethostbyname($hostname)
  ));

  $m2x->connect();
  echo "Connected to the broker\n";

  //Check for unacknowledged commands
  $response = $m2x->sendCommand(array(
    'name' => 'SAY',
    'targets' => array(
      'devices' => [$deviceId]
     )
 ));
  echo "Status code $response->statusCode\n\r";

  if ($response->statusCode == 202) {
    echo "Send Command is Successful.\n\r";
  } else {
    echo "Send Command Failed. Please Try Again.\n\r";
  }
  $m2x->disconnect();
} catch (Exception $ex) {
  echo sprintf('Exception Error: %s', $ex->getMessage());
  throw $ex;
}
