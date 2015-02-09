<?php

namespace Att\M2X\MQTT\Packet;

class PublishPacket extends Packet {

/**
 * Holds the topic that this message belongs to
 *
 * @var string
 */
  protected $topic = '';

/**
 * Holds the payload of the message
 *
 * @var string
 */
  protected $payload = '';

  public function __construct($options = array(), $flags = 0x00) {
    $this->setOptions($options);
    parent::__construct(Packet::TYPE_PUBLISH, $flags);
  }

  public function encodeBody() {
    $this->encodeString($this->topic);
    $this->buffer .= $this->payload;
  }

  public function parseBody($data) {
    $bounds = unpack('C2', $data);
    $this->topic = mb_substr($data, 2, $bounds[2]);
    $this->payload = mb_substr($data, 2 + $bounds[2]);
  }

/**
 * Returns the topic
 *
 * @return string
 */
  public function topic() {
    return $this->topic;
  }

/**
 * Returns the payload
 *
 * @return string
 */
  public function payload() {
    return $this->payload;
  }
}
