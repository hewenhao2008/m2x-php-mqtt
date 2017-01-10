<?php

include '../vendor/autoload.php';

use Att\M2X\MQTT\MQTTClient;

/**
* This example demonstrates a basic command-driven application.
*
* It has a method called process_commands that executes and the given command.
* Each command is acknowledged by either the #process or #reject method.
*
* This example application supports three basic commands:
*   SAY    - print the message given in the command data field "message".
*   REPORT - respond with a report containing the public IP and process ID.
*
* Upon startup, it queries the M2X API to check for any outstanding
* unacknowledged commands for the current device and processes them.
* After that, it enters a loop of processing each new command as it arrives
* via command delivery notifications. A more robust application would also
* periodically query the M2X API to check for outstanding commands again,
* as it is possible to miss delivery notifications in a network partition.
*
* Usage:
* ------
*
* Run the following command in your shell to run this example:
*
*   API_KEY=<device-api-key> DEVICE=<device-id> php examples/process_commands.php
*
* Example output:
*
*   DEVICE=dbe0dac3ec3154fbbe3b2a46a4115 php test.php
*   Connected to the broker
*   Processing command 201609c265ea3e4961e332de38bb76 (name=REPORT)
*   REPORT: public_ip: 192.168.1.5, pid: 41340
*   Processing command 201609b1afa1bc44ff76b467766c93 (name=SAY)
*   SAY: Hello World!
*
*/

$apiKey = getenv("API_KEY");
$deviceId  = getenv("DEVICE");

$hostname = 'api-m2x.att.com';

class Constants {
  public static $ALLOWED_COMMANDS = array('SAY', 'REPORT');
}

function process_command($command) {
  echo sprintf("Processing command %s (name=%s)\n\r", $command->id, $command->name);
  $response = $command->viewDeviceCommandDetails();
  echo "Command Details :\r\n";
  echo $response->raw();

  $name = strtoupper($command->name);

  if ($name == 'SAY') {
    process_say_command($command);
  } elseif ($name == 'REPORT') {
    process_report_command($command);
  } else {
    $reason = sprintf('unknown command name; allowed names are: %s', join(Constants::$ALLOWED_COMMANDS));
    $command->reject(compact('reason'));
  }
}

function process_say_command($command) {
  if (!empty($command->data['message'])) {
    echo sprintf("SAY: %s\n\r", $command->data['message']);
    $command->process();
  } else {
    $reason = 'The "message" param is required in data';
    $command->reject(compact('reason'));
  }
}

function process_report_command($command) {
  $public_ip = getHostByName(php_uname('n'));
  $pid = (string) getmypid();

  echo sprintf("REPORT: public_ip: %s, pid: %s\n\r", $public_ip, $pid);
  $command->process(compact('public_ip', 'pid'));
}

try {
  $client = new MQTTClient($apiKey, array(
    'clientId' => 'PHP Test Client',
    'host' => gethostbyname($hostname)
  ));

  $client->connect();
  echo "Connected to the broker\n\r";

  $device = $client->device($deviceId);

  //Check for unacknowledged commands
  $commands = $device->commands(array('status' => 'pending', 'limit' => 100));
  foreach ($commands as $command) {
    $command->refresh();
    process_command($command);
  }

  //Listen for incoming commands
  while($command = $device->receiveCommand()) {
    process_command($command);
  }

  $client->disconnect();
} catch (Exception $ex) {
  echo sprintf('Exception Error: %s', $ex->getMessage());
  throw $ex;
}
