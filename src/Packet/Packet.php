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

/**
 * Encoded the packet properties into the buffer.
 * This method has to be overwritten in the subclass.
 *
 * @return void
 */
  protected function encodeBody() {}

/**
 * Initializes the packet from a binary string received from the socket.
 *
 * @param string $header
 * @param string $data
 * @return void
 */
  public function parse($header, $data) {
    $this->buffer = $data;
    $this->parseBody($data);
  }

/**
 * Parses the body and sets the properties of the Packet object.
 * This method has to be overwritten in the subclass.
 *
 * @param string $data
 * @return void
 */
  protected function parseBody($data) {}

/**
 * Encode a packet object and return the its binary string.
 *
 * @return string
 */
  public function encode() {
    $this->encodeBody();

    //Encode remaining length
    $x = strlen($this->buffer);
    $bytes = array();
    while ($x > 0) {
      $byte = $x % 128;
      $x = $x >> 7;
      if ($x > 0) {
        $byte = $byte | 0x80;
      }
      $bytes[] = $byte;
    }

    if (empty($bytes)) {
      $bytes[] = 0x00;
    }

    $header = pack('C', $this->type | $this->flags);
    foreach ($bytes as $byte) {
      $header .= pack('C', $byte);
    }

    return $header . $this->buffer;
  }

/**
 * Read a packet from a socket.
 *
 * @param Socket $socket
 * @return Packet
 * @throws Exception
 */
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