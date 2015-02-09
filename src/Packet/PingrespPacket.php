<?php

namespace Att\M2X\MQTT\Packet;

class PingrespPacket extends Packet {

  public function __construct($options = array()) {
    foreach ($options as $name => $value) {
      if (property_exists($this, $name)) {
        $this->{$name} = $value;
      }
    }

    parent::__construct(Packet::TYPE_PINGRESP);
  }
}
