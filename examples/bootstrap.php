<?php

use ClanCats\Container\Container;
use Silicon\SiliconRunner;

 if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }

/**
 *---------------------------------------------------------------
 * Autoloader / Compser
 *---------------------------------------------------------------
 *
 * We need to access our dependencies & autloader..
 */
require __DIR__ . DS . '..' . DS . 'vendor' . DS . 'autoload.php';

/**
 * Setup the contnainer with a silicon runner
 */
$container = new Container();

$container->bind('silicon', SiliconRunner::class)
    ->addDependencyArgument('container');

/**
 * Return the container to the example
 */
return $container;