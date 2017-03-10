<?php

namespace Att\M2X\MQTT;

/**
 * Method for Wrapper for {@link https://m2x.att.com/developer/documentation/v2/distribution M2X Distribution} API
 */
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
 * @return string Distribution ID
 */
  public function id() {
    return $this->id;
  }

/**
 * Method for {@link https://m2x.att.com/developer/documentation/v2/distribution#Add-Device-to-an-existing-Distribution Add device to an existing distribution} endpoint.
 *
 * @param string $serial Serial of the device to be added
 * @return Device Newly created device
 */
  public function addDevice($serial) {
      $data = array('serial' => $serial);
    $response = $this->client->post($this->path() . '/devices', $data);
    return new Device($this->client, $response->json());
  }
}
