<?php

use Att\M2X\MQTT\MQTTClient;
use Att\M2X\MQTT\Net\Socket;

class MockMQTTClient extends MQTTClient {

  public function setSocket($socket) {
    $this->socket = $socket;
  }
}

abstract class BaseTestCase extends PHPUnit_Framework_TestCase {

  protected $socket = null;

  protected function setUp() {
    $this->socket = $this->getMockBuilder('Att\M2X\MQTT\Net\Socket')->getMock();
  }

  protected function tearDown() {
    $this->socket = null;
  }

/**
 * Utility for calling protected methods
 *
 * @param Instance $obj
 * @param string $name
 * @param array $args
 * @return mixed
 */
  protected static function callMethod($obj, $name, array $args) {
    $class = new \ReflectionClass($obj);
    $method = $class->getMethod($name);
    $method->setAccessible(true);
    return $method->invokeArgs($obj, $args);
  }
}
