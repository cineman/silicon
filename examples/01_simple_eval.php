<?php

$container = require __DIR__ . '/bootstrap.php';

$silicon = $container->get('silicon');
$context = $silicon->boot();

$result = $context->eval(<<<'LUA'
function distance(x1, y1, x2, y2)
    local dx = x1 - x2
    local dy = y1 - y2
    return math.sqrt(dx * dx + dy * dy)
end

return distance(10, 5, 50, 100)
LUA);

var_dump($result);

// call the function directly
$result = $context->invokeFunction('distance', 5, 5, 10, 10);

var_dump($result); 