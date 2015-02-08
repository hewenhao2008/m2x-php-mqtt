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

/**
 * Ignore write actions, we do not want to write
 * to our test binary file.
 *
 * @param string $data
 * @return void
 */
  public function write($data) {}

/**
 * Always return true because we preloaded the socket with
 * data from a file.
 *
 * @return boolean
 */
  public function dataAvailable() {
  	return true;
  }
}