# Instalación

composer require "d3turnes/helpers"

## Uso de la clase Cache

```php
<?php

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

## Uso de la clase Config con notación dot.

```php
<?php

use D3turnes\Helpers\Config;

$config = new Config([
	'path' => 'config',
	'file' => 'app'		// fichero php sin (.php)
]);

-- config/app.php

<?php return [
	'name' => 'App Name',
	'phones' => [
		'home' => 'xxxx',
		'work' => 'yyyy'
	]
];

/** Obtiene el valor de la llave 'name' del fichero app.php' */
$value = $config->get('key');	// return 'App Name'

/** Obtiene el valor de la llave 'pones.home' del fichero app.php */
$value = $config->get('app.phones.home');	// return 'xxxx'

La llave 'key' puede ser simple o compuesta. Si es simple la toma del fichero por defecto app 
en caso contrario, el primer valor de la llave indica el fichero y el resto la propia llave.

/** retorna la llave dbname del array mysql del fichero database.php dentro del directorio config */
$dbname = $config->get('database.mysql.dbname');

/** asigna un valor en memoria */
$config->set('key', 'value')

/** si queremos que sea persistente, llamamos al método save */
$config->set('name', 'App Name 2');
$config->save();

/** elimina una key en memoria, si queremos que los datos persistan llamamos al metodo save */
$config->delete(['key']);

/** si queremos eliminar y guardar los cambios, usamos el método purge */
$config->purge(['key']);
```

## Uso de la clase Template

```php
<?php

use D3turnes\Helpers\Template;

/** Definimos el directorio donse se almacenarán los template, que por defecto es templates */
Template::$path = 'views'; // cambia el directorio por defecto a views en lugar de templates

return Template::render('home', $data);

*** Nota: Para incluir una template parcial llamamos al helper 'template_include' que acepta dos parámetros template y data */

###### Ejemplo. Supongamos la siguiente estructura de directorios

- templates/
- templates/partials/header.php
- templates/home.php
- index.php

/** File: templates/partials/header.php */
<h1>Header</h1>

/** File: templates/home.php */
<?php template_include('/partials/header'); ?>

<?php if (isset($alumnos)):?>
	<?php foreach ($alumnos as $alumno):?>
	<h1><?php echo $alumno['name'];?></h1>
	<?php endforeach; ?>
<?php endif;?>

/** File: index.php */
<?php

require "vendor/autoload.php";

use D3turnes\Helpers\Template;

$alumnos = [
	['name' => 'Anne', 'age' => 18],
	['name' => 'Peter', 'age' => 19],
	...
];

Template::$path = 'templates/';
return Template::render('home', $alumnos);

```