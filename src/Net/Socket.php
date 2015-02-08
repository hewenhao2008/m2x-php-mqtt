<?php

namespace Att\M2X\MQTT\Net;

use Att\M2X\MQTT\Error\SocketException;

class Socket {

  const READ_BUFFER_SIZE = 512;

/**
 * Holds the internal socket resource
 *
 * @var resource
 */
  protected $socket = null;

/**
 * If log is set to true, all data received will be written
 * to the internal buffer.
 *
 * @var boolean
 */
  protected $log = false;

/**
 * Holds the internal buffer data.
 *
 * @var string
 */
  protected $buffer = '';

/**
 * Create the socket resource
 *
 * @return void
 */
  public function create() {
    $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
    socket_set_block($this->socket);
  }

/**
 * Initiates a connection on the socket
 *
 * @param string $host
 * @param integer $port
 * @return void
 * @throws SocketException
 */
  public function connect($host, $port) {
    if (!@socket_connect($this->socket, $host, $port)) {
      $error = socket_strerror(socket_last_error());
      throw new SocketException($error);
    }
  }

/**
 * Write data to the socket
 *
 * @param string $data
 * @return integer
 */
  public function write($data) {
    return socket_write($this->socket, $data, strlen($data));
  }

/**
 * Reads a maximum of length bytes from the socket
 * 
 * @param integer $length
 * @return string
 */
  public function read($length) {
    $left = $length;
    $data = '';

    while ($left > 0) {
      $toRead = min(self::READ_BUFFER_SIZE, $left);
      $bytes = socket_read($this->socket, $toRead);
      $data .= $bytes;
      $left -= strlen($bytes);
    }

    if ($this->log) {
      $this->buffer .= $data;
    }

    return $data;
  }

/**
 * Listen on the socket until new data is available
 *
 * @return boolean
 */
  public function dataAvailable() {
      $r = array($this->socket);
      $w = $e = array();
      $result = socket_select($r, $w, $e, 1);
      return $result === 1;
  }

/**
 * Start logging data to the buffer. Logging is stopped when
 * the buffer is retrieved with the buffer() method.
 *
 * @return void
 */
  public function log() {
    $this->log = true;
  }

/**
 * Stop logging and return the logged data from the buffer.
 *
 * @return string
 */
  public function buffer() {
    $this->log = false;
    $buffer = $this->buffer;
    $this->buffer = '';
    return $buffer;
  }
}