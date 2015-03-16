<?php

namespace Att\M2X\MQTT;

class Distribution extends Resource {

/**
 * REST path of the resource
 *
 * @var string
 */
  public static $path = '/distributions';

/**
 * The Key resource properties
 *
 * @var array
 */
  protected static $properties = array(
    'name', 'description', 'visibility'
  );

/**
 * The resource id for the REST URL
 *
 * @return string
 */
  public function id() {
    return $this->id;
  }

/**
 * Add a new device to an existing distribution.
 *
 * @link https://m2x.att.com/developer/documentation/v2/distribution#add-device-to-an-existing-distribution
 *
 * @param string $serial
 * @return Device
 */
  public function addDevice($serial) {
      $data = array('serial' => $serial);
    $response = $this->client->post($this->path() . '/devices', $data);
    return new Device($this->client, $response->json());
  }
}
