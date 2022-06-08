<?php

$container = require __DIR__ . '/bootstrap.php';

$silicon = $container->get('silicon');
$context = $silicon->boot();

$context->eval(<<<'LUA'
console.log(42) 
console.warn("noooo")
console.error({'Something', 'Went', 'Wrong'})
LUA);

foreach($context->console()->all() as $message) {
    echo $message[1] . PHP_EOL;
}