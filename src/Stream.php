<?php

namespace Att\M2X\MQTT;

use Att\M2X\MQTT\MQTTClient;
use Att\M2X\MQTT\Device;

/**
 * Methods for interacting M2X Device Streams
 */
class Stream extends Resource {

/**
 * REST path of the resource
 *
 * @var string
 */
  public static $path = ':parent_path/streams';

/**
 * The parent resource that this stream belongs to
 *
 * @var Resource
 */
  public $parent = null;
/**
 * The Stream resource properties
 *
 * @var array
 */
  protected static $properties = array(
    'name', 'unit', 'type'
  );

/**
 * Disable the original POST factory
 *
 * @param MQTTClient $client
 * @param array $data
 * @return void
 */
  public static function create($client, $data = array()) {
    throw new \BadMethodCallException('Not implemented, use Stream::createStream() instead.');
  }

/**
 * Method for {@link https://m2x.att.com/developer/documentation/v2/device#Create-Update-Data-Stream Create/Update Data Stream} endpoint.
 *
 * @param MQTTClient $client Client API
 * @param Resource $parent Parent resource that this collection belongs to
 * @param string $name Stream name to be created
 * @param array $data Query parameters passed as keyword arguments. View M2X API Docs for listing of available parameters.
 * @return Stream The newly created stream
 */
  public static function createStream(MQTTClient $client, Resource $parent, $name, $data) {
    $path = str_replace(':parent_path', $parent->path(), static::$path) . '/' . $name;
    $response = $client->put($path, $data);

    return new self($client, $parent, $response->json());
  }

/**
 * Create object from API data
 *
 * @param MQTTClient $client
 * @param Device $device
 * @param stdClass $data
 */
  public function __construct(MQTTClient $client, Resource $parent, $data) {
    $this->parent = $parent;
    parent::__construct($client, $data);
  }

/**
 * The stream id for the REST URL
 *
 * @return string Stream ID
 */
  public function id() {
    return $this->name;
  }

/**
 * Returns the path to the resource
 *
 * @return string Stream path
 */
  public function path() {
    return str_replace(':parent_path', $this->parent->path(), self::$path) . '/' . $this->id();
  }

/**
 * Method for {@link https://m2x.att.com/developer/documentation/v2/device#Update-Data-Stream-Value Update Data Stream Value} endpoint.
 * The timestamp is optional. If ommited, the current server time will be used.
 *
 * @param string $value Value to be updated
 * @param string $timestamp Current Timestamp
 * @return void
 */
  public function updateValue($value, $timestamp = null) {
    $data = array('value' => $value);

    if ($timestamp) {
      $data['timestamp'] = $timestamp;
    }

    $this->client->put($this->path() . '/value', $data);
  }

/**
 * Method for {@link https://m2x.att.com/developer/documentation/v2/device#Post-Data-Stream-Values Post multiple values} endpoint.
 *
 * @param array $values Query parameters passed as keyword arguments. View M2X API Docs for listing of available parameters.
 * @return void
 */
  public function postValues($values) {
    $data = array('values' => $values);
    $response = $this->client->post($this->path() . '/values', $data);
  }
}
