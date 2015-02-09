<?php

namespace Att\M2X\MQTT;

use Att\M2X\MQTT\Packet\Packet;
use Att\M2X\MQTT\Packet\ConnectPacket;
use Att\M2X\MQTT\Packet\PublishPacket;
use Att\M2X\MQTT\Packet\SubscribePacket;
use Att\M2X\MQTT\Error\ProtocolException;
use Att\M2X\MQTT\MQTTResponse;
use Att\M2X\MQTT\Net\Socket;

mb_internal_encoding('UTF-8');

class MQTTClient extends \Att\M2X\M2X {

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
 * Holds the socket class
 *
 * @var Socket
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

/**
 * Holds the counter for control packets
 *
 * @var integer
 */
  protected $lastPacketId = 0;

/**
 * The QOS level used
 *
 * @var integer
 */
  protected $qos = self::QOS0;

  public function __construct($host, $apiKey, $options = array()) {
    $this->host = $host;
    $options['username'] = $this->apiKey = $apiKey;

    foreach ($options as $name => $value) {
      if (property_exists($this, $name)) {
        $this->{$name} = $value;
      }
    }
  }

/**
 * Connect to the broker
 *
 * @return void
 * @throws ProtocolException
 * @throws SocketException
 */
  public function connect() {
    $this->socket()->create();
    $this->socket()->connect($this->host, $this->port);

    $packet = new ConnectPacket(array(
      'clientId' => $this->clientId,
      'username' => $this->username,
      'password' => $this->password
    ));

    $this->sendPacket($packet);
  	$this->receiveConnack();

    //TODO: Find a better place for this
    $this->subscribe(sprintf('m2x/%s/responses', $this->apiKey));
  }

/**
 * Publish a message to the broker.
 *
 * @param string $topic
 * @param string $payload
 * @param string $flags
 * @return void
 */
  public function publish($topic, $payload = '', $flags = 0x00) {
    $packet = new PublishPacket(array(
      'topic' => $topic,
      'payload' => $payload
    ));

    $this->sendPacket($packet);
  }

/**
 * Subscribe to a topic
 *
 * @param string $topic
 * @param string $flags
 * @return void
 */
  public function subscribe($topic, $flags = 0x00) {
    $packet = new SubscribePacket(array(
      'id' => $this->nextPacketId(),
      'topic' => $topic
    ));

    $this->sendPacket($packet);
    $this->receivePacket();
  }

/**
 * Send a Packet object to the broker
 *
 * @param Packet $packet
 * @return void
 */
  protected function sendPacket(Packet $packet) {
    $encoded = $packet->encode();
    $this->socket()->write($encoded);
  }

/**
 * Handle the response of the CONNECT call and check for a successfull connection
 *
 * @return void
 * @throws ProtocolException
 */
  protected function receiveConnack() {
    $packet = Packet::read($this->socket());

    if ($packet->type() !== Packet::TYPE_CONNACK) {
      throw new ProtocolException('Response was not a CONNACK packet');
    }

    if (!$packet->accepted()) {
      throw new ProtocolException($packet->returnMessage());
    }
  }

/**
 * Listen on the socket and receive a single packet.
 *
 * @return Packet
 */
  protected function receivePacket() {
    $socket = $this->socket();

    while(true) {
      if ($socket->dataAvailable()) {
        return Packet::read($socket);
      }
    }
  }

/**
 * Returns the next available packet id
 *
 * @return integer
 */
  public function nextPacketId() {
    $this->lastPacketId++;
    return $this->lastPacketId;
  }

/**
 * Generate a request id for M2X requests
 *
 * @return string
 */
  protected function nextRequestId() {
    return rand(1000, 9000);
  }

/**
 * Perform a GET request to the API.
 *
 * @param string $path
 * @param array $params
 * @return MQTTResponse
 */
  public function get($path, $params = array()) {
    return $this->sendRequest('GET', $path);
  }

/**
 * Perform a POST request to the API.
 *
 * @param string $path
 * @param array $vars
 * @return MQTTResponse
 */
  public function post($path, $vars = array()) {
    return $this->sendRequest('POST', $path, $vars);
  }

/**
 * Perform an PUT request to the API.
 *
 * @param string $path
 * @param array $vars
 * @return MQTTResponse
 */
  public function put($path, $vars = array()) {
    return $this->sendRequest('PUT', $path, $vars);
  }

/**
 * Perform a DELETE request to the API.
 *
 * @param string $path
 * @return MQTTResponse
 */
  public function delete($path) {
    return $this->sendRequest('DELETE', $path);
  }

/**
 * Send a pseudo HTTP request to the M2X API
 *
 * @param string $method
 * @param string $resource
 * @return MQTTResponse
 */
  protected function sendRequest($method, $resource, $vars = array()) {
    $payload = array(
      'id' => $this->nextRequestId(),
      'method' => $method,
      'resource' => '/v2' . $resource
    );

    if ($method === 'POST' || $method === 'PUT') {
      $payload['body'] = $vars;
    }

    $this->publish(sprintf('m2x/%s/requests', $this->apiKey), json_encode($payload));

$this->socket()->log();
    $packet = $this->receivePacket();
$fp = fopen('return' . $payload['id'] . '.hex', 'ab');
fwrite($fp, $this->socket()->buffer());
fclose($fp);

    $response = new MQTTResponse($packet->payload());
    return $this->handleResponse($response);
  }

/**
 * Return the socket instance, create new one if needed
 *
 * @return Socket
 */
  public function socket() {
    if ($this->socket == NULL) {
      $this->socket = new Socket();
    }
    return $this->socket;
  }
}
