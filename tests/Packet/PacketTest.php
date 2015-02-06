<?php

use Att\M2X\MQTT\Packet\Packet;

class PacketTest extends BaseTestCase {

/**
 * testEncodingFixedHeader method
 *
 * @return void
 */
  public function testEncodingFixedHeader() {
    $packet = new Packet(Packet::TYPE_CONNECT, 0x07);

    $result = $packet->encode();
    $expected = pack('C*', 0x17, 0x00);

    $this->assertSame($expected, $result);
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
 * testType method
 *
 * @return void
 */
  public function testType() {
    $packet = new Packet(Packet::TYPE_CONNACK);
    $this->assertSame(Packet::TYPE_CONNACK, $packet->type());
  }
}