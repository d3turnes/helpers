# Instalación

composer require "d3turnes/helpers"

## Uso de la Cache

```php
require "vendor/autoload.php"

use D3turnes\Helpers\Cache;

$cache = new Cache([
	'path' => 'cache',
	'prefix' => 'prefix'	// para evitar colisiones
]);

/** recupera y/o almacena una llave durante 3600 segundos */ 	
$data = $cache->remember('key', 3600, function() {
	return [
		['key' => 'value'],
		['key' => 'value']
	];
});

/** devuelve el valor de una llave o valor por defecto */
$value = $cache->get('key', 'default');

/** asocia un valor a una llave */
$cache->put('key', 'value', $seconds = null);

/** almacena una llave por tiempo indefinido */
$cache->forever('key', 'value');

/** elimina una llave */
$cache->forget('key');

/** recupera y elimina la llave */
$value = $cache->pull('key');

/** elimina todas las llaves almacenadas */
$cache->flush();
```