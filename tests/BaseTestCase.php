<?php

use Att\M2X\MQTT\MQTTClient;
use Att\M2X\MQTT\Net\Socket;
use Att\M2X\MQTT\Test\FileStreamSocket;

class MockMQTTClient extends MQTTClient {

  public $socket = null;
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
 * Creates a new FileSocket and sets it up to use a test packet
 * from the test_packets directory.
 *
 * @param string $packetName
 * @return FileStreamSocket
 */
  protected function createTestSocket($packetName) {
    $socket = new FileStreamSocket();
    $socket->connect(__DIR__ . sprintf('/test_packets/%s.hex', $packetName));
    return $socket;
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
