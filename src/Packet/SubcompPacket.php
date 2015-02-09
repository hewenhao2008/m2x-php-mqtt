<?php

namespace Att\M2X\MQTT\Packet;

class SubcompPacket extends Packet {

  public function __construct() {
    parent::__construct(Packet::TYPE_PUBCOMP);
  }
}
