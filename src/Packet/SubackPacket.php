<?php

namespace Att\M2X\MQTT\Packet;

class SubackPacket extends Packet {

  public function __construct() {
    parent::__construct(Packet::TYPE_SUBACK);
  }
}
