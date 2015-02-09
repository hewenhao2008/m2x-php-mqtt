<?php

namespace Att\M2X\MQTT\Packet;

class SubrecPacket extends Packet {

  public function __construct() {
    parent::__construct(Packet::TYPE_PUBREC);
  }
}
