<?php

namespace Att\M2X\MQTT;

use Att\M2X\MQTT\Device;
use Att\M2X\MQTT\Command;

class CommandCollection extends ResourceCollection {

/**
 * Name of the collection. This is used for the envelope key in the API.
 *
 * @var string
 */
  public static $name = 'commands';

/**
 * The resource class used in the collection
 *
 * @var string
 */
  protected static $resourceClass = 'Att\M2X\MQTT\Command';

/**
 * Boolean flag to define if the resource collection
 * is paginated or not.
 *
 * @var boolean
 */
  protected $paginate = false;

/**
 * The parent resource that this collection belongs to
 *
 * @var Resource
 */
  public $parent = null;

/**
 * Command collection constructor
 *
 * @param MQTTClient $client
 * @param array $params
 * @param Resource $parent
 */
  public function __construct(MQTTClient $client, $params = array(), Resource $parent = null) {
    if (isset($parent)) {
      $this->parent = $parent;
    }
    parent::__construct($client, $params);
  }

/**
 * Return the API path for the query
 *
 * @return string path of the corresponding command
 */
  protected function path() {
    $class = static::$resourceClass;
    if (isset($this->parent)) {
      return str_replace(':parent_path', $this->parent->path(), $class::$path);
    } else {
      return str_replace(':parent_path', '' , $class::$path);
    }
  }

/**
 * Initialize and add a resource to the collection
 *
 * @param integer $i
 * @param array $data
 */
  protected function setResource($i, $data) {
    if (isset($this->parent)) {
      $this->resources[$i] = new static::$resourceClass($this->client, $data, $this->parent);
    } else {
      parent::setResource($i, $data);
    }
  }
}
