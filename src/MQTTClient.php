<?php

namespace Att\M2X\MQTT;

use Att\M2X\MQTT\Packet\Packet;
use Att\M2X\MQTT\Packet\ConnectPacket;
use Att\M2X\MQTT\Packet\PublishPacket;
use Att\M2X\MQTT\Packet\SubscribePacket;
use Att\M2X\MQTT\Packet\DisconnectPacket;
use Att\M2X\MQTT\Error\ProtocolException;
use Att\M2X\MQTT\Error\M2XException;
use Att\M2X\MQTT\MQTTResponse;
use Att\M2X\MQTT\Net\Socket;
use Att\M2X\MQTT\Device;

mb_internal_encoding('UTF-8');

class MQTTClient {

  const VERSION = '2.0.0';

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
  protected $clientId = '';

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
  protected $qos = Packet::QOS0;

  public function __construct($host, $apiKey, $options = array()) {
    $this->host = $host;
    $options['username'] = $this->apiKey = $apiKey;

    if (!isset($options['clientId'])) {
      $options['clientId'] = $this->generateClientId();
    }

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
    ), $flags);

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
 * Send a DISCONNECT packet to the MQTT broker
 *
 * @return void
 */
  public function disconnect() {
    $packet = new DisconnectPacket();
    $this->sendPacket($packet);
    $this->socket()->close();
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
 * Generate a random client id
 *
 * @return string
 */
  protected function generateClientId() {
    return 'PHP-' . time() . '-' . substr(md5(rand()), 0, 7);
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
 * Send a pseudo HTTP request to the M2X API
 *
 * @param string $method
 * @param string $resource
 * @return MQTTResponse
 */
  protected function sendRequest($method, $resource, $vars = array()) {
    if($method == 'GET' && !empty($vars)) {
      $resource = $resource . "?" . http_build_query($vars); 
    }

    $payload = array(
      'id' => $this->nextRequestId(),
      'method' => $method,
      'resource' => '/v2' . $resource
    );

    if ($method === 'POST' || $method === 'PUT') {
      $payload['body'] = $vars;
    }

    $this->publish(sprintf('m2x/%s/requests', $this->apiKey), json_encode($payload), Packet::RETAIN);

    $packet = $this->receivePacket();

    $response = new MQTTResponse($packet->payload());
    return $this->handleResponse($response);
  }

/**
 * Checks the MQTTResponse for errors and throws an exception, if
 * no errors are encountered, the MQTTResponse is returned.
 *
 * @param MQTTResponse $response
 * @return HttpResponse
 * @throws M2XException
 */
  protected function handleResponse(MQTTResponse $response) {
    $this->lastResponse = $response;

    if ($response->success()) {
      return $response;
    }

    throw new M2XException($response);
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


/**
 * Get an instance of a Device resource
 *
 * @param string $key
 * @return Key
 */
  public function device($id) {
    return new Device($this, array('id' => $id));
  }

/**
 * Create a new device.
 *
 * @param $data
 * @return Device
 */
  public function createDevice($data) {
    return Device::create($this, $data);
  }

/**
 * Get an instance of a Distribution resource
 *
 * @param string $id
 * @return Key
 */
  public function distribution($id) {
    return new Distribution($this, array('id' => $id));
  }
}
