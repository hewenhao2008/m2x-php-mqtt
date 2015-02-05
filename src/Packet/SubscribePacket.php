<?php

namespace Att\M2X\MQTT\Packet;

class SubscribePacket extends Packet {

/**
 * The topic to subscribe to
 *
 * @var string
 */
  protected $topic = '';

  public function __construct($options = array()) {
    foreach ($options as $name => $value) {
      if (property_exists($this, $name)) {
        $this->{$name} = $value;
      }
    }

    parent::__construct(Packet::TYPE_SUBSCRIBE);
  }

  protected function encodeBody() {
    //Packet identifier
    $this->buffer .= pack('C*', 0x00, $this->id);

    //Payload
    $this->encodeString($this->topic);
    $this->buffer .= pack('C', 0x00); //Request qos0
  }
}
