# AT&T's M2X PHP (MQTT) Client

[AT&T M2X](http://m2x.att.com) is a cloud-based fully managed time-series data storage service for network connected machine-to-machine (M2M) devices and the Internet of Things (IoT). 

The [AT&T M2X API](https://m2x.att.com/developer/documentation/overview) provides all the needed operations and methods to connect your devices to AT&T's M2X service. This library aims to provide a simple wrapper to interact with the AT&T M2X API for [PHP](http://php.net). Refer to the [Glossary of Terms](https://m2x.att.com/developer/documentation/v2/glossary) to understand the nomenclature used throughout this documentation.

Getting Started
==========================
1. Signup for an [M2X Account](https://m2x.att.com/signup).
2. Obtain your _Master Key_ from the Master Keys tab of your [Account Settings](https://m2x.att.com/account) screen.
2. Create your first [Device](https://m2x.att.com/devices) and copy its _Device ID_.
3. Review the [M2X API Documentation](https://m2x.att.com/developer/documentation/overview).

## Installation

Simply add a dependency on attm2x/m2x-php-mqtt to your project's composer.json file if you use Composer to manage the dependencies of your project.

```json
{
  "require": {
    "attm2x/m2x-php-mqtt": "~2.0"
  }
}
```

## Usage

This MQTT library extends from the [attm2x/m2x-php](https://github.com/attm2x/m2x-php) library. Please refer to that library for additional usage instructions and examples to communicate with the M2X API.

```php
use Att\M2X\MQTT\MQTTClient;

$host = gethostbyname('staging-api.m2x.sl.attcompute.com');
$apiKey = '<YOUR API KEY>';

$client = new MQTTClient($host, $apiKey);
$client->connect();

$devices = $client->devices();
```

## License

This lib is provided under the MIT license. See [LICENSE](LICENSE) for applicable terms.
