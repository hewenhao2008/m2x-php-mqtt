<?php

namespace Att\M2X\MQTT\Test;

use Att\M2X\MQTT\Net\Socket;

/**
 * Utility class for the unit tests
 */
class FileStreamSocket extends Socket {

  protected $stream = null;

  public function connect($host, $port = null) {
    $this->stream = fopen($host, 'rb');
  }

  public function read($bytes) {
    return fread($this->stream, $bytes);
  }
}