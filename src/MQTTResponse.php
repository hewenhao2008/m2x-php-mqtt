<?php

namespace Att\M2X\MQTT;

class MQTTResponse {

/**
 * The HTTP status code
 *
 * @var integer
 */
  public $statusCode;

/**
 * The response body
 *
 * @var string
 */
  public $body = '';

/**
 * The full raw response
 *
 * @var string
 */
  public $raw = '';

/**
 * Parse the MQTT response data
 *
 * @param string $response
 */
  public function __construct($response) {
    $data = json_decode($response, true);
    $this->statusCode = $data['status'];
    if (isset($data['body'])) {
      $this->body = json_encode($data['body']);
    }
  }

/**
 * Returns the raw response body
 *
 * @return string
 */
  public function raw() {
    return $this->body;
  }

/**
 * Returns the json encoded data object
 *
 * @return array
 */
  public function json() {
    return json_decode($this->body, true);
  }

/**
 * Returns the HTTP Status code
 *
 * @return int
 */
  public function status() {
    return $this->statusCode;
  }

/**
 * Whether response status is a success (status code 2xx)
 *
 * @return boolean
 */
  public function success() {
    return $this->statusCode >= 200 && $this->statusCode < 300;
  }

/**
 * Whether response status is a client error (status code 4xx)
 *
 * @return boolean
 */
  public function clientError() {
    return $this->statusCode >= 400 && $this->statusCode < 500;
  }

/**
 * Whether response status is a server error (status code 5xx)
 *
 * @return boolean
 */
  public function serverError() {
    return $this->statusCode >= 500 && $this->statusCode < 600;
  }

/**
 * Wheter response status is a client or server error
 *
 * @return boolean
 */
  public function error() {
    return $this->clientError() || $this->serverError();
  }
}