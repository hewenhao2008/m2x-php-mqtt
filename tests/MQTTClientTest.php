<?php

use Att\M2X\MQTT\MQTTClient;
use Att\M2X\MQTT\Packet\Packet;

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
 * testPut method
 *
 * @return void
 */
  public function testPut() {
  $client = $this->getMockClient('0.0.0.0', 'foobar', array(), array('nextRequestId', 'publish'));
    $client->socket = $this->createTestSocket('api_update_location_success');

    $client->expects($this->once())->method('nextRequestId')
           ->willReturn('foobar');

    $expectedPayload = array(
      'id' => 'foobar',
      'method' => 'PUT',
      'resource' => '/v2/devices/5b21ef4cc18995597005da602a594ef5/location',
      'body' => array(
        'name' => 'Storage Room',
        'latitude' => '-37.9788423562422',
        'longitude' => '-57.5478776916862'
      )
    );

    $client->expects($this->once())->method('publish')
           ->with($this->equalTo('m2x/foobar/requests'), $this->equalTo(json_encode($expectedPayload)));

    $device = new Att\M2X\Device($client, array('id' => '5b21ef4cc18995597005da602a594ef5'));
    $data = array(
      'name' => 'Storage Room',
      'latitude' => '-37.9788423562422',
      'longitude' => '-57.5478776916862'
    );
    $device->updateLocation($data);
  }

/**
 * testDelete method
 *
 * @return void
 */
  public function testDelete() {
  $client = $this->getMockClient('0.0.0.0', 'foobar', array(), array('nextRequestId', 'publish'));
    $client->socket = $this->createTestSocket('api_device_delete_success');

    $client->expects($this->once())->method('nextRequestId')
           ->willReturn('123');

    $expectedPayload = array(
      'id' => '123',
      'method' => 'DELETE',
      'resource' => '/v2/devices/5b21ef4cc18995597005da602a594ef5'
    );

    $client->expects($this->once())->method('publish')
           ->with($this->equalTo('m2x/foobar/requests'), $this->equalTo(json_encode($expectedPayload)));

    $device = new Att\M2X\Device($client, array('id' => '5b21ef4cc18995597005da602a594ef5'));
    $device->delete();
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

/**
 * testPublish method
 *
 * @return void
 */
  public function testPublish() {
    $client = new MockMQTTClient('0.0.0.0', 'bar');
    $client->socket = $this->getMockBuilder('Socket')
                           ->setMethods(array('write'))
                           ->getMock();

    $expected = pack('C*', 
      0x31, // Static Header (RETAIN flag set)
      0x08, // Remaining Length
      0x00, // MSB
      0x03, // LSB
      0x61, // a
      0x2F, // /
      0x62, // b
      0x66, // f
      0x6F, // o
      0x6F  // o
    );

    $client->socket->expects($this->once())->method('write')->with($this->equalTo($expected));

    $client->publish('a/b', 'foo', Packet::RETAIN);
  }
}