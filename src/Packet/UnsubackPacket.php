<?php

namespace Att\M2X\MQTT\Packet;

class UnsubackPacket extends Packet {

  public function __construct() {
    parent::__construct(Packet::TYPE_UNSUBACK);
  }
}
