<?php

namespace Att\M2X\MQTT\Packet;

class Packet {

  static $PACKET_TYPES = array(
    1 => '\Att\M2X\MQTT\Packet\ConnectPacket',
    2 => '\Att\M2X\MQTT\Packet\ConnackPacket'
  );

  const PROTOCOL_NAME = 'MQIsdp';

  const PROTOCOL_VERSION = 0x03;

  const TYPE_CONNECT = 0x10;
  const TYPE_CONNACK = 0x20;

/**
 * Holds the byte buffer to be sent to the broker
 *
 * @var array
 */
  protected $buffer = array();

/**
 * The message type
 *
 * @var null
 */
  protected $type = null;

/**
 * The 4 bits of flags in the fixed header
 *
 * @var integer
 */
  protected $flags = 0;

  public function __construct($type) {
  	$this->type = $type;
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
    $this->buffer[] = 0x00;
    $this->buffer[] = strlen($string);

    foreach (str_split($string) as $char) {
      $this->buffer[] = ord($char);
    }
  }

  protected function encodeBody() {}

  public function encode() {
    $this->encodeBody();
    array_unshift($this->buffer, $this->type, count($this->buffer));
    return call_user_func_array('pack', array_merge(array("C*"), $this->buffer));
  }

  static function read($socket) {
    $data = socket_read($socket, 2);
    $header = unpack('C*', $data);

    $packetType = $header[1] >> 4;

    if (!array_key_exists($packetType, self::$PACKET_TYPES)) {
      throw new Exception('Invalid packet type received');
    }

    $packet = new self::$PACKET_TYPES[$packetType];

    //Read body
    $data = socket_read($socket, $header[2]);
    $packet->parseBody($data);

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
}