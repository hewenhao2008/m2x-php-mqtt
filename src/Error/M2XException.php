<?php

namespace Att\M2X\MQTT\Error;

use Att\M2X\MQTT\MQTTResponse;

class M2XException extends \Exception {

/**
 * Holds the MQTTResponse instance
 *
 * @var MQTTResponse
 */
  public $response = null;

/**
 * Create the exception from a MQTTResponse object
 *
 * @param HttpResponse $response
 */
  public function __construct(MQTTResponse $response) {
  	var_dump($response);
    $data = $response->json();
    var_dump($data);
    $this->response = $response;
    parent::__construct($data['message'], $response->statusCode);
  }
}
