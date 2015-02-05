<?php

namespace Att\M2X\MQTT;

class MQTTResponse extends \Att\M2X\HttpResponse {

	public function __construct($response) {
		$data = json_decode($response, true);
		$this->statusCode = $data['status'];
		$this->body = json_encode($data['body']);
	}
}