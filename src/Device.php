<?php

namespace Att\M2X\MQTT;

use Att\M2X\MQTT\Command;

class Device extends Resource {

/**
 * REST path of the resource
 *
 * @var string
 */
  public static $path = '/devices';

/**
 * The Device resource properties
 *
 * @var array
 */
  protected static $properties = array(
    'name', 'description', 'visibility', 'tags'
  );

/**
 * The resource id for the REST URL
 *
 * @return string
 */
  public function id() {
    return $this->id;
  }

/**
 * Update the current location of the specified device.
 *
 * @link https://m2x.att.com/developer/documentation/v2/device#Update-Device-Location
 *
 * @param array $data
 * @return Device
 */
  public function updateLocation($data) {
    $response = $this->client->put(self::$path . '/' . $this->id . '/location', $data);
    return $this;
  }

/**
 * Update the current location of the specified device.
 *
 * @link https://m2x.att.com/developer/documentation/v2/device#Post-Device-Update--Single-Values-to-Multiple-Streams-
 *
 * @param array $data
 * @return MQTTResponse
 */
  public function postSingleValueToMultipleStreams($data) {
    return $this->client->post(self::$path . '/' . $this->id . '/update', $data);
  }

/**
 * Get details of a specific data Stream associated with the device
 *
 * @link https://m2x.att.com/developer/documentation/v2/device#View-Data-Stream
 *
 * @param string $name
 * @return Stream
 */
  public function stream($name) {
    return new Stream($this->client, $this, array('name' => $name));
  }

/**
 * Update a data stream associated with the Device, if a
 * stream with this name does not exist it gets created.
 *
 * @link https://m2x.att.com/developer/documentation/v2/device#Create-Update-Data-Stream
 *
 * @param string $name
 * @param array $data
 * @return Stream
 */
  public function updateStream($name, $data = array()) {
    return Stream::createStream($this->client, $this, $name, $data);
  }

/**
 * Post values to multiple streams for this device.
 *
 * The `values` parameter is an array with the following format:
 *
 * array(
 *   'stream_a' => array(
 *     array('timestamp' => <Time in ISO8601>, 'value' => x),
 *     array('timestamp' => <Time in ISO8601>, 'value' => y)
 *   ),
 *   'stream_b' => array(
 *     array('timestamp' => <Time in ISO8601>, 'value' => t),
 *     array('timestamp' => <Time in ISO8601>, 'value' => g)
 *   )
 * )
 *
 * @link https://m2x.att.com/developer/documentation/v2/device#Post-Device-Updates--Multiple-Values-to-Multiple-Streams
 *
 * @param array $values
 * @return void
 */
  public function postUpdates($values) {
    $data = array('values' => $values);
    $response = $this->client->post($this->path() . '/updates', $data);
  }

/**
 * Retrieve a list of commamds for this devie.
 *
 * @link https://m2x.att.com/developer/documentation/v2/commands#Device-s-List-of-Received-Commands
 *
 * @param array $params
 * @return CommandsCollection
 */
  public function commands($params = array()) {
    return new CommandCollection($this->client, $params, $this);
  }

/**
 * Wait for a new command, this method will block until a packet is received.
 *
 * @link https://m2x.att.com/developer/documentation/v2/mqtt#Commands-API
 *
 * @return Command
 */
  public function receiveCommand() {
    $packet = $this->client->receivePacket($this->commandsTopic);
    $data = json_decode($packet->payload(), true);
    return new Command($this->client, $this, $data);
  }
}
