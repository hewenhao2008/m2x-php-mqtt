<?php

include '../vendor/autoload.php';

use Att\M2X\MQTT\MQTTClient;

$apiKey = getenv("API_KEY");

$hostname = 'api-m2x.att.com';

function get_command_details($command) {
  echo sprintf("CommandId = %s , CommandName = %s and ", $command->id, $command->name);
  $response = $command->details();
  echo "Command Details :";
  echo $response->raw()."\n\r";
}

try {
  $client = new MQTTClient($apiKey, array(
    'clientId' => 'PHP Test Client',
    'host' => gethostbyname($hostname)
  ));

  $client->connect();
  echo "Connected to the broker\n";

  $commands = $client->commands(array('limit' => 4));

  foreach ($commands as $command) {
    get_command_details($command);
  }

  $client->disconnect();
} catch (Exception $ex) {
  echo sprintf('Exception Error: %s', $ex->getMessage());
  throw $ex;
}
