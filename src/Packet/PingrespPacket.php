<?php

namespace Att\M2X\MQTT\Packet;

class PingrespPacket extends Packet {

  public function __construct() {
    parent::__construct(Packet::TYPE_PINGRESP);
  }
}
