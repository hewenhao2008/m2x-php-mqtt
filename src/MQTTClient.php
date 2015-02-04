<?php

namespace Att\M2X\MQTT;

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
    $packetObj = new ConnectPacket(array('clientId' => 'PHP'));
    $packet = $packetObj->encode();

  	echo "CONNECT Packet:\n\r";
  	hexdump($packet);

  	$ip = gethostbyname('api-m2x.att.com');
  	$ip = '127.0.0.1';
  	socket_connect($this->socket, $ip, 1883);

  	$written = socket_write($this->socket, $packet, strlen($packet));
  	echo "Bytes written to the socket: {$written}\n\r";

  	$result = socket_read($this->socket, 4);
  	echo "Received Packet:\n\r";
  	hexdump($result);


  }

  private function endianString($string) {
  	$buffer = array(0x00, strlen($string));
  	foreach (str_split($string) as $char) {
  		$buffer[] = ord($char);
  	}
  	return $buffer;
  }

}
