<?php

use \Att\M2X\MQTT\Packet\PublishPacket;
use \Att\M2X\MQTT\Packet\Packet;

class PublishPacketTest extends BaseTestCase {

/**
 * testCreate method
 *
 * @return void
 */
  public function testCreate() {
    $data = array(
      'topic' => 'foo/bar',
      'payload' => 'abc'
    );

    $packet = new PublishPacket($data);

    $this->assertEquals('foo/bar', $packet->topic());
    $this->assertEquals('abc', $packet->payload());
    $this->assertEquals(0x00, $packet->flags());

    $packet = new PublishPacket($data, Packet::RETAIN);
    $this->assertEquals(0x01, $packet->flags());

    $packet = new PublishPacket($data, Packet::RETAIN | Packet::DUP);
    $this->assertEquals(0x09, $packet->flags());
  }

/**
 * testEncode method
 *
 * @return void
 */
  public function testEncode() {
    $data = array(
      'topic' => 'a/b',
      'payload' => 'foo'
    );
    $packet = new PublishPacket($data, Packet::RETAIN);

    $expected = pack('C*', 
      0x31, // Static Header
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

    $this->assertEquals($expected, $packet->encode());
  }
}
