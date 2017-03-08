<?php

namespace Att\M2X\MQTT;

use Att\M2X\MQTT\MQTTClient;
use Att\M2X\MQTT\Device;

/**
 * Wrapper for {@link https://m2x.att.com/developer/documentation/v2/commands M2X Commands} API
 */
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
 * Returns the path to the command
 *
 * @return string path of the corresponding command
 */
  public function path() {
    return str_replace(':parent_path', $this->parent->path(), self::$path) . '/' . $this->id();
  }

/**
 * The resource id for the REST URL
 *
 * @return string Command ID
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
 * Method for {@link https://m2x.att.com/developer/documentation/v2/commands#Device-Marks-a-Command-as-Rejected Device Marks Command as rejected} endpoint.
 *
 * @param array $data Query parameters passed as keyword arguments. View M2X API Docs for listing of available parameters.
 * @return MQTTResponse The API response, see M2X API docs for details
 */
  public function reject($data = null) {
    return $this->client->post($this->path() . '/reject', $data);
  }

/**
 * Method for {@link https://m2x.att.com/developer/documentation/v2/commands#Device-Marks-a-Command-as-Processed Device marks Command as processed} endpoint.
 *
 * @param array $data Query parameters passed as keyword arguments. View M2X API Docs for listing of available parameters.
 * @return MQTTResponse The API response, see M2X API docs for details
 */
  public function process($data = null) {
    return $this->client->post($this->path() . '/process', $data);
  }

/**
 * Method for {@link https://m2x.att.com/developer/documentation/v2/commands#View-Command-Details View Command Details} endpoint.
 *
 * @param array $data Query parameters passed as keyword arguments. View M2X API Docs for listing of available parameters.
 * @return Command The retrieved Command
 */
  public function details($data = null) {
    return $this->client->get('/commands' . '/' . $this->id , $data);
  }

/**
 * Method for {@link https://m2x.att.com/developer/documentation/v2/commands#Device-s-View-of-Command-Details View Device Command Details} endpoint.
 *
 * @param array $data Query parameters passed as keyword arguments. View M2X API Docs for listing of available parameters.
 * @return Command The API response, see M2X API docs for details
 */
  public function viewDeviceCommandDetails($data = null) {
    return $this->client->get($this->path() , $data);
  }
}
