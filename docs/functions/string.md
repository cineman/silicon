# string

The `string` module provides functions for working with strings.

## explode

```lua
explode(string, delimiter)
```

Splits a string into an array of substrings.

Example:

```lua
local mystring = "Hello World"
local myarray = string.explode(mystring, " ")

console.log(myarray) -- prints: array("Hello", "World")
```

## implode

```lua
implode(array, delimiter)
```

Joins an array of strings into a single string.

Example:
    
```lua
local myarray = array("Hello", "World")
local mystring = string.implode(myarray, " ")

console.log(mystring) -- prints: string("Hello World")
```

## trim

```lua
trim(string)
```

Trims the given string, removing whitespace and line breaks from the beginning and end.

Example:

```lua
local mystring = " Hello World "
local mytrimmedstring = string.trim(mystring)

console.log(mytrimmedstring) -- prints: string("Hello World")
```

## replace

```lua
replace(string, search, replace)
```

Replaces the given search string with the given replace string in the given string.

Example:

```lua
local mystring = "Hello World"
local myreplacedstring = string.replace(mystring, "Hello", "Goodbye")

console.log(myreplacedstring) -- prints: string("Goodbye World")
```

## kfloor

```lua
kfloor(int)
```

Floors the given number to the nearest thousand.

Example:

```lua
local myint = 1234567
local mykfloorint = string.kfloor(myint)

console.log(mykfloorint) -- prints: string("1.23M")
```

## humanbytes

```lua
humanbytes(int)
```

Returns a human readable string of the given bytes.

Example:

```lua
local myint = 1234567
local myhumanbytes = string.humanbytes(myint)

console.log(myhumanbytes) -- prints: string("1.23MB")
```