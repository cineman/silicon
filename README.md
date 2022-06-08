# Silicon 

[![Hydrogen CI](https://github.com/cineman/silicon/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/cineman/silicon/actions/workflows/ci.yml)

Wrapper around the luaSandbox extension for Hydrogen.

## Usage 

Silicon is built to be used with the ClanCats/Container which hydrogen is built around.

### Running the examples 

There is a simple bash script to run the examples through docker in case luaSandbox is not installed locally.

```
$ bash run-example.sh ./examples/01_simple_eval.php
```

### Setup

First bind the runner to the container:

```php
@silicon: Silicon\SiliconRunner(@container)
    - setPreloadCache(@silicon.cache)

@silicon.cache: Silicon\SiliconPreloadCache(:const.PATH_CACHE)
```

In most cases you want to use a preload cache, this will dump all lua code from available modules in binary form to disk. Which improves initialization time for each sandbox. This is optional so feel free to leave that out. **Note: If you are not using the hydrogen framework, you need to use an actual path instead of `:const.PATH_CACHE`**

### Running lua code 

Now you can use the silicon runner create a sandbox and run some lua code in it.

```php
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

var_dump($result); // float(103.07764064044152)
```

The function `distance` has been declared in our lua context now, so we can also directly call it:

```php
$result = $context->invokeFunction('distance', 5, 5, 10, 10);
var_dump($result); // float(7.0710678118654755)
```

### Defining custom modules

To define a custom module it needs to be bound to the container which is passed to runner or the sandbox itself, here an example how you create a email module with sendmail. (Obviously do not do this!)

```php
@my_lua_mailer: App\LuaMailer
    = silicon.module: 'email'
```

All you have to do is to implement the `SiliconModuleInterface`: 

```php
class LuaMailer implements SiliconModuleInterface
{
    public function getExposedFunctions(LuaContext $ctx) : ?array {
        return [
            'send' => [$this, 'exposeSend']
        ];
    }

    public function preloadLua() : ?string {
        return null;
    }

    public function exposeSend(string $to, string $subject, string $message) {
        mail($to, $subject, $message . '\n\nSend from Lua!');
    }
}
```

In lua you will be able to use that module as follows:

```lua
local to = 'support@cineman.ch'
local subject = 'Please dont spam us'
local message = 'Hello there'

email.send(to, subject, message)
```