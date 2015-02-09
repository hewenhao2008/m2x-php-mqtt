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
 * testGet method
 *
 * @return void
 */
  public function testGet() {
    $client = $this->getMockClient('0.0.0.0', 'foobar', array(), array('nextRequestId', 'publish'));
    $client->socket = $this->createTestSocket('api_list_devices');

    $client->expects($this->once())->method('nextRequestId')
           ->willReturn('id-12345');

    $expectedPayload = array(
      'id' => 'id-12345',
      'method' => 'GET',
      'resource' => '/v2/devices'
    );

    $client->expects($this->once())->method('publish')
           ->with($this->equalTo('m2x/foobar/requests'), $this->equalTo(json_encode($expectedPayload)));

    $result = $client->devices();
    $this->assertEquals(3, $result->count());
  }

/**
 * testPost method
 *
 * @return void
 */
  public function testPost() {
    $client = $this->getMockClient('0.0.0.0', 'foobar', array(), array('nextRequestId', 'publish'));
    $client->socket = $this->createTestSocket('api_create_device_success');

    $client->expects($this->once())->method('nextRequestId')
           ->willReturn('554433');

    $expectedPayload = array(
      'id' => '554433',
      'method' => 'POST',
      'resource' => '/v2/devices',
      'body' => array(
        'name' => 'Foo Bar',
        'description' => 'Lorem Ipsum',
        'visibility' => 'private'
      )
    );

    $client->expects($this->once())->method('publish')
           ->with($this->equalTo('m2x/foobar/requests'), $this->equalTo(json_encode($expectedPayload)));

    $data = array(
      'name' => 'Foo Bar',
      'description' => 'Lorem Ipsum',
      'visibility' => 'private'
    );
    $device = $client->createDevice($data);
    $this->assertInstanceOf('Att\M2X\Device', $device);
    $this->assertEquals('Foo Bar', $device->name);

    $response = $client->lastResponse();
    $this->assertEquals(201, $response->statusCode);
  }

/**
 * testSocket method
 *
 * @return void
 */
  public function testSocket() {
    $client = new MockMQTTClient('0.0.0.0', 'bar');
    $this->assertNull($client->socket);
    $result = $client->socket();
    $this->assertInstanceOf('\Att\M2X\MQTT\Net\Socket', $result);
    $this->assertSame($result, $client->socket);
  }
}