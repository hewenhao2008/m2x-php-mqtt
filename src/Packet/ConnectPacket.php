<?php

namespace Att\M2X\MQTT\Packet;

class ConnectPacket extends Packet {

  protected $clientId = null;

  protected $cleanSession = true;

  protected $keepAlive = 15;

  protected $username = null;

  protected $password = null;

  public function __construct($options = array()) {
    foreach ($options as $name => $value) {
      if (property_exists($this, $name)) {
        $this->{$name} = $value;
      }
    }

    parent::__construct(Packet::TYPE_CONNECT);
  }

  public function encodeBody() {
    $this->encodeString(self::PROTOCOL_NAME);
    $this->buffer[] = self::PROTOCOL_VERSION;

    //Flags
    $flags = 0;

    if ($this->username !== null) {
      $flags = $flags || 0x80;
    }

    if ($this->password !== null) {
      $flags = $flags || 0x40;
    }

    if ($this->cleanSession) {
      $flags = $flags || 0x02;
    }

    $this->buffer[] = $flags;

    //Keep Alive
    $this->buffer[] = 0x00;
    $this->buffer[] = $this->keepAlive;

    //Payload
    $this->encodeString($this->clientId);

    if ($this->username !== null) {
      $this->encodeString($this->username);
    }

    if ($this->password !== null) {
      $this->encodeString($this->password);
    }
  }
}
