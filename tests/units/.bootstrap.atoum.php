<?php

/*
This file will automatically be included before EACH test if -bf/--bootstrap-file argument is not used.

Use it to initialize the tested code, add autoloader, require mandatory file, or anything that needs to be done before EACH test.

More information on documentation:
[en] http://docs.atoum.org/en/chapter3.html#Bootstrap-file
[fr] http://docs.atoum.org/fr/chapter3.html#Fichier-de-bootstrap
*/

/*
AUTOLOADER

// composer
require __DIR__ . '/vendor/autoload.php';
 */
require __DIR__ . '/../../Autoload.php';

$autoload = new Siwayll\Helion\Autoload();

$autoload->packagePush('Siwayll\Helion', __DIR__ . '/../../');
spl_autoload_register([$autoload, 'search']);
