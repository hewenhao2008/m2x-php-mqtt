<?php

use Att\M2X\MQTT\MQTTClient;

class MQTTClientTest extends BaseTestCase {

/**
 * testNextPacketId method
 *
 * @return void
 */
  public function testNextPacketId() {
    $client = new MQTTClient('127.0.0.1', 'foobar');

    $this->assertSame(1, $client->nextPacketId());
    $this->assertSame(2, $client->nextPacketId());
    $this->assertSame(3, $client->nextPacketId());
  }

/**
 * testConnectSocketException method
 *
 * @expectedException \Att\M2X\MQTT\Error\SocketException
 * @expectedExceptionMessage Connection refused
 *
 * @return void
 */
  public function testConnectSocketException() {
    $client = new MQTTClient('0.0.0.0', 'foobar');
    $client->connect();
  }

/**
 * testDevices method
 *
 * @return void
 */
  public function testDevices() {
    $client = new MockMQTTClient('0.0.0.0', 'foobar');
    $socket = $this->createTestSocket('api_list_devices');
    $client->socket = $socket;

    $result = $client->devices();
    $this->assertEquals(3, $result->count());
  }
}