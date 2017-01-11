<?php

namespace Att\M2X\MQTT;

use Att\M2X\MQTT\MQTTClient;
use Att\M2X\MQTT\Device;

class Command extends Resource {

/**
 * REST path of the resource
 *
 * @var string
 */
  public static $path = ':parent_path/commands';

/**
 * The parent resource that this stream belongs to
 *
 * @var Resource
 */
  public $parent = null;

/**
 * The Device resource properties
 *
 * @var array
 */
  protected static $properties = array(
    'name', 'data', 'sent_at', 'status'
  );

/**
 * Create object from API data
 *
 * @param MQTTClient $client
 * @param stdClass $data
 * @param Resource $parent
 */
  public function __construct(MQTTClient $client, $data = array(), Resource $parent = null) {
    if (isset($parent)) {
      $this->parent = $parent;
    }
    parent::__construct($client, $data);
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
 * The resource id for the REST URL
 *
 * @return string
 */
  public function id() {
    return $this->id;
  }

/**
 * Refresh the Command Info
 *
 */
  public function refresh() {
    $response = $this->client->get($this->path());
    $this->setData($response->json());
  }

/**
 * Mark a Command as rejected
 *
 * @link https://m2x.att.com/developer/documentation/v2/commands#Device-Marks-a-Command-as-Rejected
 *
 * @param array $data
 * @return MQTTResponse
 */
  public function reject($data = null) {
    return $this->client->post($this->path() . '/reject', $data);
  }

/**
 * Mark a Command as processed
 *
 * @link https://m2x.att.com/developer/documentation/v2/commands#Device-Marks-a-Command-as-Processed
 *
 * @param array $data
 * @return MQTTResponse
 */
  public function process($data = null) {
    return $this->client->post($this->path() . '/process', $data);
  }

/**
 * View Command Details
 *
 * @link https://m2x.att.com/developer/documentation/v2/commands#View-Command-Details
 *
 * @param array $data
 * @return MQTTResponse
 */
  public function details($data = null) {
    return $this->client->get('/commands' . '/' . $this->id , $data);
  }

/**
 * View Device Command Details
 *
 * @link https://m2x.att.com/developer/documentation/v2/commands#Device-s-View-of-Command-Details
 *
 * @param array $data
 * @return MQTTResponse
 */
  public function viewDeviceCommandDetails($data = null) {
    return $this->client->get($this->path() , $data);
  }
}
