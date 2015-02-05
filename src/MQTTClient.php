<?php

namespace Att\M2X\MQTT;

use Att\M2X\MQTT\Packet\Packet;
use Att\M2X\MQTT\Packet\ConnectPacket;
use Att\M2X\MQTT\Packet\PublishPacket;
use Att\M2X\MQTT\Packet\SubscribePacket;
use Att\M2X\MQTT\Error\ProtocolException;

require_once 'Hexdump.php';

mb_internal_encoding('UTF-8');

class MQTTClient {

  const VERSION = '2.0.0';

/**
 * These flags indicates the level of assurance for delivery of a PUBLISH message.
 *
 */
  const QOS0 = 0x00;
  const QOS1 = 0x02;
  const QOS2 = 0x04;
  const RETAIN = 0x01;
  const DUP = 0x08;

/**
 * Hostname of the remote server
 *
 * @var string
 */
  protected $host = null;

/**
 * Port of the remote server (default: 1883)
 *
 * @var integer
 */
  protected $port = 1883;

/**
 * Holds the socket resource
 *
 * @var resource
 */
  protected $socket = null;

/**
 * The Client Identifier (Client ID) is between 1 and 23 characters long,
 * and uniquely identifies the client to the server.
 *
 * @var string
 */
  protected $clientId = null;

/**
 * The username for authenticating with the server
 *
 * @var string
 */
  protected $username = null;

/**
 * The password for authenticating with the server
 *
 * @var string
 */
  protected $password = null;

  protected $lastPacketId = 0;

/**
 * The QOS level used
 *
 * @var integer
 */
  protected $qos = self::QOS0;

  public function __construct($host, $options = array()) {
    $this->host = $host;

    foreach ($options as $name => $value) {
      if (property_exists($this, $name)) {
        $this->{$name} = $value;
      }
    }

    $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
    socket_set_block($this->socket);
  }

/**
 * Connect to the broker
 *
 * @return void
 * @throws ProtocolException
 */
  public function connect() {
    echo "CONNECT packet\n\r";

    socket_connect($this->socket, $this->host, $this->port);

    $packet = new ConnectPacket(array(
      'clientId' => $this->clientId,
      'username' => $this->username,
      'password' => $this->password
    ));

    $this->sendPacket($packet);
  	$this->receiveConnack();
  }

  public function publish($topic, $payload = '', $flags = 0x00) {
    $packet = new PublishPacket(array(
      'topic' => $topic,
      'payload' => $payload
    ));

    $this->sendPacket($packet);
  }

  public function subscribe($topic, $flags = 0x00) {
    $packet = new SubscribePacket(array(
      'id' => $this->nextPacketId(),
      'topic' => $topic
    ));

    $this->sendPacket($packet);
    $this->receivePacket();
  }

  protected function sendPacket(Packet $packet) {
    $encoded = $packet->encode();
    echo "Sending packet:\n\r";
    hexdump($encoded);
    $written = socket_write($this->socket, $encoded, strlen($encoded));
  }

/**
 * Handle the response of the CONNECT call and check for a successfull connection
 *
 * @return void
 * @throws ProtocolException
 */
  protected function receiveConnack() {
    $packet = Packet::read($this->socket);

    if ($packet->type() !== Packet::TYPE_CONNACK) {
      throw new ProtocolException('Response was not a CONNACK packet');
    }

    if (!$packet->accepted()) {
      throw new ProtocolException($packet->returnMessage());
    }
  }

  protected function receivePacket() {
    while(true) {
      echo "socket_select() polling\n\r";

      $r = array($this->socket);
      $w = $e = array();
      $result = socket_select($r, $w, $e, 1);
      
      if ($result) {
        return Packet::read($this->socket);
      }
    }
  }

/**
 * Returns the next available packet id
 *
 * @return integer
 */
  protected function nextPacketId() {
    $this->lastPacketId++;
    return $this->lastPacketId;
  }
}
