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
    $data = $response->json();
    $this->response = $response;
    parent::__construct(current($data), $response->statusCode);
  }
}
