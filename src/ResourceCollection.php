<?php

namespace Att\M2X\MQTT;

abstract class ResourceCollection implements \Iterator, \Countable {

/**
 * Holds the instances of the resources
 *
 * @var array
 */
  protected $resources = array();

/**
 * Holds the client instance
 *
 * @var M2X
 */
  protected $client = null;

/**
 * Parameters used in the query
 *
 * @var array
 */
  protected $params = array();

/**
 * Total amount of resources
 *
 * @var integer
 */
  protected $total = null;

/**
 * Total amount of pages
 *
 * @var integer
 */
  protected $pages = null;

/**
 * The current page in the result set
 *
 * @var integer
 */
  protected $currentPage = 1;

/**
 * Number of resources to fetch per page
 *
 * @var integer
 */
  protected $limit = null;

/**
 * Current position in the iterator
 *
 * @var integer
 */
  protected $position = 0;

/**
 * Boolean flag to define if the resource collection
 * is paginated or not.
 *
 * @var boolean
 */
  protected $paginate = true;

/**
 * Resource collection constructor
 *
 * @param M2X $client
 */
  public function __construct(MQTTClient $client, $params = array()) {
    $this->client = $client;
    $this->params = $params;

    $this->fetch();
  }

  public function fetch($page = 1) {
    $params = $this->params;
    if ($this->paginate) {
      $params = array_merge($params, array('page' => $page));
    }

    $response = $this->client->get($this->path(), $params);
    $data = $response->json();

    if ($this->paginate) {
      $this->total = $data['total'];
      $this->pages = $data['pages'];
      $this->limit = $data['limit'];
      $this->currentPage = $data['current_page'];
      foreach ($data[static::$name] as $i => $deviceData) {
        $position = $i + ($this->currentPage - 1) * $this->limit;
        $this->setResource($position, $deviceData);
      }
    } else {
      $data = current($data);
      $this->total = count($data);
      foreach ($data as $i => $deviceData) {
        $this->setResource($i, $deviceData);
      }
    }
  }

/**
 * Initialize and add a resource to the collection
 *
 * @param integer $i
 * @param array $data
 */
  protected function setResource($i, $data) {
    $this->resources[$i] = new static::$resourceClass($this->client, $data);
  }

/**
 * Return the API path for the query
 *
 * @return void
 */
  protected function path() {
    $class = static::$resourceClass;
    return $class::$path;
  }

/**
 * Number of resources in the dataset
 *
 * @return integer
 */
  public function count() {
    return $this->total;
  }

/**
 * this method takes the pointer back to the beginning
 * of the dataset to restart the iteration
 *
 * @return void
 */
  public function rewind() {
    $this->position = 0;
  }

 /**
  * This method returns the resource at the current
  * position in the dataset.
  *
  * @return void
  */
  public function current() {
    $this->preloadPage();
    return $this->resources[$this->position];
  }

 /**
  * Returns the current value of the pointer
  *
  * @return integer
  */
  public function key() {
    return $this->position;
  }

 /**
  * Moves the pointer to the next resource in the dataset
  *
  * @return void
  */
  public function next() {
    ++$this->position;
  }

/**
 * Returns the current page number
 *
 * @return integer
 */
  public function page() {
    return $this->currentPage;
  }

 /**
  * Returns a boolean indicating if there is a resource
  * at the current position in the dataset
  *
  * @return boolean
  */
  public function valid() {
    if (isset($this->resources[$this->position])) {
      return true;
    }
    $this->preloadPage();
    return isset($this->resources[$this->position]);
  }

  protected function preloadPage() {
    if ($this->paginate && $this->position < $this->total && !isset($this->resources[$this->position])) {
      $this->fetch($this->currentPage + 1);
    }
  }
}
