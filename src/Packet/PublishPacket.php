<?php

namespace Att\M2X\MQTT\Packet;

class PublishPacket extends Packet {

  protected $topic = '';

  protected $payload = '';

  public function __construct($options = array(), $flags = 0x00) {
    foreach ($options as $name => $value) {
      if (property_exists($this, $name)) {
        $this->{$name} = $value;
      }
    }

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

  public function payload() {
    return $this->payload;
  }
}
