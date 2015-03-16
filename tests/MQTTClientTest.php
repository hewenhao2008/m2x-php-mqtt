<?php

use Att\M2X\MQTT\MQTTClient;
use Att\M2X\MQTT\Packet\Packet;

class MQTTClientTest extends BaseTestCase {

/**
 * testConfiguration method
 *
 * @return void
 */
  public function testConfiguration() {
    $client = new MockMQTTClient('foo-bar');
    $this->assertEquals(gethostbyname('api-m2x.att.com'), $client->getProtected('host'));

    $client = new MockMQTTClient('api-key', array('host' => '127.0.0.1'));

    $this->assertEquals('127.0.0.1', $client->getProtected('host'));
    $this->assertEquals(1883, $client->getProtected('port'));
    $this->assertEquals('api-key', $client->getProtected('apiKey'));

    //Make sure a random client id gets generated
    $this->assertNotEmpty($client->getProtected('clientId'));

    //Make sure the client id is random
    $firstClientId = $client->getProtected('clientId');
    $client = new MockMQTTClient('api-key', array('host' => '127.0.0.1'));
    $secondClientId = $client->getProtected('clientId');
    $this->assertNotEquals($firstClientId, $secondClientId);

    //Test options
    $options = array('clientId' => 'foo-client', 'port' => 5555);
    $client = new MockMQTTClient('api-key', $options);
    $this->assertEquals(5555, $client->getProtected('port'));
    $this->assertEquals('foo-client', $client->getProtected('clientId'));
  }

/**
 * testNextPacketId method
 *
 * @return void
 */
  public function testNextPacketId() {
    $client = new MQTTClient('foobar', array('host' => '127.0.0.1'));

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
    $client = new MQTTClient('foobar', array('host' => '0.0.0.0'));
    $client->connect();
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
    $this->assertInstanceOf('Att\M2X\MQTT\Device', $device);
    $this->assertEquals('Foo Bar', $device->name);
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

    $device = new Att\M2X\MQTT\Device($client, array('id' => '5b21ef4cc18995597005da602a594ef5'));
    $data = array(
      'name' => 'Storage Room',
      'latitude' => '-37.9788423562422',
      'longitude' => '-57.5478776916862'
    );
    $device->updateLocation($data);
  }

/**
 * testSocket method
 *
 * @return void
 */
  public function testSocket() {
    $client = new MockMQTTClient('bar', array('host' => '0.0.0.0'));
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
    $client = new MockMQTTClient('bar', array('host' => '0.0.0.0'));
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

/**
 * testDisconnect method
 *
 * @return void
 */
  public function testDisconnect() {
    $client = new MockMQTTClient('bar', array('host' => '0.0.0.0'));
    $client->socket = $this->getMockBuilder('Socket')
                           ->setMethods(array('write', 'close'))
                           ->getMock();

    $expected = pack('C*', 
      0xE0, // Static Header
      0x00 // Remaining Length
    );

    $client->socket->expects($this->at(0))->method('write')->with($this->equalTo($expected));
    $client->socket->expects($this->at(1))->method('close');

    $client->disconnect();
  }
}
