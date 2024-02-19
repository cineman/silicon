# console

The `console` module provides functions for working with the console. The console allows you 
to log messages, errors, and warnings to the console, similar to how you would in a web browser.

Every logged message will record a timestamp and will be persisted with the execution of the silicon script.

## log

```lua
log(...)
```

Logs a value to the console.

## print

```lua
print(...)
```

Logs a string plain to the console. Will not contain any type information and cannot log non string values.

## error

```lua
error(...)
```

Logs a value as an error to the console.

## warn

```lua
warn(...)
```

Logs a value as a warning to the console.

