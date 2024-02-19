# array

The `array` module provides additional utility functions for working with arrays over the table functions 
provided by the Lua standard library.

## merge

```lua
merge(array1, array2..)
```

Merges the given arrays together

Example:

```lua
local myarray1 = {"Hello", "World"}
local myarray2 = {"Goodbye", "World"}
local mymergedarray = array.merge(myarray1, myarray2)

console.log(mymergedarray) -- prints: array("Hello", "World", "Goodbye", "World")
```

## keys

```lua
keys(array)
```

Returns the keys of the given array

Example:

```lua
local myarray = {"name" = "Captain", "age" = 50}
local mykeys = array.keys(myarray)

console.log(mykeys) -- prints: array("name", "age")
```

## values

```lua
values(array)
```

Returns the values of the given array (drops the keys)

Example:

```lua
local myarray = {"name" = "Captain", "age" = 50}
local myvalues = array.values(myarray)

console.log(myvalues) -- prints: array("Captain", 50)
```

## count

```lua
count(array)
```

Returns the number of elements in the given array

Example:

```lua
local myarray = {'a', 'b', 'c'}
local mycount = array.count(myarray)

console.log(mycount) -- prints: 3
```

## column

```lua
column(array, column)
```

Returns the values of the given column in the given array

Example:

```lua
local myarray = {
    {"name": "Captain", "age": 50},
    {"name": "Doctor", "age": 100},
    {"name": "Master", "age": 20}
}
local mycolumn = array.column(myarray, "name")

console.log(mycolumn) -- prints: array("Captain", "Doctor", "Master")
```

## sum

```lua
sum(array)
```

Returns the sum of the given array values

Example:

```lua
local myarray = {1, 2, 3}
local mysum = array.sum(myarray)

console.log(mysum) -- prints: 6
```

## average

```lua
average(array)
```

Returns the average of the given array values

Example:

```lua
local myarray = {1, 2, 3}
local myaverage = array.average(myarray)

console.log(myaverage) -- prints: 2
```

## min

```lua
min(array)
```

Returns the minimum value of the given array

Example:

```lua
local myarray = {1, 2, 3}
local mymin = array.min(myarray)

console.log(mymin) -- prints: 1
```

## max

```lua
max(array)
```

Returns the maximum value of the given array

Example:

```lua
local myarray = {1, 2, 3}
local mymax = array.max(myarray)

console.log(mymax) -- prints: 3
```

## median

```lua
median(array)
```

Returns the median value of the given array

Example:

```lua
local myarray = {1, 2, 3}
local mymedian = array.median(myarray)

console.log(mymedian) -- prints: 2
```

## contains

```lua
contains(array, value)
```

Returns true if the given array contains the given value

Example:

```lua
local myarray = {1, 2, 3}

console.log(array.contains(myarray, 2)) -- prints: true
```

## has

```lua
has(array, key)
```

Returns true if the given array has the given key

Example:

```lua
local myarray = {"name" = "Captain", "age" = 50}

console.log(array.has(myarray, "name")) -- prints: true
console.log(array.has(myarray, "something")) -- prints: false
```

---

## groupBy

```lua
groupBy(array, key)
```

Groups the given array of arrays by the given key, if the key is not found in the array, it is ignored

Example:

```lua
local myarray = {
    {"name" = "S60", "manufacturer" = "Volvo"},
    {"name" = "S90", "manufacturer" = "Volvo"},
    {"name" = "Q7", "manufacturer" = "Audi"},
    {"name" = "X1", "manufacturer" = "BMW"},
    {"name" = "X3", "manufacturer" = "BMW"},
}

local mygroupedarray = array.groupBy(myarray, "manufacturer")

console.log(mygroupedarray)
-- prints: array(
--    "Volvo" = array(
--        {"name" = "S60", "manufacturer" = "Volvo"},
--        {"name" = "S90", "manufacturer" = "Volvo"}
--    ),
--    "Audi" = array(
--        {"name" = "Q7", "manufacturer" = "Audi"}
--    ),
--    "BMW" = array(
--        {"name" = "X1", "manufacturer" = "BMW"},
--        {"name" = "X3", "manufacturer" = "BMW"}
--    )
--)
```

## flatten

```lua
flatten(array, delimiter)
```

Flattens the given array

Example:

```lua
local myarray = {
    {"name" = "S60", "options" = {"color" = "red", "wheels" = "alloy"}},
    {"name" = "X3", "options" = {"color" = "silver", "wheels" = "alloy"}},
}

local myflattenedarray = array.flatten(myarray, "_")

console.log(myflattenedarray)
-- prints: array(
--    {"name" = "S60", "options_color" = "red", "options_wheels" = "alloy"},
--    {"name" = "X3", "options_color" = "silver", "options_wheels" = "alloy"}
--)
```
