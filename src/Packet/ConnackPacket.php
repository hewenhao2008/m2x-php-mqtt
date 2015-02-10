<?php

namespace Att\M2X\MQTT\Packet;

class ConnackPacket extends Packet {

/**
 * List of return code messages
 *
 * @var array
 */
  static $CODES = array(
    0x00 => 'Connection Accepted',
    0x01 => 'Connection refused: unacceptable protocol version',
    0x02 => 'Connection refused: client identifier rejected',
    0x03 => 'Connection refused: server unavailable',
    0x04 => 'Connection Refused: bad user name or password',
    0x05 => 'Connection Refused: not authorized'
  );

/**
 * Holds the return code
 *
 * @var int
 */
  protected $returnCode;

  public function __construct() {
    parent::__construct(Packet::TYPE_CONNACK);
  }

  public function parseBody($data) {
    $body = unpack('C*', $data);
    $this->returnCode = $body[2];
  }

/**
 * Get the return code
 *
 * @return int
 */
  public function returnCode() {
    return $returnCode;
  }

/**
 * Get a string message corresponding to a return code
 *
 * @return string
 */
  public function returnMessage() {
    if (array_key_exists($this->returnCode, self::$CODES)) {
      return self::$CODES[$this->returnCode];
    }

    return sprintf('Connection refused, error code %d', $this->returnCode);
  }

/**
 * Returns True if the connection was accepted
 *
 * @return boolean
 */
  public function accepted() {
    return $this->returnCode === 0x00;
  }
}
