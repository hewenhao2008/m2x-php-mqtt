<?php

namespace Att\M2X\MQTT;

use Att\M2X\MQTT\Command;

/**
 * Wrapper for {@link https://m2x.att.com/developer/documentation/v2/device M2X Device} API
 */
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
 * @return string Device ID
 */
  public function id() {
    return $this->id;
  }

/**
 * Method for{@link https://m2x.att.com/developer/documentation/v2/device#Update-Device-Location Update Device Location} endpoint.
 *
 * @param array $data Query parameters passed as keyword arguments. View M2X API Docs for listing of available parameters.
 * @return Device The API response, see M2X API docs for details
 */
  public function updateLocation($data) {
    $response = $this->client->put(self::$path . '/' . $this->id . '/location', $data);
    return $this;
  }

/**
 * Method for{@link https://m2x.att.com/developer/documentation/v2/device#Post-Device-Update--Single-Values-to-Multiple-Streams- Post Device Update (Single Value to Multiple Streams)} endpoint.
 *
 * @param array $data Query parameters passed as keyword arguments. View M2X API Docs for listing of available parameters.
 * @return MQTTResponse The API response, see M2X API docs for details
 */
  public function postSingleValueToMultipleStreams($data) {
    return $this->client->post(self::$path . '/' . $this->id . '/update', $data);
  }

/**
 * Method for {@link https://m2x.att.com/developer/documentation/v2/device#View-Data-Stream View Data Stream} endpoint.
 *
 * @param string $name The name of the Stream being retrieved
 * @return Stream The matching Stream
 */
  public function stream($name) {
    return new Stream($this->client, $this, array('name' => $name));
  }

/**
 * Method for {@link https://m2x.att.com/developer/documentation/v2/device#Create-Update-Data-Stream Create/Update data stream} endpoint.
 *
 * @param string $name Name of the stream to be updated
 * @param array $data Query parameters passed as keyword arguments. View M2X API Docs for listing of available parameters.
 * @return Stream The Stream being updated
 */
  public function updateStream($name, $data = array()) {
    return Stream::createStream($this->client, $this, $name, $data);
  }

/**
 * Method for {@link https://m2x.att.com/developer/documentation/v2/device#Post-Device-Updates--Multiple-Values-to-Multiple-Streams- Post Device Updates (Multiple Values to Multiple Streams)} endpoint.
 *
 * This method allows posting multiple values to multiple streams
 * belonging to a device and optionally, the device location.
 *
 * All the streams should be created before posting values using this method.
 *
 * @param array $values The values being posted, formatted according to the API docs
 * @return MQTTResponse The API response, see M2X API docs for details
 */
  public function postUpdates($values) {
    $data = array('values' => $values);
    $response = $this->client->post($this->path() . '/updates', $data);
  }

/**
 * Method for {@link https://m2x.att.com/developer/documentation/v2/commands#Device-s-List-of-Received-Commands List of Recieved Commands} endpoint.
 *
 * @param array $params Query parameters passed as keyword arguments. View M2X API Docs for listing of available parameters.
 * @return CommandCollection The API response, see M2X API docs for details
 */
  public function commands($params = array()) {
    return new CommandCollection($this->client, $params, $this);
  }

/**
 * Method for {@link https://m2x.att.com/developer/documentation/v2/mqtt#Commands-API Commands API} endpoint.
 *
 * @return Command The received command
 */
  public function receiveCommand() {
    $packet = $this->client->receivePacket($this->commandsTopic);
    $data = json_decode($packet->payload(), true);
    return new Command($this->client, $this, $data);
  }
}
