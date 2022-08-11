<?php

use Silicon\Exception\SiliconRuntimeDebugBreakpointException;

$container = require __DIR__ . '/bootstrap.php';

$silicon = $container->get('silicon');
$context = $silicon->boot();

try {
    $context->eval(<<<'LUA'
    console.log(42) 
    debug("nothing after me should be printed")
    console.error({'Something', 'Went', 'Wrong'})
    LUA);
}
catch(SiliconRuntimeDebugBreakpointException $e) {
    var_dump($e->getBreakpointValues());
}
