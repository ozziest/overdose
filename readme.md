# Overdose 

Overdose is a guardian which is protects your sistem from request flood. 

## Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
    "require": {
        "ozziest/overdose": "dev-master",
    }
}
```

```bash 
$ composer install
```

## Usage

>> This library depended to `desarrolla2/cache`. You must create cache object
>> and sending to **Overdose** for usage. 

```php
// Creating new cache object
use Desarrolla2\Cache\Cache;
use Desarrolla2\Cache\Adapter\File;

$cacheDir = '/tmp';
$adapter = new File($cacheDir);
$adapter->setOption('ttl', 3600);
$cache = new Cache($adapter);

// Creating overdose
$overdose = new Ozziest\Overdose\Overdose($cache);
$overdose->secure();
```

## Configuration

You can change runtime options for security.

```php
$overdose = new Ozziest\Overdose\Overdose($cache);
$overdose->set([
                'acceptable' => 5,
                'safe'       => 10,
                'max'        => 3,
                'recreation' => 60
            ]) 
         ->secure();
```

* `acceptable`: Acceptable sec for every request interval. 
* `safe`: Safety sec. for every request interval. If request interval bigger than safe, that request is a **overdose.**
* `max`: Maximum overdose count for recreation time activation.
* `recreation`: Recreation time. (sec)