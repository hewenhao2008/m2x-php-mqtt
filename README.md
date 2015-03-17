[![Latest Stable Version](https://poser.pugx.org/attm2x/m2x-php-mqtt/v/stable.svg)](https://packagist.org/packages/attm2x/m2x-php-mqtt)
[![License](https://poser.pugx.org/attm2x/m2x-php-mqtt/license.svg)](https://packagist.org/packages/attm2x/m2x-php-mqtt)

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

In order to communicate with the M2X API, you need an instance of [MQTTClient](src//MQTTClient.php). You need to pass the host and your API key in the constructor to access your data.

```php
use Att\M2X\MQTT\MQTTClient;

$client = new MQTTClient('<YOUR API KEY>');
$client->connect();

// Placeholder for next examples.

$client->disconnect();
```

This provides an interface to the following endpoints for the M2X API:

- Creating devices
  ```php
  $data = array(
  	'name' => '<DEVICE-NAME>',
  	'visibility' => 'private'
  );
  $device = $client->createDevice($data)
  
  //Or for a distribution
  $distribution = $client->distribution("<DISTRIBUTION-ID>");
  $distribution->addDevice('<SERIAL-NUMBER');
  ```

- Stream creation
  ```php
  $client->device('<DEVICE-ID')->updateStream('<STREAM-NAME>');
  ```

- Posting values
  ```php
  $device = $client->device('<DEVICE-ID>');

  $device->stream('<STREAM-NAME')->updateValue($value);

  $data = array(
    array('value' => '1005', 'timestamp' => '2015-03-01T10:00:00Z'),
    array('value' => '2002', 'timestamp' => '2015-03-05T10:00:00Z')

  );
  $device->postUpdates($data);
  ```
 
- Updating the device locations
  ```php
  $device = $client->device('<DEVICE-ID>');

  $data = array(
    'name' => 'Storage Room A',
    'latitude' => '-37.9788423562422',
    'longitude', '-57.5478776916862'
  );
  $device->updateLocation($data);
  ```

Refer to the documentation on each class for further usage instructions.

## Example

In order to run this example, you will need a `Device ID` and `API Key`. If you don't have any, access your M2X account, create a new [Device](https://m2x.att.com/devices), and copy the `Device ID` and `API Key` values. The following script will send your CPU load average to three different streams named `load_1m`, `load_5m` and `load_15`. Check that there's no need to create a stream in order to write values into it.

In order to execute this script, run:

```bash
> API_KEY=<YOUR-API-KEY> DEVICE=<YOUR-DEVICE-ID> php examples/uptime.php
```

```php
use Att\M2X\MQTT\MQTTClient;
use Att\M2X\MQTT\Error\M2XException;

$apiKey = getenv("API_KEY");
$deviceId  = getenv("DEVICE");

function loadAvg() {
  $pattern = '/(\d+\.\d+),? (\d+\.\d+),? (\d+\.\d+)$/';
  preg_match($pattern, shell_exec('uptime'), $matches);
  array_shift($matches);
  return $matches;
}

$m2x = new MQTTClient($apiKey);
$m2x->connect();

# Get the device
$device = $m2x->device($deviceId);

# Create the streams if they don't exist yet
$device->updateStream('load_1m');
$device->updateStream('load_5m');
$device->updateStream('load_15m');

while (true) {
  list($load_1m, $load_5m, $load_15m) = loadAvg();
  $now = date('c');

  $values = array(
    'load_1m'  => array(array('value' => $load_1m,  'timestamp' => $now)),
    'load_5m'  => array(array('value' => $load_5m,  'timestamp' => $now)),
    'load_15m' => array(array('value' => $load_15m, 'timestamp' => $now))
  );

  try {
    $device->postUpdates($values);
  } catch (M2XException $ex) {
    echo 'Error: ' . $ex->getMessage();
    echo $ex->response->raw;
    break;
  }

  sleep(1);
}
```

## Versioning

This lib aims to adhere to [Semantic Versioning 2.0.0](http://semver.org/). As a summary, given a version number `MAJOR.MINOR.PATCH`:

1. `MAJOR` will increment when backwards-incompatible changes are introduced to the client.
2. `MINOR` will increment when backwards-compatible functionality is added.
3. `PATCH` will increment with backwards-compatible bug fixes.

Additional labels for pre-release and build metadata are available as extensions to the `MAJOR.MINOR.PATCH` format.

**Note**: the client version does not necessarily reflect the version used in the AT&T M2X API.

## License

This lib is provided under the MIT license. See [LICENSE](LICENSE) for applicable terms.
