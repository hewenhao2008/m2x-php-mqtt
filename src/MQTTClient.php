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

/**
 * Version number of this library
 *
 */
  const VERSION = '3.1.0';

/**
 * The hostname of the default broker
 *
 */
  const DEFAULT_API_HOST = 'api-m2x.att.com';

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

/**
 * The topic for the responses
 *
 * @var string
 */
  protected $responsesTopic = null;

/**
 * The topic for the commands
 *
 * @var string
 */
  protected $commandsTopic = null;

/**
 * Contains received packets
 *
 * @var array
 */
  protected $inbox = array();

/**
 * Create a new instance of the MQTT Client, by default the client
 * will connect to the live API of m2x and sets a random clientId.
 *
 * Optional options:
 * - host: IP address of the MQTT broker
 * - port: Port for the MQTT broker connection
 * - clientId: The name of the Client ID to send to the broker
 *
 * @param string $apiKey
 * @param array $options
 */
  public function __construct($apiKey, $options = array()) {
    $this->responsesTopic = sprintf('m2x/%s/responses', $apiKey);
    $this->commandsTopic = sprintf('m2x/%s/commands', $apiKey);

    if (!isset($options['host'])) {
      $options['host'] = gethostbyname(self::DEFAULT_API_HOST);
    }

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

    $this->subscribe($this->responsesTopic);
    $this->subscribe($this->commandsTopic);
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
 * @param $topic Only return packets from this topic (optional)
 * @return Packet
 */
  public function receivePacket($topic = null) {
    $socket = $this->socket();

    while(true) {
      if ($topic && !empty($this->inbox[$topic])) {
        return array_shift($this->inbox[$topic]);
      }

      if ($socket->dataAvailable()) {
        $packet = Packet::read($socket);

        if ($topic && $packet->topic() != $topic) {
          $this->inbox[$packet->topic()] = $packet;
          continue;
        }

        return $packet;
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
   * Perform a GET request to the API.
   *
   * @param string $path
   * @param array $params
   * @return MQTTResponse
   */
    public function get($path, $params = array()) {
      return $this->sendRequest('GET', $path, $params);
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
