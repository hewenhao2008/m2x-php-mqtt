<?php

use Att\M2X\MQTT\Packet\Packet;
use Att\M2X\MQTT\Packet\PublishPacket;
use Att\M2X\MQTT\Test\FileStreamSocket;

class MockPacket extends Packet {
  public $buffer = '';
}

class PacketTest extends BaseTestCase {

/**
 * testEncodingFixedHeader method
 *
 * @return void
 */
  public function testEncodingFixedHeader() {
    $packet = new MockPacket(Packet::TYPE_CONNECT, 0x07);
    $result = $packet->encode();
    $expected = pack('C*', 0x17, 0x00);

    $this->assertEquals($expected, $result);
  }

/**
 * testEncodeString method
 *
 * @return void
 */
  public function testEncodeString() {
    $packet = new Packet(Packet::TYPE_PUBLISH);
    $this->assertSame('', $packet->buffer());

    $this->callMethod($packet, 'encodeString', array('foo'));
    $expected = pack('C*', 0x00, 0x03, 0x66, 0x6F, 0x6F);
    $this->assertEquals($expected, $packet->buffer());

    $this->callMethod($packet, 'encodeString', array('ab'));
    $expected .= pack('C*', 0x00, 0x02, 0x61, 0x62);
    $this->assertEquals($expected, $packet->buffer());
  }

/**
 * testEncode method
 *
 * @return void
 */
  public function testEncode() {
    $packet = new Packet(Packet::TYPE_PUBLISH, 0x05);
    $this->callMethod($packet, 'encodeString', array('foo'));

    $expected = pack('C*', 
      0x35, // Static Header
      0x05, // Remaining Length
      0x00, // MSB
      0x03, // LSB
      0x66, // f
      0x6F, // o
      0x6F  // o
    );

    $this->assertEquals($expected, $packet->encode());
  }

/**
 * testEncodeLongData method
 *
 * @return void
 */
  public function testEncodeLongData() {
    $packet = new MockPacket(Packet::TYPE_PUBLISH, 0x05);
    $packet->buffer .= str_repeat('A', 300);

    $expectedBytes = array_merge(
      array(
        0x35, // Static Header
        0xAC, // Remaining Length (44)
        0x02 // Remaining Length (2 * 128)
      ),
      array_fill(0, 300, 0x41)
    );

    $expected = call_user_func_array('pack',array_merge(array('C*'),$expectedBytes));
    $result = $packet->encode();

    $this->assertEquals($expected, $result);
  }

/**
 * testRead method
 *
 * @return void
 */
  public function testRead() {
    $socket = new FileStreamSocket();
    $socket->connect(__DIR__ . '/../test_packets/connack_not_authorized.hex');

    $result = Packet::read($socket);
    $expected = pack('C*', 0x00, 0x05);
    $this->assertEquals($expected, $result->buffer());
  }

/**
 * testType method
 *
 * @return void
 */
  public function testType() {
    $packet = new Packet(Packet::TYPE_CONNACK);
    $this->assertSame(Packet::TYPE_CONNACK, $packet->type());
  }

/**
 * testUtf8Encoding method
 *
 * @return void
 */
  public function testUtf8Encoding() {
    $packet = new MockPacket(Packet::TYPE_PUBLISH);
    $this->callMethod($packet, 'encodeString', array('α/β'));
    $this->callMethod($packet, 'encodeString', array('馬'));

    $expected = pack('C*', 
      0x30, // Static Header
      0x0C, // Remaining Length (13 bytes)
      0x00, // MSB
      0x05, // LSB
      0xCE, 0xB1, // α
      0x2F, // /
      0xCE, 0xB2,  // β
      0x00, // MSB
      0x03, // LSB
      0xE9, 0xA6, 0xAC // 馬 
    );

    $this->assertEquals($expected, $packet->encode());
  }
}
