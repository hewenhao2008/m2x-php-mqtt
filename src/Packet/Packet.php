<?php

namespace Att\M2X\MQTT\Packet;

class Packet {

  static $PACKET_TYPES = array(
    1 => '\Att\M2X\MQTT\Packet\ConnectPacket',
    2 => '\Att\M2X\MQTT\Packet\ConnackPacket',
    3 => '\Att\M2X\MQTT\Packet\PublishPacket',
    8 => '\Att\M2X\MQTT\Packet\SubscribePacket',
    9 => '\Att\M2X\MQTT\Packet\SubackPacket'
  );

  const PROTOCOL_NAME = 'MQIsdp';

  const PROTOCOL_VERSION = 0x03;

  const TYPE_CONNECT = 0x10;
  const TYPE_CONNACK = 0x20;
  const TYPE_PUBLISH = 0x30;
  const TYPE_SUBSCRIBE = 0x80;
  const TYPE_SUBACK = 0x90;

/**
 * Holds the buffer to be sent to the broker
 *
 * @var array
 */
  protected $buffer = '';

/**
 * The message type
 *
 * @var null
 */
  protected $type = null;

/**
 * Identifier to link control packets together
 *
 * @var integer
 */
  protected $id = 0;

/**
 * The 4 bits of flags in the fixed header
 *
 * @var integer
 */
  protected $flags = 0x00;

  public function __construct($type, $flags = 0x00) {
  	$this->type = $type;
    $this->flags = $flags;
  }

/**
 * Add a string to the buffer
 *
 * @todo Make this UTF-8
 *
 * @param string $string
 * @return void
 */
  protected function encodeString($string) {
    $this->buffer .= pack('C*', 0x00, strlen($string));
    $this->buffer .= $string;
  }

  protected function encodeBody() {}

  public function parse($header, $data) {
    $this->buffer = $data;
    $this->parseBody($data);
  }

  protected function parseBody($data) {}

  public function encode() {
    $this->encodeBody();
    $header = pack('C*', $this->type | $this->flags, strlen($this->buffer));
    return $header . $this->buffer;
  }

  static function read($socket) {
    $byte = $socket->read(1);
    $header = unpack('C', $byte);
    $packetType = $header[1] >> 4;

    if (!array_key_exists($packetType, self::$PACKET_TYPES)) {
      throw new \Exception('Invalid packet type received');
    }

    //Calculate remaining length
    $multiplier = 1;
    $length = 0;
    while(true) {
      $digit = current(unpack('C', $socket->read(1)));
      $length += ($digit & 127) * $multiplier;
      $multiplier *= 128;
      if (($digit & 128) == 0) {
        break;
      }
    }

    //Read body
    $data = $socket->read($length);

    $packet = new self::$PACKET_TYPES[$packetType];
    $packet->parse($header, $data);
    return $packet;
  }

/**
 * Return the packet type
 *
 * @return integer
 */
  public function type() {
    return $this->type;
  }

/**
 * Return the buffer
 *
 * @return string
 */
  public function buffer() {
    return $this->buffer;
  }
}