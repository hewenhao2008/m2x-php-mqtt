<?php

namespace Att\M2X\MQTT;

use Att\M2X\MQTT\MQTTClient;
use Att\M2X\MQTT\Device;

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
 * @param M2X $client
 * @param string $id
 * @return void
 */
  public static function create($client, $data = array()) {
    throw new \BadMethodCallException('Not implemented, use Stream::createStream() instead.');
  }

/**
 * Create or update a stream resource
 *
 * @param MQTTClient $client
 * @param Resource $parent
 * @param string $name
 * @param array $data
 * @return Stream
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
 * The resource id for the REST URL
 *
 * @return string
 */
  public function id() {
    return $this->name;
  }

/**
 * Returns the path to the resource
 *
 * @return string
 */
  public function path() {
    return str_replace(':parent_path', $this->parent->path(), self::$path) . '/' . $this->id();
  }

/**
 * Update the current value of the stream. The timestamp is optional.
 * If ommited, the current server time will be used.
 *
 * @link https://m2x.att.com/developer/documentation/v2/device#Update-Data-Stream-Value
 *
 * @param string $value
 * @param string $timestamp Time in ISO8601
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
 * Post multiple values to the stream
 *
 * The `values` parameter is an array with the following format:
 *
 * array(
 *   array('timestamp' => <Time in ISO8601>, 'value' => x),
 *   array('timestamp' => <Time in ISO8601>, 'value' => y)
 * )
 *
 * https://m2x.att.com/developer/documentation/v2/device#Post-Data-Stream-Values
 *
 * @param array $data
 * @return void
 */
  public function postValues($values) {
    $data = array('values' => $values);
    $response = $this->client->post($this->path() . '/values', $data);
  }
}
