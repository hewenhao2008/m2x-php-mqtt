<?php

namespace Att\M2X\MQTT\Net;

use Att\M2X\MQTT\Error\SocketException;

class Socket {

/**
 * Holds the internal socket resource
 *
 * @var resource
 */
  protected $socket = null;

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
 * @param integer $bytes
 * @return string
 */
  public function read($bytes) {
    return socket_read($this->socket, $bytes);
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
}