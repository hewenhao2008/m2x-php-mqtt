<?php

namespace Att\M2X\MQTT\Packet;

class DisconnectPacket extends Packet {

  public function __construct() {
    parent::__construct(Packet::TYPE_DISCONNECT);
  }
}
