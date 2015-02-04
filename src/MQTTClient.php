<?php

namespace Att\M2X\MQTT;

use Att\M2X\MQTT\Packet\Packet;
use Att\M2X\MQTT\Packet\ConnectPacket;

require_once 'Hexdump.php';

mb_internal_encoding('UTF-8');

class MQTTClient {

  const VERSION = '2.0.0';

/**
 * These flags indicates the level of assurance for delivery of a PUBLISH message.
 *
 */
  const QOS1 = 0x00;
  const QOS2 = 0x02;
  const QOS3 = 0x04;


/**
 * Holds the socket resource
 *
 * @var resource
 */
  protected $socket = null;

/**
 * The QOS level used
 *
 * @var integer
 */
  protected $qos = self::QOS1;

  public function __construct() {
    $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
    socket_set_block($this->socket);
  }

/**
 * Connect to the broker
 *
 * @return void
 */
  public function connect() {
    echo "CONNECT packet\n\r";

    $ip = gethostbyname('api-m2x.att.com');
    $ip = '127.0.0.1';
    socket_connect($this->socket, $ip, 1883);


    $packet = new ConnectPacket(array('clientId' => 'PHP'));
    $this->write($packet);
  	$this->read();
  }

  protected function write(Packet $packet) {
    $encoded = $packet->encode();

    echo "Writing to socket\n\r";
    hexdump($encoded);

    $written = socket_write($this->socket, $encoded, strlen($encoded));

    echo "Bytes written to the socket: {$written}\n\r\n\r";
  }

  protected function read() {
    $raw = socket_read($this->socket, 4);
    echo "Received Packet:\n\r";
    hexdump($raw);
  }
}
